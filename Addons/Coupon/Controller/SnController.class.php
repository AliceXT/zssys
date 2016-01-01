<?php
namespace Addons\Coupon\Controller;
use Home\Controller\AddonsController;
use Addons\Coupon\Model\SnCodeModel;
class SnController extends AddonsController {
	var $table = 'sn_code';
	var $addon = 'Coupon';
	function _initialize() {
		parent::_initialize ();
		
		$controller = strtolower ( _CONTROLLER );
		
		$res ['title'] = '优惠券';
		$res ['url'] = addons_url ( 'Coupon://Coupon/lists' );
		$res ['class'] = $controller == 'coupon' ? 'current' : '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	function lists() {
		$SnCode = new SnCodeModel();
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'search_button', false );
		$this->assign ( 'check_all', false );

		$param['target_id'] = I('target_id');
		$btn['url'] = addons_url('Coupon://Sn/load',$param);
		$btn['title'] = '导出SN码';
		$buttons[] = $btn;
		$this->assign ( 'top_more_button', $buttons);
		
		$model = $this->getModel ( $this->table );
		$page = I ( 'p', 1, 'intval' ); // 默认显示第一页数据
		
		// 搜索条件
		$map ['addon'] = $this->addon;
		$map ['target_id'] = I ( 'target_id' );
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		$map = $this->_search_map ( $model, $fields );
		
		// /* 查询记录总数 */
		$list_data = $this->_get_model_list($model, $page, $order = 'id desc',$map);

		foreach($list_data['list_data'] as &$data){
			if(empty($data['prize_id'])){
				continue;
			}else{
				$order_map['id'] = array('in',$data['prize_id']);
				$orders = M('shop_order')->where($order_map)->select();
				$str = '';
				foreach($orders as $order){
					$str .= $order['id'].":".$order['title'];
				}
				$data['prize_id'] = $str;
			}
			$data['is_use'] = $data['is_use'] || !$SnCode->canUse($data) ? $data['is_use'] :"已过期" ;
		}
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$list_data ['_page'] = $page->show ();
		}
		$this->assign ( $list_data );
		// dump($list_data);
		
		$this->display ();
	}
	public function load(){
		$map['token'] = get_token();
		$coupon['id'] = $map['target_id'] = I('target_id');
		$map['addon'] = 'Coupon';
		$coupon = M('coupon')->where($coupon)->find();
		$res = M($this->table)->field('id,sn')->where($map)->select();
		$title = array('ID','SN码');
		exportExcel($res, $title, $coupon['title'].'SN码');
	}
	function del() {
		$model = $this->getModel ( $this->table );
		parent::del ( $model );
	}
	function set_use() {
		$map ['id'] = I ( 'id' );
		$map ['token'] = get_token ();
		$data = M ( $this->table )->where ( $map )->find ();
		if (! $data) {
			$this->error ( '数据不存在' );
		}
		
		if ($data ['is_use']) {
			$data ['is_use'] = 0;
			$data ['use_time'] = '';
		} else {
			$data ['is_use'] = 1;
			$data ['use_time'] = time ();
		}
		
		$res = M ( $this->table )->where ( $map )->save ( $data );
		if ($res) {
			$this->success ( '设置成功' );
		} else {
			$this->error ( '设置失败' );
		}
	}
}
