<?php
        	
namespace Addons\HcCard\Model;
use Home\Model\WeixinModel;
        	
/**
 * HcCard的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'HcCard' ); // 获取后台插件的配置参数	

		$userword = $keywordArr['keyword'];

		$res = $this->_tryKeyword("微名片",$userword,"微名片","创建你的微名片",'HcCard://HcCard/check');
		if($res){
			return $res;
		}

		// $res = $this->_tryKeyword("添加公司介绍",$userword,"添加公司介绍",
		// 	"增加一个介绍公司的面板，建议在PC端打开该链接",'HcCard://Company/add');
		// if($res){
		// 	return $res;
		// }

		// $res = $this->_tryKeyword("公司介绍",$userword,"公司介绍",
		// 	"查看本公司介绍列表，进行详情修改",'HcCard://Company/check');
		// if($res){
		// 	return $res;
		// }

		// $res = $this->_tryKeyword("添加公司链接",$userword,"添加链接地址",
		// 	"增加一个公司的网站链接",'HcCard://Link/add');
		// if($res){
		// 	return $res;
		// }

		// $res = $this->_tryKeyword("公司链接",$userword,"公司链接",
		// 	"查看本公司链接列表，进行详情修改",'HcCard://Link/check');
		// if($res){
		// 	return $res;
		// }

		// $res = $this->_tryKeyword("查看留言",$userword,"查看留言",
		// 	"看看谁给我留言了",'HcCard://Message/check');
		// if($res){
		// 	return $res;
		// }

		$map['token'] = get_token();
		$Addons = A('Addons');
		$map ['user_id'] = strval($Addons->mid);
		$info = M ( 'hccard' )->where ( $map )->order ( 'id desc' )->find ();
		if (!$info) {
			//组装微信需要的图文数据，格式是固定的
		$articles [0] = array (
			'Title' => "您还没创建微名片",
			'Description' => "回复“微名片”创建您的微名片",
		);
		$res = $this->replyNews ( $articles );
			return $res;
		}

		//组装用户在微信里点击图文的时跳转URL
		//其中token和openid这两个参数一定要传，否则程序不知道是哪个微信用户进入了系统
		// $param ['id'] = $keywordArr ['aim_id'];

		$param['id'] = $info['id'];
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'HcCard://HcCard/show', $param );
		
		//组装微信需要的图文数据，格式是固定的
		$articles [0] = array (
			'Title' => $info ['ownername']."的微名片",
			'Description' => $info ['description'],
			'PicUrl' => get_cover_url ( $info ['portrait'] ),
			'Url' => $url
		);
		
		$res = $this->replyNews ( $articles );
		return $res;

	} 

	/**
	*       如果匹配关键词，则返回带有对应addon_url地址的微信消息,
	*		userword 用户传来的关键词
	*       keyword 匹配的关键词
	*       title 标题
	*       description 描述
	*       addon_url 链接地址
	*       param 参数,空则自动补充为公众号token和用户openid
	*		cover 封面图片的ID
	*		@return fixed 封装的微信消息/false
	**/
	public function _tryKeyword($keyword,$userword,$title,$description,$addon_url,$param = "",$cover = ""){
		if(!$param){
			$param['token'] = get_token();
			$param['openid'] = get_openid();
		}
		if($userword == $keyword){
			$url = addons_url ( $addon_url, $param);
			$articles [0] = array (
				'Title' => $title,
				'Description' => $description,
				'PicUrl' => get_cover_url ( $cover ),
				'Url' => $url
				);
			$res = $this->replyNews ( $articles );
			return $res;
		}else{
			return false;
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
        	