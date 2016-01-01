<?php
namespace Addons\Branch\Model;
use Think\Model;
/**
 * Branch模型
 */
class BranchModel extends Model{
	protected $tableName = 'branch';
	// id:操作负责人:addOpenid&type=ceo&id=[id]|增加,delOpenid&type=ceo&id=[id]|删除
	// id:操作合伙人:addOpenid&type=partner&id=[id]|增加,delOpenid&type=partner&id=[id]|删除
	// 返回有一个openid fields
	public function makeFields(){
		$field["openid"]=array( 
    	  "id" => "",
    	  "keyword" => "openid",
    	  "title" => "openid",
    	  "field" =>  "text NOT NULL",
    	  "type" => "textarea",
    	  "value" => "",
    	  "remark" =>  "填写openid,一行填写一个openid",
    	  "is_show" => "1",
    	  "extra" => "",
    	  "model_id" => "224",
    	  "is_must" => "0",
    	  "status" => "1",
    	  "update_time" =>  "1450920419",
    	  "create_time" =>  "1450919343",
    	  "validate_rule" => "",
    	  "validate_time" => "3",
    	  "error_info" => "",
    	  "validate_type" => "regex",
    	  "auto_rule" => "",
    	  "auto_time" => "3",
    	  "auto_type" => "function",
    	);
    	return array(1=>$field);
	}
	// 得到nickname数组
	public function toNickname($openids){
		$openidArr = explode("\r\n", $openids);
		foreach($openidArr as &$str){
			$arr = explode("|", $str);
			if(empty($arr[1])){
				$info = getWeixinUserInfo($arr[0]);
				$Arr[] = $info['nickname'];
			}else{
				$Arr[] = $arr[1];
			}
		}
		return implode(",", $Arr);
	}

	public function findBranch($openid,$id = 0){
		empty($id) || $map['id'] = $id;
		$map['ceo_openids'] = array('like',"%".$openid."%");
		$map['token'] = get_token();
		return $this->where($map)->find();
	}
	// 得到openid数组
	public function toOpenid($openids){
		$openidArr = explode("\r\n", $openids);
		foreach($openidArr as &$str){
			$arr = explode("|", $str);
			$Arr[] = $arr[0];
		}
		return implode(",", $Arr);
	}

	public function getListData(){
		$data = $this->repect();

		$girds ['field'] [0] = 'openid';
		$girds ['title'] = 'openid';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'nickname';
		$girds ['title'] = '重复合伙人';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'fir_id';
		$girds ['title'] = '公司ID1';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'fir_name';
		$girds ['title'] = '公司名称1';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'sec_id';
		$girds ['title'] = '公司ID2';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'sec_name';
		$girds ['title'] = '公司名称2';
		$list_data ['list_grids'] [] = $girds;

		$girds ['field'] [0] = 'sec_name';
		$girds ['title'] = '公司名称2';
		$list_data ['list_grids'] [] = $girds;

		$girds["field"] [0] = "fir_id";
		$girds["field"] [1] = "sec_id";
		$girds["field"] [2] = "openid";
      	$girds["title"] = "操作";
      	$girds["href"] = "delOpenid&oid=[openid]&id=[fir_id]|从公司1中删除,delOpenid&oid=[openid]&id=[sec_id]|从公司2中删除";
		$list_data ['list_grids'] [] = $girds;
		
		$list_data ['list_data'] = $data;

		return $list_data;
	}
	// 多公司合伙人查重
	public function repect(){
		$map['token'] = get_token();
		$branchs = $this->field("id,keyword,openids")->where($map)->select();
		foreach($branchs as $branch){
			$openids[] = explode(",",$this->toOpenid($branch['openids']));
			$nicknames[] = explode(",", $this->toNickname($branch['openids']));
		}
		$number = count($branchs);
		for ($i=0; $i < $number; $i++) { 
			$ids_arr = $openids[$i];
			foreach ($ids_arr as $key=>$openid) {
				for ($j=$i+1; $j < $number; $j++) { 
					if(in_array($openid, $openids[$j])){
						$str['openid'] = $openids[$i][$key];
						$str['nickname'] = $nicknames[$i][$key];
						$str["fir_id"] = $branchs[$i]["id"];
						$str["fir_name"] = $branchs[$i]["keyword"];
						$str['sec_id'] = $branchs[$j]["id"];
						$str['sec_name'] = $branchs[$j]["keyword"];

						$str_arr[] = $str;

					}
				}
			}
		}
		return $str_arr;
	}

	// 删除单个合伙人
	public function delOpenid($id,$openid){
		empty($openid) && $openid = I('oid');
		empty($id) && $id = I('id');

		$map['token'] = get_token();
		$map['id'] = $id;
		$branch = $this->where($map)->find();
		$openidArr = explode("\r\n", $branch['openids']);
		$openidArr = array_filter(array_unique($openidArr));
		 
		foreach($openidArr as &$str){
			$arr = explode("|", $str);
			if($arr[0] == $openid){
				continue;
			}else{
				$Arr[] = $str;
			}
		}

		$openids = implode("\r\n", $Arr);

		return $this->where($map)->setField("openids",$openids);
	}

}
