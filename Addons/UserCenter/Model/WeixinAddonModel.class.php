<?php

namespace Addons\UserCenter\Model;

use Home\Model\WeixinModel;

/**
 * UserCenter的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	var $config = array ();
	function reply($dataArr, $keywordArr = array()) {
	}
	// 关注时的操作
	function subscribe($dataArr) {
		$info = D ( 'Common/Follow' )->init_follow ( $dataArr ['FromUserName'] );

		/**
		*	@author AliceXT for 会员信息 2015-10-6
		*	2015-11-20 for 关注去重
		*	2015-12-09 for 用户信息设置
		**/
		// 保存用户信息
		$Follow = new FollowModel();
		$data = getWeixinUserInfo($dataArr['FromUserName'], $info['token']);
		$map['id'] = $info['id'];
		$info['id'] && $Follow->where($map)->setField($data);
		$info['id'] && $Follow->copyByOpenid($dataArr['FromUserName']);
		/*change end*/
		// 增加积分
		session ( 'mid', $info ['id'] );
		add_credit ( 'subscribe' );
	}
	// 取消关注公众号事件
	public function unsubscribe() {
		// 增加积分
		add_credit ( 'unsubscribe' );
	}
}

        	