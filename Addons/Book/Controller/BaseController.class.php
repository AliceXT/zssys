<?php
namespace Addons\Book\Controller;
use Home\Controller\AddonsController;
use Addons\Book\Model\CourseModel;
class BaseController extends AddonsController{
	public function __construct() {
		parent::__construct();

		$controller = strtolower ( _CONTROLLER );
		$res ['title'] = '书';
		$res ['url'] = addons_url ( 'Book://Book/lists' );
		$res ['class'] = $controller == 'book' ? 'current' : '';
		$nav [] = $res;

		$res ['title'] = '教材';
		$res ['url'] = addons_url ( 'Book://Course/lists' );
		$res ['class'] = $controller == 'course' ? 'current' : '';
		$nav [] = $res;

		$this->assign ( 'nav', $nav );

	}
}
