<?php
namespace Addons\Diy\Widget\BookDetail;
use Addons\Diy\Controller\WidgetController;
use Addons\Shop\Model\ProductModel;
class DealController extends WidgetController {
	// widget的说明，必填写
	function info() {
		return array (
				'title' => '赠书详情模块', // 必填，显示在选择widget显示的名称
				'icon' => C ( 'TMPL_PARSE_STRING.__IMG__' ) . '/m/icon_detail.png'  // 可为空，获取选择的模板的html代码，为空则使用通用的方法获取
				);
	}
	// 模块参数配置
	function param() {
		return '';
	}
	// 模块解析
	function show($widget) {
		if ($widget ['data_param'] == '[id]') {
			$widget ['data_param'] = $_REQUEST ['id'];
			if (isset ( $_REQUEST ['product_id'] )) {
				$widget ['data_param'] = $_REQUEST ['product_id'];
			}
		}
		$Course = M('book_course');
		$Book = M('book');
		$map ['id'] = intval ( $widget ['data_param'] );
		$book = $Book->where($map)->find();
		$map = null;
		$map['bookid'] = $book['course_id'];
		$course = $Course->where($map)->find();
		// dump($book);
		// dump($course);
		$map = null;
		$map['openid'] = $book['zs_openid'];
		$zsuser = M("Follow")->where($map)->find();
		empty($zsuser) || $this->assign('zsuser',$zsuser);

		$map = null;
		$map['openid'] = $book['js_openid'];
		$jsuser = M("Follow")->where($map)->find();
		empty($jsuser) || $this->assign('jsuser',$jsuser);
		$this->assign ( 'book', $book );
		$this->assign ( 'course', $course );
		return $this->getWidgetHtml ( $widget );
	}
}
