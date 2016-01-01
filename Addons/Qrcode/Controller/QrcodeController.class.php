<?php

namespace Addons\Qrcode\Controller;
use Home\Controller\AddonsController;
use Addons\Qrcode\Model\WeixinAddonModel;
use Addons\Qrcode\Controller\BaseController;
use Think\Cache\Driver\Redis;

class QrcodeController extends BaseController{
	function _initialize()
	{
		parent::_initialize();
	}

	private function qr_field()
	{
		return array(
			'qrsize'	=> array(
					'title'	=> '二维码点距',
					'type'	=> 'text',
					'value'	=> '15',
					'tip'	=> '二维码点距',
				),
			// 'data'			=> array(
			// 		'title'	=> '二维码url',
			// 		'type'	=> 'text',
			// 		'value'	=> '',
			// 		'tip'	=> '注意：url的结尾要以/结束'
			// 	),
			'doorsill'	=> 	array(
					'title'	=>	'二维码生成条件',
					'type'	=>	'radio',
					'value' => 	0,
					'options'=>array(
							'0'=>'不限制',
							'1'=>'购买一单',
							'2'=>'买满指定金额'
						),
					'tip'	=>	'当选择“买满指定金额”时，下面的指定金额生效'
				),
			'money'	=>array(
					'title'	=>	'指定金额(单位：分)',
					'type'	=>	'text',
					'value' => 	0,
					'tip'	=>	'金额为0时相当于不限制生成二维码'
				),
			'backimg' 	=> array(
					'title'	=> '海报底图',
					'type'	=> 'picture',
					'value'	=> '',
					'tip'	=> '',
				),
			'hd_size'	=> array(
					'title'	=> '头像边长',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'hd_x'	=> array(
					'title'	=> '头像横坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'hd_y'	=> array(
					'title'	=> '头像纵坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'qr_x'	=> array(
					'title'	=> '二维码在底图中的横坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'qr_y'	=> array(
					'title'	=> '二维码在底图中的纵坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'ft_x' => array(
					'title'	=> '文字横坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'ft_y'	=> array(
					'title'	=> '文字纵坐标',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			'ft_size'	=> array(
					'title'	=> '文字大小',
					'type'	=> 'text',
					'value'	=> '13',
					'tip'	=> ''
				),
			// 'qr_head_size'	=> array(
			// 		'title'	=> '二维码头像大小',
			// 		'type'	=> 'text',
			// 		'value'	=> '13',
			// 		'tip'	=> ''
			// 	),
			// 'qr_head_x'	=> array(
			// 		'title'	=> '二维码头横坐标',
			// 		'type'	=> 'text',
			// 		'value'	=> '13',
			// 		'tip'	=> ''
			// 	),
			// 'qr_head_y'	=> array(
			// 		'title'	=> '二维码头像纵坐标',
			// 		'type'	=> 'text',
			// 		'value'	=> '13',
			// 		'tip'	=> ''
			// 	)
			);
	}

	public function config()
	{
		if(IS_POST)
		{
			$flag = D('Common/AddonConfig')->set('Qrcode', I('config'));
			if($flag !== false)
			{
				$this->success('保存成功', Cookie('__forward__'));
			}
			else
			{
				$this->error('保存失败');
			}
		}
		$addon['config'] = $this->qr_field();
		$db_config = D ( 'Common/AddonConfig' )->get ( 'Qrcode' );
		if($db_config)
		{
			foreach($db_config as $key => $value)
			{
				!isset($db_config[$key]) ||$addon['config'][$key]['value'] = $db_config[$key];
			}
		}
		$this->assign('data', $addon);
		$this->display();
	}

	// 获取合并后的二维码 头像+二维码+微信昵称+底片
	public function getMergedImg($openid)
	{
		$config = D ( 'Common/AddonConfig' )->get ( 'Qrcode' );
		$openid = empty($openid) ? get_openid() : get_openid($openid);
		$token = get_token();
		$site_path = SITE_PATH;
		$qrimg = $this->generateQrcode();	//	生成二维码文件 格式:.png

		$wxuser = getWeixinUserInfo($openid, $token);

		$dst_path = get_cover_url($config['backimg']);
		$pos = strpos($dst_path, '/Uploads');
		$dst_path = $site_path.substr($dst_path, $pos);

		$dst = imagecreatefromstring(file_get_contents($dst_path));
		$src = imagecreatefromstring(file_get_contents($qrimg));

		list($src_w, $src_h) = getimagesize($qrimg);
		list($dst_w, $dst_h) = getimagesize($dst_path);

		imagecopymerge($dst, $src, $config['qr_x'], $config['qr_y'], 0, 0, $src_w, $src_h, 100);

		$target = $site_path.'/Uploads/Qrcode/'.$openid.'.jpg';
		imagejpeg($dst, $target);

		$font =  '/Addons/Qrcode/View/default/Public/font/simsun.ttf';	//字体文件
		$font = $site_path.$font;
		$fontColor = imagecolorallocate($dst, 0, 0, 0);			//字体颜色

		imagefttext($dst, $config['ft_size'], 0, $config['ft_x'], $config['ft_y'], $fontColor, $font, $wxuser['nickname']);

		imagejpeg($dst, $target);

		//头像处理，头像缩放，merge
		$hdsrc = $this->getImage($wxuser['headimgurl'], $site_path.'/Uploads/Wxheader/hd_'.$openid.'.jpg');

		$arr = getimagesize($hdsrc);	//原图大小

		$img_w = $config['hd_size'];
		$img_h = $config['hd_size'];
		$imgsrc = imagecreatefromjpeg( $hdsrc);
		$image = imagecreatetruecolor($img_w, $img_h);
		imagecopyresampled($image, $imgsrc, 0, 0, 0, 0, $img_w, $img_h, $arr[0], $arr[1]);
		imagejpeg($image, $hdsrc);	//保存缩放的图片
		// 头像下载，头像缩放结束

		imagecopymerge($dst, $image, $config['hd_x'], $config['hd_y'], 0, 0, $config['hd_size'], $config['hd_size'], 100);

		imagejpeg($dst, $target);

		// // 二维码头像
		// $qrhd = $this->getImage($wxuser['headimgurl'], $site_path.'/Uploads/Wxheader/qr_hd_'.$openid.'.jpg');

		// $arr = getimagesize($qrhd);	//原始头像大小

		// $img_w = $config['qr_head_size'];
		// $img_h = $config['qr_head_size'];

		// $imgsrc = imagecreatefromjpeg($qrhd);
		// $image = imagecreatetruecolor($img_w, $img_h);
		// imagecopyresampled($image, $imgsrc, 0, 0, 0, 0, $img_w, $img_h, $arr[0], $arr[1]);
		// imagejpeg($image, $qrhd);

		// imagecopymerge($dst, $image, $config['qr_head_x'], $config['qr_head_y'], 0, 0, $config['qr_head_size'], $config['qr_head_size'], 80);

		// imagejpeg($dst, $target);

		imagedestroy($dst); //释放
		imagedestroy($src);
		imagedestroy($imgsrc);
		imagedestroy($image);
		return $target;
	}

	//  $url:图片的地址 ,$name:图片另存的名字， $length:边长
	private function getImage($url, $name)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
 		$img = curl_exec ($ch);
 		$fp = fopen($name, 'w');
 		fwrite($fp, $img);
 		fclose($fp);
 		return $name;
	}

	private function resize($src, $img_w, $img_h)
	{
		$arr = getimagesize($src);
		$imgsrc = imagecreatefromjpeg($src);
		$image = imagecreatetruecolor($img_w, $img_h);
		imagecopyresampled($image, $src, 0, 0, 0, 0, $img_w, $img_h, $arr[0], $arr[1]);
		imagejpeg($image, $src);
		imagedestroy($image);
	}

	// php 生成二维码
	private function generateQrcode()
	{
		$db_config = D ( 'Common/AddonConfig' )->get ( 'Qrcode' );
		$qrsize = $db_config['qrsize'];

		$openid = get_openid();
		$site_path = SITE_PATH;
		$site_path = $site_path.'/';
		$qr_path = $site_path.'Uploads/Qrcode/';
		include('phpqrcode.php');
		$user = M('shop_user')->where(array('openid'=>$openid))->order('id desc')->find();
		$data = $this->wxQrCode($user['id'], 'QR_LIMIT_STR_SCENE' );
		$filename = 'qr-'.$openid.'.png';
		$filename = $qr_path.$filename;
	   // 纠错级别：L、M、Q、H
	   $errorCorrectionLevel = 'L';
	   // 点的大小：1到10
	   $matrixPointSize = $qrsize;
	   \QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);	//生成二维码文件到本地
	   return $filename;
	}


