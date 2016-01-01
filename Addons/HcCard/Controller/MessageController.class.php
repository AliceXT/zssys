<?php
namespace Addons\HcCard\Controller;

class MessageController extends BaseController {
    public function __construct() {
        parent::__construct ();
        $this->model = M ( 'Model' )->getByName ( 'hccard_message' );
        $this->model || $this->error ( '模型不存在！' );

        $this->assign ( 'model', $this->model );

        $this->option = M ( 'Model' )->getByName ( 'hccart' );
        $this->assign ( 'option', $this->option );

    }

    public function add(){
        if(IS_POST){
            // 添加
            // 自动补充token
            // $_POST ['token'] = get_token ();
            $_POST['state'] = "未查看";
            $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
            // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $this->model ['id'] );
            if ($Model->create () && $cart_id = $Model->add ()) {
            // 保存关键词
                $this->success ( '添加' . $this->model ['title'] . '成功！' );
            } else {
                $this->error ( $Model->getError () );
            }
        }else{
            $this->error("系统错误");
        }
    }

    public function check(){
        $map['user_id'] = $this->mid;
        $map['token'] = get_token();
        $data = M('hccard')->where($map)->find();

        if($data){
            $model_map['hccard_id'] = $data['id'];
        }else{
            $this->error("您还没有创建您的名片");
        }

        $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
        $msgs = $Model->where($model_map)->order('id desc')->select();

        if($msgs){
            foreach($msgs as &$m){
                $m['url'] = addons_url('HcCard://Message/single?id='.$m['id']);
            }

            $this->assign("msgs",$msgs);
        }else{
            // dump($Model->getLastSql());
            $this->error("还没有人给您留言");
        }

        $this->display( T ( 'Addons://HcCard@Message/check' ) );
    }

    public function single(){
        $map['id'] = I('id');

        if(!$map['id']){
            $this->error("在留言不存在");
        }

        $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
        $msg = $Model->where($map)->find();
        $this->assign("msg",$msg);
        $this->display( T ( 'Addons://HcCard@Message/single' ) );
    }
}