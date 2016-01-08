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
}