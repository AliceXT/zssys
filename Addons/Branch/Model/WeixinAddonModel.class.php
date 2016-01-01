<?php
        	
namespace Addons\Branch\Model;
use Home\Model\WeixinModel;
use Addons\Branch\Model\BranchModel;        	
/**
 * Branch的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		// $config = getAddonConfig ( 'Branch' ); // 获取后台插件的配置参数	
		// $this->replyText(json_encode($dataArr).json_encode($keywordArr));
		$Branch = new BranchModel();
		$branch = $Branch->findBranch($dataArr['FromUserName']);

		empty($keywordArr) || $branch = $Branch->findBranch($dataArr['FromUserName'],$keywordArr['aim_id']);
		if(empty($branch)){
			$this->replyText("您不是分公司负责人，无法查看分公司信息");
		}else{
			//组装微信需要的图文数据，格式是固定的
			$param['id'] = $dataArr['FromUserName'];
			$param['token'] = $dataArr['ToUserName'];
			empty($keywordArr) || $param['aim_id'] = $keywordArr['aim_id'];
			$url = addons_url("Branch://Branch/show",$param);
			$articles [0] = array (
				'Title' => $branch['keyword'],
				'Description' => "点击查看分公司信息",
				'Url' => $url,
			);
			$res = $this->replyNews ( $articles );
		}
	} 
	// 关注公众号事件
	public function subscribe() {
		return true;
	}
	
	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}
	
	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}
	
	// 上报地理位置事件
	public function location() {
		return true;
	}
	
	// 自定义菜单事件
	public function click() {
		return true;
	}	
}
        	