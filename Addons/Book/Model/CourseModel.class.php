<?php
namespace Addons\Book\Model;
use Think\Model;
/**
 * Book模型
 */
class CourseModel extends Model{
	protected $tableName = 'book_course';

	public function getName($id){
		$course = $this->field("name")->find($id);
		return $course['name'];
	}
}
