<?php
namespace Addons\Shop\Model;
use Think\Model;
class DistributeModel extends Model{
	public function __construct(){
		parent::__construct('shop_distribute');
	}
	public function getByOpenid($openid){
		$map['openid'] = $openid;
        $map['token'] = get_token();
        return $this->where($map)->find();
	}
	// 清空上线
	public function clearLeader($openid){
		$d = $this->getByOpenid($openid);
		$data['f_openid'] = '';
		$this->where($d)->setField($data);
	}
	// 寻找线上的第一个合伙人
	public function findPartner($openid){
		$dis = $this->getByOpenid($openid);
		$map['openid'] = $openid;
		$user = M('shop_user')->where($map)->find();
		// 本人为合伙人
		if($user['user_cat']  == "0"){
			return $openid;
		}else{
			// 父节点非空
			if($dis['f_openid']){
				return $this->findPartner($dis['f_openid']);
			}else{
				return "";
			}
		}
	}
	

}