	public function add_material($file_info, $access_token='')
	{
		  $url="https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type=image";
		  $ch1 = curl_init ();
		  $timeout = 5;
		  $site_path = SITE_PATH;
		  $real_path = $file_info['filename'];

		  $data= array("media"=>"@{$real_path}",'form-data'=>$file_info);
		  curl_setopt ( $ch1, CURLOPT_URL, $url );
		  curl_setopt ( $ch1, CURLOPT_POST, 1 );
		  curl_setopt ( $ch1, CURLOPT_RETURNTRANSFER, 1 );
		  curl_setopt ( $ch1, CURLOPT_CONNECTTIMEOUT, $timeout );
		  curl_setopt ( $ch1, CURLOPT_SSL_VERIFYPEER, FALSE );
		  curl_setopt ( $ch1, CURLOPT_SSL_VERIFYHOST, false );
		  curl_setopt ( $ch1, CURLOPT_POSTFIELDS, $data );
		  $result = curl_exec ( $ch1 );
		  addWeiXinLog($result,'__add_material');
		  curl_close ( $ch1 );

		  if(curl_errno()==0){
		    $result=json_decode($result,true);
		    return $result['media_id'];
		  }else {
		    return false;
		  }
	}

	public function wxQrCode($qrcodeID, $qrcodeType = 'QR_SCENE')
	{
		$tempJson = '{"expire_seconds": 1800, "action_name": "' . $qrcodeType . '", "action_info": {"scene": {"scene_str": ' . $qrcodeID . '}}}';
		$access_token = get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
		$tempArr = json_decode ( $this->JsonPost ( $url, $tempJson ), true );
		if (@array_key_exists ( 'ticket', $tempArr )) {
			return $tempArr['url'];
		} else {
			return false;
		}
	}

