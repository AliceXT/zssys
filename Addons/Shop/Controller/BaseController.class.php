<?php



namespace Addons\Shop\Controller;



use Home\Controller\AddonsController;



class BaseController extends AddonsController {

	var $config;

	function _initialize() {

		parent::_initialize();



		$controller = strtolower ( _CONTROLLER );



		$res ['title'] = '商店设置';

		$res ['url'] = addons_url ( 'Shop://Shop/config' );

		$res ['class'] = $controller == 'shop' ? 'current' : '';

		$nav [] = $res;



		$res ['title'] = '订单';

		$res ['url'] = addons_url ( 'Shop://Order/lists' );

		$res ['class'] = $controller == 'order' ? 'current' : '';

		$nav [] = $res;



		$res ['title'] = '设置';

		$res ['url'] = addons_url ( 'Shop://Pay/config' );

		$res ['class'] = $controller == 'pay' ? 'current' : '';

		$nav [] = $res;



		$res ['title'] = '商品分类';

		$res ['url'] = addons_url ( 'Shop://Category/lists' );

		$res ['class'] = $controller == 'category' ? 'current' : '';

		$nav [] = $res;



		$res ['title'] = '商品管理';

		$res ['url'] = addons_url ( 'Shop://Product/lists' );

		$res ['class'] = $controller == 'product' ? 'current' : '';

		$nav [] = $res;



		$res ['title'] = '会员管理';

		$res ['url'] = addons_url ( 'Shop://User/lists' );

		$res ['class'] = $controller == 'user' ? 'current' : '';

		$nav [] = $res;



		$res['title'] = '分销管理';

		$res['url'] = addons_url('Shop://Distribute/to_be_settle');

		$res ['class'] = $controller == 'distribute' ? 'current' : '';

		$nav[] =  $res;



		$res ['title'] = '底部导航';

		$res ['url'] = addons_url ( 'Shop://Footer/lists' );

		$res ['class'] = $controller == 'footer' ? 'current' : '';

		$nav [] = $res;

		/**
		*	@author AliceXT2015-9-10 2015-9-14更改
		**/

		$res ['title'] = '配置';

		$res ['url'] = addons_url ( 'Shop://Web/config' );

		$res ['class'] = $controller == 'web' || $controller == 'seckill' ? 'current' : '';

		$nav [] = $res;

		/* change end */

		$this->assign ( 'nav', $nav );



		$config = getAddonConfig ( 'Shop' );

		$config ['cover_url'] = get_cover_url ( $config ['cover'] );

		$config ['background'] = get_cover_url ( $config ['background'] );

		$this->config = $config;

		$this->assign ( 'config', $config );



		// $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';	//店铺id

		$sid = get_sid();

		if($sid != '')

		{

			$this->initDistribute($sid);

		}

	}



	// 分销用户的初始化upopenidcuo×

	private function initDistribute($sid)

	{



		$token = get_token();

		if(!$upopenid || !$token)

		{

			return false;

		}

		$map['token'] = $token;

		$map['openid'] = get_openid();



		$dis = M('shop_distribute')->where($map)->order('id desc')->find();

		if($dis)	//已经在分销系统中了

		{

			return true;

		}

		$user = M('shop_user')->where(array('token'=> $token, 'openid'=>$sid))->find();

		if($user['user_cat'] == 1)	//如果是总代理

		{

			$dis = M('shop_distribute')->where(array('token'=>$token, 'openid'=> $sid, 'manager_openid'=>$sid))->order('id desc')->find();

			if(empty($dis))	//分销总代理已经在

			{

				M('shop_distribute')->add(array('token'=>$token, 'openid'=> $sid, 'manager_openid'=>$sid, 'f_openid'=> $sid));

			}



		}

		$dis = M('shop_distribute')->where(array('token'=>$token, 'openid'=>$sid))->order('id desc')->find();

		if($dis)

		{

			$nmap['token'] = $token;

			$nmap['openid'] = get_openid();

			$nmap['f_openid'] = $sid;

			if($dis['manager_openid'])

			{

				$nmap['manager_openid'] = $dis['manager_openid'];

			}

			if($dis['seller_openid'])

			{

				$nmap['seller_openid'] = $dis['seller_openid'];

			}

			M('shop_distribute')->add($nmap);

		}

	}

}