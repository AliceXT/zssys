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
	public function scan(){

		$this->assign("config",set_jsapi_config());

		$this->display();
	}
}
