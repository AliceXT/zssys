<?php
namespace Addons\Qrcode\Controller;
use Home\Controller\AddonsController;
use Addons\Qrcode\Model\WeixinAddonModel;
use Addons\Qrcode\Controller\BaseController;
class MsgController extends BaseController{
	function _initialize()
	{
		parent::_initialize();
	}
// 图文信息
	public function config()
	{
		if(IS_POST)
		{
			$flag = D('Common/AddonConfig')->set('QrcodeInfo', I('config'));
			if($flag !== false)
			{
				$this->success('保存成功', Cookie('__forward__'));
			}
			else
			{
				$this->error('保存失败');
			}
		}
		$addon['config'] = $this->info_field();
		$db_config = D ( 'Common/AddonConfig' )->get ( 'QrcodeInfo' );
		if($db_config)
		{
			foreach($db_config as $key => $value)
			{
				!isset($db_config[$key]) ||$addon['config'][$key]['value'] = $db_config[$key];
			}
		}
		$this->assign('data', $addon);
		$this->display();
	}
	public function info_field()
	{
		return array(
			'title'	=> array(
					'title'	=> '标题',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> '',
				),
			'content'			=> array(
					'title'	=> '内容',
					'type'	=> 'textarea',
					'value'	=> '',
					'tip'	=> ''
				),
			'image' 	=> array(
					'title'	=> '图片',
					'type'	=> 'picture',
					'value'	=> '',
					'tip'	=> '',
				),
			'link'	=> array(
					'title'	=> '链接',
					'type'	=> 'text',
					'value'	=> '',
					'tip'	=> ''
				),
			);
	}
}
?>