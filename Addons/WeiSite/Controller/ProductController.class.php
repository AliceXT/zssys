<?php

namespace Addons\WeiSite\Controller;

use Addons\WeiSite\Controller\BaseController;
use Addons\WeiSite\Model\ProductModel;
// use Think\Lib\Think\Util\Cookie;

class ProductController extends BaseController
{
	var $model;
	public function _initialize()
	{
		$this->model = $this->getModel('product');
		parent::_initialize();
	}
	// 商品列表
	public function lists()
	{
		$normal_tips = '商品列表';
		$this->assign('normal_tips',$normal_tips);
		$pd = M('Product');

		$map['token'] = get_token();	//获取token
		session('common_condition', $map);

		$list_data = $this->_get_model_list($this->model);

		$map['is_show'] = 1;
		$list = M('weisite_category')->where($map)->field('id,title')->select();

		$cate[0] = '';
		foreach( $list as $vo)
		{
			$cate[$vo['id']] = $vo['title'];
		}

		foreach( $list_data['list_data'] as &$vo)
		{
			$vo['cate_id'] = intval( $vo['cate_id']);
			$vo['cate_id'] = $cate[$vo['cate_id']];
		}

		$this->assign($list_data);

		$templateFile = $this->model['template_list'] ? $this->model['template_list'] : '';
		$this->display($templateFile);
	}

	public function add()
	{
		$model = $this->model;
		$Model = D(parse_name( get_table_name($model['id']), 1));

		if(IS_POST)
		{
			$Model = $this->checkAttr( $Model, $model['id']);
			if($Model->create() && $id = $Model->add())
			{
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );
				// $this->success('添加'. $model['title']. '成功', U('lists?model='. $model['name']));
			}
			else
			{
				$this->error( $Model->getError());
			}
		}
		else
		{
			$fields = get_model_attribute ( $model ['id'] );

			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields [1] as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}

			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];

			$this->display ();
		}
	}

		// 获取所属分类
	private function getCateData() {
		$map ['is_show'] = 1;
		$map ['token'] = get_token ();
		$list = M ( 'weisite_category' )->where ( $map )->select ();
		foreach ( $list as $v ) {
			$extra .= $v ['id'] . ':' . $v ['title'] . "\r\n";
		}
		return $extra;
	}

	// 删除列表数据
	public function del()
	{
		parent::common_del( $this->model);
	}

	// 通用插件的编辑模型
	public function edit() {
		$model = $this->model;
		$id = I ( 'id' );

		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				D ( 'Common/Keyword' )->set ( $_POST ['keyword'], _ADDONS, $id, $_POST ['keyword_type'], 'custom_reply_news' );

				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );

			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields [1] as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}

			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );

		$token = get_token ();
		if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
			$this->error ( '非法访问！' );
		}

			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];

			$this->display ();
		}
	}

	// 向购物车中添加物品
	public function addCart($goods_id = 0, $goods_count = 0)
	{
		$inCart = false;
		if($goods_id == 0 || $goods_count == 0)
		{
			if(!isset($_REQUEST['goods_id']))
			{
				exit('error:can not find goods id');
			}
			$goods_id = $_REQUEST['goods_id'];

			if(!isset($_REQUEST['goods_count']))
			{
				exit('error:can not find goods count');
			}
			$goods_count = $_REQUEST['goods_count'];
		}

		$goods = unserialize(cookie('goods'));

		if($goods !== null && !empty( $goods))	//查看购物车内是否有商品
		{
			foreach($goods as & $vo)
			{
				if( $vo['id'] == $goods_id)	//如果购物车内商品和新添加的为同种是商品，则累加
				{
					$vo['count'] += $goods_count;
					$inCart = true;		//该货物在购物车里
				}
			}
			if(!$inCart)	//该货物没有在购物车里
			{
				$goods[] = array(
					'id' 	=> intval($goods_id),
					'count' => intval($goods_count)
				);
			}
		}
		else
		{
			$goods[] = array(
						'id' 	=> intval($goods_id),
						'count' => intval($goods_count)
					);
		}

		cookie('goods', serialize($goods));
		echo count($goods);
	}

	// 购物车内货物数量
	public function addCartCount()
	{
		return 3;
	}

	// 购物车内货物
	public function cartList()
	{
		$pd = new ProductModel;
		$goods = unserialize(cookie('goods'));
		$order = array();
		$sum = 0;
		if($goods !== null && !empty($goods))
		{
			foreach ($goods as $x)
			{
				$map['id'] = $x['id'];		//商品id
				$cell = $pd->productDetail($map);	//通过商品id 获取商品详情
				$cell['img1'] = get_cover_url($cell['img1']);
				$cell['count'] = $x['count'];
				$order[] = $cell;
				$sum += $cell['price'] * $x['count'];
			}
		}

		$this->assign('sum', $sum);
		$this->assign('cartList', $order);
		$this->display('cartList');
	}

	//清空购物车
	public function setCartEmpty()
	{
		cookie('goods', null);
		echo json_encode(array('code' => '1', 'info' => '购物车已清空'));
	}

	//删除购物车内的某件物品
	public function removeGoods()
	{
		if(!isset($_REQUEST['id']))
		{
			exit('error: id parm is required');
		}
		$id = $_REQUEST['id'];
		$goods = unserialize(cookie('goods'));
		foreach($goods as $k => $v)
		{
			if($v['id'] == $id)
			{
				unset($goods[$k]);
			}
		}
		cookie('goods', $goods);
		echo json_encode(array('code'=> 1, 'info' => '删除物品成功！'));
	}

}