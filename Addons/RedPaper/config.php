<?php
return array(
	// 'random'=>array(//配置在表单中的键名 ,这个会是config[random]
	// 	'title'=>'是否开启随机:',//表单的文字
	// 	'type'=>'radio',		 //表单的类型：text、textarea、checkbox、radio、select等
	// 	'options'=>array(		 //select 和radion、checkbox的子选项
	// 		'1'=>'开启',		 //值=>文字
	// 		'0'=>'关闭',
	// 	),
	// 	'value'=>'1',			 //表单的默认值
	// ),
	'oldpass'=>array(//配置在表单中的键名 ,这个会是config[random]
		'title'=>'旧密码：',//表单的文字
		'type'=>'password',		 //表单的类型：text、textarea、checkbox、radio、select等
	),
	'newpass'=>array(//配置在表单中的键名 ,这个会是config[random]
		'title'=>'新密码：',//表单的文字
		'type'=>'password',		 //表单的类型：text、textarea、checkbox、radio、select等
	),
	'dblpass'=>array(//配置在表单中的键名 ,这个会是config[random]
		'title'=>'重复密码：',//表单的文字
		'type'=>'password',		 //表单的类型：text、textarea、checkbox、radio、select等
	),
	'hidpass'=>array(//配置在表单中的键名 ,这个会是config[random]
		'title'=>'重复密码：',//表单的文字
		'type'=>'hidden',		 //表单的类型：text、textarea、checkbox、radio、select等
	),
);
					