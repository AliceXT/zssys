<?php
namespace Addons\Book\Model;
use Think\Model;
/**
 * Book模型
 */
class BookModel extends Model{

	public function __construct(){
		parent::__construct("book");
	}

	public function create(){
		$data = parent::create();
		$map['bookid'] = $data['course_id'];
		$course = M('book_course')->where($map)->find();
		if(empty($course)){
			$this->data = null;
			$this->error = "该教材还没被创建";
			return false;
		}else{
			return $data;
		}

	}
}
