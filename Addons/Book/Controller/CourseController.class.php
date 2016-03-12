<?php
namespace Addons\Book\Controller;
use Home\Controller\AddonsController;
class CourseController extends BaseController{
	public function __construct() {
		parent::__construct();
		$this->model = "book_course";
	}

	public function lists(){
		parent::lists($this->model);
	}
	public function add(){
		parent::add($this->model);
	}
	public function edit(){
		parent::edit($this->model);
	}
	public function del(){
		parent::del($this->model);
	}

	public function mobileForm(){
		$model = $this->model;
		$model = $this->getModel ( $model );
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->add ()) {
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
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
            $forms['title'] = "输入教材信息";
            $forms['intro'] = "请输入教材相关信息，上传教材照片";
            $this->assign("forms",$forms);

            // $this->assign("post_url",addons_url("Book://Book/mobileForm"));

			$this->meta_title = '编辑' . $model ['title'];
			$this->display ( T ( 'Addons://Book@Book/mobileForm' ) );
		}
	}
}