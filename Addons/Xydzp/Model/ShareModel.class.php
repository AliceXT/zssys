<?php
namespace Addons\Xydzp\Model;
use Think\Model;
class ShareModel extends Model
{
	var $max = 2;
	var $section = 86400;
	// 可以重来一次
	public function canAgain(){
		$openid = get_openid();
		if(empty($openid) || $openid == '-1')
			return json_encode(array('errmsg'=>'openid错误','errcode'=>'1'));
		$Model = M('xydzp_userlog');
		$now = time();
		$map['uid'] = $openid;
		$userlog = $Model->where($map)->find();

		// 原来的机会还没有使用
		if(date('Ymj',$userlog['cjdate']) != date('Ymj',$now)){
			return json_encode(array('errmsg'=>'请先使用完抽奖次数再进行分享','errcode'=>'6'));
		}
		
		// 同一天
		if(date('Ymj',$userlog['share_time']) == date('Ymj',$now)){
			if($userlog['share_count'] >= $this->max){
				return json_encode(array('errmsg'=>'检查已达分享上限','errcode'=>'2'));
			}else{
				return json_encode(array('errcode'=>'0'));
			}
		}else{
			return json_encode(array('errcode'=>'0'));
		}
	}

	// 抽奖次数减一,以增加一次抽奖次数,返回bool，表示能否再一次
	public function onceAgain(){
		$openid = get_openid();
		if(empty($openid) || $openid == '-1')
			return json_encode(array('errmsg'=>'openid错误','errcode'=>'1'));
		$Model = M('xydzp_userlog');
		$now = time();
		$map['uid'] = $openid;
		$userlog = $Model->where($map)->find();
		
		// 同一天
		if(date('Ymj',$userlog['share_time']) == date('Ymj',$now)){
			if($userlog['share_count'] >= $this->max){
				return json_encode(array('errmsg'=>'已达分享上限','errcode'=>'2'));
			}else{
				if($Model->where($map)->setInc('share_count')){
					$result = $Model->where($map)->setDec('num');
					if($result){
						return json_encode(array('errcode'=>'0'));
					}else{
						$Model->where($map)->setDec('share_count');
						return json_encode(array('errmsg'=>'修改抽奖次数错误','errcode'=>'4'));
					}
				}else{
					return json_encode(array('errmsg'=>'修改分享次数错误','errcode'=>'3'));
				}
			}
		}else{
			$data['share_count'] = 1;
			$data['share_time'] = time();
			$result = $Model->where($map)->setField($data);
			if($result){
				$result = $Model->where($map)->setDec('num');
				if($result){
						return json_encode(array('errcode'=>'0'));
					}else{
						return json_encode(array('errmsg'=>'修改抽奖次数错误','errcode'=>'4'));
					}
			}else{
				return json_encode(array('errmsg'=>'重设用户分享次数时出错','errcode'=>'5'));
			}
		}
	}


	// 是否在期限内
	public function inTime($id = ''){
		empty($id) && $id = I('id');
		$map['id'] = $id;
		$map['openid'] = get_openid();
		$log = M('xydzp_log')->where($map)->find();
		if($log['zjdate'] + $this->section > time()){
			return true;
		}else{
			return false;
		}
	}

	// 领奖
	public function getAwards($id = ''){
		empty($id) && $id = I('id');
		$map['id'] = $id;
		$map['openid'] = get_openid();
		$data['state'] = 1;
		$data['address'] = I('address');
		$data['iphone'] = I('iphone');
		$data['receiver'] = I('receiver');
		if(M('xydzp_log')->where($map)->setField($data)){
			return true;
		}else{
			return false;
		}

	}

	// 保存收货地址
	public function setAddress($id = ''){
		empty($id) && $id = I('id');
		$map['id'] = $id;
		$map['openid'] = get_openid();
		$data['address'] = I('address');
		$data['iphone'] = I('iphone');
		$data['receiver'] = I('receiver');
		if(M('xydzp_log')->where($map)->setField($data)){
			return true;
		}else{
			return false;
		}
	}

	// 保存收货地址
	public function getLog($id = ''){
		empty($id) && $id = I('id');
		$map['id'] = $id;
		$map['openid'] = get_openid();
		return M('xydzp_log')->field('')->where($map)->find();
	}

}