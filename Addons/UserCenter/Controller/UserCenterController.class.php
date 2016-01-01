<?php

namespace Addons\UserCenter\Controller;

use Home\Controller\AddonsController;
use User\Api\UserApi;
use Addons\UserCenter\Model\FollowModel;

class UserCenterController extends AddonsController {

	/**
	 * 显示微信用户列表数据
	 */
	public function lists() {
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );

		$model = $this->getModel ( 'follow' );

		$list_data = $this->_get_model_list ( $model, $page, $order );
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo['headimgurl'] = '<img src="' . $vo ['headimgurl']  . '" width="50px" >';
		}

		$this->assign ( $list_data );

		$templateFile || $templateFile = $model ['template_list'] ? $model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 用户绑定
	public function edit() {
		$is_admin_edit = false;
		if(!empty($_REQUEST['id'])){
			$map['id'] = intval($_REQUEST['id']);
			$is_admin_edit = true;
			$msg = '编辑';
			$html = 'edit';
		}else{
			$msg = '绑定';
		    $openid = $map ['openid'] = get_openid ();
			$html = 'moblieForm';
		}
		$token = $map ['token'] = get_token ();
		$model = $this->getModel ( 'follow' );

		if (IS_POST) {
			$is_admin_edit && $_POST['status'] = 2;
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->where ( $map )->save ()) {
				//lastsql();exit;
				$url = '';
				$bind_backurl = cookie('__forward__');
				$config = getAddonConfig ( 'UserCenter' );
				$jumpurl = $config['jumpurl'];

				if(!empty($bind_backurl)){
					$url = $bind_backurl;
					cookie('__forward__', NULL);
				}elseif(!empty($jumpurl)){
					$url = $jumpurl;
				}elseif(!$is_admin_edit){
					$url = addons_url ( 'WeiSite://WeiSite/index', $map );
				}

				$this->success ( $msg.'成功！',  $url);
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			if(!$is_admin_edit){
				$fieldArr = array('nickname','sex','mobile'); //headimgurl
				foreach($fields[1] as $k=>$vo){
					if(!in_array($vo['name'], $fieldArr))
					   unset($fields[1][$k]);
				}
			}

			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->where ( $map )->find ();

		$token = get_token ();
		if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
			$this->error ( '非法访问！' );
		}

			// 自动从微信接口获取用户信息
			empty($openid) || $info = getWeixinUserInfo ( $openid, $token );
			if (is_array ( $info )) {
				if (empty ( $data ['headimgurl'] ) && ! empty ( $info ['headimgurl'] )) {
					// 把微信头像转到WeiPHP的通用图片ID保存 TODO
					$data ['headimgurl'] = $info ['headimgurl'];
				}
				$data = array_merge ( $info, $data );
			}

			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = $msg.'用户消息';

			$this->assign('post_url', U('edit'));

			$this->display ($html);
		}


	}
	public function userCenter() {
		$this->display();
	}
	function config(){
		// 使用提示
		$normal_tips = '如需用户关注时提示先绑定，请进入‘欢迎语’插件按提示进行配置提示语';
		$this->assign ( 'normal_tips', $normal_tips );

		parent::config();
	}

	/**
	*	@author AliceXT for 会员信息
	**/
	public function info(){
		$map['id'] = I('id');
		$info = M('Follow')->where($map)->find();
		if(!$info){
			$this->error("该粉丝不存在");
		}else{
			$Follow = new FollowModel();
			$data = $Follow->get_user_info($info['openid']);
			if(!empty($data['errcode'])){
				$this->error($data['errcode'].$data['errmsg']);
			}
			if($Follow->create($data)){
				$Follow->where($map)->save();
				$this->success("获取成功");
			}else{
				$this->error("返回信息保存失败");
			}
		}
	}

	// 刷新用户信息,get两个参数i是从id为i+1的坐标开始，m是刷新个数
	public function refresh(){
		header("Content-type:text/html;charset=utf-8");
		$index = I('get.i');
		$num = I('get.m');
		// dump($num);
		$num = !empty($num) ? intval($num) : 10;
		// dump($num);
		$map['id'] = array('GT',$index);
		$map['token'] = get_token();
		$fans = M('follow')->where($map)->order('id asc')->limit('1,'.$num)->select();
		// $fans = M('follow')->where($map)->order('id desc')->limit($index.','.$num)->select();
		dump(M('follow')->getLastSql());
		$Follow = new FollowModel();
		$i = 0;
		foreach($fans as $info){
			$m['id'] = $info['id'];
			$data = $Follow->get_user_info($info['openid']);
			$result = $Follow->where($m)->save($data);
			if($result === false){
				// 没有得到用户数据
				echo "<b>用户ID为".$info['id']."的用户刷新用户信息失败</b><br>";
			}else{
				echo "<p>用户ID为".$info['id']."的用户昵称为【".$data['nickname']."】</p>";
			}
			if($i > $num) break; else $i++;
		}
	}

	/**
	*	复制用户信息到shop_user表中
	* 	@param i 开始定位，倒数开始
	*	@param m 复制的数量
	**/
	public function copy(){
		$Follow = new FollowModel();
		header("Content-type:text/html;charset=utf-8");
		$map['token'] = get_token();
		$index = I('get.i');
		$index = empty($index) ? 1 : $index;
		$num = I('get.m');
		$num = empty($num) ? 10 : $num;
		$fans = $Follow->where($map)->order('id desc')->limit($index.','.$num)->select();
		// dump($fans);
		dump($Follow->getLastSql());
		// $users = M('shop_user')->where($map)->select();
		foreach($fans as $info){
			if($Follow->copyByOpenid($info['openid'])){
				echo "<p>在shop_user表中复制在follow表中用户ID为".$info['id']."的用户【成功】</p>";
			}else{
				echo "<b>在shop_user表中复制在follow表中用户ID为".$info['id']."的用户【失败】</b>";
			}
		}
	}
	/*change end*/
	public function test(){
		header("Content-type: text/html; charset=utf-8");
		$Follow = new FollowModel();
		$openid = 'oeD7Qt17qewuJXCyNen1O8LCyGTw';
		$Follow->copyByOpenid($openid);
	}
}

