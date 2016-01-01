<?php
namespace Addons\Coupon\Controller;
use Home\Controller\AddonsController;
use Addons\Coupon\Model\CouponModel;
use Addons\Coupon\Model\SnCodeModel;
class CouponController extends AddonsController {
	function lists(){
		$param['token'] = get_token();
		$url = addons_url ( 'Coupon://Coupon/prev',$param);
		$normal_tips = '创建SN码之前请检查优惠券信息，创建之后请不要更改优惠券信息<br>';
		$normal_tips .= '通过地址访问领取页面：' . $url . ' ，也可点击<a target="_blank" href="' . $url . '">这里</a>在预览';
		$this->assign ( 'normal_tips', $normal_tips );
		parent::lists();
	}
	function edit() {
		$id = I ( 'id' );
		$model = $this->getModel ();
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			// 获取数据
			$data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$token = get_token ();
			if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
				$this->error ( '非法访问！' );
			}
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $model ['title'];
			
			$this->_deal_data ();
			
			$this->display ();
		}
	}
	function add() {
		$model = $this->getModel ();
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$this->_saveKeyword ( $model, $id );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->_deal_data ();
			
			$this->display ();
		}
	}
	
	// 增加或者编辑时公共部分
	function _deal_data() {
		$normal_tips = '插件场景限制参数说明：格式：[插件名:id],如<br/>
				[投票:10]，表示对ID为10的投票投完对能领取<br/>
				[投票:*]，表示只要投过票就可以领取<br/>
				[微调研:15]，表示完成ID为15的调研就能领取<br/>
				[微考试:10]，表示完成ID为10的考试就能领取<br/>';
		$this->assign ( 'normal_tips', $normal_tips );
	}
	// 预览
	function preview() {
		$this->prev ();
	}
	// 开始领取页面
	function prev() {
		header("Content-type: text/html; charset=utf-8");
		// $map2 ['target_id'] = I ( 'id', 0, 'intval' );
		$follow = get_followinfo($this->mid);
		// exit(dump($follow));
		if(empty($follow)){
			echo "<h1>您不是微信系统用户</h1>";
			// exit();
			$this->assign("sysusr",true);
		}else{
			get_openid($follow['openid']);
		}
		
		$SnCode = new SnCodeModel();
		$map2 ['uid'] = $this->mid;
		$list = $SnCode->where ( $map2 )->select ();
		$list = $SnCode->selectCanUse($list);
		$this->assign ( 'my_sn_list', $list );
		// exit(dump($this->mid));
		$isfan = $this->_is_fan($follow['openid']);
		// 修改用户在后台的关注状态
		M('follow')->where(array("id"=>$this->mid))->setField("status","1");
		$this->assign("notfan",!$isfan);

		if(!$isfan){
			$param['token'] = get_token();
			$url = U('home/Index/leaflets',$param);
			redirect($url);
			exit;
		}
		$this->_set_share();
		//签名配置
        $this->set_jsapi_config();

		// $this->_detail ();
		$this->display ( 'prev' );
	}
	//创建分享内容
    function _set_share(){
        $config = get_addon_config('Shop');
        
        // 分享的信息
		$share['title'] = "领取优惠券";
        $share['desc'] = "优惠券领取页面";
        $param['token'] = get_token();
        $share['link'] = addons_url("Coupon://Coupon/prev",$param);
        $share['pic'] = $config['cover'];
        $this->assign('share',$share);
    }
	// 设置签名信息
    function set_jsapi_config(){
        $ticket = get_jsapi_ticket();

        $param['jsapi_ticket'] = $ticket['ticket'];

        $param['noncestr'] = "123456789";
        $param['timestamp'] = $ticket['timestamp'];
        $param['url'] = "http://".$_SERVER['SERVER_NAME'].__SELF__;

        $string = $this->toVarString($param);
        $sha = sha1($string);

        $appinfo = get_token_appinfo();

        $config['timestamp'] = $param['timestamp'];
        $config['noncestr'] = $param['noncestr'];
        $config['appid'] = $appinfo['appid'];
        $config['signature'] = $sha;

        $this->assign('config',$config);
    }
    // 生成签名字符串
    function toVarString($arr){
        $string = null;
        foreach($arr as $key=>$value){
            $string .= $key."=".$value."&";
        }
        $len = strlen($string);
        return substr($string, 0, $len-1);
    }
	// 是否已经关注公众号
    function _is_fan($openid)
    {
        $token = get_token();
        $data = getWeixinUserInfo($openid,$token);
        if($data['subscribe'] == 0){
            return false;
        }
        return true;
    }
	function _get_error($data, $my_count = false) {
		$error = '';
		if(empty($data)){
			return "找不到该优惠券";
		}
		
		// 抽奖记录
		if ($my_count === false) {
			$map2 ['target_id'] = $data ['id'];
			$map2 ['uid'] = $this->mid;
			$my_count = M ( 'sn_code' )->where ( $map2 )->count ();
		}
		
		// 权限判断
		$map ['token'] = get_token ();
		$map ['openid'] = get_openid ();
		$follow = M ( 'follow' )->where ( $map )->find ();
		$isfan = $this->_is_fan($follow['openid']);
		$isfan || $follow['status'] = 0;
		$is_admin = is_login ();
		
		if (! empty ( $data ['end_time'] ) && $data ['end_time'] <= time ()) {
			$error = '您来晚啦';
		} else if ($data ['max_num'] > 0 && $data ['max_num'] <= $my_count) {
			$error = '您的领取名额已用完啦';
		} else if ($data ['follower_condtion'] > intval ( $follow ['status'] ) && ! $is_admin) {
			switch ($data ['follower_condtion']) {
				case 1 :
					$error = '关注后才能领取';
					break;
				case 2 :
					$error = '用户绑定后才能领取';
					break;
				case 3 :
					$error = '领取会员卡后才能领取';
					break;
			}
		} else if ($data ['credit_conditon'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不足';
		} else if ($data ['credit_bug'] > intval ( $follow ['score'] ) && ! $is_admin) {
			$error = '您的财富值不够扣除';
		} else if (! empty ( $data ['addon_condition'] )) {
			addon_condition_check ( $data ['addon_condition'] ) || $error = '权限不足';
		}
		$this->assign ( 'error', $error );
		// dump ( $error );
		
		return $error;
	}
	// 生成sn码
	function create_sn(){
		$SnCode = new SnCodeModel();
		$return_str = $SnCode->create_sn();
		if($return_str == '设置成功'){
			$this->success($return_str);
		}else{
			$this->error($return_str);
		}
	}
	
	// 记录中奖数据到数据库
	function set_sn_code() {
		if(IS_POST){
			$SnCode = new SnCodeModel();
			$map['sn'] = I('sn');
			$sn = $SnCode->where($map)->find();
			if($sn){
				if($sn['uid'])
					$this->error('该优惠券已被领取');

				$Coupon = new CouponModel();
				$data = $Coupon->getCoupon($sn['target_id']);
				
				$error = $this->_get_error ( $data );
				if (! empty ( $error )) {
					$this->error($error);

				}

				// 保存用户领取信息
				$sn_data['uid'] = $this->mid;
				$sn_data['status'] = 1;
				$sn_data['cTime'] = time();
				$result = $SnCode->where($map)->setField($sn_data);

				if($result === false){
					$this->error ( '领取失败，请稍后再试' );
				}else{
				
					// 增加领取数量
					$coupon_map['id'] = $sn['target_id'];
					$Coupon->where($coupon_map)->setInc('collect_count');

					// 重新获得数据
					$sn = $SnCode->where($map)->find();

					// 在时间格式化之前修改过期时间
					$flag = $SnCode->canUse($sn);

					// 格式化时间
					$sn['start_time'] = time_format($sn['start_time']);
					$sn['end_time'] = time_format($sn['end_time']);
					// 判断是否能够使用
					if($flag){
						exit(json_encode(array('state'=>1,'info'=>'领取成功','sn'=>$sn)));
					}else{
						exit(json_encode(array('state'=>1,'info'=>'领取成功,但该优惠券已经过期','sn'=>$sn)));
					}
				}
			}else{
				$this->error('不合法的SN码');
			}
		}else{
			$this->error('不合法的访问',addons_url("Shop://Shop/index"));
		}
	}

	public function test(){
		$Coupon = new CouponModel();
		$data = $Coupon->getCoupon(8);
		dump($data);
	}
}
