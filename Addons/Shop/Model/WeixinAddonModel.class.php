<?php



namespace Addons\Shop\Model;

use Home\Model\WeixinModel;



/**

 * Shop的微信模型

 */

class WeixinAddonModel extends WeixinModel{

	function reply($dataArr, $keywordArr = array()) {

		/**
		*	@author AliceXT 2015-9-16
		**/
		$aim_id = $keywordArr ['aim_id'];
		if($aim_id == "10000"){
			$param ['token'] = get_token ();
			$param ['openid'] = get_openid ();
			$url = addons_url ( 'Shop://Seckill/waiting', $param );
			$articles [0] = array (
				'Title' => "秒杀测试",
				// 'Description' => $info ['description'],
				// 'PicUrl' => get_cover_url ( $info ['portrait'] ),
				'Url' => $url
			);
			$res = $this->replyNews ( $articles );
			return $res;
		}
		/*change end */

		$config = getAddonConfig ( 'Shop' ); // 获取后台插件的配置参数

		//dump($config);



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

