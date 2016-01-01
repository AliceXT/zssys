<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Controller\BaseController;
class ProductController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'shop_product' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		if(I('model')){
			// exit(dump($this->getModel(I('model'))));
			$model = $this->getModel(I('model'));
			if($model['name'] == "shop_multi"){
				$url = addons_url("Shop://Product/multiLists");
				redirect($url);
			}elseif($model['name'] == "shop_option"){
				$url = addons_url("Shop://Product/optionLists");
				redirect($url);
			}
		}
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );

		$btn['title'] = "多规格管理";
		$btn['url'] = addons_url("Shop://Product/multiLists");
		$top_more_button[] = $btn;
		$this->assign("top_more_button",$top_more_button);
		/**
		*@author AliceXT
		**/
		$list_data = $this->_get_model_list ( $this->model ,0,'shangjia asc,id desc');
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['icon'] = '<img src="' . get_cover_url ( $vo ['icon'] ) . '" width="50px" >';
		}
		$this->assign ( $list_data );
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 通用插件的编辑模型
	public function edit() {
		parent::common_edit ( $this->model );
	}
	// 通用插件的增加模型
	public function add() {
		parent::common_add ( $this->model );
	}
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
	// 首页
	function index() {
		$this->display ();
	}
	// 分类列表
	function product() {
		$this->display ();
	}
	// 相册模式
	function picList() {
		$this->display ();
	}
	// 详情
	function detail() {
		$this->display ();
	}
	// 加入购物车
	function buy() {
		$id = I ( 'id', 0, 'intval' );
		if (empty ( $id ))
			return false;
		$prodct ['id'] = $id; // 商品ID
		$prodct ['count'] = I ( 'count', 1, 'intval' ); // 购买数
		$prodct ['param'] = I ( 'param' ); // 其它参数，如颜色、大小等
		$key = 'buy_list_' . $this->mid;
		$list = ( array ) session ( $key );
		$list [] = $prodct;
		session ( $key, $list );
	}
	public function config()
	{
		// $pid = $_REQUEST['pid'];
		I('config') && $config = I('config');
		I('pid') && $pid = I('pid');
		$pid || $pid = $config['pid'];
		$map['pid'] = $pid;
		$token = get_token();
		$map['token'] = $token;
		$db_conf = M('shop_product_dis')->where($map)->order('id desc')->find();
		// exit(dump($db_conf));
		if(IS_POST)
		{
			if($db_conf){
				$flag = M('shop_product_dis')->where($map)->save(I('config'));
			}else{
				$flag = M('shop_product_dis')->add( I('config'));
			}
			if ($flag !== false) {
				$this->success ( '保存成功', '/index.php?s=/addon/Shop/Product/lists.html' );
			} else {
				$this->error ( '保存失败' );
			}
		}
		$addon ['config'] = $this->dis_fields();
		$db_conf['pid'] = $pid;
		$db_conf['token'] = $token;
		if($db_conf)
		{
			foreach($addon['config'] as $key=>$value)
			{
				!isset($db_conf[$key]) || $addon['config'][$key]['value'] = $db_conf[$key];
			}
		}
		$this->assign('data', $addon);
		$this->display('config');
	}
	function dis_fields() {
		return array (
				'pid' => array (
						'title' => '商品id',
						'type' => 'hidden',
						'value' => '',
						// 'tip' => '公众账号的id--wx*****'
				),
				'token' => array (
						'title' => 'token',
						'type' => 'hidden',
						'value' => '',
						// 'tip' => '公众账号的id--wx*****'
				),
				'partner_deduct' => array (
						'title' => '合伙人总提成',
						'type' => 'text',
						'value' => '0',
						// 'tip' => '公众账号的id--wx*****'
				),
				'manager_deduct' => array (
						'title' => '经理提成:',
						'type' => 'text',
						'value' => '0',
						// 'tip' => '微信支付分配的商户号'
				),
				'seller_deduct' => array (
						'title' => '销售员提成:',
						'type' => 'text',
						'value' => '0',
						// 'tip' => 'method'
				),
				'partner_credit' => array (
						'title' => '合伙人积分:',
						'type' => 'text',
						'value' => '0',
						// 'tip' => '微信支付的API秘钥'
				),
				'manager_credit' => array (
						'title' => '经理积分:',
						'type' => 'text',
						'value' => '0',
						// 'tip' => '微信支付的API秘钥'
				),
				'seller_credit' => array (
						'title' => '销售员积分:',
						'type' => 'text',
						'value' => '0',
						// 'tip' => '微信支付的API秘钥'
				),
		);
	}
	public function multiLists(){
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		$model = $this->getModel("shop_multi");
		$list_data = $this->_get_model_list ( $model ,0,'id desc',$map);
		$this->assign ( $list_data );
		$this->assign("add_url",addons_url("Shop://Product/addMulti"));
		$this->assign("del_url",addons_url("Shop://Product/delMulti"));
		$this->display("lists");
	}
	public function addMulti(){
		$model = $this->getModel("shop_multi");
		$this->assign("post_url",addons_url("Shop://Product/addMulti"));
		parent::common_add ( $model ,"add");
	}
	public function editMulti(){
		$model = $this->getModel("shop_multi");
		$this->assign("post_url",addons_url("Shop://Product/editMulti"));
		parent::common_edit ( $model ,I('id'),"edit");
	}
	public function delMulti(){
		$model = $this->getModel("shop_multi");
		$this->assign("post_url",addons_url("Shop://Product/delMulti"));
		parent::common_del ( $model);
	}
	public function optionLists(){
		get_multi(I("multi"));
		$map ['token'] = get_token ();
		$map['multi_id'] = get_multi();
		session ( 'common_condition', $map );
		$model = $this->getModel("shop_option");
		$list_data = $this->_get_model_list ( $model ,0,'id desc',$map);
		$this->assign ( $list_data );
		$this->assign("add_url",addons_url("Shop://Product/addOption"));
		$this->assign("del_url",addons_url("Shop://Product/delOption"));
		$this->display("lists");
	}
	public function addOption(){
		$model = $this->getModel("shop_option");
		$this->assign("post_url",addons_url("Shop://Product/addOption"));
		parent::common_add ( $model ,"add");
	}
	public function editOption(){
		$model = $this->getModel("shop_option");
		$this->assign("post_url",addons_url("Shop://Product/editOption"));
		parent::common_edit ( $model ,I('id'),"edit");
	}
	public function delOption(){
		$model = $this->getModel("shop_option");
		$this->assign("post_url",addons_url("Shop://Product/delOption"));
		parent::common_del ( $model);
	}
}
