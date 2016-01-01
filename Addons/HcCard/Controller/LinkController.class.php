<?php
namespace Addons\HcCard\Controller;

class LinkController extends BaseController {
	protected $hccard_id;
    public function __construct() {
        parent::__construct ();
        $this->model = M ( 'Model' )->getByName ( 'hccard_link' );
        $this->model || $this->error ( '模型不存在！' );

        $this->assign ( 'model', $this->model );

        $this->option = M ( 'Model' )->getByName ( 'hccart' );
        $this->assign ( 'option', $this->option );

        $map['user_id'] = $this->mid;
        $map['token'] = get_token();
        $data = M('hccard')->where($map)->find();

        if($data){
        	$this->hccard_id = $data['id'];
        }else{
        	$this->error("您还没有创建您的名片");
        }
    }

    public function nextStep(){
        $map['user_id'] = $this->mid;
        $card = M('hccard')->where($map)->find();
        $param['id'] = $card['id'];

        $jump = addons_url("HcCard://HcCard/show",$param);
        $this->add($jump);
    }

    public function add($jump = ""){
        if(IS_POST){
            // 图片文件上传
            if(count($_FILES) && !$this->uploadpic() && !$this->uploadfile())
                $this->error ( "文件上传错误" );

            $_POST['hccard_id'] = $this->hccard_id;

            //修改链接，如果没有http://则加上
            if(substr($_POST['url'],0,7) != "http://" && substr($_POST['url'],0,8) != "https://" ){
                $_POST['url'] = "http://".$_POST['url'];
            }
            // 添加
            // 自动补充token
            $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
            // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $this->model ['id'] );
            if ($Model->create () && $cart_id = $Model->add ()) {
                // 保存关键词
                $this->success ( '添加' . $this->model ['title'] . '成功！' ,$jump);
            } else {
                if($jump){
                    redirect($jump);
                }
                $this->error ( $Model->getError () );
            }
        }else{
            $cart_fields = get_model_attribute ( $this->model ['id'] );
            $this->assign ( 'fields', $cart_fields );

            $this->meta_title = '新增' . $this->model ['title'];
            // 查找相同token,user_id的资料
            // $data = M('hccard')->where("token='".get_token()."' and user_id='".$this->mid."'")->find();
            // $this->assign("mid",$this->mid);
            // $this->assign("data",$data);
            $this->assign("post_url",U('add?model='.$model['id']));


            $this->assign("next_url",addons_url("HcCard://Link/nextStep"));

            $pic_id = I('pic_id');
            $forms['cover'] = get_picture_url($pic_id);
            $forms['title'] = "公司链接";
            $forms['intro'] = "第三步：填写公司链接，公司链接需要输入链接全部，包括前面的<b>http://</b>，没有公司链接可点击下一步，查看名片";
            $this->assign("forms",$forms);

            $this->display ( T ( 'Addons://HcCard@Company/add' ) );
        }
    }

    public function check() {
        $Model = $this->model;
        $map['hccard_id'] = strval($this->hccard_id);
        $opts = M($this->model['name'])->where($map)->select();
        foreach($opts as &$opt){
            $opt['url'] = addons_url('HcCard://Link/edit?id='.$opt['id']);//编辑地址
            $opt['del_url'] = addons_url('HcCard://Link/del?id='.$opt['id']."&model=".$this->model['id']);//删除地址
        }
        $this->assign("opts",$opts);
        $this->display( T ( 'Addons://HcCard@Company/check' ) );
    }

    public function edit() {
        if(IS_POST){
            // 图片文件上传
            if(count($_FILES) && !$this->uploadpic() && !$this->uploadfile())
                $this->error ( "文件上传错误" );

            // $_POST['hccard_id'] = $this->hccard_id;

            // 修改
            $Model = D ( parse_name ( get_table_name ( $this->model ['id'] ), 1 ) );
                // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $this->model ['id'] );
            if ($Model->create () && $Model->save ()) {
                $this->success ( '保存' . $this->model ['title'] . '成功！' );
            } else {
                $this->error ( $Model->getError () );
            }
        }else{
            $cart_fields = get_model_attribute ( $this->model ['id'] );
            $this->assign ( 'fields', $cart_fields );

            $this->meta_title = '新增' . $this->model ['title'];
            $Model = $this->model;
            $map['hccard_id'] = $this->hccard_id;
            $data = M($this->model['name'])->where($map)->find();
            // $this->assign("mid",$this->mid);
            $this->assign("data",$data);
            $this->assign("post_url",U('edit?model='.$model['id']));

            $this->display ( T ( 'Addons://HcCard@Company/add' ) );
        }
    }

    public function del(){
        $map['id'] = I('id');
        if(M($this->model['name'])->delete(intval(I('id')))){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
}