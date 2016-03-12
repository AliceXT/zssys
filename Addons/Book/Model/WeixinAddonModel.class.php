<?php

        	

namespace Addons\Book\Model;

use Home\Model\WeixinModel;

        	

/**

 * Book的微信模型

 */

class WeixinAddonModel extends WeixinModel{

	function reply($dataArr, $keywordArr = array()) {

		$config = getAddonConfig ( 'Book' ); // 获取后台插件的配置参数	

		$param['token'] = $dataArr['ToUserName'];
		$param['openid'] = $dataArr['FromUserName'];

		//dump($config);
		if($keywordArr['keyword'] == "我要赠书"){
			$url = addons_url("Book://Book/mobileForm",$param);

			$articles [0] = array (
				'Url'=>$url,
				'Title' => "书本收录",
				'Description' => "你手头的教材都可以收录到系统里",
			);
			$res = $this->replyNews ( $articles );
		}elseif($keywordArr['keyword'] == "我要借书"){
			$url = addons_url("Book://Book/index",$param);

			$articles [0] = array (
				'Url'=>$url,
				'Title' => "赠书列表",
				'Description' => "查看赠书列表",
			);
			$res = $this->replyNews ( $articles );
		}else{
			$res = $this->replyText("系统错误");
		}

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

        	