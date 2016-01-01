<?php

namespace Addons\Shop\Model;

use Think\Model;
/**
*	@author AliceXT 2015-9-25 for 收款确认
**/
use Addons\Logistics\Controller\MsgController;
/*change end*/

/**
 * Shop
 */
class OrderModel extends Model {
	protected $tableName = 'shop_order';
	protected $token = '';
	public function __construct()
	{
		$this->token = get_token();
	}
	function get_list($map) {
		$map ['token'] = get_token ();
		$list = $this->where ( $map )->order ( 'pid asc, sort asc' )->select ();

		foreach ( $list as &$vo ) {
			$vo ['icon'] = get_cover_url ( $vo ['icon'] );
			$vo ['icon'] && $vo ['icon'] = '<img src="' . $vo ['icon'] . '" >';
		}

		return $list;
	}

	//添加到购物车（一个订单包含多中商品，订单中的第一个商品为主订单，也就是其他的商品的负订单，fid=第一件商品的订单id）
	public function addOrder($map, $list)
	{
		$order = M($this->tableName);
		if(!$map['token'] = get_token())
		{
			exit('error:addCart can not find token');
		}
		$map['fid'] = 0;
		$orderList = array();

		foreach($list as $vo)
		{
			$map['unit_price'] = $vo['unit_price'];
			$map['title'] = $vo['title'];
			$map['count'] = $vo['count'];
			$map['total_fee'] = $vo['total_fee'];
			$map['product_field'] = $vo['product_field'];
			$map['receiver'] = $vo['receiver'];
			$map['address'] = $vo['address'];
			$map['phone'] = $vo['phone'];
			$map['pid'] = $vo['pid'];
			$map['by_from_openid'] = $vo['by_from_openid'];
			$insertId = $order->add($map);	//订单的id
			$orderList[] = $insertId;
			if($map['fid'] == 0)
			{
				$map['fid'] = $insertId;
			}
		}
		return $orderList;
	}

	// 下订单时添加用户，如果不存在则添加
	public function addUser($map)
	{
		if(!$map['token'] = get_token())
		{
			exit('error:addCart can not find token');
		}
		$user = M('shop_user');
		// $isExist = $user->where(['token'=>$map['token'],'openid'=>$map['openid']])->limit(1)->select();	//查看用户是否存在
		$isExist = $user->where(array('token'=>$map['token'], 'openid'=>$map['openid']))->limit(1)->select();	//查看用户是否存在

		if(!$isExist)	//如果不存在
		{
			$insterId = $user->add($map);
			return $insterId;
		}
		return $isExist[0]['id'];
	}

	//获取用户的订单
	public function myOrder()
	{
		$userId = $this->getShopUserId();	//获取当前用户的shop_user_id
		if(!$map['token'] = get_token())
		{
			exit('error:addCart can not find token');
		}
		$map['user_id'] = $userId;
		$map['order_status'] = array('NEQ',4);
		$Order = M('shop_order');
		$myOrder = $Order->where($map)->order('id desc')->select();
		return $myOrder;
	}

	private function getShopUserId()
	{
		if(!$map['token'] = get_token())
		{
			exit('error:addCart can not find token');
		}
		if($openid == '')
		{
			$openid = get_openid();
		}
		$map['token'] = get_token();
		$map['openid'] = $openid;
		$Shop_user = M('shop_user');
		$user = $Shop_user->where($map)->select();
		return $user[0]['id'];
	}

	// 通过id获取订单
	public function getOrderById($id)
	{
		$token = get_token();
		$order_status = 1;
		$map['token'] = $token;
		$map['order_status'] = $order_status;

		$map['id'] = $id;
		$Order = M('shop_order');
		$order = $Order->where($map)->find();

		return $order;
	}

	public function getOrder($id)
	{
		$token = get_token();
		$map['token'] = $token;
		$map['id'] = $id;
		$Order = M('shop_order');
		return $Order->where($map)->find();
	}
	//获取订单成功后返回的信息
	public function getOrderMsg($id)
	{
		$map['id'] = $id;
		$order = M('shop_order')->where($map)->find();

		$rmap['out_trade_no'] = $order['out_trade_no'];
		$orderMsg = M('shop_pay_order_return')->where($rmap)->find();
		return $orderMsg;
	}

	//改变订单状态
	public function turnOrderStatus($id, $status)
	{
		$token = get_token();
		$map['token'] = $token;
		$map['id'] = $id;
		$Order = M("shop_order");
		$Order->find($id);
		$Order->order_status = $status;
		$Order->save();
	}

