<?php

namespace Addons\FormsCjt\Controller;

use Home\Controller\AddonsController;

class FormsCjtController extends AddonsController {

	function _initialize() {
		parent::_initialize();
		$this->model = $this->getModel ( 'forms_cjt' );
	}

	function forms_cjt_attribute() {
		$param ['forms_cjt_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'FormsCjt://FormsCjtAttribute/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function forms_cjt_value() {
		$param ['forms_cjt_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'FormsCjt://FormsCjtValue/lists', $param );
		// dump($url);
		redirect ( $url );
	}
	function forms_cjt_export() {
	}
	function preview() {
		$param ['forms_cjt_id'] = I ( 'id', 0, 'intval' );
		$url = addons_url ( 'FormsCjt://FormsCjtValue/add', $param );
		// dump($url);
		redirect ( $url );
	}	
	function nulldeal() {
		$url = addons_url( 'FormsCjt://FormsCjt/lists');
		redirect( $url );
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
