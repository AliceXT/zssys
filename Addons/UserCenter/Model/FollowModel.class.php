<?php

namespace Addons\UserCenter\Model;
use Think\Model;

/**
 * UserCenter模型
 */
class FollowModel extends Model{
	public function get_user_info($openid){
		$access_token = get_access_token();

		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
		$res = $this->http_request($url);
		return json_decode($res,true);
	}

	protected function http_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	public function copyByOpenid($openid){
		$map['openid'] = $openid;
		$map['token'] = get_token();
		$info = $this->where($map)->find();

		$user = M('shop_user')->where($map)->find();

		if($user){
			// 存在这个账号在shop_user表中
			!empty($user['nickname']) || $user['nickname'] = $info['nickname'];
			!empty($user['phone']) || $user['phone'] = $info['mobile'];
			!empty($user['address']) || $user['address'] = $info['province'].$info['city'];
			if(M('shop_user')->create($user)){
				M('shop_user')->where($map)->setField($user);
				return true;
			}else{
				return false;
			}
		}else{
			// 本来不存在在shop_user表
			$user['ctime'] = empty($info['subscribe_time']) ? time() : $info['subscribe_time'];
			$user['nickname'] = empty($info['nickname']) ? '': $info['nickname'];
			$user['phone'] = empty($info['moblie']) ? '' : $info['moblie'];
			$user['openid'] = empty($info['openid']) ? $openid : $info['openid'];
			$user['token'] = empty($info['token']) ? get_token() : $info['token'];
			$user['address'] = empty($info['province']) && empty($info['city']) ? '' : $info['province'].$info['city'];
			$user['user_cat'] = 999;
			if(M('shop_user')->create($user)){
				M('shop_user')->add();
				return true;
			}else{
				return false;
			}
		}

	}
}