<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Controller\BaseController;
class PayController extends BaseController {
	public function _initialize()
	{
		parent::_initialize();
		$action = strtolower(_ACTION);
		$res['title'] = '微支付设置';
		$res['url'] = addons_url('Shop://pay/config');
		$res['class'] = $action == 'config' ? 'cur' : '';
		$nav[] = $res;
		$res['title'] = '页面配置';
		$res['url'] = addons_url('Shop://template/lists');
		$res['class'] = $action == 'lists' ? 'cur' : '';
		$nav[] = $res;
		$this->assign('sub_nav', $nav);
	}
	/**
	*	@author AliceXT 2015-11-16 for 支付密钥自动生成
	**/
	public function config() {
		header("Content-type: text/html; charset=utf-8");
		if (IS_POST) {
			$flag = D ( 'Common/AddonConfig' )->set ( 'ShopPay', I ( 'config' ) );
			if ($flag !== false) {
				$this->success ( '保存成功', Cookie ( '__forward__' ) );
			} else {
				$this->error ( '保存失败' );
			}
		}
		$addon ['config'] = $this->pay_fields ();
		$db_config = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );
		$this->assign('config',$db_config);
		// 使用提示
		$normal_tips = '所有项目必填，不可为空，否则会支付失败';
		$this->assign ( 'normal_tips', $normal_tips );
		$this->display (T('Addons://Shop@Pay/config'));
	}
	// ajax请求返回32位随机数
	public function randKey(){
		exit(json_encode(array('state'=>1,'randKey'=>get_rand_char(32))));
	}
	/*change end*/
	function pay_fields() {
		return array (
				'appid' => array (
						'title' => 'APPID:',
						'type' => 'text',
						'value' => '',
						'tip' => '公众账号的id--wx*****'
				),
				'mch_id' => array (
						'title' => 'MCH_ID:',
						'type' => 'text',
						'value' => '',
						'tip' => '微信支付分配的商户号'
				),
				'signtype' => array (
						'title' => 'SIGNTYPE:',
						'type' => 'text',
						'value' => 'MD5',
						'tip' => 'method'
				),
				'api_key' => array (
						'title' => 'APIKEY:',
						'type' => 'text',
						'value' => '',
						'tip' => '微信支付的API秘钥'
				),
		);
	}
	public function dis_conf()
	{
		if (IS_POST) {
			$flag = D ( 'Common/AddonConfig' )->set ( 'ShopDis', I ( 'config' ) );
			if ($flag !== false) {
				$this->success ( '保存成功', Cookie ( '__forward__' ) );
			} else {
				$this->error ( '保存失败' );
			}
		}
		$addon ['config'] = $this->pay_fields ();
		$db_config = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );
		if ($db_config) {
			foreach ( $addon ['config'] as $key => $value ) {
				! isset ( $db_config [$key] ) || $addon ['config'] [$key] ['value'] = $db_config [$key];
			}
		}
		$this->assign ( 'data', $addon );
		// 使用提示
		$normal_tips = '分销设置';
		$this->assign ( 'normal_tips', $normal_tips );
		$this->display ('config');
	}
	// 批量提现
	public function getCasher()
	{
		$ids = trim($_REQUEST['ids']);
		$map['id'] = array('in', $ids);
		$cashOrder = M('shop_get_cash')->where($map)->select();
		foreach($cashOrder as $x)
		{
			$r = $this->mchPay($x['openid'], $x['fee'],$x['id']);	//提现
			if($r['return_code'] == 'SUCCESS')
			{
				$User = M('shop_user');
				$this->turnGetCashStatus($x['id']);
			}
			elseif($r['return_code'] == 'FAIL')
			{
				if($r['err_code'] == 'NOTENOUGH')
				{
					exit(json_encode(array('code'=>1, 'info'=>'余额不足，批量提现中断！')));
				}
				elseif($r['err_code'] == 'AMOUNT_LIMIT')
				{
					exit(json_encode(array('code'=>1, 'info'=>'超过最多转账次数,批量提现中断！')));
				}
				else
				{
					exit(json_encode(array('code'=>1, 'info'=>$r['return_code'])));
				}
			}
		}
		exit(json_encode(array('code'=>1, 'info'=>'允许提现！')));
	}
	//	商户支付
	private function mchPay($openid, $amount, $trad_no)
	{
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
		$sitePath = SITE_PATH;
		$certPath = $sitePath."/Addons/Shop/View/default/Public/cert/";
		$certArr = array(
			'cert'	=> $certPath.'apiclient_cert.pem',
			'key'	=> $certPath.'apiclient_key.pem',
			'rootca'	=> $certPath.'rootca.pem'
			);
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );
		// $openid = 'osALzjvsweDff9UGcO13UWf2uYbw';
		$ip = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '1.1.1.1';
		$postArr = array(
			'amount'	=> $amount, //支付费用
			'check_name'=> 'NO_CHECK', //是否对实名认证的用户支付
			'desc'		=> '佣金',
			'mch_appid'	=> $pay_conf['appid'],
			'mchid'		=> $pay_conf['mch_id'],
			'nonce_str'	=> '123kl2jk3o12j3o12j3oi13',
			'openid'	=> $openid,
			'partner_trade_no'	=> $trad_no,
			'spbill_create_ip'	=> $ip
			);
		$key = $pay_conf['api_key'];
		$sign = $this->getSign($postArr, $key);
		$postArr['sign'] = $sign;
		$xml = $this->generateXml($postArr);
		$res = $this->sentPost($url, $xml, $certArr);
		$msg = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $msg;
	}
		//获取签名
	private function getSign($arr, $key='')
	{
		if($key == '')
		{
			$key = "c23b76fe5a1c7befb230debe7cdcdc83";
		}
		$stringA = $this->kvglue($arr);
		$stringSignTemp = $stringA."&key=$key";
		$sign = md5($stringSignTemp);
		$sign = strtoupper($sign);		//签名
		return $sign;
	}
	// xml POST请求
	private function sentPost($url, $para, $certArr=array())
	{
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);//严格认证
		curl_setopt($curl, CURLOPT_VERBOSE, 1); //debug模式
		if(!empty($certArr))
		{
			curl_setopt($curl, CURLOPT_SSLCERT, $certArr['cert']);
			curl_setopt($curl, CURLOPT_SSLKEY, $certArr['key']);
			curl_setopt($curl, CURLOPT_CAINFO, $certArr['rootca']);
			//curl_setopt($curl, CURLOPT_SSLKEYPASSWD, 'c23b76fe5a1c7befb230debe7cdcdc83');
		}
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
	}
	private function kvglue($array)
	{
		$str = '';
		foreach ($array as $key => $value)
		{
			if($str !== '')
			{
				$str .= '&'.$key.'='.$value;
			}
			else
			{
				$str = $key.'='.$value;
			}
		}
		return $str;
	}
	//生成xml
	private function generateXml($arr)
	{
		$xml = '<xml>';
		foreach($arr as $k => $x)
		{
			$xml .= "<$k>".$x."</$k>";
		}
		$xml .= '</xml>';
		return $xml;
	}
	// 取现状态改为曲线成功
	public function turnGetCashStatus($id, $status= 1)
	{
		$C = M('shop_get_cash');
		$C->status = $status;
		$C->where('id='.$id)->save();
	}
}
