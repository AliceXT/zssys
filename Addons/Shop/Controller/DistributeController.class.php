<?php

namespace Addons\Shop\Controller;

use Addons\Shop\Controller\BaseController;
use Addons\Shop\Model\ProductModel;
use Addons\Shop\Model\UserModel;

class DistributeController extends BaseController {
	var $model;
	var $order_model;

	public function _initialize() {
		$this->model = $this->getModel ( 'shop_distribute' );
		$this->order_model = $this->getModel('shop_order');
		parent::_initialize ();
		$action = strtolower ( _ACTION );

		$res['title'] = '待结佣金订单';
		$res['url'] = addons_url('Shop://distribute/to_be_settle');
		$res['class'] = $action == 'to_be_settle' ? 'cur' : '';
		$nav[] = $res;

		$res['title'] = '已结佣金订单';
		$res['url'] = addons_url('Shop://distribute/settled');
		$res['class'] = $action == 'settled' ? 'cur' : '';
		$nav[] = $res;

		$this->assign('sub_nav', $nav);
	}


	// 待结款
	public function to_be_settle()
	{
		$order_model = $this->getModel('shop_order');
		// dump($order_model);
		$normal_tips = '待结款订单';
		$this->assign('normal_tips', $normal_tips);

		$map['order_status'] = array('eq', 7);
		$map['settle_status'] = array('eq', 0);
		// session('common_condition', $map);

		$list_data = $this->_get_model_list($order_model,0, 'id desc', $map);

		foreach($list_data['list_data'] as & $v)
		{
			$info = json_decode($v['userinfo']);
			$v['userinfo'] = $info->nickname.'-'.$info->phone.'-'.$info->address;
			$v['timestamp'] = date('Y-m-d H:i:s', $v['timestamp']);
		}
		$this->assign($list_data);
		$this->assign('search_url',__SELF__);// AliceXT for 修改搜索跳转页面
		$this->display('disLists');
	}

	//已结款订单
	public function settled()
	{
		$order_model = $this->getModel('shop_order');
		// dump($order_model);
		$normal_tips = '待结款订单';
		$this->assign('normal_tips', $normal_tips);

		$map['order_status'] = array('eq', 7);	//已经完成的订单
		$map['settle_status'] = array('eq', 1);	//未结算的状态

		$list_data = $this->_get_model_list($order_model,0, 'id desc', $map);

		foreach($list_data['list_data'] as & $v)
		{
			$info = json_decode($v['userinfo']);
			$v['userinfo'] = $info->nickname.'-'.$info->phone.'-'.$info->address;
			$v['timestamp'] = date('Y-m-d H:i:s', $v['timestamp']);
		}

		$this->assign('del_button','');
		$this->assign($list_data);
		$this->assign('search_url',__SELF__);// AliceXT for 修改搜索跳转页面
		$this->display('disLists');
	}

	public function edit()
	{
		parent::common_edit($this->order_model);
	}

