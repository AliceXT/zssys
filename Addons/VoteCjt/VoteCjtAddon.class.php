<?php

namespace Addons\VoteCjt;
use Common\Controller\Addon;

/**
 * 报名n投票插件
 * @author 昌佳科技
 */

    class VoteCjtAddon extends Addon{

        public $info = array(
            'name'=>'VoteCjt',
            'title'=>'报名n投票',
            'description'=>'报名参加投票活动、自定义报名表单、具有投票参赛选手单页、投票页面',
            'status'=>1,
            'author'=>'昌佳科技',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

        public $admin_list = array(
            'model'=>'Vote',        //要查的表
            'fields'=>'*',          //要查的字段
            'map'=>'',              //查询条件, 如果需要可以再插件类的构造方法里动态重置这个属性
            'order'=>'id desc',     //排序,
            'listKey'=>array(       //这里定义的是除了id序号外的表格里字段显示的表头名
                '字段名'=>'表头显示名'
            ),
        );

	public function install() {
		$install_sql = './Addons/VoteCjt/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/VoteCjt/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }