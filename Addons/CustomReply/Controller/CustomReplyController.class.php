<?php
namespace Addons\CustomReply\Controller;
use Addons\CustomReply\Controller\BaseController;
use Addons\Qrcode\Model\QrcodeModel;
use Addons\Qrcode\Controller\QrcodeController;
class CustomReplyController extends BaseController {
	var $model;
	function _initialize() {
		$this->model = $this->getModel ( 'custom_reply_news' );
		parent::_initialize ();
	}
	// 通用插件的列表模型
	public function lists() {
		$map ['token'] = get_token ();
		session ( 'common_condition', $map );
		
		$list_data = $this->_get_model_list ( $this->model );
		
		// 分类数据
		$map ['is_show'] = 1;
		$list = M ( 'weisite_category' )->where ( $map )->field ( 'id,title' )->select ();
		$cate [0] = '';
		foreach ( $list as $vo ) {
			$cate [$vo ['id']] = $vo ['title'];
		}
		
		foreach ( $list_data ['list_data'] as &$vo ) {
			$vo ['cate_id'] = intval ( $vo ['cate_id'] );
			$vo ['cate_id'] = $cate [$vo ['cate_id']];
		}
		$this->assign ( $list_data );
		// dump ( $list_data );
		
		$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( $templateFile );
	}
	// 通用插件的编辑模型
	public function edit() {
		$model = $this->model;
		$id = I ( 'id' );
		
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $Model->save ()) {
				$this->_saveKeyword ( $model, $id, 'custom_reply_news' );
				
				$this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields [1] as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}
			
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
			
			$this->display ();
		}
	}
	
	// 通用插件的增加模型
	public function add() {
		$model = $this->model;
		$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
		
		if (IS_POST) {
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );
			if ($Model->create () && $id = $Model->add ()) {
				$this->_saveKeyword ( $model, $id, 'custom_reply_news' );
				
				$this->success ( '添加' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			
			$extra = $this->getCateData ();
			if (! empty ( $extra )) {
				foreach ( $fields [1] as &$vo ) {
					if ($vo ['name'] == 'cate_id') {
						$vo ['extra'] .= "\r\n" . $extra;
					}
				}
			}
			
			$this->assign ( 'fields', $fields );
			$this->meta_title = '新增' . $model ['title'];
			
			$this->display ();
		}
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}
	
	// 获取所属分类
	function getCateData() {
		$map ['is_show'] = 1;
		$map ['token'] = get_token ();
		$list = M ( 'weisite_category' )->where ( $map )->select ();
		foreach ( $list as $v ) {
			$extra .= $v ['id'] . ':' . $v ['title'] . "\r\n";
		}
		return $extra;
	}
	
	// 内容页面
	function detail() {
		// 设置分销关系,请确保二维码插件运行正常
		// $Qrcode = new QrcodeModel();
		// I('fid') && $Qrcode->tobeaDis(I('fid'));
		$Qrcode = new QrcodeController();
		I('fid') && $path = $Qrcode->getMergedImg(I('fid'));
		$pos = strpos($path, '/Uploads');
		$qrcode_img = substr($path, $pos);
		// exit(dump($qrcode_img));

		$qrcode_img && $this->assign("qrcode_img",$qrcode_img);

		$map ['id'] = I ( 'get.id', 0, 'intval' );
		$info = M ( 'custom_reply_news' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		M ( 'custom_reply_news' )->where ( $map )->setInc ( 'view_count' );
		
		// 增加积分
		add_credit ( 'custom_reply', 300 );

		$this->set_jsapi_config();

		// 设置分享内容
		$this->_set_share($info);
		
		$this->display ();
	}
	// 增加点赞数量
	function addLike(){
		$map ['id'] = I ( 'post.id', 0, 'intval' );
		$info = M ( 'custom_reply_news' )->where ( $map )->find ();
		$this->assign ( 'info', $info );
		
		$result = M ( 'custom_reply_news' )->where ( $map )->setInc ( 'like_count' );
		
		if($result === false){
			$this->error("没有找到该文章");
		}else{
			$this->success("感谢点赞");
		}
	}
	// 设置分享内容
	private function _set_share($info){
        $share['title'] = $info['title'];
        $share['desc'] = $info['intro'];
        $share['pic'] = $info['cover'];

        $param['fid'] = get_openid();
        $param['token'] = get_token();
        $param['id'] = I('id');
        $url = addons_url("CustomReply://CustomReply/detail",$param);
        $share['link'] = $url;

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
}
