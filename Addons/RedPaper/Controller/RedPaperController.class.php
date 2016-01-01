<?php
namespace Addons\RedPaper\Controller;
use Home\Controller\AddonsController;
use Addons\RedPaper\Model\RedPaperModel;
class RedPaperController extends AddonsController{
	var $model;
	public function _initialize()
    {
		header("Content-type: text/html; charset=utf-8");
		$this->model = $this->getModel('red_paper');

		$res ['title'] = '红包记录';
		$res ['url'] = addons_url ( 'RedPaper://RedPaper/lists' );
		$res ['class'] = strtolower ( _ACTION ) == 'lists' ? 'current' : '';
		$nav [] = $res;

		$res ['title'] = '修改密码';
		$res ['url'] = addons_url ( 'RedPaper://RedPaper/config' );
		$res ['class'] = strtolower ( _ACTION ) == 'config' ? 'current' : '';
		$nav [] = $res;

		$res ['title'] = '登录红包';
		$res ['url'] = addons_url ( 'RedPaper://RedPaper/login' );
		$res ['class'] = strtolower ( _ACTION ) == 'login' ? 'current' : '';
		$nav [] = $res;

		if(strtolower( _CONTROLLER) == 'redpaper')
			$this->assign ( 'nav', $nav );
    }
	//可更改密码的配置
	public function config(){
		$this->getModel ();
		if (IS_POST) {
			$old = D('Common/AddonConfig')->get( _ADDONS );
			$config = I('config');
			$oldpass = md5($config['oldpass']);
			$newpass = $config['newpass'];
			$dblpass = $config['dblpass'];
			// $this->error("return :".$oldpass.$newpass.$dblpass);
			if(!empty($old['hidpass']) && $old['hidpass'] != $oldpass){
				$this->error('旧密码错误');
			}elseif($newpass != $dblpass){
				$this->error('两个新密码不一致');
			}else{
				$data['hidpass'] = md5($newpass);
				$flag = D ( 'Common/AddonConfig' )->set ( _ADDONS, $data );
				if ($flag !== false) {
					$this->success ( '保存成功', Cookie ( '__forward__' ) );
				} else {
					$this->error ( '保存失败' );
				}
			}
		}else{
			$map ['name'] = _ADDONS;
			$addon = M ( 'Addons' )->where ( $map )->find ();
			$addon_class = get_addon_class ( $addon ['name'] );
			$data = new $addon_class ();
			$addon ['addon_path'] = $data->addon_path;
			$addon ['custom_config'] = $data->custom_config;
			$this->meta_title = '设置插件-' . $data->info ['title'];
            $addon['config'] = include $data->config_file;
            $db_config = D('Common/AddonConfig')->get( _ADDONS );
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display();
        }
	}

	public function lists(){
        // $this->assign('del_button', false);	//默认不可以删除订单
        $RedPaper = new RedPaperModel();
        $map['token'] = get_token();
        session('common_condition', $map);
        $list_data = $this->_get_model_list($this->model);

        $table = null;
        $table['same_name'] = 're_openid';
        $table['link_name'] = 'openid';
        $table['fields'] = 'nickname wx_nickname,headimgurl';
        $table['html_format'] = array('headimgurl'=>'<img src="{var}" width="50px" >');
        $join['follow'] = $table;

        $table = null;
        $table['same_name'] = 're_openid';
        $table['link_name'] = 'openid';
        $table['fields'] = 'nickname';
        $join['shop_user'] = $table;

        $table = null;
        $table['same_name'] = 'mid';
        $table['link_name'] = 'id';
        $table['fields'] = 'username mid';//更改字段名称
        $join['ucenter_member'] = $table;

        $RedPaper->listJoinTable($list_data,$join);

        // dump($list_data);
        $this->assign($list_data);
        $this->display('lists');
	}

