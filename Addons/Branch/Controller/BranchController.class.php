<?php
namespace Addons\Branch\Controller;
use Home\Controller\AddonsController;
use Addons\Branch\Model\BranchModel;
class BranchController extends AddonsController{

	public function lists(){
		$normal_tips = '在公众号回复关键字“分公司”查看您的分公司信息';
		$this->assign("normal_tips",$normal_tips);
		// 获取模型信息
		$model = $this->getModel ( "branch" );

		$map['token'] = get_token();
		$list_data = $this->_get_model_list ( $model, $page, "id desc" ,$map);

		$Branch = new BranchModel();
		foreach($list_data['list_data'] as &$vo){
			$vo['openids'] = $Branch->toNickname($vo['openids']);
			$vo['ceo_openids'] = $Branch->toNickname($vo['ceo_openids']);
		}
		$this->assign ( $list_data );
		// exit(dump($list_data));

		$btn['title'] = "合伙人查重";
		$btn['url'] = addons_url("Branch://Branch/repect");
		$btns[] = $btn;
		$this->assign("top_more_button",$btns);

		$this->display ();
	}
	public function show(){
		$map['openid'] = $openid = I('id') ? I('id') : get_openid();

		$info = getWeixinUserInfo($openid);
		$this->assign("info",$info);

		$Branch = new BranchModel();
		$branch = I("aim_id") ? $Branch->findBranch($openid,I('aim_id')) : $Branch->findBranch($openid);
		if(empty($branch)){
			$this->error("您不是分公司负责人");
		}
		$this->assign("partner",count(explode("\r\n",$branch['openids'])));
		$branch['ceo_openids'] = $Branch->toNickname($branch['ceo_openids']);
		$branch['openids'] = $Branch->toOpenid($branch['openids']);
		$this->assign("branch",$branch);

		$this->display();
	}
	// 获得该页所有订单费用的综合，参数openids,返回统计营业额fee
	public function orderPage($count = 100){
		$map['by_from_openid'] = array('in',I('id'));
		$map['order_status'] = array('in','2,3,7,10');
		$map['token'] = get_token();
		$page = I('page') ? I('page') : 1;
		$order = M('shop_order')->field("SUM(total_fee) sum")->where($map)->page($page,$count)->find();
		exit(json_encode(array("state"=>1,"fee"=>$order['sum'])));
	}
	// 查看合伙人
	public function partner(){
		$map['openid'] = $openid = I('id') ? I('id') : get_openid();
		$this->assign("openid",$openid);
		$Branch = new BranchModel();
		$branch = $Branch->findBranch($openid);
		if(empty($branch)){
			$this->error("您不是分公司负责人");
		}
		$this->assign("partner",count(explode("\r\n",$branch['openids'])));
		$branch['ceo_openids'] = $Branch->toNickname($branch['ceo_openids']);
		$branch['openids'] = $Branch->toOpenid($branch['openids']);
		$this->assign("branch",$branch);
		
		$this->display();
	}
	// 合伙人查重
	public function repect(){
		header("Content-type: text/html; charset=utf-8");
		$Branch = new BranchModel();
		$list_data = $Branch->getListData();
		$this->assign($list_data);
		$this->display();
	}
	// 删除某个合伙人
	public function delOpenid(){
		$Branch = new BranchModel();
		$result = $Branch->delOpenid();
		if($result === false){
			$this->error("删除失败");
		}else{
			$this->success("删除成功");
		}
	}
}
