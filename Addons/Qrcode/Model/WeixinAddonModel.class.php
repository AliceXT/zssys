<?php

namespace Addons\Qrcode\Model;
use Home\Model\WeixinModel;
use Addons\Qrcode\Controller\QrcodeController;
use Addons\Qrcode\Model\QrcodeModel;

/**
 * Qrcode的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		// json_encode($dataArr) == {"ToUserName":"gh_acf8ed38291a","FromUserName":"oeD7Qt17qewuJXCyNen1O8LCyGTw","CreateTime":"1450081779","MsgType":"event","Event":"CLICK","EventKey":"\u6211\u7684\u4e8c\u7ef4\u7801","Content":"\u6211\u7684\u4e8c\u7ef4\u7801"}
		$config = getAddonConfig ( 'Qrcode' ); // 获取后台插件的配置参数
		$qr = new QrcodeController;

		// 校正数据
		get_openid($dataArr['FromUserName']);
		$openid = $map['openid'] = $dataArr['FromUserName'];
		$token = $map['token'] = $dataArr['ToUserName'];
		$result = M('shop_user')->where($map)->find();
		if(empty($result)){
			M('shop_user')->add($map);
		}
		get_token($dataArr['ToUserName']);

		/**
		*	@author AliceXT
		*	2015-12-14 for 二维码生成门槛
		**/
		// 门槛判断
		$msg = $qr->doorsill();
		if($msg === true){
			
			$qrfile = $qr->getMergedImg();	//
			$token = get_access_token();
	
			$file_info=array(
			    'filename'=> $qrfile,  //片相对于网站根目录的路径
			    'content-type'=>'image/jpeg',  //文件类型
			    'filelength'=>'11011'         //图文大小
			);
			$media_id = $qr->add_material($file_info, $token);
	
			$this->replyImage($media_id);
		}else{
			$this->replyText($msg);
		}
	}

	// 关注公众号事件
	public function subscribe() {
		$weixin = D('Weixin');
		$data = $weixin->getData();
		$eventKey = $data['EventKey'];
		$uid = substr($eventKey, 8);
		$user = M('shop_user')->where(array('id'=>$uid))->find();
		$qr = new QrcodeModel();


		if($user)
		{
			$qr->tobeaDis($user['openid']);	//上线的openid
		}
		$this->sentQrNews();

	}

	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}

	// 扫描带参数二维码事件
	public function scan() {
		$weixin = D('Weixin');
		$data = $weixin->getData();
		$qr = new QrcodeModel();	//

		$conf = D('Common/AddonConfig')->get('QrcodeInfo');
		$usreId = $data['EventKey'];
		$user = M('shop_user')->where(array('id'=>$usreId))->order('id desc')->find();

		if($user)
		{
			$qr->tobeaDis($user['openid']);
		}
		$this->sentQrNews();
	}

	public function sentQrNews()
	{
		$conf = D('Common/AddonConfig')->get('QrcodeInfo');
		if(!empty($conf))
		{
			$conf['image'] = get_cover_url($conf['image']);
			$this->replyNews(array(
				array('Title'=>$conf['title'],'Description'=>$conf['content'],'PicUrl'=>$conf['image'],'Url'=>$conf['link'])
				));
		}
	}

	// 上报地理位置事件
	public function location() {
		return true;
	}

	// 自定义菜单事件
	public function click() {
		return true;
	}
}
