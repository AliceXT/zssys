<?php
namespace Addons\HcCard\Controller;

class VcfController extends BaseController {
    public function __construct() {
        parent::__construct ();
        $this->model = M ( 'Model' )->getByName ( 'hccard_company' );
        $this->model || $this->error ( '模型不存在！' );

        $this->assign ( 'model', $this->model );

        $this->option = M ( 'Model' )->getByName ( 'hccart' );
        $this->assign ( 'option', $this->option );
    }

    public function download(){
        if($str = strpos($_SERVER['HTTP_USER_AGENT'], "MicroMessenger")){
            $this->display(T ( 'Addons://HcCard@Vcf/download' ));
            return;
        }
    	$id = I('id');
    	$map['id'] = $id;
    	$info = M('hccard')->where($map)->find();
    	$filename = $info['ownername'].".vcf";
    	header('Content-Type:vcf'); //指定下载文件类型
    	header('Content-Disposition: attachment; filename="'.$filename.'"'); //指定下载文件的描述
    	echo $this->_makeStr($info);
    }

    function _makeStr($info){
    	$txt = "BEGIN:VCARD\n";
    	$txt .= "VERSION:3.0\n";
    	$txt .= "N:".$info['ownername']."\n";
    	$txt .= "FN:".$info['ownername']."\n";
    	$txt .= "TEL;type=WORK;type=pref:".$info['phone']."\n";
    	$txt .= "TEL;type=WORK;type=pref:".$info['tel']."\n";
    	$txt .= "EMAIL;type=INTERNET;type=WORK;type=pref:".$info['email']."\n";
    	$txt .= "ORG:".$info['company_name']."\n";
    	$txt .= "TITLE:".$info['post']."\n";
    	$txt .= "item1.ADR;type=WORK;type=pref:;;".$info['company_addr'].";;;;\n";
    	$txt .= "item1.X-ABADR:cn\n";
    	$txt .= "item2.URL;type=pref:".$info['company_url']."\n";
    	$txt .= "item2.X-ABLabel:_$!<HomePage>!$\_\n";
    	$txt .= "NOTE:由昌佳科技保存\nEND:VCARD";
    	return $txt;
    }
}