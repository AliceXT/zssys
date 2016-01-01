<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Controller\BaseController;
use Addons\Shop\Model\UserModel;
use Addons\RedPaper\Controller\RedPaperController;
use Addons\RedPaper\Model\RedPaperModel;
class UserController extends BaseController
{
	var $model;
	public function _initialize()
	{
		$this->model = $this->getModel('shop_user');
		parent::_initialize();
		$action = strtolower ( _ACTION );
		$res['title'] = '所有会员';
		$res['url'] = addons_url('Shop://user/lists');
		$res['class'] = $action == 'lists' ? 'cur' : '';
		$nav[] = $res;
		$res['title'] = '合伙人';
		$res['url'] = addons_url('Shop://user/partner');
		$res['class'] = $action == 'partner' ? 'cur' : '';
		$nav[] = $res;
		$res['title'] = '待提现会员';
		$res['url'] = addons_url('Shop://user/getCash');
		$res['class'] = $action == 'getcash' ? 'cur' : '';
		$nav[] = $res;
		$res['title'] = '已提现会员';
		$res['url'] = addons_url('Shop://user/alreadyGetCash');
		$res['class'] = $action == 'alreadygetcash' ? 'cur' : '';
		$nav[] = $res;
        $res['title'] = '提现配置';
        $res['url'] = addons_url('Shop://user/config');
        $res['class'] = $action == 'config' ? 'cur' : '';
        $nav[] = $res;
		$this->assign('sub_nav', $nav);
	}
	public function lists()
	{
		$normal_tips = '会员列表';
		$map =array();
		/**
		*	@author AliceXT for 删除增加按钮 2015-10-6
		**/
		$this->assign('add_button',false);
		/*change end*/
		
		$this->fetchUsersByCat($map,$normal_tips);
	}
	public function partner()
	{
		$map['user_cat'] = array('EQ', 0);
		$this->fetchUsersByCat($map, '合伙人列表,到"编辑"里设置合伙人分成，注意分成为小数形式，所有的合伙人的分成总和不超过1');
	}
	private function fetchUsersByCat($nmap, $normal_tips = '列表')
	{
		$this->assign('normal_tips', $normal_tips);
		$users = M('shop_user');
		$map['token'] = get_token();
		session('common_condition', $map);
		// $map['user_cat'] = array('EQ', 0);	//合伙人
		$map = array_merge($map, $nmap);
		$list_data = $this->_get_model_list($this->model, 0, 'id desc', $map);
		$F = M('follow');
		foreach($list_data['list_data'] as &$x)
		{
			$x['remain_money'] = number_format($x['remain_money']/100, 2);
			$F_map['openid'] = $x['openid'];
			$vo = $F->where($F_map)->find();
			$x['headimgurl'] = '<img src="' . $vo ['headimgurl']  . '" width="50px" >';
			$x['wx_nickname'] = $vo['nickname'];
		}
		$this->assign($list_data);
		$this->assign("search_url",addons_url('Shop://User/search'));// AliceXT 2015-10-26 for 修改搜索按钮
		$templateFile = $this->model['template_list'] ? $this->model['template_list'] : '';
		$this->display('lists');
	}
	/**
	*	@author AliceXT 2015-10-26 for 修改搜索按钮
	**/
	public function search(){
		header("Content-type: text/html; charset=utf-8");
		$wx_nickname = I('wx_nickname');
		if(!empty($wx_nickname)){
			$map['wp_follow.nickname'] = array('like',"%".$wx_nickname."%");
			$map['wp_follow.token'] = get_token();
			$F = M('follow');
			$U = M('shop_user');
			$users = $U->field('wp_shop_user.*')->where($map)->join('RIGHT JOIN wp_follow ON wp_follow.openid = wp_shop_user.openid')->select();
			$list_data = $this->_get_model_list($this->model, 0, 'id desc', $map);
			$list_data['list_data'] = $users;
			foreach($list_data['list_data'] as &$x)
			{
				$x['remain_money'] = number_format($x['remain_money']/100, 2);
				$F_map['openid'] = $x['openid'];
				$vo = $F->where($F_map)->find();
				$x['headimgurl'] = '<img src="' . $vo ['headimgurl']  . '" width="50px" >';
				$x['wx_nickname'] = $vo['nickname'];
			}
			$this->assign($list_data);
			$this->assign("search_url",addons_url('Shop://User/search'));// AliceXT 2015-10-26 	for 修改搜索按钮
			$templateFile = $this->model['template_list'] ? $this->model['template_list'] : '';
		}

		$this->display('lists');
	}
	public function edit()
	{
		$model = $this->model;
		$id || $id = I ( 'id' );

		// 获取数据
		$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
		$data || $this->error ( '数据不存在！' );

		$token = get_token ();
		if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
			$this->error ( '非法访问！' );
		}

