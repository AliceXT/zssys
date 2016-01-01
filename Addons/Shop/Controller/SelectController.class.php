<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Model\SelectModel;
class SelectController extends OrderController{

	function _initialize(){
		parent::_initialize();

		$action = strtolower ( _ACTION );

		$res['title'] = '所有订单';
		$res['url'] = addons_url('Shop://order/lists');
		$res['class'] = $action == 'lists' ? 'cur' : '';
		$nav[] = $res;

		$res['title'] = '退货单';
		$res['url'] = addons_url('Shop://order/returnLists');
		$res['class'] = $action == 'returnlists' ? 'cur' : '';
		$nav[] = $res;

		$res['title'] = '成功退款';
		$res['url'] = addons_url('Shop://order/returnSuccess');
		$res['class'] = $action == 'returnsuccess' ? 'cur' : '';
		$nav[] = $res;

		$res['title'] = '筛选导出';
		$res['url'] = addons_url('Shop://Select/fliter');
		$res['class'] = $action == 'fliter' ? 'cur' : '';
		$nav[] = $res;

		$this->assign('sub_nav', $nav);
	}

	public function lists(){
		$this->assign('del_button',false);
		$this->assign('add_button',false);
		$this->assign('search_button',false);

		// 按钮导出全部
		$btn['url'] = addons_url('Shop://Select/loadOrderByMap');
		$btn['title'] = "导出满足条件";
		$btn['is_buttion'] = false;
		$top_more_button[] = $btn;

		// 按钮导出选择的订单
		// $btn['url'] = addons_url('Shop://Select/loadOrderById');
		$btn['url'] = 'javascript:download_id()';
		$btn['title'] = "导出选中";
		$btn['is_buttion'] = false;
		$top_more_button[] = $btn;

		$this->assign("top_more_button",$top_more_button);

		$model = $this->getModel ( 'shop_order' );
		$map = session('select_map');
		// exit(dump($map));
		$order = 'id desc';
		$list_data = $this->_get_model_list ( $model, $page, $order ,$map);
		foreach($list_data['list_data'] as &$vo)
		{
			$vo['timestamp'] = date('Y-m-d H:i:s',$vo['timestamp']);
			$userinfo = json_decode($vo['userinfo']);
			$vo['userinfo'] = $userinfo->nickname.'-'.$userinfo->phone.'-'.$userinfo->address;
			$vo['total_fee'] = number_format($vo['total_fee']/100, 2);
            /**
            *   @author AliceXT 2015-10-16 for nickname
            **/
            $map['id'] = $vo['id'];
            $order = M('shop_order')->where($map)->find();
            $info = json_decode($order['userinfo'],true);
            $vo['nickname'] = get_wx_nickname($order['user_id']);
            /*change end */
        }
		$this->assign ( $list_data );

		$this->display();
	}

	public function loadOrderById(){
		$Select = new SelectModel();
		$Select->loadOrderById();
	}

	public function loadOrderByMap(){
		$map = session('select_map');
		$Select = new SelectModel();
		$Select->loadOrderByMap($map);
	}

	public function fliter(){
		$model = $this->getModel ( 'shop_order' );
		if (IS_POST) {
			$Select = new SelectModel();
			// dump($Select);
			$map = $Select->makeMap();
			session('select_map',$map);
			$url = addons_url("Shop://Select/lists");
			$this->success('筛选成功',$url);
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			// dump($fields);

			foreach($fields[1] as $key=>$f){
				if($key == 'token') continue;
				if($key == 'timestamp') $f['remark'] = "选择日期，得到该日期之后的订单";
				if($key == 'order_status') $f['extra'] = "%:所有状态\n".$f['extra'];
				$arr[] = $f;
			}

			$fields[1] = $arr;
			$this->assign ( 'fields', $fields );
			$this->meta_title = '选择' . $model ['title'];

			$this->assign('submit_name',"筛选");

			$post_url = addons_url("Shop://Select/fliter");
			$this->assign("post_url",$post_url);

			$templateFile || $templateFile = $model ['template_add'] ? $model ['template_add'] : '';
			$this->display ( );
		}
	}

}