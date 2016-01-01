<?php

namespace Addons\Logistics\Controller;
use Home\Controller\AddonsController;

class MsgController extends LogisticsController{

    function _initialize() {
        parent::_initialize();

        $action = strtolower(_ACTION);

        $res['title'] = '收款通知';
        $res['url'] = addons_url('Logistics://Msg/payment');
        $res['class'] = $action == 'payment' ? 'cur' : '';
        $nav[] = $res;

        $res['title'] = '发货通知';
        $res['url'] = addons_url('Logistics://Msg/delivery');
        $res['class'] = $action == 'delivery' ? 'cur' : '';
        $nav[] = $res;

        $res['title'] = '退货通知';
        $res['url'] = addons_url('Logistics://Msg/inform');
        $res['class'] = $action == 'inform' ? 'cur' : '';
        $nav[] = $res;

        $this->assign('sub_nav', $nav);
    }

    // 配置收款通知
    public function payment(){
        if(IS_POST){
            $flag = D("Common/AddonConfig")->set("LogisticsPayment",I('config'));
            if($flag !== false){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->fields();
            $db_config = D('Common/AddonConfig')->get("LogisticsPayment");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display(T("Addons://Logistics@Msg/config"));
        }
    }

    // 配置发货通知
    public function delivery(){
        if(IS_POST){
            $flag = D("Common/AddonConfig")->set("LogisticsDelivery",I('config'));
            if($flag !== false){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->fields();
            $db_config = D('Common/AddonConfig')->get("LogisticsDelivery");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display(T("Addons://Logistics@Msg/config"));
        }
    }

    // 配置用户取消订单通知
    public function inform(){
        if(IS_POST){
            $flag = D("Common/AddonConfig")->set("LogisticsInform",I('config'));
            if($flag !== false){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->inform_fields();
            $db_config = D('Common/AddonConfig')->get("LogisticsInform");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display(T("Addons://Logistics@Msg/config"));
        }
    }

    //用户取消订单通知配置项
    public function inform_fields(){
        return array (
            'openid' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),
            'openid1' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),
            'openid2' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),
            'openid3' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),
            'openid4' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),
            'openid5' => array (
                    'title' => '收件人:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '收件人OPENID'
            ),

        );
    }

    // 配置项
    public function fields(){
        return array (
            'template_id' => array (
                    'title' => '模板ID:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '请输入微信公众平台上的模板ID'
            ),
            // 'access_token' => array (
            //         'title' => 'ACCESS_TOKEN:',
            //         'type' => 'text',
            //         'value' => '',
            //         'tip' => '使用微信公众平台的在线接口调试工具得到的ACCESS_TOKEN'
            // ),
            'topcolor' => array (
                    'title' => '顶端颜色:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '回复模板的顶端颜色如#FFFFFF'
            ),
            'data' => array (
                    'title' => '模板的数据:',
                    'type' => 'textarea',
                    'value' => '',
                    'tip' => '如：first|您好，我们已收到您的付款 。|#743A3A'
            ),
        );
    }

    // 发送收款消息
    public function send_payment_message($order){
        $map['id'] = I('id');
        $order || $order = M('shop_order')->where($map)->find();
        $config = D('Common/AddonConfig')->get("LogisticsPayment");
        //构造数据表和表单表
        $data_list = array(0=>$order,1=>json_decode($order['product_field'],true));
        $table_list = array(0=>'shop_order',1=>'shop_product');

        // 跳转addon路径
        $addons_str = "Shop://Shop/myorder";

        // 传进去的参数
        // $param = array('id'=>$post['id']);

        $template = $this->_make_template($config,$order,$addons_str,$param,
            count($table_list),$table_list,$data_list);

        return $this->_send($template);
    }

    private function _send($template){
        if(empty($template)) return ;

        $map['public_id'] = get_token();
        $public = M('member_public')->where($map)->find();

        $appid = $public['appid'];
        $appsecret = $public['secret'];

        $access_token = get_access_token();
        $url = "http://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        $res = $this->http_request($url,urldecode(json_encode($template)));
        return $res;
        // require_once('weixin.class.php');
        // $weixin = new class_weixin($appid,$appsecret);
        // return $weixin->send_template_message(urldecode(json_encode($template)));
    }


    // 发送发货消息
    // order是订单信息，post是快递单信息
    public function send_delivery_message($order,$post){
        // $order || $order = M('logistics')->where('id="340"')->find();
        $map['id'] = I('id');
        $post || $post = M('logistics')->where($map)->find();
        $map['id'] = $post['order_id'];
        $order || $order =  M('shop_order')->where($map)->find();


        // 获得配置
        $config = D('Common/AddonConfig')->get("LogisticsDelivery");

        //构造数据表和表单表
        $data_list = array(0=>$order,1=>$post);
        $table_list = array(0=>'shop_order',1=>'logistics');

        // 跳转addon路径
        $addons_str = "Logistics://Logistics/client_more";

        // 传进去的参数
        $param = array('id'=>$post['id']);

        $template = $this->_make_template($config,$order,$addons_str,$param,
            count($table_list),$table_list,$data_list);
        // dump($template);

        return $this->_send($template);
        // dump($this->_send($template));
    }

    /*
    *   将订单数据、商品数据与配置的魔板相融合，得到返回给用户的信息
    *   @param config 配置数据
    *   @param order 订单数据
    *   @param addons_str 插件链接字符串
    *   @param param 传入的参数
    *   @param list_num 列表的数量
    *   @param table_list 表单列表
    *   @param data_list 数据列表
    */
    private function _make_template($config,$order,$addons_str,$param = "",
        $list_num,$table_list,$data_list){
        if(!$order) return "没有订单信息";
        // dump($table);
        $data = $config['data'];
        $arr = explode("\r\n",$data);
        foreach($arr as $a){
            $str = explode("|",$a);
            $change_str = $str[1];
            // 遍历数据列表
            for($i = 0;$i < $list_num;++$i){
                //整理数据和表单
                $table = $table_list[$i];
                $data = $data_list[$i];
                $change_str = $this->_preg_English($change_str,$data,$table);
                $change_str = $this->_preg_Chinese($change_str,$data,$table);
            }
            $change_str = strip_tags($change_str);

            $sub_data[$str[0]] = array(
                'value' => $change_str,
                'color' => $str[2]
                );
        }
        // dump($sub_data);

        $userinfo = json_decode($order['userinfo'],true);
        $param['openid'] = $touser = $userinfo['openid'];
        $param['token'] = get_token();

        $url = addons_url($addons_str,$param);

        return array(
            'touser' => $touser,
            'template_id' => $config['template_id'],
            'url' => $url,
            'topcolor' => $config['topcolor'],
            'data' => $sub_data
            );
    }

    // 替换string中的中文参数为订单中的具体的值
    private function _preg_Chinese($string,$order,$table){

        if(!$order) return ;

        // 要搜索的模式，字符串类型。
        $pattern = "/\[[\x{4e00}-\x{9fa5}A-Za-z]+]/u";

        // 提取中文参数，带[]方括号
        preg_match_all($pattern,$string,$matches);

        // 获得中文参数数组vars
        foreach($matches[0] as $m){
            $pat = "/[\x{4e00}-\x{9fa5}A-Za-z]+/u";//只拿出来中文字符串
            preg_match($pat,$m,$arr);
            $vars[] = $arr[0];
        }
        // dump($vars);
        // foreach($table_list as $table){
            $val = $this->_change_Chinese_param($table,$vars,$order);
            // $vars = $val;
        // }
        // dump($val);
        $str_replace = $string;
        foreach($val as $replace){
            $str_replace = preg_replace($pattern,$replace,$str_replace,1);
        }

        // 输出替换结果
        return $str_replace;
    }

    private function _change_Chinese_param($table_name,$vars,$order){

        if(!$table_name || !$vars || !$order){
            return $vars;
        }
        //获取模型信息
        $model = $this->getModel ($table_name);
        //获取模型列表数据
        $list_data = $this->_get_model_list( $model );

        $list_grids = $list_data['list_grids'];
        // dump($list_grids);
        // 配对
        foreach($vars as $v){
            $value = null;
            foreach($list_grids as $grids){
                if($v == $grids['title']){
                    $field = explode("|",$grids['field'][0]);
                    // 先保存函数，以免被覆盖
                    $function = $field[1];
                    $field = $field[0];
                    $int = strpos($field,"time");
                    // 没有函数，不是时间
                    if(!$function && $int === false){
                        $value = $v == "总费用" ? $order[$field] / 100 : $order[$field];
                    }elseif($function){
                        $value = call_user_func($function,$order[$field],$field,$model['id']);
                    }elseif($int !== false){
                        $value = date("Y-m-j H:i:s",$order[$field]);
                    }
                }
            }
            $val[] = $value ? $value : "[".$v."]";
        }
        return $val;
    }

    // 替换string中的英文参数为订单中的具体的值
    private function _preg_English($string,$order,$table){
        if(!$order) return ;

        $pattern = "/\[[A-Za-z]+]/u";

        preg_match_all($pattern,$string,$matches);

        // 获得英文参数数组vars
        foreach($matches[0] as $m){
            $pat = "/[A-Za-z]+/u";//只拿出来中文字符串
            preg_match($pat,$m,$arr);
            $vars[] = $arr[0];
        }

        // foreach($table_list as $table){
            $val = $this->_change_English_param($table,$vars,$order);
            // $vars = $val;
        // }

        $str_replace = $string;
        foreach($val as $replace){
            $str_replace = preg_replace($pattern,$replace,$str_replace,1);
        }

        // 输出替换结果
        return $str_replace;
    }

    private function _change_English_param($table_name,$vars,$order){
        //获取模型信息
        $model = $this->getModel ($table_name);
        //获取模型列表数据
        $list_data = $this->_get_model_list( $model );

        $list_grids = $list_data['list_grids'];

        // 配对
        foreach($vars as $v){
            $value = null;
            $offset=array_search($v,$list_data["fields"]);
            $field = explode("|",$list_grids[$offset]["field"][0]);
            // 先保存函数，以免被覆盖
            $function = $field[1];
            $field = $field[0];
            $int = strpos($field,"time");
            // 没有函数，不是时间
            if(!$function && $int === false){
                $value = $order[$field];
            }elseif($function){
                $value = call_user_func($function,$order[$field],$field,$model['id']);
            }elseif($int !== false){
                $value = date("Y-m-j H:i:s",$order[$field]);
            }
            $val[] = $value ? $value : "[".$v."]";
        }
        return $val;
    }

    protected function http_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    
}