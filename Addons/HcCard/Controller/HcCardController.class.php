<?php
namespace Addons\HcCard\Controller;

class HcCardController extends BaseController{
    public function __construct() {
        parent::__construct ();
        $this->model = M ( 'Model' )->getByName ( $_REQUEST ['_controller'] );
        $this->model || $this->error ( '模型不存在！' );

        $this->assign ( 'model', $this->model );
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
        $this->display (  );
    }

    public function edit() {
        // 获取模型信息
        $id = I ( 'id', 0, 'intval' );

        if (IS_POST) {
            // $_POST ['mTime'] = time ();
            $_POST['user_id'] = $this->mid;

            $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
            // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $this->model ['id'] );
            // D ( 'Common/Keyword' )->del( 'HcCard',I ( 'post.id' ) );
            if ($Model->create () && $Model->save ()) {

                // 保存关键词
                // D ( 'Common/Keyword' )->set ( I ( 'post.keyword' ), 'HcCard', I ( 'post.id' ) );

                // $this->createQRcode($_POST['id']);

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

            $this->assign ( 'fields', $fields );
            $this->assign ( 'data', $data );
            $this->meta_title = '编辑' . $this->model ['title'];

            $this->display ();
        }
    }

    public function createQRcode($id) {



    }

    public function nextStep(){
        $jump = addons_url("HcCard://Company/add");
        $this->addCard($jump);
    }

    public function addCard($jump = ""){
        if(IS_POST){
            // 图片文件上传
            if(count($_FILES) && !$this->uploadpic() && !$this->uploadfile())
                $this->error ( "文件上传错误" );

            if(I('id') != ""){
                //修改链接，如果没有http://则加上
                if(substr($_POST['company_url'],0,7) != "http://" && substr($_POST['company_url'],0,8) != "https://" ){
                    $_POST['company_url'] = "http://".$_POST['company_url'];
                }
                // 修改
                $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
                // 获取模型的字段信息
                $Model = $this->checkAttr ( $Model, $this->model ['id'] );
                // D ( 'Common/Keyword' )->del( 'HcCard',I ( 'post.id' ) );
                if ($Model->create () && ($Model->save () || $jump)) {

                // 保存关键词
                    // D ( 'Common/Keyword' )->set ( I ( 'post.keyword' ), 'HcCard', I ( 'post.id' ) );

                // $this->createQRcode($_POST['id']);
                    // $param['id'] = I('id');
                    // $url = addons_url('HcCard://HcCard/show',$param);
                    // $url = addons_url("HcCard://Company/add");
                    // $this->success ( '保存' . $this->model ['title'] . '成功！' ,$url);
                    $this->success ( '保存' . $this->model ['title'] . '成功！',$jump);
                } else {
                    $this->error ( $Model->getError () );
                }
            }else{
                // 添加
                // 自动补充token
                $_POST ['token'] = get_token ();
                $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
                // 获取模型的字段信息
                $Model = $this->checkAttr ( $Model, $this->model ['id'] );
                if ($Model->create () && $cart_id = $Model->add ()) {
                // 保存关键词
                    // D ( 'Common/Keyword' )->set ( I ( 'keyword' ), 'HcCard', $cart_id );
                    // $param['id'] = $cart_id;
                    // $url = addons_url('HcCard://HcCard/show',$param);
                    $this->success ( '添加' . $this->model ['title'] . '成功！' ,$jump);
                } else {
                    $this->error ( $Model->getError () );
                }
            }

        }else{
            $cart_fields = get_model_attribute ( $this->model ['id'] );
            $this->assign ( 'fields', $cart_fields );

            $this->meta_title = '新增' . $this->model ['title'];
            // 查找相同token,user_id的资料
            $data = M('hccard')->where("token='".get_token()."' and user_id='".$this->mid."'")->find();
            $this->assign("mid",$this->mid);
            $this->assign("data",$data);

            // 添加表单信息
            $pic_id = I('pic_id');
            $forms['cover'] = get_picture_url($pic_id);
            $forms['title'] = "个人信息";
            $forms['intro'] = "第一步：填写个人信息，请认真填写个人资料";
            $this->assign("forms",$forms);

            $this->assign("next_url",addons_url("HcCard://HcCard/nextStep"));

            $this->display ( T ( 'Addons://HcCard@HcCard/addCard' ) );
        }
    }

    public function check(){
        $card_map['token']= get_token();
        $card_map['user_id'] = $this->mid;
        $hccard = M('hccard')->where($card_map)->find();
        $hccard_id = $hccard['id'];

        // 公司介绍
        $Model = M ( 'Model' )->getByName ( 'hccard_company' );
        $map['hccard_id'] = strval($hccard_id);
        $opts = M($Model['name'])->where($map)->select();
        foreach($opts as &$opt){
            $opt['url'] = addons_url('HcCard://Company/edit?id='.$opt['id']);//编辑地址
            $opt['del_url'] = addons_url('HcCard://Company/del?id='.$opt['id']."&model=".$Model['id']);//删除地址
        }
        $this->assign("company_info",$opts);

        // 公司链接
        $Model = M ( 'Model' )->getByName ( 'hccard_link' );
        // $map['hccard_id'] = strval($hccard_id);
        $opts = M($Model['name'])->where($map)->select();
        foreach($opts as &$opt){
            $opt['url'] = addons_url('HcCard://Link/edit?id='.$opt['id']);//编辑地址
            $opt['del_url'] = addons_url('HcCard://Link/del?id='.$opt['id']."&model=".$Model['id']);//删除地址
        }
        $this->assign("company_link",$opts);

        //名片留言
        $Model = M ( 'Model' )->getByName ( 'hccard_message' );
        $msgs = M($Model['name'])->where($map)->order('id desc')->select();
        foreach($msgs as &$m){
            $m['url'] = addons_url('HcCard://Message/single?id='.$m['id']);
        }
        $this->assign("message",$msgs);

        $this->display( T ( 'Addons://HcCard@HcCard/check' ) );
    }

    public function add() {
        if (IS_POST) {
            // 自动补充token
            $_POST ['token'] = get_token ();
            $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
            // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $this->model ['id'] );
            if ($Model->create () && $cart_id = $Model->add ()) {
                // 保存关键词
                // D ( 'Common/Keyword' )->set ( I ( 'keyword' ), 'HcCard', $cart_id );

                $this->createQRcode($Model->getLastInsID());

                $this->success ( '添加' . $this->model ['title'] . '成功！', U ( 'lists?model=' . $this->model ['name'] ) );
            } else {
                $this->error ( $Model->getError () );
            }
        } else {

            $cart_fields = get_model_attribute ( $this->model ['id'] );
            $this->assign ( 'fields', $cart_fields );

            $this->meta_title = '新增' . $this->model ['title'];
            $this->display ( $this->model ['template_add'] ? $this->model ['template_add'] : T ( 'Addons://HcCart@HcCart/add' ) );
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
        $card_id = I ( 'id', 0, 'intval' );
        $openid = get_openid ();
        $token = get_token ();

        $info = $this->_getCartInfo ( $card_id );//获取名片个人信息内容
        $attrs = $this->_getCompanyInfo( $card_id );//获取公司内容
        $links = $this->_getLinkInfo( $card_id );//获取公司链接

        $this->display ( T ( 'Addons://HcCard@HcCard/show' ) );
    }
    function _getLinkInfo( $id ){
        // 检查ID是否合法
        if (empty ( $id ) || 0 == $id) {
            $this->error ( "错误的名片ID" );
        }

        $map['hccard_id'] = intval($id);
        $links = M( 'hccard_link' )->where( $map )->order('id asc')->select();

        $this->assign( 'links', $links);

        return $links;
    }

    function _getCompanyInfo ( $id ){
        // 检查ID是否合法
        if (empty ( $id ) || 0 == $id) {
            $this->error ( "错误的名片ID" );
        }

        $map['hccard_id'] = intval($id);
        $attrs = M( 'hccard_company' )->where( $map )->order('id asc')->select();

        $this->assign( 'attrs', $attrs);

        return $attrs;

    }

    function _getCartInfo ( $id ) {
// 检查ID是否合法
        if (empty ( $id ) || 0 == $id) {
            $this->error ( "错误的名片ID" );
        }

        $map ['id'] = intval ( $id );
        $info = M ( 'hccard' )->where ( $map )->find ();

        $this->assign ( 'info', $info );

        return $info;
    }

    public function map(){
        $this->display( T ( 'Addons://HcCard@HcCard/map' ) );
    }
}
