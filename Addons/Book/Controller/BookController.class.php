<?php
namespace Addons\Book\Controller;
use Addons\Book\Model\CourseModel;
class BookController extends BaseController{
	public function __construct() {
		parent::__construct();
		$this->model = "book";
	}
	public function lists(){
		$param['token'] = get_token();
		$url = addons_url ( 'Book://Book/index', $param );
		$this->assign("normal_tips",'在微信里回复教材赠送或者通过地址访问商店：' . $url . ' ，也可点击<a target="_blank" href="' . $url . '">这里</a>在预览');
		parent::lists($this->model);
	}
	public function index(){
		echo diyPage( '赠书' );
	}
	public function detail(){
		echo diyPage( '赠书详情' );
	}
	public function mobileForm(){

		// $this->assign("config",set_jsapi_config());
		// defined ( '_ACTION' ) or define ( '_ACTION', 'mobileForm' );
		$model = "book";
		$model = $this->getModel ( $model );
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			$map['bookid'] = I('post.course_id');
			$course = M('book_course')->where($map)->find();
			if(empty($course)){
				$param['token'] = get_token();
				$param['openid'] = get_openid();
				$url = addons_url("Book://Course/mobileForm",$param);
				$this->error("该教材还没被录入，你将成为第一个录入的人~",$url);
			}
			if ($Model->create () && $Model->add ()) {
				$this->success ( '保存' . $model ['title'] . '成功！' );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			// 获取数据
			$id = I ( 'id' );
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			
			$pic_id = I('pic_id');
            $forms['cover'] = get_picture_url($pic_id);
            $forms['title'] = "输入书本信息";
            $forms['intro'] = "请输入书号";
            $this->assign("forms",$forms);

            // $this->assign("post_url",addons_url("Book://Book/mobileForm"));

			$this->meta_title = '编辑' . $model ['title'];
			$this->display ( 'mobileForm' );
		}
	}
}