		if (IS_POST) {
			$Model = M('shop_user');
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->_saveKeyword ( $model, $id );

				$field['old_money'] = $data['remain_money'];
				$field['new_money'] = intval(I('remain_money'));
				if($field['old_money'] != $field['new_money']){
					action_log('edit_remain_money','shop_user',$id,$this->mid,$field);
				}

				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'], $this->get_param ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );

			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];

			$templateFile || $templateFile = $model ['template_edit'] ? $model ['template_edit'] : '';
			$this->display ( $templateFile );
		}
	}
	/*change end*/
	public function del()
	{
		parent::common_del($this->model);
	}
	
	public function footer()
	{
		$list = D ( 'Addons://Shop/Footer' )->get_list ();
		
		// 取一级菜单
		foreach ( $list as $k => $vo ) {
			$vo ['url'] = str_replace ( '{site_url}', SITE_URL, $vo ['url'] );
			if ($vo ['pid'] != 0)
				continue;
			
			$one_arr [$vo ['id']] = $vo;
			unset ( $list [$k] );
		}
		
		foreach ( $one_arr as &$p ) {
			$two_arr = array ();
			foreach ( $list as $key => $l ) {
				if ($l ['pid'] != $p ['id'])
					continue;
				
				$two_arr [] = $l;
				unset ( $list [$key] );
			}
			
			$p ['child'] = $two_arr;
		}
		
		$this->assign ( 'footer', $one_arr );
	}
	public function editinfo()
	{
		$token = get_token();
		$openid = get_openid();
		$map = array('token' => $token, 'openid' => $openid);
		if(IS_POST)
		{
			$nickname = trim($_REQUEST['nickname']);
			$phone = trim($_REQUEST['phone']);
			$address = trim($_REQUEST['address']);
			$User = M('shop_user');
			$User->nickname = $nickname;
			$User->phone = $phone;
			$User->address = $address;
			$User->where($map)->save();
			if($is_dis)
			{
				$this->tobeaDis();
			}
			exit(json_encode(array('url' => '/index.php?s=/addon/Shop/User/info.html')));
		}
		$info = M('shop_user')->where($map)->find();
		$this->assign('info', $info);
		$this->display();
	}
	public function distr()
	{
		$dis_model = $this->getModel('shop_distribute');
		$normal_tips = '分销管理';
		$this->assign('normal_tips', $normal_tips);
		$list_data = $this->_get_model_list($dis_model);
		$this->assign($list_data);
		$this->display('disLists');
	}
	public function distrUser()
	{
		$normal_tips = '分销会员';
		$this->assign('normal_tips', $normal_tips);
		$users = M('shop_user');
		$map['token'] = get_token();
		session('common_condition', $map);
		$list_data = $this->_get_model_list($this->model);
		$this->assign($list_data);
		$templateFile = $this->model['template_list'] ? $this->model['template_list'] : '';
		$this->display($templateFile);
	}
	/**
	*	@author AliceXT
	*	2015-11-11 for 待提现会员
	**/
	private function handleCash(&$list_data){
		$F = M('follow');
		$U = M('shop_user');
		$user_model = $this->getModel('shop_user');
		$user_model_id = $user_model['id'];
		// 返回model以处理各种数据
		$Cash = $this->getModel('shop_get_cash');
		foreach($list_data['list_data'] as &$x)
		{
			$F_map['openid'] = $U_map['openid'] = $x['openid'];
			$user = $U->where($U_map)->find();
			$follow = $F->where($F_map)->find();
			$x['wx_nickname'] = $follow['nickname'];
			$x['headimgurl'] = '<img src="' . $follow ['headimgurl']  . '" width="50px" >';
			$x['nickname'] = $user['nickname'];
			$x['user_cat'] = get_name_by_status($user['user_cat'],'user_cat',$user_model_id);
			$x['fee'] = number_format($x['fee']/100, 2);
			// $x['ctime'] = date('Y-m-d H:i:s', $x['ctime']);
		}
	}
	// 待取现列表
	public function getCash()
	{
		$normal_tips = '申请取现的用户,请不要给同一个用户批量提现';
		// 隐藏删除和增加按钮
		$this->assign('add_button',false);
		$this->assign('del_button',false);
		$this->assign('normal_tips', $normal_tips);
		$Cash = $this->getModel('shop_get_cash');
		$map['token'] = get_token();
		session('common_condition', $map);
		$map['status'] = 0;
		$list_data = $this->_get_model_list($Cash, 0, 'id desc', $map);
		$this->handleCash($list_data);
		// dump($list_data['list_data']);

		// 增加批量提现按钮
		$btn['title'] = "批量提现";
		$btn['url'] = addons_url('Shop://User/changeStatus');
		$btn['is_buttion'] = true;
		$btn['class'] = "ajax-post confirm";
		$top_more_button[] = $btn;
		$this->assign('top_more_button',$top_more_button);

		$this->assign($list_data);
		$this->display('lists');
	}
	// 已经取现列表
	public function alreadyGetCash()
	{
		$normal_tips = '申请取现的用户';
		$this->assign('normal_tips', $normal_tips);
		$Cash = $this->getModel('shop_get_cash');
		$map['token'] = get_token();
		session('common_condition', $map);
		$map['status'] = 1;
		$list_data = $this->_get_model_list($Cash, 0, 'id desc', $map);
		$this->handleCash($list_data);
		$this->assign($list_data);
		$this->display();
	}
	/*change end*/
	// 提出取现请求
	/**
	*	@author AliceXT
	*	2015-11-14 for 修改错误的判断,自动提现
	**/
	public function getCasherReport()
	{
		$user_model = new UserModel;
		$openid = get_openid();
		$map['openid'] = $openid;
		$user = M('shop_user')->where($map)->find();
		if(IS_POST)
		{
			$times = M("shop_get_cash")->field("count(*) num")->where("openid='$openid' And FROM_UNIXTIME(ctime, '%Y/%m/%d')=DATE_FORMAT(CURRENT_DATE,'%Y/%m/%d') ")->find();
			$config = D('Common/AddonConfig')->get("RedPaperSet");
			// exit(json_encode($times));
			if($config['apply'] == "1" && (int)$config['times'] <= (int)$times['num']){
				exit(json_encode(array('code' => -1, 'info' => '亲，今天提现超出上限~\(≧▽≦)/~啦啦啦')));
			}
			$fee = trim($_REQUEST['fee'])*100;
			if($fee > $user['remain_money'])	//取现金额大于用户的账户余额
			{
				exit(json_encode(array('code' => -1, 'info' => '取现金额，大于用户账户余额')));
			}
			$map['fee'] = $fee;
			$map['ctime'] = time();
			$id = M('shop_get_cash')->add($map);
			$user_model->decUserProfile($openid, $fee);
			$result = $this->auto($id);
			if($result > 1){
				// TODO
			}else{
				exit(json_encode(array('code' => 1, 'info' => '请等待管理员审核', 'url' => '/index.php?s=/addon/Shop/User/info.html')));
			}
		}
		// 输出提现次数上限
		$config = D('Common/AddonConfig')->get("RedPaperSet");
		$config['apply'] == "1" && $this->assign("times",$config['times']);

		$this->assign('remain_money',$user['remain_money']/100);
		$user['remain_money'] = number_format($user['remain_money']/100, 2);
		$this->assign('info', $user);
		$this->display();
	}
	/*change end*/
	// 成为分销
	public function tobeaDis()
	{
		$manager = M('shop_user')->where(array('token'=>get_token(),'user_cat'=>'1'))->order('id desc')->find();	//总代理
		$token = get_token();
		$map['token'] = $token;
		$openid = get_openid();
		$map['openid'] = $openid;
		$user = M('shop_user')->where(array('openid'=>get_openid()))->order('id desc')->find();
		$username = $_REQUEST['username'];
		$address = $_REQUEST['address'];
		$phone = $_REQUEST['phone'];
		if(IS_POST)
		{
			if(!$manager)
			{
				exit(json_encode(array('info'=>'没有找到总代理', 'code'=> -1)));
			}
			if($user)	//如果用户已经存在
			{
				$U = M('shop_user');
				$U->ctime = time();
				$U->nickname = $username;
				$U->address = $address;
				$U->phone = $phone;
				$U->user_cat = 2;
				$U->where(array('openid'=>$openid))->save();
			}
			else 	// 用户不存在
			{
				$U = M('shop')->add(
					array(
						'ctime'	=> time(),
						'nickname'	=> $username,
						'openid'	=> $openid,
						'token'		=> $token,
						'address'	=> $address,
						'phone'		=> $phone
						));
			}
			$dis = M('shop_distribute')->where(array('openid'=>$openid))->order('id desc')->find();
			if($dis)
			{
				$D = M('shop_distribute');
				$D->manager_openid = $manager['openid'];
				$D->seller_openid = $openid;
				$D->openid = $openid;
				$D->where(array('openid'=>$openid))->save();
			}
			else
			{
				$D = M('shop_distribute')->add(	//在分销表中添加用户
					array(
						'manager_openid'=>$manager['openid'],
						'seller_openid'	=> $openid,
						'openid'		=> $openid,
						'token'			=> $token,
						)
					);
			}
			exit(json_encode(array('code'=>1, 'info'=>'success','url'=> 'index.php?s=/addon/Shop/User/info.html')));
		}
		$this->assign('info', $user);
		$this->display();
	}
	/**
	*	@author AliceXT 
	* 	for 会员列表分页展示
	**/
	// 直接会员
	public function zjUser()
	{
		$this->assign('openid',I('id'));
		$this->assign('hyType','zhijie');
		$this->display('userlist');
	}
	// 间接会员
	public function jjUser()
	{
		$this->assign('openid',I('id'));
		$this->assign('hyType','jianjie');
		$this->display('userlist');
	}
	public function getInfo(){
		$map['openid'] = $openid = I('id');
		$info = M('follow')->where($map)->find();
		exit(json_encode($info));
	}
	/*change end*/
	// 直接会员
	public function zjOrder()
	{
		// $token = get_token();
		// $openid = I('id');
		// $User = new UserModel();
		// $zjlist = $User->zjUserList($openid);
		// $wxUser = array();
		// foreach($zjlist as $v)
		// {
		// 	$wxUser[] = getWeixinUserInfo($v['openid'], $token);
		// }
		// $this->assign('list', $wxUser);
		$this->assign('openid',I('id'));
		$this->assign('hyType','zhijie');
		$this->display('orderlist');
	}
	// 间接下单
	public function jjOrder()
	{
		// $token = get_token();
		// $openid = I('id');
		// // $openid = 'osALzjlbZUPpBeBQAadPaywgQwfU'; //test
		// $User = new UserModel();
		// $jjList = $User->jjUserList($openid);
		// $wxUser = array();
		// $length = count($jjList);	//分销数量
		// $page = ceil($length/20);
		// $length = $length < 20 ? $length : 20;
		// for($i = 0; $i < $length; $i++)
		// {
		// 	$wxUser[] = getWeixinUserInfo($jjList[$i]['openid']);
		// }
		// $this->assign('list', $wxUser);
		$this->assign('openid',I('id'));
		$this->assign('hyType','jianjie');
		$this->display('orderlist');
	}
	/**
	*	@author AliceXT 2015-10-17 for 改变上线关系
	**/
	public function sell(){
		$D = new \Addons\Shop\Model\DistributeModel();
		$F = new \Addons\UserCenter\Model\FollowModel();
		$U = M('shop_user');
		$map['openid'] = I('openid');
		$map['token'] = get_token();
		$d = $D->where($map)->find();
		if($d){

		}else{
			// $this->error('该用户在')
			if($d = $D->create($map)){
				$D->add();
				$data['user_cat'] = '2';
				$U->where($map)->setField($data);
			}else{
				$this->error('给该用户创建分销关系时出错');
			}
		}
		// $this->assign('d',$d);
		$u = $F->get_user_info($d['openid']);
		$this->assign('u',$u);


		$user = $U->where($map)->find();
		if($user['user_cat'] == '0'){
			$D->clearLeader(I('openid'));
			$url = "javascript:alert('该用户身份为合伙人，不能为其添加上线');";
		}else{
			empty($d['f_openid']) || $f = $F->get_user_info($d['f_openid']);
			empty($f) || $this->assign('f',$f);
			$param['id'] = $d['id'];
			$url = addons_url('Shop://User/editDis',$param);
		}
		
		$this->assign('jump_url',$url);

		// dump($d);
		$this->display(T('Addons://Shop@User/sell'));
	}
	public function editDis(){
		$model = $this->getModel('shop_distribute');
		$id = I('id');
		$this->assign('post_url',__SELF__);
		parent::common_edit($model,$id,'edit');
	}
	/*change end*/
	/**
	*	@author AliceXT 
	*	2015-10-20 for 用户详情页开启
	**/
	public function info()
	{
		header("Content-type: text/html; charset=utf-8");
		/**
		*	@author AliceXT 
		*	2015-12-04 for 会员中心用户校正
		**/
		// $follow = get_followinfo($this->mid);
		// // exit(dump($follow));
		// if(empty($follow)){
		// 	echo "<h1>您不是系统用户</h1>";
		// 	exit();
		// }else{
		// 	get_openid($follow['openid']);
		// }
		/*change end*/
		$token = get_token();
		$openid = get_openid();
		$wxUserinfo = getWeixinUserInfo($openid, $token);
		$user = new UserModel();
		$map['token'] = $token;
		$map['openid'] = $openid;
		$info = M('shop_user')->where($map)->find();
		$info['headimgurl'] = $wxUserinfo['headimgurl'];
		if($wxUserinfo['sex'] != 0)
		{
			$info['sex'] = $wxUserinfo['sex']== 1 ? '男' : '女';
		}
		else
		{
			$info['sex'] = '未设置';
		}
		$info['wxnickname'] = $wxUserinfo['nickname'];	//微信昵称
		$info['remain_money'] = number_format($info['remain_money']/100, 2);
		$fdis = M('shop_distribute')->where(array('openid'=>$openid))->find();
		$sx = ''; //上线
		if($fdis)
		{
			$fwx = getWeixinUserInfo($fdis['f_openid']);
			$sx = $fwx['nickname'];
		}
		else
		{
			$sx = '系统';
		}
		$this->assign('sx', $sx);
		$this->footer();
		$this->assign('info', $info);
		$this->display('info');
	}
	public function zjhy(){
		$openid = I('id');
		$map['f_openid'] = array('in',$openid);
		$sub_line = M('shop_distribute')->field('count(*) num')->where($map)->find();
		$sub_count = intval($sub_line['num']);
		// $this->assign('sub_count', $sub_count); //直接下线
		exit(json_encode(array('state'=>1,'num'=>$sub_count)));
	}
	public function orderCount(){
		$map['by_from_openid'] = $openid = array('in',I('id'));
		$map['order_status'] = array('in','2,3,7,10');
		// $user = new UserModel();
		$count = M('shop_order')->field('count(*) num')->where($map)->find();
		$arr['count'] = intval($count['num']);
		$arr['state'] = 1;
		exit(json_encode($arr));
	}
	// 返回该页的粉丝的openid
	public function hyPage($count = 100){
		$map['f_openid'] = $openid = array('in',I('id'));
		$page = I('page') ? I('page') : 1;
		$sub_line = M('shop_distribute')->field('openid')->where($map)->page($page,$count)->select();
		foreach($sub_line as $s){
			$arr[] = $s['openid'];
		}
		$res['openid'] = $arr;
		$res['state'] = 1;
		exit(json_encode($res));
	}
	// 返回该页粉丝的用户信息
	public function Page($count = 100){
		$openids = I('id');
		$follow_map['openid'] = array('in',$openids);
		$follow = M('follow')->field('nickname,headimgurl,openid')->where($follow_map)->select();
		$res['info'] = $follow;
		$res['state'] = 1;
		exit(json_encode($res));
	}
	/*change end */
	/**
	*	@author AliceXT
	*	2015-11-12 for 提现更改按钮
	*	2015-11-13 for 红包提现
	*	2015-11-14 for 提现整合
	**/
	public function changeStatus($id){
		$RedPaper = new RedPaperController();
		if(!$RedPaper->canUse()){
			$this->error('您还没有登录红包1',addons_url('RedPaper://RedPaper/login'));
  		}
		$this->getRedPaper($id,false);
	}
	public function config(){
		$RedPaper = new RedPaperModel();
		if(!$RedPaper->canUse()){
			$this->error('您还没有登录红包',addons_url('RedPaper://RedPaper/login'));
  		}
		if(IS_POST){
            $flag = D("Common/AddonConfig")->set("RedPaperSet",I('config'));
            if($flag !== false){
                $this->success("保存成功",Cookie('__forward__'));
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->fields();
            $db_config = D('Common/AddonConfig')->get("RedPaperSet");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display();
        }
	}
	public function fields(){
        return array (
            'act_name' => array (
                    'title' => '活动名称:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '红包的名称'
            ),
            'wishing' => array (
                    'title' => '祝福语:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '红包祝福语，可以写一些鼓励的话'
            ),
            'remark' => array (
                    'title' => '备注:',
                    'type' => 'textarea',
                    'value' => '',
                    'tip' => '用于备注该红包'
            ),
            'auto' 	=> array(
            	'title' => '自动提现:',
                'type' => 'radio',
                'value' => '',
                'options'=>array(		 //select 和radion、checkbox的子选项
						'1'=>'开启',		 //值=>文字
						'0'=>'关闭',
					),
                'tip' => '是否开启自动提现'
            ),
            'apply' 	=> array(
            	'title' => '限制提现次数:',
                'type' => 'radio',
                'value' => '',
                'options'=>array(		 //select 和radion、checkbox的子选项
						'1'=>'开启',		 //值=>文字
						'0'=>'关闭',
					),
                'tip' => '是否开启每天提现次数限制'
            ),
            'times' => array (
                    'title' => '次数上限:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '每日提现次数上限'
            ),
        );
    }

    private function auto($id){
    	$config = D('Common/AddonConfig')->get("RedPaperSet");
    	if($config['auto'] == '1'){
    		$return = $this->getRedPaper($id,true);
    	}else{
    		// 非自动
    		return 1;
    	}
    }

    private function getRedPaper($id,$flag){
		$RedPaper = new RedPaperController();
		$config = D('Common/AddonConfig')->get( 'RedPaperSet' );
		foreach($config as $key=>$c){
			if(empty($c) && 'auto' != $key){
				$this->error('请设置好提现配置再提现');
			}
		}
    	$C = M('shop_get_cash');
		$id = $id ? $id : I('id');
		$map['id'] = I('ids') ? array('in',implode(',',I('ids'))) : array('in',strval($id));
		// id项不为空
		if(!empty($map['id'][1])){
			$jsonData['token'] = get_token();
			$jsonData['mid'] = get_mid();

			$get_cashs = $C->where($map)->select();
			foreach($get_cashs as $data){
				$jsonData['re_openid'] = $data['openid'];
				$jsonData['total_amount'] = $data['fee'];
				$jsonData['act_name'] = $config['act_name'];
				$jsonData['wishing'] = $config['wishing'];
				$jsonData['remark'] = $config['remark'];
				$content = $RedPaper->shopAdd($jsonData,$flag);
				if($content['state'] == 0){
					$this->error($content['error']);
				}else{
					continue;
				}
			}

			$data['status'] = 1;
			$data['ptime'] = time();
			$result = $C->where($map)->setField($data);
			if($result){
				$this->success('提现成功');
			}else{
				$this->error('红包已经发放，但提现状态更改失败');
			}
		}else{
			$this->error('提现失败');
		}
    }
	/*change end*/
	/**
	*	@author AliceXT
	*	后台查看用户详情
	**/
	public function more(){
		header("Content-type: text/html; charset=utf-8");
		$token = get_token();
		$openid = I('id');
		$wxUserinfo = getWeixinUserInfo($openid, $token);
		$user = new UserModel();
		$map['token'] = $token;
		$map['openid'] = $openid;
		$info = M('shop_user')->where($map)->find();
		$info['headimgurl'] = $wxUserinfo['headimgurl'];
		if($wxUserinfo['sex'] != 0)
		{
			$info['sex'] = $wxUserinfo['sex']== 1 ? '男' : '女';
		}
		else
		{
			$info['sex'] = '未设置';
		}
		$info['wxnickname'] = $wxUserinfo['nickname'];	//微信昵称
		$info['remain_money'] = number_format($info['remain_money']/100, 2);
		$fdis = M('shop_distribute')->where(array('openid'=>$openid))->find();
		$sx = ''; //上线
		if($fdis)
		{
			$fwx = getWeixinUserInfo($fdis['f_openid']);
			$url = addons_url("Shop://User/more",array('id'=>$fwx['openid']));
			$sx = '<a href="'.$url.'">'.$fwx['nickname']."</a>";
		}
		else
		{
			$sx = '系统';
		}
		$this->assign('sx', $sx);
		$this->footer();
		$follow = M('follow')->where($map)->find();
		$this->assign("follow",$follow);
		$this->assign('info', $info);

		//要更改的属性
		$model = $this->model;
		$fields = get_model_attribute ( $model ['id'] );
		$this->assign ( 'fields', $fields[1] );
		$param['id'] = $info['id'];
		$param['model'] = $model ['id'];
		$this->assign('post_url',addons_url('Shop://User/edit',$param));
		// exit(dump($fields));

		// 订单
		$order_map['by_from_openid'] = $info['openid'];
		$order = M('shop_order')->field('count(*) num')->where($order_map)->find();
		session('select_map',$order_map);//记录map
		$this->assign("order",$order);

		$this->display('more');
	}
	public function spread(){
		$this->assign('openid',I('id'));
		$this->display('spread');
	}
	/*change end*/

}