<?php

namespace Addons\Branch;
use Common\Controller\Addon;

/**
 * 分公司插件
 * @author AliceXT
 */

    class BranchAddon extends Addon{

        public $info = array(
            'name'=>'Branch',
            'title'=>'分公司',
            'description'=>'设置分公司的负责人，结合微分销插件记录该公司的合伙人，通过合伙人获得该公司的订单量和营业额。',
            'status'=>1,
            'author'=>'AliceXT',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Branch/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Branch/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }