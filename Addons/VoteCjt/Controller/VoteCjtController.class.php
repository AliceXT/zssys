<?php

namespace Addons\VoteCjt\Controller;
use Home\Controller\AddonsController;

class VoteCjtController extends AddonsController{
	protected $model;
	protected $option;

	public function __construct() {
		parent::__construct ();
		header("Content-Type:text/html; charset=utf-8");

		$res ['title'] = '投票列表';
		$res ['url'] = addons_url ( 'VoteCjt://VoteCjt/lists' );
		$res ['class'] = 'current';
		$nav [] = $res;

		$res ['title'] = '自动生成投票';
		$res ['url'] = addons_url ( 'VoteCjt://VoteCjt/autoToVote' );
		$res ['class'] = '';
		$nav [] = $res;
		
		$this->assign ( 'nav', $nav );
		// $this->model = M ( 'Model' )->getByName ( $_REQUEST ['_controller'] );
		$this->model = M ( 'Model' )->getByName ( 'vote' );
		$this->model || $this->error ( '模型不存在！' );
		
		$this->assign ( 'model', $this->model );
		
		$this->option = M ( 'Model' )->getByName ( 'vote_option' );
		$this->assign ( 'option', $this->option );
		
	}

	public function nulldeal(){
		$url = addons_url ( 'VoteCjt://VoteCjt/lists' );
		redirect ( $url );
	}
	/**
	 * 显示指定模型列表数据
	 */
	public function lists() {
		$page = I ( 'p', 1, 'intval' ); // 默认显示第一页数据
		                                
		// 解析列表规则
		$list_data = $this->_list_grid ( $this->model );
		$grids = $list_data ['list_grids'];
		$fields = $list_data ['fields'];
		
		// 关键字搜索
		$map ['token'] = get_token ();
		$key = $this->model ['search_key'] ? $this->model ['search_key'] : 'title';
		if (isset ( $_REQUEST [$key] )) {
			$map [$key] = array (
					'like',
					'%' . htmlspecialchars ( $_REQUEST [$key] ) . '%' 
			);
			unset ( $_REQUEST [$key] );
		}
		// 条件搜索
		foreach ( $_REQUEST as $name => $val ) {
			if (in_array ( $name, $fields )) {
				$map [$name] = $val;
			}
		}
		$row = empty ( $this->model ['list_row'] ) ? 20 : $this->model ['list_row'];
		
		// 读取模型数据列表
		
		empty ( $fields ) || in_array ( 'id', $fields ) || array_push ( $fields, 'id' );
		$name = parse_name ( get_table_name ( $this->model ['id'] ), true );
		$data = M ( $name )->field ( empty ( $fields ) ? true : $fields )->where ( $map )->order ( 'id DESC' )->page ( $page, $row )->select ();
		
		/* 查询记录总数 */
		$count = M ( $name )->where ( $map )->count ();
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$this->assign ( '_page', $page->show () );
		}
		
