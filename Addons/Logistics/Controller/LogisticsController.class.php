<?php

namespace Addons\Logistics\Controller;
use Home\Controller\AddonsController;
use Addons\Logistics\Model\LogisticsModel;

class LogisticsController extends AddonsController{

	function _initialize() {
		parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		
        $controller = strtolower(_CONTROLLER);

		$res ['title'] = '物流列表';
		$res ['url'] = addons_url ( 'Logistics://Logistics/lists' );
		$res ['class'] = strtolower(_ACTION) == "lists"? 'current' : "" ;
		$nav [] = $res;

		$res ['title'] = '刷新配置';
		$res ['url'] = addons_url ( 'Logistics://Logistics/config' );
		$res ['class'] = strtolower(_ACTION) == "config"? "current" : '';
		$nav [] = $res;
		
		$res ['title'] = '通知配置';
		$res ['url'] = addons_url ( 'Logistics://Msg/payment' );
		$res ['class'] = $controller == "msg"? "current" : '';
		$nav [] = $res;

		$this->assign ( 'nav', $nav );

		$Logistics = new LogisticsModel();
		$config = D ( 'Common/AddonConfig' )->get ( 'Logistics');
		$Logistics->refresh(intval($config['days']));
	}

	public function nulldeal(){
		$url = addons_url ( 'Logistics://Logistics/lists' );
		redirect ( $url );
	}

	public function lists(){
		//获取模型信息
		$model = $this->getModel ();

		!I('title') || $nmap['postid'] = I('title');

		//获取模型列表数据
		$list_data = $this->_get_model_list( $model=$model,$nmap=$nmap );

		//刷新页面快递信息
		$Logistics = new LogisticsModel();

		$config = D ( 'Common/AddonConfig' )->get ( 'Logistics');
		$refresh = $config['refresh'];

		if($refresh){
			$Logistics->list_refresh($list_data['list_data']);
		}

		$data = $list_data['list_data'];
		foreach($data as &$d){
			$map = null;
			$map['id'] = $d['order_id'];
			$order = M('shop_order')->where($map)->find();
			// if($refresh){
			// 	// $Logistics->more($d['id']);
			// 	// $d = $Logistics->getById($d['id']);
			// }
			$d['name'] = $order['receiver'];
		}
		$list_data['list_data'] = $data;

		$this->assign($list_data);
		// dump($list_data);
		$this->display();

	}

	public function more(){
		$Logistics = new LogisticsModel();

		$list_data = $Logistics->more();

		$this->assign($list_data);

		$this->display();

	}

	public function add(){
		// $_GET['id'] = "6";
		is_array ( $model ) || $model = $this->getModel ( $model );
		if (IS_POST) {
			$Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
			// 获取模型的字段信息
			$Model = $this->checkAttr ( $Model, $model ['id'] );

			//通知后台已经发货
			$notice = $this->notice($_POST['order_id'],$_POST['postid']);

			if ($Model->create () && $id = $Model->add ()) {
				
				// 通知用户已发货
				$msg = new MsgController();
				$map['id'] = $id;
            	$post = M('logistics')->where($map)->find();
            	$map['id'] = $post['order_id'];
            	$order = M('shop_order')->where($map)->find();

            	$res = $msg->send_delivery_message($order,$post);

            	// dump($res);
            	$res = json_decode($res,true);

				$jump_url = session("back_url");
				if($res['errcode'] !== 0){
					$this->error("未能通知用户已经发货 errcode:[".$res['errcode']."]",$jump_url);
				}

				if(!$notice){
					$this->error("重复发货",$jump_url);
				}

				$this->success ( '添加' . $model ['title'] . '成功！', $jump_url );
			} else {
				$msg = $Model->getError ();
				$this->error ( $msg );
			}
		} else {
			$fields = get_model_attribute ( $model ['id'] );
			$order_id = $fields[1]['order_id'];
			if(empty($_GET['id'])){
				$order_id['type'] = "num";
			}else{
				$order_id['value'] = $_GET['id'];
				$order_id['is_show'] = "4";
			}
			$fields[1]['order_id'] = $order_id;

			$this->assign ( 'fields', $fields );
			// dump($fields[1]);

			$this->meta_title = '新增' . $model ['title'];
			
			$templateFile || $templateFile = $model ['template_add'] ? $model ['template_add'] : '';
			$this->display ( $templateFile );
		}
	}

	private function notice($order_id,$postid){
		$param['id'] = $order_id;
		$url = addons_url("Shop://Order/notice",$param);
		$json = json_decode(file_get_contents($url),true);
		if($json['state'] == "1"){
			return true;
		}else{
			return false;
		}
	}
	public function client_more(){
		$Logistics = new LogisticsModel();

		$list_data = $Logistics->more();

		$this->assign($list_data);

		$this->display();

	}
}
