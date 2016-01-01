<?php
namespace Addons\Coupon\Model;
use Think\Model;
/**
 * Coupon模型
 */
class CouponModel extends Model{
	protected $name = 'coupon';

	public function getCoupon($id){
		$id || $id = I('id');
		$map['id'] = $id;
		// $field = "id,keyword,title,intro,cTime,token,start_timme,use_tips,end_tips,num,max_num,follower_condition,credit_dug,addon_condition,collect_count,view_count,end_img,full,discount,sn_date,same";
		// return $this->field($field)->where($map)->find();
		return $this->where($map)->find();

	}

	public function getKey($id = ''){
		empty($id) && $id = I('id');
		$map['id'] = $id;
		$coupon = $this->where($map)->find();
		if(empty($coupon)){
			return '';
		}else{
			return $coupon['key'];
		}
	}

	public function onlock($str = '',$key = ''){
		empty($key) && $key = $this->getKey();
		if(empty($key) || empty($str)){
			return '';
		}
		$cipher = MCRYPT_DES; //密码类型
		$modes = MCRYPT_MODE_ECB; //密码模式
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher,$modes),MCRYPT_RAND);//初始化向量
		$str_encrypt = mcrypt_encrypt($cipher,$key,$str,$modes,$iv);
		$ciphertext_base64 = base64_encode($str_encrypt);
		return $ciphertext_base64;
	}
	
	public function unlock($str = '',$key = ''){
		empty($key) && $key = $this->getKey();
		if(empty($key) || empty($str)){
			return '';
		}
		$cipher = MCRYPT_DES; //密码类型
		$modes = MCRYPT_MODE_ECB; //密码模式
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher,$modes),MCRYPT_RAND);//初始化向量
		$ciphertext_dec = base64_decode($str);
		$str_decrypt = mcrypt_decrypt($cipher,$key,$ciphertext_dec,$modes,$iv); //解密函数
		return $str_decrypt;
	}

}

