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
		$this->assign("normal_tips",'在微信里回复“我要借书”或者通过地址访问：' . $url . ' ，也可点击<a target="_blank" href="' . $url . '">这里</a>在预览');
		parent::lists($this->model);
	}
	public function index(){
		echo diyPage( '我要借书' );
	}
	public function detail(){
		$id = I('id');

		$Course = M('book_course');
		$Book = M('book');
		$map ['id'] = $id;
		$book = $Book->where($map)->find();
		$map = null;
		$map['bookid'] = $book['course_id'];
		$course = $Course->where($map)->find();

		// 用户信息
		$HcCard = M('hccard');
		$map = null;
		$map['openid'] = $book['zs_openid'];
		$zsuser = $HcCard->where($map)->find();
		empty($zsuser) || $this->assign('zsuser',$zsuser);

		$map = null;
		$map['openid'] = $book['js_openid'];
		$jsuser = $HcCard->where($map)->find();
		empty($jsuser) || $this->assign('jsuser',$jsuser);
		$this->assign ( 'book', $book );
		$this->assign ( 'course', $course );

		$this->assign( 'js' ,get_openid());

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
	public function search(){
		$keyword = I('id');

		$map['course_id'] = $keyword;

		$map['token'] = get_token();
		$map['give_time'] = '';
		$map['js_openid'] = '';

		$books = M('book')->where($map)->order('ctime desc')->select();

		$HcCard = M('hccard');

		$hccard_map['token'] = get_token();
		foreach($books as &$book){
			$hccard_map['openid'] = $book['zs_openid'];
			$hccard = $HcCard->where($hccard_map)->find();
			$book['addr'] = $hccard['company_addr'];
			$book['school_name'] = $hccard['school_name'];
		}

		$this->assign ( 'list', $books );

		echo diyPage("赠书列表");
	}
	public function reg(){
		$map['id'] = $id = I('id');
		$book = M('book')->where($map)->find();
		if(empty($book['js_openid'])){
			$data['js_openid'] = get_openid();
			$result = M('book')->where($map)->setField($data);
			if($result){
				$this->success("预定成功");
			}else{
				$this->error("预定失败");
			}
		}else{
			$this->error("该书已被别人预定走了");
		}
	}
	public function my(){
		$Book = M('book');
		$map['zs_openid'] = get_openid();
		// 所有书本
		$fields = 'wp_book.id as id,wp_book_course.name as name';
		$books = $Book->join('__BOOK_COURSE__ ON __BOOK_COURSE__.bookid = __BOOK__.course_id')
		->field($fields)->where($map)->select();
		$this->assign("books",$books);

		$map['js_openid'] = array('neq','');
		$map['give_time'] = array('eq','');

		$fields = 'wp_book.id as id,wp_hccard.id as card_id,wp_hccard.ownername as ownername,wp_hccard.school_name as school_name,wp_book_course.name as name';

		// 预约中
		$booking = 
		$Book->join('__HCCARD__ ON __HCCARD__.openid = __BOOK__.js_openid')
		->join('__BOOK_COURSE__ ON __BOOK_COURSE__.bookid = __BOOK__.course_id')
		->field($fields)->where($map)->select();
		$this->assign("booking",$booking);

		$this->display('my');
	}
	public function pass(){
		$data['give_time'] = time();
		$map['id'] = I('id');
		$map['zs_openid'] = get_openid();
		$result = M('Book')->where($map)->setField($data);
		if($result){
			$this->success("赠送成功");
		}else{
			$this->error("赠送失败");
		}
	}
	public function ignore(){
		$data['js_openid'] = '';
		$map['id'] = I('id');
		$map['zs_openid'] = get_openid();
		$result = M('Book')->where($map)->setField($data);
		if($result){
			$this->success("忽略成功，该书重新上架");
		}else{
			$this->error("忽略失败");
		}
	}
}
