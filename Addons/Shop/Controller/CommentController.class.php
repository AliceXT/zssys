<?php namespace Addons\Shop\Controller;

use Addons\Shop\Controller\BaseController;
use Addons\Shop\Model\OrderModel;

class CommentController extends BaseController
{
	function lists()
	{

	}

	// 添加评论
	function addComments()
	{
		$id = $_REQUEST['id'];	//商品id
		$oid = $_REQUEST['oid'];	//订单id
		if(IS_POST)
		{
			$comment = $_REQUEST['comment'];
			if($id != '' && $comment != ''){
				$Order = new OrderModel;
				$cid = $Order->addComments($id, $comment, $oid);
				if($cid == 0)
				{
					exit(json_encode(array('code'=>-1, 'info'=> '评论失败，当前的商品您还不能评论!')));
				}
				exit(json_encode(
						array('code'=> 1, '
							info'=> '评论成功!',
							'cid'=> $cid,
							'next_url'=> '/index.php?s=/addon/Shop/Shop/myOrder.html')));
			}
			exit(json_encode(array('code'=> -1, 'info'=> '不可为空！')));

		}
		$this->assign('id', $id);
		$this->assign('oid', $oid);
		$this->display('comments');
	}
}