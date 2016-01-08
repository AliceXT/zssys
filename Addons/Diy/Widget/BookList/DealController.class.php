<?php
namespace Addons\Diy\Widget\BookList;
use Addons\Diy\Controller\WidgetController;
class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '赠书列表模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_product.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
		);
	}
	
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		$course_map['token'] = $map['token'] = get_token();
		$map['give_time'] = '';
		$books = M("book")->where($map)->select();
		foreach($books as &$book){
			$course_map['bookid'] = $book['course_id'];
			$course = M('book_course')->field('cover,name,verson,major')->where($course_map)->find();
			array_merge(array1)
		}
		$this->assign ( 'list', $books );
		return $this->getWidgetHtml ( $widget );
	}
}
