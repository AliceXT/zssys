<?php

namespace Addons\Book;
use Common\Controller\Addon;

/**
 * 教材赠送插件
 * @author AliceXT
 */

    class BookAddon extends Addon{

        public $info = array(
            'name'=>'Book',
            'title'=>'教材赠送',
            'description'=>'教材赠送系统',
            'status'=>1,
            'author'=>'AliceXT',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Book/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Book/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }