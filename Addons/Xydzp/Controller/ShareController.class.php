<?php
namespace Addons\Xydzp\Controller;
use Home\Controller\AddonsController;
use Addons\Xydzp\Model\ShareModel;
class ShareController extends AddonsController
{
	public function share_success(){
		$Share = new ShareModel();

		$canAgain = json_decode($Share->canAgain(),true);
		if($canAgain['errcode'] == 0){
			$onceAgain = json_decode($Share->onceAgain(),true);
			if($onceAgain['errcode'] == 0){
				exit(json_encode(array('errcode'=>0,'errmsg'=>"分享成功，请等待页面刷新")));
			}else{
				exit(json_encode($onceAgain));
			}
		}else{
			exit(json_encode($canAgain));
		}
	}

	public function receive(){
		$Share = new ShareModel();
		if($Share->inTime()){
			if($Share->getAwards()){
				exit(json_encode(array('errcode'=>0,'errmsg'=>"收货信息保存成功，将为您尽快发货")));
			}else{
				exit(json_encode(array('errcode'=>1,'errmsg'=>"收货信息保存失败，请联系客服")));
			}
		}else{
			exit(json_encode(array('errcode'=>2,'errmsg'=>"该奖品已经超过领奖期，请下次中奖及时领奖")));
		}
	}

	public function modify(){
		$Share = new ShareModel();
		if($Share->inTime()){
			if($Share->setAddress()){
				exit(json_encode(array('errcode'=>0,'errmsg'=>"收货信息保存成功，将为您尽快发货")));
			}else{
				exit(json_encode(array('errcode'=>1,'errmsg'=>"收货信息保存失败，请联系客服")));
			}
		}else{
			exit(json_encode(array('errcode'=>2,'errmsg'=>"该商品已超过更改地址的时间")));
		}
	}

	public function getLog(){
		$Share = new ShareModel();
		exit(json_encode($Share->getLog()));
	}
}