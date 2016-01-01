<?php
namespace Addons\Shop\Model;
use Think\Model;
class SelectModel extends Model{
	// 订单导出根据id
	public function loadOrderById()
	{
		$ids = $_REQUEST['ids'];
		// dump($ids);
		// dump(I('id'));
		$map['id'] = array('in', $ids);
		$this->loadOrderByMap($map);
	}
	//订单导出根据map
	public function loadOrderByMap($map){
		$res = M('shop_order')->where($map)->field('id,pid,title,count,unit_price,total_fee,receiver,phone,address,note')->select();
		// foreach($res as & $x)
		// {
		// 	$info = json_decode($x['userinfo']);
		// 	unset($x['userinfo']);
		// 	// $x['receiver'] = $info->nickname;	//收件人
		// 	$x['phone'] = $info->phone;
		// 	$x['address'] = $info->address;
		// }
		$title = array('订单id','商品id','商品名称','购买数量','单价','总价格','收件人','电话','地址','备注');
		exportExcel($res, $title, '订单');
	}

	public function makeMap(){
		$data = I('post.');
		foreach($data as $key=>$value){
			if(empty($value))continue;
			if($key != 'timestamp' && $key != 'order_status' && $key != 'user_id'){
				$map[$key]= array("like",'%'.$value.'%');
			}else if($key == 'order_status'){
				if($value == '%'){
					// $map[$key] = array( 'like' ,$value);
					continue;
				}else{
					$map[$key] = array( 'eq' ,$value);
				}
			}else if($key == 'timestamp'){
				// 时间大于等于时间戳
				$time = strtotime($value);
				$map[$key] = array('egt',$time);
			}else if($key == 'user_id'){
				// 时间大于等于时间戳
				$map[$key] = $value;
			}
		}

		return $map;
	}
}