	// 发放佣金
	public function settle_pay()
	{
		$token = get_token();
		$ids = trim($_REQUEST['ids']);	//订单
		$map['id'] = array('in', $ids);
		$settle_status = 0;
		$pd_model = new ProductModel;
		$user_model = new UserModel;
		$order = M('shop_order')->where($map)->select();

		foreach($order as $v)
		{
			$foundhhr = 0;
			$pdetail = json_decode($v['product_field']);
			//$singleProfile = $v['unit_price'] - $pdetail->stock_price;	//购买的1件商品的利润 -- origin
			$unit_price = ($pdetail->discount_price=="") ? $pdetail->market_price : $pdetail->discount_price;
			$singleProfile = $unit_price - $pdetail->stock_price;	//购买的1件商品的利润
			$sum = $singleProfile * $v['count'];	//所有商品的总利润
					$map['ctime'] = time();
					$map['data'] = 'Order id:'.$pdetail->id." 总数0: - $singleProfile - ".$v['count']." - $sum";
					M('shop_log')->add($map);	
						
			if(!$this->hasUpLine($v['by_from_openid']))	//查看订单是否有上线（如果没有说明商品不是被分销出去的）所有利润由合伙人分享
			{
					$map['ctime'] = time();
					$map['data'] = 'Order id:'.$pdetail->id."总数upline: ".$sum.'singleProfile:'.$singleProfile;
					M('shop_log')->add($map);
								
				$this->settlePayToPartner($sum);	//为合伙人发佣金
			}
			else 	//	商品是由分销商销售出去的
			{
					$map['ctime'] = time();
					$map['data'] = 'Order id:'.$pdetail->id."总数1: ".$sum;
					M('shop_log')->add($map);	
										
				$dis = $this->getDis($v['by_from_openid']);	//获取该购买上线（前三级）
				$manger_per = $pd_model->getDeduct('manager', $v['pid']);	//获取商品给经理的分成比例
				$seller_per = $pd_model->getDeduct('seller', $v['pid']);	//给销售员的提成比例
				$partner_per = $pd_model->getDeduct('partner', $v['pid']);
				$manager_credit = $pd_model->getCredit('manager', $v['pid']); //获取经理的积分返利
				$seller_credit = $pd_model->getCredit('manager', $v['pid']); //销售员的积分返利

				$map['ctime'] = time();
				$map['data'] = 'Order id:'.$pdetail->id."总数2: ".$sum.'by_from_openid:'.$v['by_from_openid']."经理: ".$dis['manager_openid']."销售员: ".$dis['seller_openid']."manger_per: ".$manger_per."partner_per: ".$partner_per.'singleProfile:'.$singleProfile;
				M('shop_log')->add($map);
				
				if($dis['seller_openid'])	//给销售员发佣金
				{
					$map['ctime'] = time();
					$map['data'] = 'Order id:'.$pdetail->id."销售员提成: ".$sum * $seller_per;
					M('shop_log')->add($map);							
					$user_model->incrUserProfile($dis['seller_openid'], $sum * $seller_per);
					$user_model->incrUserCredit($dis['seller_openid'], $seller_credit);			

					//检查销售员是否是合伙人
					unset($map);
					$map['openid'] = $dis['seller_openid'];
					$user = M('shop_user')->where($map)->find();
					if($user && $user['user_cat']==0){
						$map['ctime'] = time();
						$map['data'] = 'Order id:'.$pdetail->id."-合伙人销售员:".$dis['seller_openid']."-提成: ".$sum * $partner_per;
						M('shop_log')->add($map);							
						$user_model->incrUserProfile($dis['seller_openid'], $sum * $partner_per);		
						$foundhhr = 1;				
					}
				}
				else 	//如果没有销售员，则佣金给经理，积分不做重复返利
				{			
					$user_model->incrUserProfile($dis['manager_openid'], $sum * $seller_per);
					$user_model->incrUserCredit($dis['manager_openid'], $seller_credit);
				}
									
				if($dis['manager_openid'])	//给经理发佣金
				{
					$map['ctime'] = time();
					$map['data'] = 'Order id:'.$pdetail->id."经理提成: ".$sum * $manger_per;
					M('shop_log')->add($map);					
					$user_model->incrUserProfile($dis['manager_openid'], $sum * $manger_per);
					$user_model->incrUserCredit($dis['manager_openid'], $manager_credit);		
					
					//检查经理是否是合伙人
					unset($map);
					$map['openid'] = $dis['manager_openid'];
					$user = M('shop_user')->where($map)->find();
					if($foundhhr == 0 && $user['user_cat']==0){
						$map['ctime'] = time();
						$map['data'] = 'Order id:'.$pdetail->id."-合伙人经理".$dis['manager_openid']."提成: ".$sum * $partner_per;
						M('shop_log')->add($map);							
						$user_model->incrUserProfile($dis['manager_openid'], $sum * $partner_per);		
						$foundhhr = 1;				
					}											
				}

				if($foundhhr == 0){
						$id = $this -> findHHR($dis['f_openid']);
						if($id){
							$map['ctime'] = time();
							$map['data'] = 'Order id:'.$pdetail->id."-合伙人openid:".$id." 提成: ".$sum * $partner_per;
							M('shop_log')->add($map);	
							$user_model->incrUserProfile($id, $sum * $partner_per);		
						}
				}
					
				//	$map['ctime'] = time();
				//	$map['data'] = 'Order id:'.$pdetail->id."总数3: ".$sum."合伙人提成: ".$sum * $partner_per;
				//	M('shop_log')->add($map);				
				//$this->settlePayToPartner($sum * $partner_per);

			}

			$O = M('shop_order');
			$O->settle_status = 1;		//更改定当中结算佣金的状态
			$O->where('id='.$v['id'])->save();
		}
		echo json_encode(array('code' => '1', 'info' => '已结算成功！'));
	}

