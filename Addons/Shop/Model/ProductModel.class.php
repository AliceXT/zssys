<?php

namespace Addons\Shop\Model;
use Think\Model;

/**
 * WeiSite模型
 */
class ProductModel extends Model{

	protected $tableName = 'shop_product';

	public function plist($map = array())
	{
		$token = get_token();
		$map['token'] = $token;
		$list = M('shop_product')->where($map)->select();
		return $list;

	}

	public function productDetail($map)
	{
		$token = get_token();
		$map['token'] = $token;
		$list = M('shop_product')->where($map)->find();
		return $list;
	}

	//获取商品单价
	public function getUnitCost($id)
	{
		$map['token'] = get_token();
		$map['id'] = $id;
		$price  = M('shop_product')->where($map)->field('discount_price')->limit(1)->select();
		return $price[0]['discount_price'];
	}

	// 返回对应商品的返利金额
	public function getDeduct($level,$pid)
	{
		$map['token'] = get_token();
		$map['pid'] = $pid;
		$dis = M('shop_product_dis')->where($map)->order('id desc')->find();
		return $dis[$level.'_deduct'];
	}

	// 对应商品的返利积分
	public function getCredit($level, $pid)
	{
		$map['token'] = get_token();
		$map['pid'] = $pid;
		$dis = M('shop_product_dis')->where($map)->order('id desc')->find();
		return $dis[$level.'_credit'];
	}

	/**
	*	@author AliceXT
	*	根据多规格的ID获得多规格的属性及其选项
	**/
	public function getMultiWithOption($ids){
		$map['id'] = array('in',$ids);
		$option_map['token'] = $map['token'] = get_token();
		$multis = M('shop_multi')->where($map)->select();
		foreach($multis as &$multi){
			$option_map['multi_id'] = $multi['id'];
			$multi['options'] = M('shop_option')->where($option_map)->select();
		}
		return $multis;
	}

	/**
	*	@author AliceXT
	*	根据id获得option的浮动价格
	**/
	public function getFloat($id,$multi_id){
		$map['id'] = $id;
		$map['token'] = get_token();
		$map['multi_id'] = $multi_id;
		$option = M('shop_option')->where($map)->find();
		return $option['float'];
	}

	/**
	*	@author AliceXT
	*	根据多规格的id获得规格名称和选项名称
	**/
	public function getNames($id,$multi_id){
		$map['id'] = $id;
		$multi_map['token'] = $map['token'] = get_token();
		$multi_map['id'] = $map['multi_id'] = $multi_id;
		$multi = M('shop_multi')->where($multi_map)->find();
		$option = M('shop_option')->where($map)->find();
		return array("option"=>$option['name'],"multi"=>$multi['show_name']);
	}

	/**
	*	@author AliceXT
	*	根据goods获得展示结果
	**/
	// 根据cookie中的goods获得输出的信息
	public function _get_order($goods){
		// $pd = new ProductModel;
		$pd = $this;
		$goods = empty($goods) ? unserialize(cookie('goods')) : $goods;
		
		$order = array();
		
		$sum = 0;
		$count = 0;
		$total_fee = 0;
		foreach ($goods as & $x)
		{
			$x['multis'] = json_decode($x['multis'],true);
			$map['id'] = $x['id'];		//商品id
			$cell = $pd->productDetail($map);	//通过商品id 获取商品详情
			$cell['cover'] = get_cover_url($cell['cover']);
			$cell['count'] = $x['count'];
			$cell['discount_price'] = number_format($cell['discount_price']/100, 2);
			foreach($x['multis'] as $key=>$value){
				$cell['multi_price'] += change_fee($pd->getFloat($value,$key));
				$names = $pd->getNames($value,$key);	
				$cell['multi_string'] = empty($cell['multi_string']) ? $names['multi']."：".$names['option'] : $cell['multi_string'].",".$names['multi']."：".$names['option'] ;
				// exit(dump($cell['multi_string']));	
			}
			$cell['total_price'] = $cell['discount_price'] + $cell['multi_price'];
			$order[] = $cell;
			$sum += ($cell['total_price']) * $x['count'];
			$count += $x['count'];
		}
		$total_fee = $sum;

		$result = array(
			'total_fee' => $total_fee,
			'count'	=> $count,
			'goods'	=> $order
			);
		return $result;
	}
	/**
	*	@author AliceXT
	*	后台验证数据是否通过
	**/
	public function check($data){
		$data = empty($data) ? I('post.') : $data;
		if($goods_id == 0 || $goods_count == 0)
		{
			if(!isset($data['goods_id']))
			{
				exit(json_encode(array('code' => 1, 'info' => '没有选择购买商品')));
			}
			$goods_id = $data['goods_id'];
			$good = M('shop_product')->where(array('id'=>$goods_id))->find();
			if(!isset($data['goods_count']))
			{
				exit(json_encode(array('code' => 1, 'info' => '商品数量不能为0！')));
			}
			$goods_count = $data['goods_count'];
			if($good['stock_count'] < $goods_count)	//判断库存是否充足
			{
				exit(json_encode(array('code' => 1, 'info' => '商品库存不足！')));
			}
			if($good['is_limit'] == 1)	//商品是限购的
			{
				$openid = get_openid();
				$order = M('shop_order')->where(array('by_from_openid' => $openid,'pid'=>$goods_id))->find();
				if($order['order_status'] != 4)	//限购产品，4=取消订单
				{
					exit(json_encode(array('code' => 1, 'info' => '限购产品不可重复购买！')));
				}
				if($count > $good['limit_count'])
				{
					exit(json_encode(array('code' => 1, 'info' => '该产品超过限购的数量！')));
				}
			}
			if(!empty($good['multi_ids'])){
				if(empty($data['multis'])){
					exit(json_encode(array('code' => 1, 'info' => '您还没有选择规格！')));
				}else{
					$arr = explode(",", $good['multi_ids']);
					$multis = $data['multis'];
					foreach($arr as $id){
						if(empty($multis[$id])){
							$multi = M('shop_multi')->find($id);
							exit(json_encode(array('code' => 1, 'info' => '您还没有选择'.$multi['show_name'].'！')));
						}
					}
				}
			}
		}
		return array(
            'id'    => intval($goods_id),
            'count' => intval($goods_count),
            'multis'    => json_encode($data['multis']),
        );;
	}

}