	private function JsonPost($url, $jsonData)
	{
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $jsonData );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $curl );
		if (curl_errno ( $curl )) {
		}
		curl_close ( $curl );
		return $result;
	}

	private function resizeImg($imgsrc, $imgdst, $img_w, $img_h)
	{
		$arr = getimagesize($imgsrc);
		header('Content-type: image/jpg');

		$imgsrc = imagecreatefromstring($imgsrc);
		$image = imagecreatetruecolor($img_w, $img_h);
		imagecopyresampled($image, $imgsrc, 0, 0, 0, 0, $img_w, $img_h, $arr[0], $arr[1]);
	}

	public function doorsill(){
		$config = getAddonConfig ( 'Qrcode' ); // 获取后台插件的配置参数

		if($config['doorsill'] == 0){
			return true;
		}elseif($config['doorsill'] == 1){
			$map['by_from_openid'] = $openid = get_openid();
			$map['order_status'] = array('in','2,3,7,10');
			$order = M('shop_order')->where($map)->find();
			if(empty($order)){
				return "您还没有购买我们的商品，不能生成二维码";
			}else{
				return true;
			}
		}elseif($config['doorsill'] == 2){
			$map['by_from_openid'] = $openid = get_openid();
			$map['order_status'] = array('in','2,3,7,10');
			$sum_total_fee = M('shop_order')->where($map)->sum('total_fee');
			if($sum_total_fee >= (int)$config['money']){
				return true;
			}else{
				return "您还没有在商城买满".number_format($config['money']/100,2)."元，不能生成二维码";
			}
		}
		return "由于神秘原因，您还不能生成二维码";
	}
}

?>