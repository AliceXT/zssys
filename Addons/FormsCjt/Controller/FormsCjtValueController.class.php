<?php

namespace Addons\FormsCjt\Controller;

use Addons\FormsCjt\Controller\BaseController;

class FormsCjtValueController extends BaseController {
	var $model;
	var $forms_cjt_id;
	function _initialize() {
		parent::_initialize ();
		
		$this->model = $this->getModel ( 'forms_cjt_value' );
		
		$param ['forms_cjt_id'] = $this->forms_cjt_id = intval ( $_REQUEST ['forms_cjt_id'] );
		
		$res ['title'] = '报名';
		$res ['url'] = addons_url ( 'FormsCjt://FormsCjt/lists' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$res ['title'] = '数据管理';
		$res ['url'] = addons_url ( 'FormsCjt://FormsCjtValue/lists', $param );
		$res ['class'] = 'current';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
	}
	
	// 通用插件的列表模型
	public function lists() {
		// 解析列表规则
		$fields [] = 'openid';
		$fields [] = 'cTime';
		$fields [] = 'forms_cjt_id';
		
		$girds ['field'] [0] = 'openid';
		$girds ['title'] = 'OpenId';
		$list_data ['list_grids'] [] = $girds;
		
		$girds ['field'] [0] = 'cTime|time_format';
		$girds ['title'] = '增加时间';
		$list_data ['list_grids'] [] = $girds;
		
		$map ['forms_cjt_id'] = $this->forms_cjt_id;
		$attribute = M ( 'forms_cjt_attribute' )->where ( $map )->order('sort asc, id asc')->select ();
		foreach ( $attribute as $vo ) {
			$girds ['field'] [0] = $fields [] = $vo ['name'];
			$girds ['title'] = $vo ['title'];
			$list_data ['list_grids'] [] = $girds;
			
			$attr [$vo ['name']] ['type'] = $vo ['type'];
			
			if ($vo ['type'] == 'radio' || $vo ['type'] == 'checkbox' || $vo ['type'] == 'select') {
				$extra = parse_config_attr ( $vo ['extra'] );
				if (is_array ( $extra ) && ! empty ( $extra )) {
					$attr [$vo ['name']] ['extra'] = $extra;
				}
			} elseif ($vo ['type'] == 'cascade') {
				$attr [$vo ['name']] ['extra'] = $vo ['extra'];
			}
		}
		
		$fields [] = 'id';
		$girds ['field'] [0] = 'id';
		$girds ['title'] = '操作';
		$girds ['href'] = '[EDIT]&forms_cjt_id=[forms_cjt_id]|编辑,[DELETE]&forms_cjt_id=[forms_cjt_id]|	删除';
		$list_data ['list_grids'] [] = $girds;
		
		$list_data ['fields'] = $fields;
		
		$param ['forms_cjt_id'] = $this->forms_cjt_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
		// 搜索条件
		$map = $this->_search_map ( $this->model, $fields );
		
		$page = I ( 'p', 1, 'intval' );
		$row = 20;
		
		$name = parse_name ( get_table_name ( $this->model ['id'] ), true );
		dump($name);
		$list = M ( $name )->where ( $map )->order ( 'id DESC' )->selectPage ();
		$list_data = array_merge ( $list_data, $list );
		
		foreach ( $list_data ['list_data'] as &$vo ) {
			$value = unserialize ( $vo ['value'] );
			foreach ( $value as $n => &$d ) {
				$type = $attr [$n] ['type'];
				$extra = $attr [$n] ['extra'];
				if ($type == 'radio' || $type == 'select') {
					if (isset ( $extra [$d] )) {
						$d = $extra [$d];
					}
				} elseif ($type == 'checkbox') {
					foreach ( $d as &$v ) {
						if (isset ( $extra [$v] )) {
							$v = $extra [$v];
						}
					}
					$d = implode ( ', ', $d );
				} elseif ($type == 'datetime') {
					$d = time_format ( $d );
				} elseif ($type == 'picture') {
					$d = get_cover_url ( $d );
				} elseif ($type == 'cascade') {
					$d = getCascadeTitle ( $d, $extra );
				}
			}
			
			unset ( $vo ['value'] );
			$vo = array_merge ( $vo, $value );
		}
		
		$this->assign ( $list_data );
		// dump ( $list_data );
		
		$this->display ();
	}
	
	// 通用插件的编辑模型
	public function edit() {
		$this->add ();
	}
	
	// 通用插件的增加模型
	public function add() {
		$id = I ( 'id', 0 );

		$forms_cjt = M ( 'forms_cjt' )->find ( $this->forms_cjt_id );
		$forms_cjt ['cover'] = ! empty ( $forms_cjt ['cover'] ) ? get_cover_url ( $forms_cjt ['cover'] ) : ADDON_PUBLIC_PATH . '/background.png';
		$this->assign ( 'forms_cjt', $forms_cjt );
		
		if (! empty ( $id )) {
			$act = 'save';
			
			$data = M ( get_table_name ( $this->model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			// dump($data);
			$value = unserialize ( htmlspecialchars_decode ( $data ['value'] ) );
			// dump($value);
			unset ( $data ['value'] );
			$data = array_merge ( $data, $value );
			
			$this->assign ( 'data', $data );
			// dump($data);
		} else {
			$act = 'add';
			if ($this->mid != 0 && $this->mid != '-1') {
				$map ['uid'] = $this->mid;
				$map ['forms_cjt_id'] = $this->forms_cjt_id;
				
				$data = M ( get_table_name ( $this->model ['id'] ) )->where ( $map )->find ();
				if ($data && $forms_cjt ['jump_url']) {
					redirect ( $forms_cjt ['jump_url'] );
				}
			}
		}
		
		// dump ( $forms_cjt );
		
		$map ['forms_cjt_id'] = $this->forms_cjt_id;
		$map ['token'] = get_token ();
		$fields [1] = M ( 'forms_cjt_attribute' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		
		if (IS_POST) {
			// 图片文件上传
			if(count($_FILES) && !$this->uploadpic() && !$this->uploadfile())
				$this->error ( "文件上传错误" );

			foreach ( $fields [1] as $vo ) {
				$error_tip = ! empty ( $vo ['error_info'] ) ? $vo ['error_info'] : '请正确输入' . $vo ['title'] . '的值';
				$value = $_POST [$vo ['name']];
				if (($vo ['is_must'] && empty ( $value )) || (! empty ( $vo ['validate_rule'] ) && ! M()->regex ( $value, $vo ['validate_rule'] ))) {
					$this->error ( $error_tip );
					exit ();
				}

				$post [$vo ['name']] = $vo ['type'] == 'datetime' ? strtotime ( $_POST [$vo ['name']] ) : $_POST [$vo ['name']];
				unset ( $_POST [$vo ['name']] );
			}
			
			$_POST ['value'] = serialize ( $post );
			$act == 'add' && $_POST ['uid'] = $this->mid;
			// dump($_POST);exit;
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'], $fields [1] );
			
			if ($Model->create () && $res = $Model->$act ()) {
				// 增加积分
				add_credit ( 'forms_cjt' );
				
				$param ['forms_cjt_id'] = $this->forms_cjt_id;
				$param ['id'] = $act == 'add' ? $res : $id;
				$param ['model'] = $this->model ['id'];
				$url = empty ( $forms_cjt ['jump_url'] ) ? U ( 'edit', $param ) : $forms_cjt ['jump_url'];
				
				$tip = ! empty ( $forms_cjt ['finish_tip'] ) ? $forms_cjt ['finish_tip'] : '提交成功，谢谢参与';
				$this->success ( $tip, $url, 5 );
			} else {
				$this->error ( $Model->getError () );
			}
			exit ();
		}
		
		$fields [1] [] = array (
				'is_show' => 4,
				'name' => 'forms_cjt_id',
				'value' => $this->forms_cjt_id 
		);
		
		$this->assign ( 'fields', $fields );
		$this->meta_title = '新增' . $this->model ['title'];
		
		$this->display ( 'add' );
	}
	function detail() {
		$forms_cjt = M ( 'forms_cjt' )->find ( $this->forms_cjt_id );
		$forms_cjt ['cover'] = ! empty ( $forms_cjt ['cover'] ) ? get_cover_url ( $forms_cjt ['cover'] ) : ADDON_PUBLIC_PATH . '/background.png';
		$this->assign ( 'forms_cjt', $forms_cjt );
		
		$this->display ();
	}
	
	// 通用插件的删除模型
	public function del() {
		parent::common_del ( $this->model );
	}

	public function uploadpic(){
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器

        if(count($info) == 0){
        	return false;
        }

        foreach($info as $key=>$value){
        	$_POST[$key] = $value['id'];
        }

        return true;
	}

	public function uploadfile(){
		/* 调用文件上传组件上传文件 */
		$File = D('File');
		$file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
		$info = $File->upload(
			$_FILES,
			C('DOWNLOAD_UPLOAD'),
			C('DOWNLOAD_UPLOAD_DRIVER'),
			C("UPLOAD_{$file_driver}_CONFIG")
		);

		if(count($info) == 0){
        	return false;
        }

        foreach($info as $key=>$value){
        	$_POST[$key] = $value['id'];
        }

        return true;
	}
}
