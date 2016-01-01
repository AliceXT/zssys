<?php
namespace Addons\Shop\Model;
use Think\Model;
// 支付
class PayModel extends Model{
	// 微信同意下单
	public function wxUnityOrder($body, $notify_url, $out_trade_no, $total_fee)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";	//微信支付接口地址
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
		$key = $pay_conf['api_key'];	//微信支付的api秘钥
		$nonce_str = $this->getNonceStr(); //随机字符串，随机的... <=32位
		$openid = get_openid();
		$ip = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] :'1.1.1.1';
		$arr = array(
			'appid'=> $pay_conf['appid'],				//
			'body' =>$body,							//商品详情
			'mch_id' => $pay_conf['mch_id'],						//微信支付的mch_id
			'nonce_str' => $nonce_str,								//随机字符串
			'notify_url' => $notify_url,				//回调地址
			'openid' => $openid,									//用户的openid
			'out_trade_no' => $out_trade_no,			//商户内部订单号
			'spbill_create_ip' => $ip,	//下单地址
			'total_fee' => $total_fee,				//总金额，转换后单位：分
			'trade_type' => 'JSAPI',								//JSAPI
			);
		addWeiXinLog($arr, '__unityarr');
		$sign = $this->getSign($arr, $key);	//获取签名
		$arr['sign'] = $sign;
		$para = $this->generateXml($arr);		//xml格式
		$result = $this->sentPost($url, $para);
		$msg = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);	//处理返回的xml,转为数组
		addWeixinLog($msg, '__unitypay');
		return $msg;
	}
	// 查看订单状态，返回订单状态值，商户的订单的id
	public function payingStatus($out_trade_no)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		$nonce_str = $this->getNonceStr();	//随机字符串
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
		$order = array(
			'appid'	=> $pay_conf['appid'],
			'mch_id'		=> $pay_conf['mch_id'],
			'nonce_str'		=> $nonce_str,
			'out_trade_no'	=> $out_trade_no,
			);
		$sign = $this->getSign($order, $pay_conf['api_key']);
		$order['sign'] = $sign;
		$xml = $this->generateXml($order);
		$result = $this->sentPost($url, $xml);
		$msg = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $msg;	//返回订单的状态和参数
	}
	// 微信退单
	public function wxRefund($transaction_id, $out_trade_no, $out_refund_no, $total_fee, $refund_fee)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
		$sitePath = SITE_PATH;
		$certPath = $sitePath."/Addons/Shop/View/default/Public/cert/";	//证书的存储位置
		$certArr = array(
			'cert'	=> $certPath.'apiclient_cert.pem',
			'key'	=> $certPath.'apiclient_key.pem',
			'rootca'	=> $certPath.'rootca.pem'
			);
		$refundArr = array(
			'appid'	=> $pay_conf['appid'],
			'mch_id'	=> $pay_conf['mch_id'],
			'nonce_str'	=> $this->getNonceStr(),
			'transaction_id'	=> $transaction_id,	//微信订单号
			'out_trade_no'		=> $out_trade_no,
			'out_refund_no'		=> $out_refund_no, // 退款单号
			'total_fee'			=> $total_fee, //总费用
			'refund_fee'		=> $refund_fee, //退款总金额
			'op_user_id'		=> $pay_conf['mch_id']
			);
		$sign = $this->getSign($refundArr, $pay_conf['api_key']);
		$refundArr['sign'] = $sign;
		$para = $this->generateXml($refundArr);
		$result = $this->sentPost($url, $para, $certArr);
		$msg = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);	//处理返回的xml,转为数组
		return $msg;
	}
	/**
	*	@author AliceXT
	*	2015-12-19 for 查询退款
	**/
	public function wxRefundQuery($out_refund_no){
		$url = "https://api.mch.weixin.qq.com/pay/refundquery";
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
		$refundArr = array(
			'appid'	=> $pay_conf['appid'],
			'mch_id'	=> $pay_conf['mch_id'],
			'nonce_str'	=> $this->getNonceStr(),
			'out_refund_no'		=> $out_refund_no,//退单ID
			);
		$sign = $this->getSign($refundArr, $pay_conf['api_key']);
		$refundArr['sign'] = $sign;
		$para = $this->generateXml($refundArr);
		// $result = $this->sentPost($url, $para, $certArr);
		$result = $this->sentPost($url, $para);
		$msg = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);	//处理返回的xml,转为数组
		return $msg;
	}

	/*change end */
	// 生成随机字符串，长度32位
	private function getNonceStr()
	{
		$rand = rand(1,1000);
		$nonce_str = md5($rand);
		return $nonce_str;
	}
	// 生成xml
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
	// 获取签名
	public function getSign($arr, $key)
	{
		ksort($arr); 	// 讲数组的键由高到低进行排序
		$stringA = $this->kvglue($arr);
		$stringSingTemp = $stringA."&key=".$key;
		$sign = md5($stringSingTemp);
		$sign = strtoupper($sign);
		return $sign;
	}
	// 将键和值& 连起来
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
	// xml POST
		// xml POST请求
	private function sentPost($url, $para, $certArr=array())
	{
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		if(!empty($certArr))
		{
			curl_setopt($curl, CURLOPT_SSLCERT, $certArr['cert']);
			curl_setopt($curl, CURLOPT_SSLKEY, $certArr['key']);
			curl_setopt($curl, CURLOPT_CAINFO, $certArr['rootca']);
		}
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
	}
}
