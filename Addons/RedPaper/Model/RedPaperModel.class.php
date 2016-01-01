<?php
namespace Addons\RedPaper\Model;
use Think\Model;
/**
 * RedPaper模型
 */
class RedPaperModel extends Model{

	protected $tableName = 'red_paper';

	public function _initialize()
    {
    	$_POST['token'] = get_token();
    	$_POST['mid'] = get_mid();
    }

	// 红包支付，借用微分销插件的函数
	/**
	*	返回红包交互的结果
	*	@param $act_name string 活动名称
	*	@param openid string 收红包用户的openid
	*	@param remark string 备注
	*	@param total_amount 金额(单位：分)
	*	@param wishing 祝福语
	*	@return array 从微信平台返回的数组数据
	**/
	public function mchPay($act_name,$openid,$remark,$total_amount,$wishing){
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		// 证书信息
		$sitePath = SITE_PATH;
		$certPath = $sitePath."/Addons/Shop/View/default/Public/cert/";
		$certArr = array(
			'cert'	=> $certPath.'apiclient_cert.pem',
			'key'	=> $certPath.'apiclient_key.pem',
			'rootca'	=> $certPath.'rootca.pem'
			);
		// 参数信息
		$pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );
		// $openid = 'osALzjvsweDff9UGcO13UWf2uYbw';
		$ip = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '1.1.1.1';
		$nonce_str = get_rand_char(32);
		$mch_billno = $pay_conf['mch_id'].date('Ymj').mt_rand();
		$appinfo = get_token_appinfo(get_token());
		// 整合参数
		$postArr = array(
			'act_name'	=> $act_name,
			'client_ip'	=> $ip,
			'mch_billno'=> $mch_billno,
			'mch_id'	=> $pay_conf['mch_id'],
			'nonce_str'	=> $nonce_str,
			're_openid' => $openid,
			'remark'	=> $remark,
			'send_name' => $appinfo['public_name'],
			'total_amount' 		=> $total_amount,
			'total_num'	=> 1,
			'wishing' 	=> $wishing,
			'wxappid'	=> $appinfo['appid'],
			);
		$key = $pay_conf['api_key'];
		$sign = $this->getSign($postArr, $key);
		$postArr['sign'] = $sign;
		$xml = $this->generateXml($postArr);
		$res = $this->sentPost($url, $xml, $certArr);
		$msg = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $msg;
	}
	//获取签名
	private function getSign($arr, $key='')
	{
		if($key == '')
		{
			$key = "c23b76fe5a1c7befb230debe7cdcdc83";
		}
		$stringA = $this->kvglue($arr);
		$stringSignTemp = $stringA."&key=$key";
		$sign = md5($stringSignTemp);
		$sign = strtoupper($sign);		//签名
		return $sign;
	}
	// xml POST请求
	private function sentPost($url, $para, $certArr=array())
	{
		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);//严格认证
		curl_setopt($curl, CURLOPT_VERBOSE, 1); //debug模式
		if(!empty($certArr))
		{
			curl_setopt($curl, CURLOPT_SSLCERT, $certArr['cert']);
			curl_setopt($curl, CURLOPT_SSLKEY, $certArr['key']);
			curl_setopt($curl, CURLOPT_CAINFO, $certArr['rootca']);
			//curl_setopt($curl, CURLOPT_SSLKEYPASSWD, 'c23b76fe5a1c7befb230debe7cdcdc83');
		}
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
	}
	//生成URI参数形式
	private function kvglue($array)
	{
		$str = '';
		foreach ($array as $key => $value)
		{
			if($str !== '')
			{
				$str .= '&'.$key.'='.$value;
			}
			else
			{
				$str = $key.'='.$value;
			}
		}
		return $str;
	}
	//生成xml
	private function generateXml($arr)
	{
		$xml = '<xml>';
		foreach($arr as $k => $x)
		{
			$xml .= "<$k>".$x."</$k>";
		}
		$xml .= '</xml>';
		return $xml;
	}

	/**
	*	给$list_data级联$jion中的表数据
	*	@param $join = array(
	*					'%table_name%'=>array(
	*						'same_name' =>	'%same_field_name%',
	*						'link_name'	=>	'%link_field_name%',
	*						'fields'	=> 	'%fields%'
	*						'html_format'=>	array('%field_name%'=>'%html_formate%',...)
	*					)
	*					...
	*				)
	*	其中
	*		%table_name%		代表级联的表名
	*		%same_field_name%	代表$list_data中与%table_name%表级联的属性
	*		%link_field_name%	代表%table_name%表中与$list_data级联的属性
	*		%fields%			代表从%table_name%表中取出的属性
	*		%field_name%		代表%table_name%表中要更改格式的属性名
	*		%html_formate%		代表要更改成的html代码,例如
	*								'<img src="{var}" width="50px" >'，
	*								其中，`{var}`代表要替换的属性原本的值
	*	
	*	@param $list_data要修改的数组
	*	@param $search 被替换成属性原本的值的字符串
	**/
	public function listJoinTable(&$list_data,$join,$search = '{var}'){
		
		// 实例化级联的表
		foreach($join as $key=>$table){
			$model[$key] = M($key);
		}
			
		foreach($list_data['list_data'] as $k=>&$vo)
        {
        	// dump('1');
        	foreach($model as $key=>$M){
            	// dump('2');
            	$table = $join[$key];
            	$link_name = $table['link_name'];
            	$same_name = $table['same_name'];
            	$map = null;
            	$map[$link_name] = $vo[$same_name];
            	$data = $M->where($map)->field($table['fields'])->find();
            	if(!empty($table['html_format'])){
            		foreach($table['html_format'] as $var=>$html){
            			// dump('3');
            			$value = $data[$var];
            			$data[$var] = str_replace($search,$value,$html);
            			// dump($data[$var]);
            		}//三层
            	}
            	$vo = array_merge($vo,$data);
            }//二层
        }//一层
	}

	// 验证用户,合法用户则返回true
	public function canUse(){
		$session = session('red_paper_pass');
		$config = D('Common/AddonConfig')->get( 'RedPaper' );
		$pass = $config['hidpass'];
		if($session && $pass == $session){
			return true;
		}else{
			return false;
		}
	}
}
