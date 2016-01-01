<?php

namespace Addons\Qrcode\Controller;

use Home\Controller\AddonsController;

class BaseController extends AddonsController
{
	var $config;
	function _initialize()
	{
		parent::_initialize();

		$controller = strtolower( _CONTROLLER);
		$action = strtolower(_ACTION);

		$res['title'] = '二维码设置';
		$res['url'] = addons_url('Qrcode://Qrcode/config');
		$res['class'] = $controller == 'qrcode' ? 'current' : '';
		$nav [] = $res;

		$res['title'] = '图文信息';
		$res['url'] = addons_url('Qrcode://Msg/config');
		$res['class'] = $controller == 'msg' ? 'current' : '';
		$nav [] = $res;

		$this->assign('nav', $nav);

		if($action == 'nulldeal'){
			header("Location:/index.php?s=/addon/Qrcode/Qrcode/config.html");
		}
	}
}
