<?php

namespace Addons\Qrcode\Model;
use Think\Model;

/**
 * Qrcode模型
 */
class QrcodeModel extends Model{

	// 成为分销
	public function tobeaDis($fopenid)
	{
		$openid = get_openid();	//当前用户
		$token = get_token();

		$fuser = M('shop_user')->where(array('openid'=>$fopenid))->order('id desc')->find();	//查看上线
		$user = M('shop_user')->where(array('openid'=> $openid))->order('id desc')->find(); //当前用户
		$dis = M('shop_distribute')->where(array('openid'=>$openid))->order('id desc')->find();

//		if($fuser['user_cat'] == 1) // 总代理
//		{
			if(!$dis)	//如果此前不是分销
			{
				$fdis = M('shop_distribute')->where(array('openid'=>$fopenid))->order('id desc')->find();
				M('shop_distribute')->add(array(
						'manager_openid' => $fdis['f_openid'],			//一级分销
						'seller_openid'	=> $fopenid, //二级分销
						'openid'		=> $openid,
						'f_openid'		=> $fopenid,
						'token'			=> $token,
					));
			}
			if(!$user) //之前不是商城用户
			{
				M('shop_user')->add(array(
						'ctime'=>time(),
						'openid'=>$openid,
						'token'	=> $token,
						'user_cat'	=> 2
					));
			}
			$fwxuser = getWeixinUserInfo($fopenid);
			$wxuser = getWeixinUserInfo($openid);
			$this->msgTo($fopenid,'您的朋友'.$wxuser['nickname'].'，成了您的支持者！');
			$this->msgTo($openid, "您的朋友".$fwxuser['nickname'].'，又获得了一个新的支持者！');
/*
		}
		else
		{
			$fdis = M('shop_distribute')->where(array('openid'=>$fopenid))->order('id desc')->find();
			if(!$dis)
			{
				M('shop_distribute')->add(array(
						'manager_openid'	=> $fdis['manager_openid'],
						'seller_openid'		=> $fdis['seller_openid'],
						'openid'			=> $openid,
						'f_openid'			=> $fopenid,
						'token'				=> $token
					));
			}
			if(!$user)
			{
				M('shop_user')->add(array(
						'ctime'	=> time(),
						'openid'=> $openid,
						'token'	=> $token,
						'user_cat'	=> 2
					));
			}
			$manager_openid = $fdis['manager_openid'];
			$seller_openid	= $fdis['seller_openid'];
			$manager_wxinfo = getWeixinUserInfo( $manager_openid);
			$seller_wxinfo = getWeixinUserInfo( $seller_openid);
			$userinfo = getWeixinUserInfo( $openid);
			$fuserinfo = getWeixinUserInfo($fopenid);
			$this->msgTo($manager_openid,  '经过'.$fuserinfo['nickname'].'的努力，'.$userinfo['nickname'].'，成了您的下线！');
			$this->msgTo($seller_openid, '经过'.$fuserinfo['nickname'].'的努力，'.$userinfo['nickname'].'，成了您的下线！');

			$this->msgTo($fopenid, '您的朋友'.$userinfo['nickname'].'，成了您的支持者！');
			$this->msgTo($openid, "您的朋友".$fuserinfo['nickname'].'，又获得了一个新的支持者！');
		}
		*/
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

	// 给openid发送给消息，如果在48小时内没有过互动，会发送失败，接口为客服接口
	public function msgTo($openid, $msg)
	{
		 $access_token = get_access_token();
		 $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
		 $jsonArr = '{
					    "touser":"'.$openid.'",
					    "msgtype":"text",
					    "text":
					    {
					         "content":"'.$msg.'"
					    }
					}';
		 $this->JsonPost($url, $jsonArr);
	}


}
