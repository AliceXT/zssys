<?php namespace Addons\WeiSite\Controller;

use Addons\WeiSite\Controller\BaseController;

/**
 *	微信支付类
 *
 *
 */
class PayWeixinController extends BaseController{

	public function __construct()
	{
		parent::__construct();
	}

	public function config()
	{
		var_dump( unserialize("a:2:{i:0;a:2:{s:4:&quot;type&quot;;s:1:&quot;1&quot;;s:7:&quot;widgets&quot;;a:1:{i:0;a:5:{s:9:&quot;widget_id&quot;;s:14:&quot;14374820284171&quot;;s:11:&quot;widget_name&quot;;s:6:&quot;Footer&quot;;s:8:&quot;template&quot;;s:4:&quot;icon&quot;;s:9:&quot;data_from&quot;;s:11:&quot;shop_footer&quot;;s:7:&quot;page_id&quot;;s:1:&quot;2&quot;;}}}i:1;a:2:{s:4:&quot;type&quot;;s:1:&quot;1&quot;;s:7:&quot;widgets&quot;;a:1:{i:0;a:13:{s:9:&quot;widget_id&quot;;s:14:&quot;14374815039191&quot;;s:11:&quot;widget_name&quot;;s:9:&quot;ShopLists&quot;;s:8:&quot;template&quot;;s:6:&quot;simple&quot;;s:9:&quot;data_from&quot;;s:1:&quot;2&quot;;s:8:&quot;data_ids&quot;;s:0:&quot;&quot;;s:14:&quot;data_condition&quot;;s:0:&quot;&quot;;s:8:&quot;list_row&quot;;s:2:&quot;10&quot;;s:5:&quot;order&quot;;s:0:&quot;&quot;;s:13:&quot;widget_height&quot;;s:0:&quot;&quot;;s:12:&quot;widget_title&quot;;s:0:&quot;&quot;;s:17:&quot;widget_more_title&quot;;a:1:{i:0;s:0:&quot;&quot;;}s:15:&quot;widget_more_url&quot;;a:1:{i:0;s:0:&quot;&quot;;}s:7:&quot;page_id&quot;;s:1:&quot;2&quot;;}}}}"));
	}
}