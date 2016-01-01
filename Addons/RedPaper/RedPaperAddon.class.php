<?php
namespace Addons\RedPaper;
use Common\Controller\Addon;
/**
 * 红包插件
 * @author AliceXT
 */
    class RedPaperAddon extends Addon{
        public $info = array(
            'name'=>'RedPaper',
            'title'=>'红包',
            'description'=>'微信红包的发放、可用于微分销提现',
            'status'=>1,
            'author'=>'AliceXT',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );
    public function install() {
        $install_sql = './Addons/RedPaper/install.sql';
        if (file_exists ( $install_sql )) {
            execute_sql_file ( $install_sql );
        }
        return true;
    }
    public function uninstall() {
        $uninstall_sql = './Addons/RedPaper/uninstall.sql';
        if (file_exists ( $uninstall_sql )) {
            execute_sql_file ( $uninstall_sql );
        }
        return true;
    }
        //实现的weixin钩子方法
        public function weixin($param){
        }
    }