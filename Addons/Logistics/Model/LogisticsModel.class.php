<?php

namespace Addons\Logistics\Model;
use Think\Model;

/**
 * Logistics模型
 */
class LogisticsModel extends Model{
	protected $tableName = 'logistics';

	public function getById($id = ''){
		if(empty($id))
			return '';
		$map['id'] = $id;
		return $this->where($map)->find();
	}

	public function changeOrderStatus($id = ''){
		if(empty($id))
			return '';
		$map['id'] = $id;
		$Order = M('shop_order');
		$order = $Order->where($map)->find();
		if($order['order_status'] == '3'){
			$data['order_status'] = '7';
			$Order->where($map)->setField($data);
		}
	}
	public function more($id = ''){
		$map['id'] = $id = empty($id) ? I('id') : $id;
		if(empty($id)){
			$map = null;
			$map['order_id'] = $order_id = I('order_id');
			if(empty($order_id)){
				return '';
			}
		}
		$post = $this->where($map)->find();
		// if($post['last_state'] == 3){
		// 	if(strtotime($post['last_time']) < time() - 3600*24*5)
		// 		$this->changeOrderStatus($post['order_id']);
		// 	return "";
		// }
		$url = 'http://www.kuaidi100.com/query?type='.$post['type'].'&postid='.$post['postid'];
		$json = file_get_contents($url);
		$arr = json_decode($json,true);
		$this->saveMore($map['id'],$arr);

		$girds ['field'] [0] = 'time';
		$girds ['title'] = '时间';
		$list_data ['list_grids'] [0] = $girds;

		$girds ['field'] [0] = 'context';
		$girds ['title'] = '描述';
		$list_data ['list_grids'] [] = $girds;

		$list_data ['list_data'] = $arr['data'];

		return $list_data;
	}

	private function saveMore($id,$arr){
		$map['id'] = $id;

		$updatetime = empty($arr['updatetime']) ? $arr['data'][0]['time'] : $arr['updatetime'];
		$data['last_time'] = $updatetime;
		$data['last_state'] = $arr['state'];

		$this->where($map)->setField($data);

		return $this->where($map)->find();
	}

	public function refresh($days = 5){
		$section = 3600*24;
		$dec = 3600*24*$days;
		$now = time();
		$config = D ( 'Common/AddonConfig' )->get ( 'Logistics');
		$last = strtotime($config['last']);
		// 满足刷新时长
		if($now - $last > $section){
			$config['last'] = time_format($now);
			D ( 'Common/AddonConfig' )->set ( 'Logistics', $config );
			$map['last_state'] = 3;
			$map['last_time'] = array('lt',date("Y-m-j H:i:s",$now - $dec));
			$map['last_time'] = array('gt',date("Y-m-j H:i:s",$last - $dec));

			$list = $this->where($map)->select();
			if($list){
				foreach($list as $l){
					$this->changeOrderStatus($l['order_id']);
				}
			}
		}else{
			return ;
		}
	}
	public function list_refresh(&$data){
		foreach($data as $i=>$post){
			if($post['last_state'] != 3){
				$urls[$i] = 'http://www.kuaidi100.com/query?type='.$post['type'].'&postid='.$post['postid'];
			}
		}
		if(empty($urls))
			return ;
		$mh = curl_multi_init();    
   
		foreach ($urls as $i => $url) {   
		  $conn[$i] = curl_init($url);   
		  curl_setopt($conn[$i], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");   
		  curl_setopt($conn[$i], CURLOPT_HEADER ,0);   
		  curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT,60);
		  curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER,1);
		  curl_setopt($conn[$i], CURLOPT_FILE,$st); // 设置将爬取的代码写入文件   
		  curl_multi_add_handle ($mh,$conn[$i]);   
		} // 初始化  
		do {   
		  $mrc = curl_multi_exec($mh,$active); 
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);  // 执行   
		while($active && $mrc == CURLM_OK){
			if(curl_multi_select($mh) != -1){
				do{
					$mrc = curl_multi_exec($mh, $active);
				}while($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		     
		foreach ($urls as $i => $url) {  
		  $data[$i] = $this->saveMore($data[$i]['id'],json_decode(curl_exec($conn[$i]),true)); 
		  curl_multi_remove_handle($mh,$conn[$i]);   
		  curl_close($conn[$i]);   
		} // 结束清理   
		     
		curl_multi_close($mh);   

	}
}
