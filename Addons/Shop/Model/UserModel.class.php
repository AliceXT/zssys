<?php

namespace Addons\Shop\Model;

use Think\Model;

class UserModel extends Model{

	private $disList = array();
	protected $tableName = 'shop_user';// AliceXT for action_log

	// 给用户增加余额
	function incrUserProfile($openid, $fee)
	{	
		$User = M('shop_user');
		$map['openid'] = $openid;
		$User->where($map)->setInc('remain_money', $fee);
	}

	// 给用户减余额
	function decUserProfile($openid, $fee)
	{
		$User = M('shop_user');
		$map['openid'] = $openid;
		$User->where($map)->setDec('remain_money', $fee);
	}

	function incrUserCredit($openid, $total)
	{
		$User = M('shop_user');
		$map['openid'] = $openid;
		$User->where($map)->setInc('credit', $total);
	}

	function decUserCredit($openid, $total)
	{
		$User = M('shop_user');
		$map['openid'] = $openid;
		$User->where($map)->setDec('credit', $total);
	}

	public function countDis()
	{
		$openid = get_openid();
		$dis = M('shop_distribute')->where(array('token'=>get_token()))->order('id asc')->select();
		$this->init_user_tree($dis, $openid);
		$order_sum = 0;
		foreach($this->disList as $v)
		{
			$order_sum = $order_sum + $this->countOrder($v['openid']);
		}
		return array(
			'user_count'	=> count($this->disList),
			'order_count'	=> $order_sum
			);
	}

	// 直接下线
	public function zjUserList($openid)
	{
		$list = M('shop_distribute')->where(array('f_openid' => $openid))->order('id asc')->select();
		return $list;
	}

	// 间接下线
	public function jjUserList($openid)
	{
		$token = get_token();
		$list = M('shop_distribute')->where(array('token'=>$token))->order('id asc')->select();
		$zjlist = M('shop_distribute')->where(array('f_openid' => $openid))->order('id asc')->select();
		$this->init_user_tree($list, $openid);
		$disList = $this->disList;
		$jjfx = array();
		foreach($disList as $k => $x)
		{
			if(!$this->is_in_list($zjlist, $x['openid']))
			{
				$jjfx[] = $x;
			}
		}
		return $jjfx;
	}

	private function is_in_list($list, $openid)
	{
		foreach($list as $v)
		{
			if($v['openid'] == $openid)
			{
				return true;
			}
		}

		return false;
	}

	// 生成树
	private function init_user_tree($list, $openid)
	{
		$child = $this->find_child($list, $openid);
		if(empty( $child))
		{
			return NULL;
		}
		foreach($child as $k=>$v)
		{
			$res = $this->init_user_tree($list, $v['openid']);
		}
		return $child;
	}

	// 找到直接下线
	private function find_child($list, $openid)
	{
		$child = array();

		foreach($list as $x)
		{
			if($x['f_openid'] == $openid)
			{
				$child[] = $x;
				$this->disList[] = $x;
			}
		}
		return $child;
	}

	// 查看用户订单的数量
	public function countOrder($openid = '')
	{
		if($openid == '')
		{
			$openid = get_openid();
		}
		$orders = M('shop_order')->where(array('by_from_openid'=>$openid))->select();
		return count($orders);
	}

	public function userOrder()
	{
		$openid = get_openid();
		$users = M('shop_distribute')->where(array('f_openid' => $openid))->select();
		$sum = 0;
		foreach($users as $v)
		{
			$sum = $sum + $this->countOrder($v['openid']);
		}
		return $sum;
	}

	// 通过下单者找到整个上线
	public function getDisLine($orderid)
	{
		$token = get_token();
		$disList = M('shop_distribute')->where(array('token'=>$token))->order('id asc')->select();
		$order = M('shop_order')->where(array('token'=>$token,'id'=>$orderid))->find();
		$line = $this->init_father_tree($disList, $order['by_from_openid']);
		return krsort($line);
	}

	// 该openid 是消费者的openid
	private function init_father_tree($list, $openid)
	{
		$line = array();
		$userDis = M('shop_distribute')->where(array('openid'=>$openid))->find();
		$ropenid = $userDis['f_openid'];
		foreach($list as $v)
		{
			if($v['openid'] == $ropenid)
			{
				$v['user_cat'] = $this->find_user_state($v['openid']);
				$line[] = $v;
				$ropenid = $v['f_openid'];
			}
		}
		return $line;
	}
	// 查看用户当前的类别
	private function find_user_cat($openid)
	{
		$user = M('shop_user')->where(array('openid'=>$openid))->find();
		return $user['user_cat'];
	}
	/**
	*	@author AliceXT 2015-11-5
	**/
	/**
	*	for 获取一组数据
	*	@param ids 会员id
	*	@param index 定位，第一个标号为0
	*	@param count 这组信息的长度
	*	@return 用户信息数组
	**/
	//获得一组微信用户信息
	//
	public function getInfos($ids = '',$index = 0,$count = 10){
		$openid = get_openid();
		empty($ids) || $ids = session('ids_'.$openid);
		$arr = explode(',', $ids);
		$max = count($arr);
		if($index >= $max){
			return array('errmsg'=>'没有更多会员了，快去加人吧');
		}
		// 得到数组
		$arr = array_slice($arr, $index,$count);
		$in_str = implode(',',$arr);

		$map['id'] = array('in',$in_str);
		$users = M('follow')->where($map)->select();
		return $users;
	}
	/*change end*/
}