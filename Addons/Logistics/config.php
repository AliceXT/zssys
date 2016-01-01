<?php
return array(
		'last' => array (
				'title' => '最后更新时间',
				'type' => 'datetime',
				'value' => '',
				'tip' => '已发货订单在物流状态更新为已签收状态n天后修改为已确认收货状态，这是记录最后进行该操作的时间',
		),
		'days'=> array (
				'title' => '间隔天数',
				'type' => 'text',
				'value' => '',
				'tip' => '已发货订单在物流状态更新为已签收状态n天后修改为已确认收货状态，这是记录n天',
		),
		'refresh' => array (
				'title' => '展开列表时刷新物流信息',
				'type' => 'radio',
				'value' => '0',
				'options' => array(
					'1' => '开启',
					'0' => '关闭',
					),
				'tip' => '选择开启时查看列表就会刷新当前页面物流状态',
		),
);