	// 获取上线合伙人
	private function findHHR($openid)
	{
		$map['openid'] = $openid;
		$user = M('shop_user')->where($map)->find();
		if($user && $user['user_cat']==0){
			return $user['openid'];
		}
		else if($user){
			$dis = $this->getDis($openid);	
			if($dis)
				return $this->findHHR($dis['f_openid']);
			else
				return false;
		}
		else
			return false;
	}


	// 获取上线
	private function getDis($openid)
	{
		$map['openid'] = $openid;
		$dis = M('shop_distribute')->where($map)->find();
		return $dis;
	}

	// 利润平均发放给合伙人
	private function averageToPartner($total)
	{
		$map['token'] = get_token();
		$map['user_cat'] = 0;
		$partner = M('shop_user')->where($map)->select();
		$count = count($partner);
		$average = $total/$count;

		foreach($partner as $x)
		{
			$User = M('shop_user');
			$User->remain_money = $x['remain_money'] + $average;
			$User->where( 'id='.$x['id'])->save();
		}
	}

	// 给用户加利润
	private function addProfileToUser($openid, $total)
	{
		$User = M('shop_user');
		$User->where('openid=\''.$openid.'\'')->setInc('remain_money', $total);
	}

	// 为合伙分发放佣金
	private function settlePayToPartner($total)
	{
		$map['ctime'] = time();
		$map['data'] = '合伙人代码';
		M('shop_log')->add($map);	

		$token = get_token();
		$user_cat = 0;	//合伙人
		$map['user_cat'] = $user_cat;
		$map['token'] = $token;
		$partners = M('shop_user')->where($map)->select();	//合伙人	
		foreach($partners as $v)
		{
			$User = M('shop_user');
			$User->where("openid='".$v['openid']."'")->setInc('remain_money', $total*$v['profit_percentage']);	//为partner发放一定比例的佣金	
					$map['ctime'] = time();
					$map['data'] = '合伙人 openid:'.$v['openid']." - 返利 - ".$total*$v['profit_percentage'];
					M('shop_log')->add($map);	

//					$user_model->incrUserProfile($v['openid'], $total*$v['profit_percentage']);
		}
	}

	// 查看购买商品的用户是否有上线
	private function hasUpLine($openid)
	{
		$map['openid'] = $openid;
		$map['token'] = get_token();
		$r = M('shop_distribute')->where($map)->find();
		if($r)
		{
			return 1;
		}
		return 0;
	}

	// 成为分销
	public function tobeaDis()
	{
		$user = M('shop_user')->where(array('user_cat'=>'1'))->order('id desc')->find();	//总经销
		if(!$user)
		{
			exit(json_encode(array('code'=> -1, 'info'=> '没有总代理，请设置总代理')));
		}

		$map['token'] = get_token();
		$openid = get_openid();
		$map['openid'] = $openid;
		$dis = M('shop_distribute')->where($map)->order('id desc')->find();		//如果已经存在
		// if(IS_POST)
		// {
			$sid = $user['openid'];		//	总经销的openid

			$map['manager_openid'] = $sid;
			$map['seller_openid'] = $openid;

			if($dis) //已经是分销
			{
				$D = M('shop_distribute');
				$D->manager_openid = $sid;
				$D->seller_openid = $openid;
				$D->where(array('token'=>$token, 'openid'=> $openid))->save();
				exit(json_encode(array('code'=>1, 'info'=> '修改成功')));
			}
			else 		//新的分销
			{
				$r =  M('shop_distribute')->add($map);
				if($r)
				{
					$U = M('shop_user');
					$U->user_cat = 2;
					$U->where(array('token'=> $token, 'openid'=> $openid))->save();
					exit(json_encode(array('code'=>1, 'info'=>'添加成功')));
				}
				else
				{
					exit(json_encode(array('code'=> -1, 'info'=> '失败')));
				}
			}
		// }
	}
}