		$this->assign ( 'list_grids', $grids );
		$this->assign ( 'list_data', $data );
		$this->meta_title = $this->model ['title'] . '列表';
		$this->display ( T ( 'Addons://VoteCjt@VoteCjt/lists' ) );
	}
	public function del() {
		$ids = I ( 'id', 0 );
		if (empty ( $ids )) {
			$ids = array_unique ( ( array ) I ( 'ids', 0 ) );
		}
		if (empty ( $ids )) {
			$this->error ( '请选择要操作的数据!' );
		}
		
		$Model = M ( get_table_name ( $this->model ['id'] ) );
		$map = array (
				'id' => array (
						'in',
						$ids 
				) 
		);
		$map ['token'] = get_token ();
		if ($Model->where ( $map )->delete ()) {
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	public function edit() {
		// 获取模型信息
		$id = I ( 'id', 0, 'intval' );
		
		if (IS_POST) {
			$_POST ['mTime'] = time ();
			
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $Model->save ()) {
				// 增加选项
				D ( 'Addons://VoteCjt/VoteOption' )->set ( I ( 'post.id' ), I ( 'post.' ) );
				
				// 保存关键词
				D ( 'Common/Keyword' )->set ( I ( 'post.keyword' ), 'VoteCjt', I ( 'post.id' ) );
				
				$this->success ( '保存' . $this->model ['title'] . '成功！', U ( 'lists?model=' . $this->model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			$fields = get_model_attribute ( $this->model ['id'] );
			
			// 获取数据
			$data = M ( get_table_name ( $this->model ['id'] ) )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$token = get_token ();
			if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
				$this->error ( '非法访问！' );
			}
			
			$option_list = M ( 'vote_option' )->where ( 'vote_id=' . $id )->order ( '`order` asc' )->select ();
			$this->assign ( 'option_list', $option_list );
			
			$this->assign ( 'fields', $fields );
			$this->assign ( 'data', $data );
			$this->meta_title = '编辑' . $this->model ['title'];
			$this->display ( T ( 'Addons://VoteCjt@VoteCjt/edit' ) );
		}
	}
	public function add() {
		if (IS_POST) {
			// 自动补充token
			$_POST ['token'] = get_token ();
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );
			if ($Model->create () && $vote_id = $Model->add ()) {
				// 增加选项
				D ( 'Addons://VoteCjt/VoteOption' )->set ( $vote_id, I ( 'post.' ) );
				
				// 保存关键词
				D ( 'Common/Keyword' )->set ( I ( 'keyword' ), 'VoteCjt', $vote_id );
				
				$this->success ( '添加' . $this->model ['title'] . '成功！', U ( 'lists?model=' . $this->model ['name'] ) );
			} else {
				$this->error ( $Model->getError () );
			}
		} else {
			
			$vote_fields = get_model_attribute ( $this->model ['id'] );
			$this->assign ( 'fields', $vote_fields );
			// 选项表
			$option_fields = get_model_attribute ( $this->option ['id'] );
			$this->assign ( 'option_fields', $option_fields );
			
			$this->meta_title = '新增' . $this->model ['title'];
			$this->display ( $this->model ['template_add'] ? $this->model ['template_add'] : T ( 'Addons://VoteCjt@VoteCjt/add' ) );
		}
	}
	protected function checkAttr($Model, $model_id) {
		$fields = get_model_attribute ( $model_id, false );
		$validate = $auto = array ();
		foreach ( $fields as $key => $attr ) {
			if ($attr ['is_must']) { // 必填字段
				$validate [] = array (
						$attr ['name'],
						'require',
						$attr ['title'] . '必须!' 
				);
			}
			// 自动验证规则
			if (! empty ( $attr ['validate_rule'] ) || $attr ['validate_type'] == 'unique') {
				$validate [] = array (
						$attr ['name'],
						$attr ['validate_rule'],
						$attr ['error_info'] ? $attr ['error_info'] : $attr ['title'] . '验证错误',
						0,
						$attr ['validate_type'],
						$attr ['validate_time'] 
				);
			}
			// 自动完成规则
			if (! empty ( $attr ['auto_rule'] )) {
				$auto [] = array (
						$attr ['name'],
						$attr ['auto_rule'],
						$attr ['auto_time'],
						$attr ['auto_type'] 
				);
			} elseif ('checkbox' == $attr ['type']) { // 多选型
				$auto [] = array (
						$attr ['name'],
						'arr2str',
						3,
						'function' 
				);
			} elseif ('datetime' == $attr ['type']) { // 日期型
				$auto [] = array (
						$attr ['name'],
						'strtotime',
						3,
						'function' 
				);
			}
		}
		return $Model->validate ( $validate )->auto ( $auto );
	}
	
	/**
	 * **************************微信上的操作功能************************************
	 */
	function show() {
		$vote_id = I ( 'id', 0, 'intval' );
		$this->voteHead($vote_id);
		
		$this->display ( T ( 'Addons://VoteCjt@VoteCjt/show' ) );
		
	}
	public function voteHead($vote_id){
		// 投票表头信息
		// $vote_id = I('vote_id');
		$openid = get_openid ();
		$token = get_token ();
		
		$info = $this->_getVoteInfo ( $vote_id );
		
		$canJoin = ! empty ( $openid ) && ! empty ( $token ) && ! ($this->_is_overtime ( $vote_id )) && ! ($this->_is_join ( $vote_id, $this->mid, $token ));
		$this->assign ( 'canJoin', $canJoin );
		
		$test_id = intval ( $_REQUEST ['test_id'] );
		$this->assign ( 'event_url', event_url ( '投票', $vote_id ) );
		$this->assign('mid', $this->mid);

		$map['public_id'] = $token;
		$owner = M('member_public')->where($map)->find();
		$this->assign('owner',$owner['public_name']);

		$this->assign("Qcode","365");//关注用二维码
	}

	public function find_order(){
		$map['order'] = I('opt_order');
		$map['vote_id'] = I('vote_id');

		$opt = M('vote_option')->where($map)->select();

		if(count($opt) == 0)
			$this->error("没有该序号的参赛者");

		$url = U ( 'single', 'vote_id=' . I('vote_id') . '&opt_id=' . $opt[0]['id']);
		redirect ( $url );
	}
	public function single(){
		$vote_id = I('vote_id');
		// 得到forms_id
		$result = M('vote_forms')->where('vote_id='.$vote_id)->select();
		$forms_id = $result[0]['forms_id'];

		$this->voteHead($vote_id);

		// 投票单个详细
		$opt_link_map['opt_id'] = I('opt_id');

		$result = M('vote_option_link')->where($opt_link_map)->select();
		if(count($result) != 1){
			$this->display();//数据出错，直接输出;
		}
		$id = $result[0]['value_id'];
		
		$forms = M ( 'forms_cjt' )->find ( $forms_id );
		$forms ['cover'] = ! empty ( $forms ['cover'] ) ? get_cover_url ( $forms ['cover'] ) : ADDON_PUBLIC_PATH . '/background.png';
		$this->assign ( 'forms', $forms );
		
		if (! empty ( $id )) {
			$act = 'save';
			
			$data = M ( 'forms_cjt_value' )->find ( $id );
			$data || $this->error ( '数据不存在！' );
			
			$value = unserialize ( htmlspecialchars_decode ( $data ['value'] ) );
			unset ( $data ['value'] );
			$data = array_merge ( $data, $value );
			
			$this->assign ( 'data', $data );
		} else {
			$act = 'add';
			if ($this->mid != 0 && $this->mid != '-1') {
				$map ['uid'] = $this->mid;
				$map ['forms_id'] = $this->forms_id;
				
				$data = M ( get_table_name ( $this->model ['id'] ) )->where ( $map )->find ();
				if ($data && $forms ['jump_url']) {
					redirect ( $forms ['jump_url'] );
				}
			}
		}
		
		$map ['forms_cjt_id'] = $forms_id;
		$map ['token'] = get_token ();
		$fields [1] = M ( 'forms_cjt_attribute' )->where ( $map )->order ( 'sort asc, id asc' )->select ();
		
		$this->assign ( 'fields', $fields );

		$next_opt_id = (int)I('opt_id') + 1;
		$pre_opt_id = (int)I('opt_id') - 1;

		$result = M('vote_option')->where('id='.$next_opt_id.' and vote_id='.$vote_id)->select();
		if(count($result) == 1){
			$this->assign("next_opt_id",$next_opt_id);
		}
		$result = M('vote_option')->where('id='.$pre_opt_id.' and vote_id='.$vote_id)->select();
		if(count($result) == 1){
			$this->assign("pre_opt_id",$pre_opt_id);

		}

		$this->display('single');
	}

	function _getVoteInfo($id) {
		// 检查ID是否合法
		if (empty ( $id ) || 0 == $id) {
			$this->error ( "错误的投票ID" );
		}
		
		$map ['id'] = $map2 ['vote_id'] = intval ( $id );
		$info = M ( 'vote' )->where ( $map )->find ();
		//添加主办方链接
		$info['sponsor'] = $info['sponsor']? '<a>'.$info['sponsor'].'</a>' : '<a>芳芳庄园10000</a>';
		//设置投票表尾
		// $info['end'] = $info['end']? $info['end'] : '我是投票表尾';

		
		$opts = M ( 'vote_option' )->where ( $map2 )->order ( '`order` asc' )->select ();
		$info['member'] = count($opts);
		foreach ( $opts as $p ) {
			$total += $p ['opt_count'];
		}
		foreach ( $opts as &$vo ) {
			$vo ['percent'] = round ( $vo ['opt_count'] * 100 / $total, 1 );
		}
		$this->assign ( 'opts', $opts );
		$this->assign ( 'num_total', $total );

		// 对选项进行排名
		$f = null;
		$rank_number = 3;
		foreach($opts as $k=>$p){
			$i = 0;
			while($f[$i] !== NULL && (int)$opts[$f[$i]]['opt_count'] > (int)$p['opt_count'] && $i < $rank_number){
				$i++;
			}
			if($i == $rank_number){
				continue;
			}
			$s = $i;
			for($i = $rank_number-1; $i > $s; $i--){
				$f[$i] = $f[$i-1];
			}
			$f[$s] = $k;
		}
		$str = "目前排名：";
		for($i = 0;$i < $rank_number && $f[$i] !== NULL ; $i++){
			$opt = $opts[$f[$i]];
			$url = U('single','opt_id='.$opt['id']."&vote_id=".$opt['vote_id']);
			$html = " 第".($i+1)."名：<a href=".$url.">".$opt['name']."</a>";
			$str .= $html;
		}
		$info['rank'] = $str;
		$this->assign ( 'info', $info );
		return $info;
	}
	// 用户投票信息
	function join() {
		$token = get_token ();
		$opts_ids = array_filter ( I ( 'post.optArr' ) );
		
		$vote_id = intval ( $_POST ["vote_id"] );
		// 检查ID是否合法
		if (empty ( $vote_id ) || 0 == $vote_id) {
			$this->error ( "错误的投票ID" );
		}
		//只能关注微信号才能投票
		if($this->mid < 0 ) {
            $this->error('请先关注微信号才能投票');
        }
		if ($this->_is_overtime ( $vote_id )) {
			$this->error ( "请在指定的时间内投票" );
		}
		if ($this->_is_join ( $vote_id, $this->mid, $token )) {
			$this->error ( "您已经投过,请不要重复投" );
		}
		if (empty ( $_POST ['optArr'] )) {
			$this->error ( "请先选择投票项" );
		}
		// 如果没投过，就添加
		$data ["user_id"] = $this->mid;
		$data ["vote_id"] = $vote_id;
		$data ["token"] = $token;
		$data ["options"] = implode ( ',', $opts_ids );
		$data ["cTime"] = time ();
		$addid = M ( "vote_log" )->add ( $data );
		// 投票选项信息的num+1
		foreach ( $opts_ids as $v ) {
			$v = intval ( $v );
			$res = M ( "vote_option" )->where ( 'id=' . $v )->setInc ( "opt_count" );
		}
		
		// 投票信息的vote_count+1
		$res = M ( "vote" )->where ( 'id=' . $vote_id )->setInc ( "vote_count" );
		
		// 增加积分
		add_credit ( 'vote' );
		
		// 连续投票
		$next_id = M ( "vote" )->where ( 'id=' . $vote_id )->getField ( "next_id" );
		if (! empty ( $next_id )) {
			$vote_id = $next_id;
		}
		
		redirect ( U ( 'show', 'id=' . $vote_id ) );
	}
	//已过期返回 true ,否则返回 false
	private function _is_overtime($vote_id) {
		// 先看看投票期限过期与否
		$the_vote = M ( "vote" )->where ( "id=$vote_id" )->find ();
		
		if(!empty($the_vote['start_date']) && $the_vote ['start_date'] > NOW_TIME) return ture;
		
		$deadline = $the_vote ['end_date'] + 86400;
		if(!empty($the_vote['end_date']) && $deadline <= NOW_TIME) return ture;
		
		return false;
	}
	private function _is_join($vote_id, $user_id, $token) {
		// $vote_limit = M ( 'vote' )->where ( 'id=' . $vote_id )->getField ( 'vote_limit' );
		$vote_limit = 1;
		$list = M ( "vote_log" )->where ( "vote_id=$vote_id AND user_id='$user_id' AND token='$token' AND options <>''" )->select ();
		$count = count ( $list );
		$info = array_pop ( $list );
		if ($info) {
			$joinData = explode ( ',', $info ['options'] );
			$this->assign ( 'joinData', $joinData );
		}
		if ($count >= $vote_limit) {
			return true;
		}
		return false;
	}

	/**
	**********************  自动生成投票******************************************
	**/
	public function autoToVote(){
		if (IS_POST) {
			// 自动补充token
			$_POST ['token'] = get_token ();
			$Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $this->model ['id'] );

			if ($Model->create () && $vote_id = $Model->add ()) {
				// 增加选项
				D ( 'Addons://VoteCjt/VoteOption' )->setAuto ( $vote_id, I ( 'post.' ) );
				
				// 保存关键词
				D ( 'Common/Keyword' )->set ( I ( 'keyword' ), 'VoteCjt', $vote_id );

				// 保存表单关系
				$data['vote_id'] = $vote_id;
				$data['forms_id'] = $_POST['forms_id'];
				if(M('vote_forms')->add($data)){
					$this->success ( '添加' . $this->model ['title'] . '成功！', U ( 'lists?model=' . $this->model ['name'] ) );
				}
				
			} else {
				$this->error ( $Model->getError () );
			}
		}else {
			
			$vote_fields = get_model_attribute ( $this->model ['id'] );
			$this->assign ( 'fields', $vote_fields );
			// 选项表
			$option_fields = get_model_attribute ( $this->option ['id'] );
			$this->assign ( 'option_fields', $option_fields );
			
			// 通用列表填入选项
			$table['name'] = "SelectedTable";
			$forms = M('forms_cjt')->field('id,title')->select();
			foreach($forms as $f){
				$arr[$f['id']] = $f['title'];
			}
			$table['extra'] = $arr;
			$this->assign("table",$table);

			$this->meta_title = '新增' . $this->model ['title'];

			$this->assign("post_url",U('autoToVote'));
			$this->display ( T ( 'Addons://VoteCjt@VoteCjt/autoToVote' ) );
		}
	}

	// 读取表单属性列表
	public function readAttr(){
		if(IS_POST){
			$id = I('id');
			if(empty($id)){
				exit("输入没有选择表单");
			}
			$map['forms_cjt_id'] = $id;
			$data = M('forms_cjt_attribute')->field('name,title')->where($map)->select();
			if(empty($data)){
				exit("该表单没有设置字段");
			}
			exit(json_encode($data));
		}else{
			exit("ERROR");
		}
	}

	// 自动生成选项
	public function createVote(){
		if(IS_POST){
			$title = I('title');
			$cover = I('cover');
			$type = I('type');//0为文字投票，1为图片投票
			$forms_id = I('forms_id');
			if(empty($title))
				exit("标题对应的字段不能为空");
			if($type && empty($cover))
				exit("图片不能为空");
			// 读取数据
			if($type){
				$data = $this->getData($forms_id,$title,$cover);
			}else{
				$data = $this->getData($forms_id,$title);
			}
			exit(json_encode($data));
		}else{
			exit("ERROR");
		}
	}

	function getData($forms_id,$title,$cover = ""){
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
		
		$map ['forms_cjt_id'] = $forms_id;
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
		
		$param ['forms_cjt_id'] = $forms_cjt_id;
		$param ['model'] = $this->model ['id'];
		$add_url = U ( 'add', $param );
		$this->assign ( 'add_url', $add_url );
		
		// 搜索条件
		// $map = $this->_search_map ( $this->model, $fields );
		
		$page = I ( 'p', 1, 'intval' );
		$row = 20;
		
		$map = null;
		$map['forms_cjt_id'] = $forms_id;
		$list = M ( "forms_cjt_value" )->where ( $map )->order ( 'id asc' )->selectPage ();
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
					// $d = get_cover_url ( $d );
				} elseif ($type == 'cascade') {
					$d = getCascadeTitle ( $d, $extra );
				}
			}
			
			unset ( $vo ['value'] );
			$vo = array_merge ( $vo, $value );
		}
		
		// 处理$list_data['list_data']里的数据
		$data = $list_data['list_data'];
		foreach($data as $i=>$v){
			$arr[$i]['id'] = $v['id'];
			$arr[$i]['title'] = $v[$title];
			if(!empty($cover)) $arr[$i]['cover'] = get_cover_url($v[$cover]);
			$arr[$i]['pic_id'] = $v[$cover];
		}
		return $arr;
	}
}
