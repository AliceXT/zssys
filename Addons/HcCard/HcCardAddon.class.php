<?php

namespace Addons\HcCard;
use Common\Controller\Addon;

/**
 * 名片插件
 * @author 画城科技
 */

    class HcCardAddon extends Addon{

        public $info = array(
            'name'=>'HcCard',
            'title'=>'名片',
            'description'=>'在线名片作为企事业单位对外商务及交往的重要载体满足统一VI和管理的需求，将传统纸质名片的内容电子化，同时赋予了纸质名片没有的“一键到电话薄、一键加为好友、一键分享和一键到微站”等功能。它既保留了纸质名片作为商务、社交的入口属性，同时赋予移动互联网的社交、易于传播和社会化营销等属性，将社交和商业价值最大化。',
            'status'=>1,
            'author'=>'画城科技',
            'version'=>'1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/HcCard/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/HcCard/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }