<?php
        	
namespace Addons\VoteCjt\Model;
use Home\Model\WeixinModel;
        	
/**
 * VoteCjt的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		//先获取投票消息
		$map['token'] = get_token();
		if (! empty ( $keywordArr ['aim_id'] )) {
			$map ['id'] = $keywordArr ['aim_id'];
		}
		
		$info = M ( 'vote' )->where ( $map )->order ( 'id desc' )->find ();
		if (! $info) {
			return false;
		}
		
		//组装用户在微信里点击图文的时跳转URL
		//其中token和openid这两个参数一定要传，否则程序不知道是哪个微信用户进入了系统
		$param ['id'] = $info ['id'];
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'VoteCjt://VoteCjt/show', $param );
		
		//组装微信需要的图文数据，格式是固定的
		$articles [0] = array (
				'Title' => $info ['title'],
				'Description' => $info ['info'],
				'PicUrl' => get_cover_url ( $info ['picurl'] ),
				'Url' => $url 
		);

		$res = $this->replyNews ( $articles );
		return $res;
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
        	