	// 通关过 out_trade_no更改订单状态
	public function turnOrderStatusByOTN($out_trade_no, $status)
	{
		$token = get_token();
		$map['token'] = $token;
		$map['out_trade_no'] = $out_trade_no;
		$orders = M('shop_order')->where($map)->select();

		/**
		*	@author AliceXT  
		*	2015-10-17 for 用户下单通知三位上线,将new MsgController移除循环
		*	2015-10-19 for 用户名称为用户昵称
		**/
		$msg = new MsgController();
		$D = new \Addons\Shop\Model\DistributeModel();
		$F = new \Addons\UserCenter\Model\FollowModel();
		$config = D ( 'Common/AddonConfig' )->get('Shop');
		/*change end*/
		foreach($orders as $x)
		{
			$Order = M('shop_order');
			$newMap['token'] = $token;
			$newMap['id'] = $x['id'];
			$Order->order_status = $status;
			$Order->where($newMap)->save();

			/**
			*	@author AliceXT  
			*	2015-9-25 for 收款确认
			*	2015-10-17 for 用户下单通知三位上线,将new MsgController移除循环
			*	2015-12-22 for 增加商品成交量
			**/
            $res = $msg->send_payment_message($x);
           	$info = json_decode($x['userinfo'],true);
        	$d = $D->getByOpenid($info['openid']);
        	$wx = $F->get_user_info($info['openid']);
        	$str = '您的{}'.$wx['nickname'].'在'.$config['title'].'购买了『'.$x['title'];
        	$str .= '』，您也将获得相应的提成';
        	$time = 2;
            $this->inform($d['f_openid'],$time,$str);

            $pmap['id'] = $x['pid'];
            M('shop_product')->where($pmap)->setInc("bug_count",$x['count']);
            /*change end*/

            /**
            *	@author AliceXT
            *	2015-10-21 for 积分增加
            **/
            $p = json_decode($x['product_field'],true);
            empty($p['experience']) || $credit['experience'] = $p['experience']*$x['count'];
            empty($p['score']) || $credit['score'] = $p['score']*$x['count'];
            if(empty($credit)){
            	add_credit('shop_buy',0);
            }else{
            	add_credit('shop_buy',0,$credit);
            }
            /*change end*/
		}
	}
	/**
	*	@author AliceXT  
	*	2015-10-17 for 用户下单通知三位上线,将new MsgController移除循环
	*	2015-10-20 for str添加一级代理、二级代理
	**/
	/**
	*	替换{}为特定字符
	*	@param str string 带插入格式的字符串，等待替换
	*	@param level int 为1时代表二级代理，2时代表一级代理
	*	@return string 处理后的字符串
	**/
	public function addVars($str,$level){
		$search = "{}";
		switch ($level) {
			case 1:
				$new_str = str_replace($search, '二级代理', $str);
				break;
			case 2:
				$new_str = str_replace($search, '一级代理', $str);
				break;
			default:
				break;
		}
		return $new_str;
	}
	/**
	*	通知上线、上线的上线下线购买了商品
	*	@param openid 微信用户openid
	*	@param time 第几次
	*	@param str 要发送的消息
	**/
	public function inform($openid,$time=1,$str = ''){
		if($time == 0) return ;
		$D = new \Addons\Shop\Model\DistributeModel();
		$code = new \Addons\Qrcode\Model\QrcodeModel();
		$map['openid'] = $info['openid'];
		$msg = $this->addVars($str,$time);
		dump($msg);
		$code->msgTo($openid,$msg);
        $d = $D->getByOpenid($openid);
		empty($d) || $this->inform($d['f_openid'],$time-1,$str);
	}
	/*change end*/
	public function clearOTN( $out_trade_no)
	{
		$token = get_token();
		$map['token'] = $token;
		$map['out_trade_no'] = $out_trade_no;
		$orders = M('shop_order')->where($map)->select();
		foreach($orders as $x)
		{
			$Order = M('shop_order');
			$newMap['token'] = $token;
			$newMap['id'] = $x['id'];
			$Order->out_trade_no = '';
			$Order->where($newMap)->save();
		}
	}

	// 给已经成交的订单添加评论
	public function addComments($gid, $comments, $oid)
	{
		if(!$this->isOrderOk($oid))
		{
			return 0;	//订单尚未确认，不能进行评论
		}

		$token = get_token();
		$Comment = M('shop_comments');
		$map['token'] = $token;
		$map['gid'] = $gid;
		$map['comments'] = $comments;
		$cid = $Comment->add($map);
		return $cid;
	}

	// 判断订单是否已经成交
	public function isOrderOk($oid)
	{
		$map['id'] = $oid;
		$r = M('shop_order')->where($map)->field('order_status')->select();
		if($r['0']['order_status'] == 'orderOk')		//订单成功
		{
			return 1;
		}
		return 0;
	}

	// 将一种物品加入订单
	public function addSingleOrder($orderArr)
	{
		$keys = array('user_id','product_field','total_fee','timestamp','order_status', 'count','title','unit_price','by_from_openid','receiver','address','phone','out_trade_no','pid','fid','ip','express_fee','note','multis');
		$order = array();
		foreach($keys as $k)
		{
			if(!isset($orderArr[$k]))
			{
				$order[$k] = '';
			}
			else
			{
				$order[$k] = $orderArr[$k];
			}
		}
		$order['token'] = get_token();
		$order['userinfo'] = $orderArr['user_info'];
		$ip = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] :'1.1.1.1';
		$order['ip'] = $ip;
		$id = M('shop_order')->add($order);
		$this->minute_stock_count($orderArr['pid'], $orderArr['count']);
		return $id;
	}

	public function minute_stock_count($pid, $count)
	{
		$product = M('shop_product');
		$map['id'] = $pid;
		$product->where($map)->setDec('stock_count',$count);
	}

	/**
	*	@author AliceXT
	*	20151201 for 积分优惠券备注
	*	根据$score和$sn返回备注信息
	*	@param score 使用的积分
	*	@param sn 经过处理具有title,discount的sn数组
	*	@param flag true表示为单个订单，false表示为多个订单
	**/
	public function getNoteStr($score = 0,$sn = array(),$flag = true){
		$str = "";
		if(!$flag){
			$str .= "【这是一个多单备注，同一时间产生的同样备注的订单是同时支付的】\n\n";
		}
		if($score){
			$str .= "用户使用了『".$score."』积分购买商品，折扣了『".number_format($score / 100,2) ."』元\n";
		}
		if($sn){
			$str .= "用户使用了ID为『".$sn['id']."』,SN码为『".$sn['sn']."』的『".$sn['title']."』优惠券，折扣了『".number_format($sn['discount'] / 100,2)."』元\n";
		}
		return $str;
	}
}
