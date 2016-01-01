<?php
namespace Addons\HcCard\Controller;
use Home\Controller\AddonsController;

class BaseController extends AddonsController {
	protected $model;
    protected $option;

	public function __construct() {
        parent::__construct ();
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
}