<?php

namespace Addons\Logistics;
use Common\Controller\Addon;

/**
 * 物流插件
 * @author AliceXT
 */

    class LogisticsAddon extends Addon{

        public $info = array(
            'name'=>'Logistics',
            'title'=>'物流',
            'description'=>'查看快递单的信息、物流跟踪、订单的流程进度、客户签收情况',
            'status'=>1,
            'author'=>'AliceXT',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Logistics/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Logistics/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }