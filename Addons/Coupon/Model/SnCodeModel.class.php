<?php
namespace Addons\Coupon\Model;
use Think\Model;
use Addons\Coupon\Model\CouponModel;
/**
 * Coupon模型
 */
class SnCodeModel extends Model{
	protected $name = 'sn_code';

	public function create_sn($id){
		$id || $id = I('id');
		$Coupon = new CouponModel();
		$coupon = $Coupon->getCoupon($id);
		if(empty($coupon)) return '优惠券不存在';
		if($coupon['num']){
			$data['cTime'] = time();
			$map['addon'] = $data['addon'] = 'Coupon';
			$map['target_id'] = $data['target_id'] = $coupon['id'];
			$map['token'] = $data['token'] = $coupon['token'];
			$old = $this->where($map)->select();
			$exist = count($old);
			if($exist >= $coupon['num']){
				return '优惠券已生成';
			}
			if($this->create($data)){
				for ($i=0; $i < $coupon['num'] - $exist; $i++) { 
					$this->add($data);
				}
			}else{
				return '添加SN_CODE数据失败';
			}

			$sn_codes = $this->where($map)->select();
			$map = null;
			foreach($sn_codes as $sn){
				$source = $coupon['token'].'_'.$coupon['id'].'_'.$sn['id'];
				$key = $Coupon->getKey($coupon['id']);
				$c['sn'] = $Coupon->onlock($source,$key);
				$map['id'] = $sn['id'];
				$result = $this->where($map)->setField($c);
				if($result === false){
					return '设置sn码失败';
				}
			}
			return '设置成功';
		}else{
			return '该优惠券没有设置数量';
		}
	}

	public function have_coupon($uid,$order_fee){
		$map['uid'] = $uid;
		$map['is_use'] = 0;
		$map['status'] = 1;
		$map['addon'] = 'Coupon';
		$map['token'] = get_token();
		$sn = $this->where($map)->select();
		return $this->selectCanUse($sn,$order_fee);
	}

	// 判断单个sn是否能用,返回bool,判断是否过期时不传入order_fee
	public function selectCanUse($sns,$order_fee = 0){
		if(empty($order_fee)){
			// 查看已经获得的优惠券
			foreach($sns as $sn){
				$this->canUse($sn);
				$arr[] = $sn;
			}
		}else{
			// 订单中获得可用优惠券
			foreach($sns as $sn){
				if($this->canUse($sn,$order_fee)){
					$arr[] = $sn;
				}
			}
		}
		return $arr;
	}

	// 判断单个sn是否能用,返回bool,判断是否过期时不传入order_fee
	public function canUse(&$sn,$order_fee = 0){
		$Coupon = new CouponModel();
		$coupon = $Coupon->getCoupon($sn['target_id']);
		// 优惠券跟随活动失效
		if($coupon['same']){
			$sn['start_time'] = $coupon['start_time'];
			$sn['end_time'] = $coupon['end_time'];
		}else{
			$sn['start_time'] = $sn['cTime'];
			empty($coupon['sn_date']) || $sn['end_time'] = $sn['cTime'] + $coupon['sn_date']*24*3600;
		}

		$sn['title'] = $coupon['title'];
		$sn['discount'] = $coupon['discount'];
		$end_time = $sn['end_time'];
		// （没有结束时间或未到结束时间）和订单费用大于使用金额
		if(empty($end_time) || $end_time > time()){
			if(empty($order_fee) || $order_fee >= $coupon['full']){
				if($sn['is_use']){
					return false;
				}else{
					return true;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}

	}

	public function useSn($id){
		$map['id'] = $id;
		$data['is_use'] = 1;
		$data['use_time'] = time();
		return $this->where($map)->setField($data);
	}

	public function setPrize($id,$oid){
		if(empty($id)){
			return ;
		}
		$map['id'] = $id;
		$data['prize_id'] = $oid;
		$data['prize_title'] = 'shop_order';
		return $this->where($map)->setField($data);
	}

	public function returnCoupon($out_trade_no){
		$map['out_trade_no'] = $out_trade_no;
        $orders = M('shop_order')->where($map)->select();
        foreach($orders as $order){
        	$arr[] = $order['id'];
        }
        $order_data['note'] = '';
        M('shop_order')->where($map)->setField($order_data);
        $sn_map['prize_id'] = implode(",", $arr);
        $data['is_use'] = 0;
        $data['use_time'] = 0;
        $data['prize_id'] = 0;
        $data['prize_title'] = 0;
        $result = $this->where($sn_map)->setField($data);
        return $result;
    }
}