	public function add(){
		$RedPaper = new RedPaperModel();
		if($RedPaper->canUse()){
			$model = $this->model;
			if (IS_POST) {
				$Model = $RedPaper;
				// 获取模型的字段信息
				$Model = $this->checkAttr ( $Model, $model ['id'] );
				if ($Model->create () && $id = $Model->add ()) {
					$this->_saveKeyword ( $model, $id );

					$map['id'] = $id;
					$red_paper = $Model->where($map)->find();
					// dump($red_paper);
					// 发送红包
					$msg = $RedPaper->mchPay($red_paper['act_name'],$red_paper['re_openid'],$red_paper['remark'],$red_paper['total_amount'],$red_paper['wishing']);

					// dump($msg);
					$data['return_msg'] = $msg['return_msg'];
					$data['mch_billno'] = $msg['mch_billno'];
					$data['send_time'] = strtotime($msg['send_time']);

					$result = $Model->where($map)->setField($data);
					// dump($result);
					// exit();
					if($result !== false){
						$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'], $this->get_param ) );
					}else{
						$this->error('处理支付平台返回数据失败');
					}
				} else {
					$this->error ( $Model->getError () );
				}
			} else {
				$fields = get_model_attribute ( $model ['id'] );
				$this->assign ( 'fields', $fields );
				$this->meta_title = '新增' . $model ['title'];

				$templateFile || $templateFile = $model ['template_add'] ? $model ['template_add'] : '';
				$this->display ( $templateFile );
			}
		}else{
			$this->login();
		}
	}
	// 登录红包密码
	public function login()
	{
		if (IS_POST) {
			$pass = I('config');
			$pass = md5($pass['pass']);
			$config = D('Common/AddonConfig')->get( _ADDONS );
			if($config['hidpass'] == $pass){
				session('red_paper_pass',$pass);
				$this->success('密码正确');
			}else{
				$this->error('红包密码错误');
			}
		} else {
			$addon['config'] = $this->fields();
			$db_config = array('pass'=>'');
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
			$this->meta_title = '请输入红包密码';
			$this->assign('posturl',__SELF__);
			$this->display(T('Addons://RedPaper@RedPaper/login'));
		}

	}
	public function fields(){
		return array(
			'pass' => array (
                    'title' => '红包密码:',
                    'type' => 'password',
                    'value' => '',
                    'tip' => '设置的红包密码，忘记密码请联系管理员'
            ),
        );
	}

	public function edit(){
		$RedPaper = new RedPaperModel();
		if($RedPaper->canUse()){
			parent::edit($this->model);
		}else{
			$this->login();
		}
	}
	public function del(){
		$RedPaper = new RedPaperModel();
		if($RedPaper->canUse()){
			parent::del($this->model);
		}else{
			$this->login();
		}
	}

	// ajax是否可以使用红包提现
	public function canUse(){
		$RedPaper = new RedPaperModel();
		return $RedPaper->canUse();
	}

	// ajax红包提现
	public function shopAdd($jsonData,$flag = false){
		$RedPaper = new RedPaperModel();
		// flag参数用于自动发放红包，跳过密码验证
		if($RedPaper->canUse() || $flag){
			$model = $this->model;
			if ($jsonData) {
				$Model = $RedPaper;
				// 获取模型的字段信息
				$Model = $this->checkAttr ( $Model, $model ['id'] );
				if ($Model->create ($jsonData) && $id = $Model->add ()) {
					$this->_saveKeyword ( $model, $id );

					$map['id'] = $id;
					$red_paper = $Model->where($map)->find();
					// 发送红包
					$msg = $RedPaper->mchPay($red_paper['act_name'],$red_paper['re_openid'],$red_paper['remark'],$red_paper['total_amount'],$red_paper['wishing']);

					$data['return_msg'] = $msg['return_msg'];
					$data['mch_billno'] = $msg['mch_billno'];
					$data['send_time'] = strtotime($msg['send_time']);

					$result = $Model->where($map)->setField($data);
					if($result){
						if($msg['return_code'] == 'SUCCESS'){
							return array('state'=>1,'error'=>'添加'.$model ['title'].'成功');
						}else{
							return array('state'=>0,'error'=>$msg['return_msg']);
						}
					}else{
						return array('state'=>0,'error'=>'添加'.$model ['title'].'失败');
					}
				} else {
					return array('state'=>0,'error'=>$Model->getError ());
				}
			} else {
				return array('state'=>0,'error'=>'没有传入数据');
			}
		}else{
			return array('state'=>0,'error'=>'您还没有登录红包');
		}
	}
}
