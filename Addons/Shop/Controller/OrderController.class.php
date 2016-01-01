<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Controller\BaseController;
use Addons\Shop\Model\OrderModel;
use Addons\Shop\Model\ProductModel;
use Addons\Shop\Model\PayModel;
use Addons\Shop\Controller\Trans;
use Addons\Coupon\Model\SnCodeModel;
use Addons\Shop\Model\DistributeModel;
class OrderController extends BaseController {
    var $model;
    public function _initialize()
    {
        $this->model = $this->getModel('shop_order');
        parent::_initialize();
        $action = strtolower ( _ACTION );
        $res['title'] = '所有订单';
        $res['url'] = addons_url('Shop://order/lists');
        $res['class'] = $action == 'lists' ? 'cur' : '';
        $nav[] = $res;
        $res['title'] = '退货单';
        $res['url'] = addons_url('Shop://order/returnLists');
        $res['class'] = $action == 'returnlists' ? 'cur' : '';
        $nav[] = $res;
        $res['title'] = '成功退款';
        $res['url'] = addons_url('Shop://order/returnSuccess');
        $res['class'] = $action == 'returnsuccess' ? 'cur' : '';
        $nav[] = $res;
        /**
        *	@author AliceXT for 筛选
        **/
        $res['title'] = '筛选导出';
        $res['url'] = addons_url('Shop://Select/fliter');
        $res['class'] = $action == 'fliter' ? 'cur' : '';
        $nav[] = $res;
        /*change end*/
        $this->assign('sub_nav', $nav);
    }
    /**
    *   @author AliceXT
    *   2015-12-16 for 增加无数个btn
    *   1:已下单，未付款
    *   2:已付款，未发货
    *   3:已经发货
    *   4:取消订单
    *   5:申请退货
    *   6:同意退货
    *   7:已确认收货
    *   8:拒绝退货
    *   9:已退款
    *   10:自提
    **/
    private function _addBtn(){
        $title = array(1=>"未付款","已付款","已发货","取消订单","申请退货","同意退货","确认收货","拒绝退货","已退款","自提");
        for ($i=1; $i < 11; $i++) { 
            $btn['title'] = $title[$i];
            $btn['class'] = "btn-blue btn-small";//btn-small,btn-mini,opt-btn,btn-group,btn-toolbar,btn-    arrowdown,btn-yellow
            $btn['url'] = addons_url('Shop://Order/lists',array("status"=>$i));
            $btns[] = $btn;
        }

        $btn['title'] = "所有";
        $btn['class'] = "btn-yellow btn-small";//btn-small,btn-mini,opt-btn,btn-group,btn-toolbar,btn-arrowdown,btn-yellow
        $btn['url'] = addons_url('Shop://Order/lists');
        $btns[] = $btn;

        $this->assign("top_more_button",$btns);
    }
    /*change end*/
    public function lists()
    {
        // $r = $_REQUEST;
        // dump($r);exit;
        $normal_tips = '订单列表';
        $this->assign('normal_tips', $normal_tips);
        $this->assign('del_button', false); //默认不可以删除订单
        $this->assign('add_button_name', '订单导出');
        $this->_addBtn();
        $orders = M('shop_order');
        $map['token'] = $nmap['token'] = get_token();
        session('common_condition', $map);
        I('status') ? $nmap['order_status'] = I('status'): session("btnCondition",null);
        $list_data = $this->_get_model_list($this->model,0,'id desc',$nmap);
        $this->assign($list_data);
        $templateFile = $this->model['template_list'] ? $this->model['template_list'] : '';
        $this->display('orderLists');
    }
    // 退货单
    public function returnLists()
    {
        $return_model = $this->getModel('shop_return_order');
        $normal_tips = '退货单';
        $this->assign('normal_tips', $normal_tips);
        $map['token'] = get_token();
        session('common_condition', $map);
        $map['status'] = 2;
        $list_data = $this->_get_model_list($return_model, 0, 'id desc', $map);
        $Order = M('shop_order');
        foreach($list_data['list_data'] as &$x)
        {
            $order = $Order->where(array('id'=>$x['oid']))->find();
            $x['note'] = $order['note'];
        }
        $this->assign($list_data);
        $this->display();
    }
    public function returnSuccess()
    {

        $return_model = $this->getModel('shop_return_order');
        $normal_tips = '退货单';
        $this->assign('normal_tips', $normal_tips);
        $map['token'] = get_token();
        session('common_condition', $map);
        $map['status'] = 1;
        $list_data = $this->_get_model_list($return_model, 0, 'id desc', $map);
        // exit(dump($list_data));
        foreach($list_data['list_grids'] as $k => &$v)
        {
            if($v['title'] == '退款操作')
            {
                // unset($list_data['list_grids'][$k]);
                $v['href'] = 'query&id=[id]|查询';
            }
        }
        $Order = M('shop_order');
        foreach($list_data['list_data'] as &$x)
        {
            $order = $Order->where(array('id'=>$x['oid']))->find();
            $x['note'] = $order['note'];
        }
        $this->assign($list_data);
        $this->display('returnLists');
    }
    public function edit()
    {
        parent::common_edit($this->model);
    }
    /**
    *   @author AliceXT
    *   2015-12-18 for 退款完善
    */
    public function query(){
        header("Content-type: text/html; charset=utf-8");
        $id = I('id');
        $pay   = new PayModel();
        $msg = $pay->wxRefundQuery($id);
        // exit(dump($msg));
        $msg['refund_channel_0'] = "ORIGINAL"==$msg['refund_channel_0'] ? "原路退款" : "退回到余额" ;
        $refund_status_arr = array(
            'SUCCESS'=>"退款成功",
            'FAIL'=>"退款失败",
            'PROCESSING'=>"退款处理中",
            'NOTSURE'=>"未确定，需要商户原退款单号重新发起",
            'CHANGE'=>"转入代发，退款到银行发现用户的卡作废或者冻结了，导致原路退款银行卡失败，资金回流到商户的现金帐号，需要商户人工干预，通过线下或者财付通转账的方式进行退款。"
            );
        $msg['refund_status_0'] = $refund_status_arr[$msg['refund_status_0']];

        $msg['total_fee'] = number_format($msg['total_fee']/100 ,2);
        $msg['cash_fee'] = number_format($msg['cash_fee']/100 ,2);
        $msg['refund_fee_0'] = number_format($msg['refund_fee_0']/100 ,2);
        if($msg['result_code'] == "SUCCESS"){
            // 设置输出
            $arr['cash_fee'] = array("title"=>"现金支付金额","value"=>$msg['cash_fee']);
            $arr['refund_channel_0'] = array("title"=>"退款渠道","value"=>$msg['refund_channel_0']);
            $arr['refund_count'] = array("title"=>"退款笔数","value"=>$msg['refund_count']);
            $arr['refund_fee_0'] = array("title"=>"退款金额","value"=>$msg['refund_fee_0']);
            $arr['refund_recv_accout_0'] = array("title"=>"退款入账账户","value"=>$msg['refund_recv_accout_0']);
            $arr['refund_status_0'] = array("title"=>"退款状态","value"=>$msg['refund_status_0']);
            $arr['total_fee'] = array("title"=>"订单总金额","value"=>$msg['total_fee']);
            $Arr[] = $arr;

            $arr = null;
            $arr['transaction_id'] = array("title"=>"微信订单号","value"=>$msg['transaction_id']);
            $arr['out_trade_no'] = array("title"=>"商户订单号","value"=>$msg['out_trade_no']);
            $arr['out_refund_no_0'] = array("title"=>"商户退款单号","value"=>$msg['out_refund_no_0']);
            $arr['refund_id_0'] = array("title"=>"微信退款单号","value"=>$msg['refund_id_0']);
            $Arr[] = $arr;

            $this->assign("Arr",$Arr);
        }else{
            $this->error($msg['err_code_des']);
        }
        $this->display();
    }
    public function orderQuery(){
        header("Content-type: text/html; charset=utf-8");
        $no = I('no');
        $pay   = new PayModel();
        $msg = $pay->payingStatus($no);
        // exit(dump($msg));
        $msg['is_subscribe'] = $msg['is_subscribe'] == "Y" ? "是" : "否";
        $bank_arr = array(
            "CFT"=>"财付通",
            "CCB"=>"中国建设银行",
            "BC"=>"中国银行/交通银行",
            "ICBC"=>"中国工商银行",
            "CMB"=>"中国招商银行",
            "HSBC"=>"汇丰银行",
            "PBC"=>"中国人民银行",
            "CDB"=>"国家开发银行",
            'HXB'=>"华夏银行",
            'CMBC'=>"民生银行",
            'CUP'=>"中国银联",
            "SDB"=>"深圳发展银行",
            'SPDB'=>"上海浦东发展银行",
            );
        // $msg['bank_type'] = $bank_arr[$msg['bank_type']];

        $msg['fee_type'] = "CNY" == $msg['fee_type'] ? "人民币" : $msg['fee_type'];

        $msg['time_end'] = time_format(strtotime($msg['time_end']));

        $trade_state_arr = array(
            "SUCCESS"=>"支付成功",
            "REFUND"=>"转入退款",
            "NOTPAY"=>"未支付",
            "CLOSED"=>"已关闭",
            "REVOKED"=>"已撤销（刷卡支付）",
            "USERPAYING"=>"用户支付中",
            "PAYERROR"=>"支付失败(其他原因，如银行返回失败)",
            );
        $msg['trade_state'] = $trade_state_arr[$msg['trade_state']];

        $msg['total_fee'] = number_format($msg['total_fee']/100 ,2);
        $msg['cash_fee'] = number_format($msg['cash_fee']/100 ,2);

        if($msg['result_code'] == "SUCCESS"){
            // 设置输出
            $arr['openid'] = array("title"=>"OPENID","value"=>$msg['openid']);
            $arr['is_subscribe'] = array("title"=>"是否关注公众账号","value"=>$msg['is_subscribe']);
            $arr['trade_type'] = array("title"=>"交易类型","value"=>$msg['trade_type']);
            $arr['bank_type'] = array("title"=>"付款银行","value"=>$msg['bank_type']);
            $arr['total_fee'] = array("title"=>"总金额","value"=>$msg['total_fee']);
            $arr['fee_type'] = array("title"=>"货币种类","value"=>$msg['fee_type']);
            $arr['time_end'] = array("title"=>"支付完成时间","value"=>$msg['time_end']);
            $arr['trade_state'] = array("title"=>"交易状态","value"=>$msg['trade_state']);
            $arr['cash_fee'] = array("title"=>"现金支付金额","value"=>$msg['cash_fee']);
            $Arr[] = $arr;

            $arr = null;
            $arr['transaction_id'] = array("title"=>"微信订单号","value"=>$msg['transaction_id']);
            $arr['out_trade_no'] = array("title"=>"商户订单号","value"=>$msg['out_trade_no']);
            $Arr[] = $arr;
            
            $this->assign("Arr",$Arr);
            $this->display("query");
        }else{
            $this->error($msg['err_code_des']);
        }
    }
    public function setRefundFee(){
        $id = I('id');
        $returnOrder = M('shop_return_order')->where(array('id'=>$id))->find(); //退货单
        $order = M('shop_order')->where(array('id'=>$returnOrder['oid']))->find();//相应订单
        // $this->assign("note",$order['note']);
        $this->assign("suggest",$returnOrder['fee']);
        $this->assign("rid",$id);
        $this->assign("normal_tips","请根据订单备注设置退款金额</br>".$order['note']);
        $this->assign("post_url",addons_url("Shop://Order/agreeReturn"));
        $this->display("setRefundFee");
    }
    // 同意退款
    public function agreeReturn()
    {
        $id = $_REQUEST['id'];  // 退货单id
        $returnOrder = M('shop_return_order')->where(array('id'=>$id))->find(); //退货单
        $refund_fee = I('post.refund_fee') ? I('post.refund_fee') : $returnOrder['fee'];//设置退款金额
        $Order = new OrderModel();
        $pay   = new PayModel();
        $oid = $returnOrder['oid'];
        $msg = $Order->getOrderMsg($oid);    //获取订单信息
        $rmsg = $pay->wxRefund($msg['transaction_id'], $msg['out_trade_no'], $id, $msg['total_fee'], $refund_fee);  //微信退单返回的信息
        if($rmsg['return_code'] == 'SUCCESS')
        {
            $Order->turnOrderStatus($oid, 9);	//同意退货
            $rOrder = M('shop_return_order');
            $rOrder->status = 1;
            $rOrder->refund_fee = $refund_fee;
            $rOrder->where(array('id'=>$id))->save(); //将退单中的状态改为已退款
            $this->success("退款成功",addons_url("Shop://Order/returnSuccess"));
        }else{
            $this->error("退款失败",addons_url("Shop://Order/returnLists"));
        }
    }
    /*change end*/
    // 订单导出
    public function loadOrder()
    {
        $ids = $_REQUEST['ids'];
        $title = trim($_REQUEST['title']);
        // $idfield = $this->fetchArrVal($ids);
        // $map['id'] = array('in',implode(',', $idfield));
        $map['id'] = array('in', $ids);
        $map['title'] = array('like','%'.$title.'%');
        $res = M('shop_order')->where($map)->field('id,pid,title,count,unit_price,total_fee,receiver,phone,address,note')->select();
        // foreach($res as & $x)
        // {
        //     $info = json_decode($x['userinfo']);
        //     unset($x['userinfo']);
        //     // $x['receiver'] = $info->nickname;	//收件人
        //     // $x['phone'] = $info->phone;
        //     // $x['address'] = $info->address;
        // }
        $title = array('订单id','商品id','商品名称','购买数量','单价','总价格','收件人','电话','地址','备注');
        exportExcel($res, $title, '订单');
    }
    // 去除数组中的键值
    private function fetchArrVal($arr)
    {
        $val = array();
        foreach($arr as $x)
        {
            $val[] = $x;
        }
        return $val;
    }
    // 确认收货
    public function affirm()
    {
        $id = $_REQUEST['id'];
        $id = trim($id);
        $O = new OrderModel;
        $O->turnOrderStatus($id, 7);
        exit(json_encode(array('code'=>1, 'info'=>'确认成功')));
    }
    public function returnGoods()
    {
        $order = new OrderModel;
        $token = get_token();
        $orderId = trim($_REQUEST['id']);
        $map['token'] = $token;
        $map['oid'] = $orderId;	//要退货订单号(非退货单号)
        $order_item = $order->getOrder($orderId);
        $roid = M('shop_return_order')->where($map)->find(); //退单
        if(IS_POST)
        {
            $reason = $_REQUEST['reason'];
            if(!$roid)	//新增
            {
                $map['reason'] = $reason;
                $map['fee'] = $order_item['total_fee'];	//退单价格
                M('shop_return_order')->add($map);
            }
            else 	//修改
            {
                $returnOrder = M('shop_return_order');
                $returnOrder->reason = $reason;
                $returnOrder->where($map)->save();
            }
            $order->turnOrderStatus($orderId, 5); //订单的状态更改为正在申请退货
            /**
            *   @author AliceXT 2015-10-23 for 用户更改订单状态提示信息
            **/
            $config = D('Common/AddonConfig')->get("LogisticsInform");
            $code = new \Addons\Qrcode\Model\QrcodeModel();
            $msg = "订单退货提醒\n\n";
            $msg .= "时间：".date('H:i')."\n";
            $msg .= "订单ID：".$order_item['id']."\n";
            $msg .= "商品『".$order_item['title']."』\n";
            $msg .= "收件人:".$order_item['receiver']."\n";
            $msg .= "联系电话:".$order_item['phone']."\n";
            $msg .= "退货原因:".$reason;
            foreach($config as $openid){
                empty($openid) || $code->msgTo($openid,$msg);
            }
            /*change end*/
            exit(json_encode(array('info'=> '填写成功!', 'url'=> "/index.php?s=/addon/Shop/Shop/myOrder.html")));
        }
        $roid['orderdetail'] = $order_item;
        $this->assign('info', $roid);
        $this->display();
    }
    // 生成订单
    public function addOrder()
    {
        // echo "<script>alert('测试中...');</script>";
        // exit;
        $username = $_REQUEST['username'];
        $phone = $_REQUEST['phone'];
        $address = $_REQUEST['address'];
        /**
        *   @author AliceXT 2015-11-24 for 化繁为简
        **/
        $go = new Trans;
        $address = $go->t2c($address);
        /*change end*/

        $openid = get_openid();
        $token = get_token();
        $order  = new OrderModel();
        $pd 	= new ProductModel();
        $pay 	= new PayModel();
        $umap = array(
            'openid'	=> $openid,
            'nickname'	=> $username,
            'phone'		=> $phone,
            'address'	=> $address,
            'ctime'		=> time(),
            'user_cat'	=> 999,
            );
        $userid = $order->addUser($umap);		//新的用户则添加，老的用户不变
        $out_trade_no = $this->generate_out_trade_no($userid);
        $goods = unserialize(cookie('goods'));
        foreach($goods as $x)
        {
            $cell = $pd->productDetail(array('id'=> $x['id']));
            $cell['multis'] = $x['multis'];
            $x['multis'] = json_decode($x['multis'],true);
            foreach($x['multis'] as $key=>$value){
                $cell['multi_fee'] += $pd->getFloat($value,$key);
                $names = $pd->getNames($value,$key);    
                // $cell['note'] = empty($cell['note']) ? $names['multi']."：".$names['option'] : $cell['note'].",".$names['multi']."：".$names['option'] ;
                // exit(dump($cell['multi_string']));   
            }
            $cell['count'] = $x['count'];
            $cell['total_fee'] = $cell['discount_price'] + $cell['multi_fee'];
            $cell['total_fee'] = $cell['total_fee'] * $x['count'];
            $cartTotalPrice += $cell['total_fee'];			//购物车内商品总价
            $cell['unit_price'] = $cell['discount_price'];	//单价
            $cell['phone'] = $phone;
            $cell['address'] = $address;
            $cell['receiver'] = $username;
            $body .= $cell['title'];						//传到微信接口的商品名
            $cell['pid'] = $x['id'];	//商品的id
            $cell['by_from_openid'] = $openid;
            $cell['product_field'] = json_encode($cell);
            $cell['out_trade_no']	= $out_trade_no;
            $cell['user_id'] = $userid;
            $cell['user_info'] = json_encode($umap);
            $cell['order_status'] = 1;
            $cell['timestamp']	= time();
            $cell['note'] = $cell['note'].I('note');
            $oid = $order->addSingleOrder($cell);
            $oids[] = $oid;
        }
        cookie('goods', null);	//清空购物车
        $notify_url = addons_url('Shop://Shop/notify');// AliceXT 20151130 for 返回地址错误修复

        /**
        *   @author AliceXT
        *   2015-12-01 for 优惠券使用
        **/
        $SnCode = new SnCodeModel();
        $sn_map['sn'] = I('sn');
        $sn_map['is_use'] = 0;
        $sn_map['status'] = 1;
        $sn = $SnCode->where($sn_map)->find();
        $order_fee = $cartTotalPrice;
        if($sn && $sn['uid'] == $this->mid && $SnCode->canUse($sn,$order_fee)){
            $discount = $sn['discount'];
            $result = $SnCode->useSn($sn['id']);
            if($result === false){
                exit(json_encode(array('errmsg'=>'服务器开小猜，优惠券使用失败，请重新下单购买','errcode'=>'1002')));
            }
        }
        $order_map['out_trade_no'] = $out_trade_no;
        $data['note'] = I('note').$order->getNoteStr($score,$sn,false);//插入的数据
        M('shop_order')->where($order_map)->setField($data);
        // exit(dump($oids."  ".implode(',', $oids)));
        if($sn) $SnCode->setPrize($sn['id'],implode(',', $oids));
        /*change end*/

        $msg = $pay->wxUnityOrder($body, $notify_url, $out_trade_no, $cartTotalPrice-$discount);
        $jsPara = array(
            'appId'	=> (string)$msg['appid'],
            'nonceStr' 	=> (string)$msg['nonce_str'],
            'package'	=> 'prepay_id='.$msg['prepay_id'],
            'signType' 	=> 'MD5',
            'timeStamp' => (string)time(),
            );
        $pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
        $key = $pay_conf['api_key'];
        $sign = $pay->getSign($jsPara, $key);
        $jsPara['paySign'] = $sign;
        $jsPara['out_trade_nos'] = $out_trade_no;
        echo json_encode($jsPara);
    }
    // 产生商户订单号
    public function generate_out_trade_no( $uid )
    {
        $out_trade_no = md5($uid.time());
        return $out_trade_no;
    }
    // 查看微信服务器的订单的状态，并对后台数据做相应修改
    public function payingStatus()
    {
        $pay = new PayModel();
        $Order = new OrderModel;
        $out_trade_no = $_REQUEST['out_trade_nos'];
        $msg = $pay->payingStatus($out_trade_no);
        // if($msg['return_code'] == 'SUCCESS' && $msg['trade_state'] == 'SUCCESS')
        /**
        *   @author AliceXT 2015-10-21 优化支付
        **/
        if($msg['return_code'] == 'SUCCESS' && $msg['trade_state'] == 'SUCCESS')
        {
            $Order->turnOrderStatusByOTN($out_trade_no, 2);
            M('shop_pay_order_return')->add($msg);
            $this->decScore();// @author AliceXT 2015-10-23 for 积分兑换
            echo json_encode(array('code'=> 1, 'info'=>'SUCCESS','url'=>'/index.php?s=/addon/Shop/Shop/myOrder/token/'.get_token().'.html'));
        }
        else 	// 支付失败
        {
            // $Order->clearOTN( $out_trade_no);
            /**
            *   @author AliceXT 2015-12-02 for sncode return
            **/
            $SnCode = new SnCodeModel();
            $SnCode->returnCoupon($out_trade_no);

            echo json_encode(array('code'=> -1, 'info' => '支付失败，请联系管理员', 'url'=>'/index.php?s=/addon/Shop/Shop/myOrder/token/'.get_token().'.html'));
        }
        /*change end*/
    }
    /**
    *   @author AliceXT 2015-10-23 for 积分兑换
    **/
    public function decScore(){
        $score = session('score');
        if(!empty($score)){
            $credit['score'] = 0 - $score*100;
            add_credit('use_for_buying',0,$credit);
        } 
    }
    /*change end */
    // 对已经形成的订单进行结算
    public function jiesuan()
    {
        $id = $_REQUEST['id'];
        $O = new OrderModel();
        $pay = new PayModel();
        $order = $O->getOrderById($id);
        /**
        *   @author AliceXT
        *   for 下架修改
        **/
        $info = json_decode($order['product_field'],true);
        $pd = new ProductModel();
        $pd_map['id'] = $info['id'];
        $pd_info = $pd->productDetail($pd_map);
        if($pd_info['shangjia'] != "上架"){
            // header("HTTP/1.1 424 Product is off the shelf");
            $jsPara = array(
            'nonceStr'  => "商品已下架",
            );
            // echo json_encode(array("return_msg"=>"商品已下架"));
            echo json_encode($jsPara);
            exit();
        }
        /* change end*/
        $notify_url = addons_url('Shop://Shop/notify');// AliceXT 20151130 for 返回地址错误修复
        $user_id = $order['user_id'];
        $map['id'] = $id;
        $out_trade_no = $this->generate_out_trade_no($user_id); // 商户订单id，
        $tempO = M('shop_order');
        $tempO->out_trade_no = $out_trade_no;
        $tempO->where($map)->save();    //保存商户订单id
        $msg = $pay->wxUnityOrder($order['title'], $notify_url, $out_trade_no, $order['total_fee']);
        $jsPara = array(
            'appId' => (string)$msg['appid'],
            'nonceStr'  => (string)$msg['nonce_str'],
            'package'   => 'prepay_id='.$msg['prepay_id'],
            'signType'  => 'MD5',
            'timeStamp' => (string)time(),
            );
        $pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );  //获取微支付的配置信息
        $key = $pay_conf['api_key'];
        $sign = $pay->getSign($jsPara, $key);
        $jsPara['paySign'] = $sign;
        $jsPara['out_trade_nos'] = $out_trade_no;
        echo json_encode($jsPara);
    }
    // 设置cookie
    public function setSession(){
        $pd = new ProductModel();
        $newGoods[] = $pd->check();
        session('buy', serialize($newGoods));
        exit(json_encode(array('code' => -1, 'info' => 'OK')));
    }
    public function buyNowShow()
    {
        /**
        *   @author AliceXT
        *   2015-11-23 for 获取oauth的access_token
        **/
        // $this->getAccessToken();
        /*change end*/
        $buy = unserialize(session("buy"));
        $order = $buy[0];
        $id = $order['id'];
        $count = $order['count'];
        $multis = $order['multis'];
        /**
        *   @author AliceXT
        **/
        
        $good = M('shop_product')->where(array('id'=>$id,'shangjia'=>"上架"))->find();
        
        /* change ending*/
        if($good['stock_count'] < $count)
        {
            header("charset='utf-8'");
            echo "<script>alert('商品库存不足！'); history.go(-1);</script>";
        }
        if($good['is_limit'] == 1)  //商品是限购的
        {
            $openid = get_openid();
            $order = M('shop_order')->where(array('by_from_openid' => $openid,'pid'=>$id))->find();
            if($order['order_status'] != 4) //限购产品，4=取消订单
            {
                header("charset='utf-8'");
                echo "<script>alert('限购产品不可重复购买！'); history.go(-1);</script>";
            }
            if($count > $good['limit_count'])
            {
                header("charset='utf-8'");
                echo "<script>alert('该产品超过限购的数量！'); history.go(-1);</script>";
            }
        }
        $pd = new ProductModel();
        $result = $pd->_get_order($buy);
        $user = M('shop_user')->where(array('token'=>get_token(), 'openid'=>get_openid()))->find();
        $this->assign('user', $user);
        $this->assign('info', $result['goods'][0]);
        //@author AliceXT 2015-10-22 for 购物积分使用
        $score = $this->can_exchange($good['exchange'],$count);
        empty($score) || session('score',$score);
        $this->assign('score',$score);
        /*change end*/
        /**
        *   @author AliceXT
        *   2015-11-28 for 优惠券购物
        **/
        $SnCodeModel = new SnCodeModel();
        $sn = $SnCodeModel->have_coupon($this->mid,$total_fee);
        $this->assign('CouponList',$sn);
        /*change end*/
        $this->display();
    }
    /**
    *   @author AliceXT 2015-10-22 for 购物积分使用
    *   计算可以兑换的积分数量
    **/
    public function can_exchange($pd_exchange,$count = 1){
        $openid = get_openid();
        $map['openid'] = $openid;
        $map['token'] = get_token();
        $follow = M('follow')->where($map)->find();

        $user_score = $follow['score'];

        if($user_score < $pd_exchange){
            return number_format($user_score * $count/100,2);
        }else{
            return number_format($pd_exchange * $count/100,2);
        }
    }
    /*change end*/
    public function buyNow()
    {
        // echo "<script>alert('测试中...');</script>";
        // exit;
        $username = $_REQUEST['username'];
        $phone = $_REQUEST['phone'];
        $address = $_REQUEST['address'];
        /**
        *   @author AliceXT 2015-11-24 for 化繁为简
        **/
        $go = new Trans;
        $address = $go->t2c($address);
        /*change end*/
        // $express_fee = $_REQUEST['express_fee'];
        $openid = get_openid();
        $token = get_token();
        $use_score = I('use_score');//@author AliceXT 2015-10-23 for 使用积分
        $order  = new OrderModel();
        /**
        *   @author AliceXT 2015-10-15 for 秒杀产品禁止重复购买
        **/
        $seckill = D('Common/AddonConfig')->get("ShopSeckill");
        if($seckill['pid'] == $id){
            $user_map['openid'] = $openid;
            $user = M('shop_user')->where($user_map)->find();

            $sec_map['user_id'] = $user['id'];
            $sec_map['pid'] = $id;
            $sec_map['token'] = $token;
            $exist = M('shop_order')->where($sec_map)->find();
            if($exist){
                echo json_encode(array('errmsg'=>'秒杀产品不可重复购买！','errcode'=>'1001'));
                return ;
            }
        }
        /*change end*/
        $pd     = new ProductModel();
        $pay    = new PayModel();

        $umap = array(
            'openid'    => $openid,
            'nickname'  => $username,
            'phone'     => $phone,
            'address'   => $address,
            'ctime'     => time(),
            'user_cat'  => 999,
            );
        $userid = $order->addUser($umap);
        $out_trade_no = $this->generate_out_trade_no($user_id); //每次生成的不一样
        $buy = unserialize(session("buy"));
        // exit(dump($buy));
        $info = $pd->productDetail(array('id'=>$buy[0]['id']));
        // $info = $buy[0];
        $cell['multis'] = $buy[0]['multis'];
        $multis = json_decode($cell['multis'],true);
        foreach($multis as $key=>$value){
            $cell['multi_fee'] += $pd->getFloat($value,$key);
        }
        $cell['total_fee'] = $info['discount_price']+$cell['multi_fee'];// @author AliceXT 2015-10-23 for 使用积分 2015-11-28 for 优惠券使用
        /**
        *   @author AliceXT
        *   2015-11-28 for 优惠券使用
        **/
        $SnCode = new SnCodeModel();
        $sn_map['sn'] = I('sn');
        $sn_map['is_use'] = 0;
        $sn_map['status'] = 1;
        $sn = $SnCode->where($sn_map)->find();
        $order_fee = $cell['total_fee'];
        if($sn && $sn['uid'] == $this->mid && $SnCode->canUse($sn,$order_fee)){
            $discount = $sn['discount'];
            $result = $SnCode->useSn($sn['id']);
            if($result === false){
                exit(json_encode(array('errmsg'=>'服务器开小猜，优惠券使用失败，请重新下单购买','errcode'=>'1002')));
            }
        }
        /*change end*/
        $cell['count'] = $buy[0]['count'];
        // $cell['total_fee'] = $info['discount_price'] * $count + $express_fee * 100;
        $cell['phone'] = $phone;
        $cell['address'] = $address;
        $cell['receiver'] = $username;
        $cell['pid'] = $info['id'];
        $cell['title'] = $info['title'];
        $cell['by_from_openid'] = $openid;
        $cell['product_field'] = json_encode($info);
        $cell['out_trade_no'] = $out_trade_no;
        $cell['user_id'] = $userid;
        $cell['user_info'] = json_encode($umap);
        $cell['order_status'] = 1;
        $cell['timestamp'] = time();
        // $cell['express_fee'] = $express_fee;
        $cell['note'] = I('note');
        // dump($cell);
        $oid = $order->addSingleOrder($cell);
        // exit(dump($cell));
        /**
        *   @author AliceXT
        *   2015-11-30 for 优惠券使用,记录订单
        **/
        if($sn) $SnCode->setPrize($sn['id'],$oid);
        $order_map['out_trade_no'] = $out_trade_no;
        $data['note'] = $cell['note'] . $order->getNoteStr($score,$sn,true);//插入的数据
        M('shop_order')->where($order_map)->setField($data);
        /*change end*/
        $notify_url = addons_url('Shop://Shop/notify');// AliceXT 20151130 for 返回地址错误修复
        $msg =  $pay->wxUnityOrder($info['title'], $notify_url, $out_trade_no, $cell['total_fee']  - $use_score *100 - $discount);
        $jsPara = array(
            'appId'	=> (string)$msg['appid'],
            'nonceStr' 	=> (string)$msg['nonce_str'],
            'package'	=> 'prepay_id='.$msg['prepay_id'],
            'signType' 	=> 'MD5',
            'timeStamp' => (string)time(),
            );
        $pay_conf = D ( 'Common/AddonConfig' )->get ( 'ShopPay' );	//获取微支付的配置信息
        $key = $pay_conf['api_key'];
        $sign = $pay->getSign($jsPara, $key);
        $jsPara['paySign'] = $sign;
        $jsPara['out_trade_nos'] = $out_trade_no;
        echo json_encode($jsPara);
        // 清除session
        // session('buy',null);
    }
    /**
    *	@author AliceXT 2015-9-7
    *	for 跳转到物流插件
    **/
    function jumpLogistics(){
        $param['id'] = I('id');
        $url = addons_url("Logistics://Logistics/add",$param);
        session("back_url",$_SERVER['HTTP_REFERER']);
        redirect($url);
    }
    public function notice(){
        $map['id'] = I('id');
        $order = M('shop_order')->where($map)->find();
        if($order){
            // 修改订单状态，状态3为“已经发货”
            $result = M('shop_order')->where($map)->setField('order_status',"3");
            if($result){
                echo json_encode(array('state'=>"1"));
                exit();
            }
        }
        
        echo json_encode(array('state'=>"0"));
        
    }
    function jumpMore(){
        $param['order_id'] = I('id');
        $url = addons_url("Logistics://Logistics/more",$param);
        redirect($url);
    }
    function ziti(){
        $map['id'] = I('id');
        $order = M('shop_order')->where($map)->find();
        if($order){
            // 修改订单状态，状态10为“自提”
            $result = M('shop_order')->where($map)->setField('order_status',"10");
            if($result){
                $this->success("订单状态已改为自提");
            }else{
                $this->error("数据库错误，更改失败");
            }
        }else{
            $this->error("不存在该订单");
        }	
    }
    /*end change*/
    /**
    *	@author AliceXT
    *	2015-10-13 for 订单详情
    *   2015-12-12 for 订单分利情况
    **/
    private function _get_dis($openid){
        $id || $id = I ( 'id' );
        $order = M ( 'shop_order' )->find ( $id );

        $pid = $map['pid'] = $order['pid'];
        $pDis = M('shop_product_dis')->where($map)->find();
        if(empty($pDis) || ($order['order_status'] != "2" && $order['order_status'] != "3" && $order_status != "7" && $order['order_status'] != "10")){
            return ;
        }
        $deduct['manager'] = number_format($pDis['manager_deduct'] * $order['total_fee']/100,2);
        $deduct['seller'] = number_format($pDis['seller_deduct'] * $order['total_fee']/100,2);
        $deduct['partner'] = number_format($pDis['partner_deduct'] * $order['total_fee']/100,2);
        $this->assign("deduct",$deduct);

        $openid || $openid = $order['by_from_openid'];

        $D = new DistributeModel();
        $F = M('follow');
        $dis = $D->getByOpenid($openid);
        $map = null;
        // manager
        $param['id'] = $map['openid'] = $dis['manager_openid'];
        $user = $F->field('nickname')->where($map)->find();
        $url = addons_url("Shop://User/more",$param);
        $disUser['manager'] = '<a href="'.$url.'">'.cutstr($user['nickname'],4,'utf-8',"*").'</a>';
        // seller
        $param['id'] = $map['openid'] = $dis['seller_openid'];
        $user = $F->field('nickname')->where($map)->find();
        $url = addons_url("Shop://User/more",$param);
        $disUser['seller'] = '<a href="'.$url.'">'.cutstr($user['nickname'],4,'utf-8',"*").'</a>';
        // partner
        $param['id'] = $map['openid'] = $D->findPartner($openid);
        $user = $F->field('nickname')->where($map)->find();
        $url = addons_url("Shop://User/more",$param);
        $disUser['partner'] = '<a href="'.$url.'">'.cutstr($user['nickname'],4,'utf-8',"*").'</a>';
        $this->assign("disUser",$disUser);
    }
    public function more(){
        $this->_get_dis();
        $model = $this->getModel ( 'shop_order' );
        $id || $id = I ( 'id' );

        // 获取数据
        $data = M ( get_table_name ( $model ['id'] ) )->find ( $id );
        $data || $this->error ( '数据不存在！' );

        $token = get_token ();
        if (isset ( $data ['token'] ) && $token != $data ['token'] && defined ( 'ADDON_PUBLIC_PATH' )) {
            $this->error ( '非法访问！' );
        }
        if(IS_POST){
            $Model = D ( parse_name ( get_table_name ( $model ['id'] ), 1 ) );
            // 获取模型的字段信息
            $Model = $this->checkAttr ( $Model, $model ['id'] );
            if ($Model->create () && $Model->save ()) {
                $this->_saveKeyword ( $model, $id );

                $this->success ( '保存' . $model ['title'] . '成功！', U ( 'lists?model=' . $model ['name'], $this->get_param ) );
            } else {
                $this->error ( $Model->getError () );
            }
        }else{
            $fields = get_model_attribute ( $model ['id'] );

            foreach($data as $key=>$value){
                $order[$key] = get_name_by_status($value,$key,$model['id']);
                if($fields[1][$key]["type"] == 'date'){
                    $order[$key] = time_format($value);
                }
                if($key == 'total_fee'){
                    $order[$key] = $value /100;
                }
            }
            // dump($order);
            $this->assign("o",$order);

            // 通过openid在公众平台获取用户账号
            $tmp = json_decode($order['userinfo'],true);
            $Follow = new \Addons\UserCenter\Model\FollowModel();
            $user = $Follow->get_user_info($tmp['openid']);
            // dump($user);
            $this->assign('u',$user);

            // 解析商品信息
            $tmp = json_decode($order['product_field'],true);
            $model = $this->getModel ( 'shop_product' );
            $fields = get_model_attribute($model['id']);
            foreach($tmp as $key=>$value){
                $product[$key] = get_name_by_status($value,$key,$model['id']);
                if($fields[1][$key]["type"] == 'date'){
                    $product[$key] = time_format($value);
                }
                switch ($key) {
                    case 'market_price':
                    case 'discount_price':
                    case 'express_fee':
                    case 'express_fee_in_province':
                    case 'express_fee_out_province':

                        $product[$key] = $value / 100;
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
            // dump($product);
            $this->assign('p',$product);

            // 物流信息
            $map['order_id'] = $id;
            $tmp = M('logistics')->where($map)->select();
            $model = $this->getModel ( 'logistics' );
            $fields = get_model_attribute($model['id']);
            foreach($tmp as $l){
                foreach($l as $key=>$value){
                    $val = get_name_by_status($value,$key,$model['id']);
                    if(empty($logistics[$key])) $logistics[$key] = $val;
                    $logistics[$key] = ($logistics[$key] ==  $val ) || empty($logistics[$key])? $logistics[$key] : $logistics[$key].",".$val;
                    if($fields[1][$key]["type"] == 'date'){
                        $logistics[$key] = time_format($value);
                    }
                }
            }
            $this->assign('l',$logistics);

            $this->assign('post_url',__SELF__);
            $this->meta_title = '详情' . $model ['title'];
            $templateFile = T('Addons://Shop@Order/more');
            $this->display ( $templateFile );
        }
        
    }
    /*change end*/
    /**
    *   @author AliceXT
    *   for 微信昵称增加到订单列表中
    **/
    private function getNickname($openid){
        if(empty($openid)) return '';
        // 通过openid在公众平台获取用户账号
        $map['openid'] = $openid;
        $follow = M('follow')->where($map)->find();
        return $follow['nickname'];
    }
    /*change end*/
    /**
    *   @author AliceXT
    *   2015-11-21 for 收货地址共享
    **/
    public function getAccessToken(){
        $token = I('state');
        $tmp = session('oauth_access_token'.$token);
        if(!empty($tmp)){
            return ;
        }
        $appinfo = get_token_appinfo();
        $timestamp = time();
        $noncestr = get_rand_char();
        $url = GetCurUrl ();
        $redirect_url = $url;
        $code = I('code');
        if(empty($code)){//获取code
            $this->get_oath_code($url);
        }else{//获取access_token
            $content = $this->get_oath_accesstoken($token,$code);
            $value = $content['access_token'];
            session('oauth_access_token'.$token,$value);
        }
    }
    public function getAddrParam(){
        $token = get_token();
        $redirect_url = I('url');
        $appinfo = get_token_appinfo();
        $timestamp = (string)time();
        $noncestr = get_rand_char();

        $data['appId'] = $param['appid'] = $appinfo['appid'];
        $data['url'] = $param['url'] = $redirect_url;
        $data['timeStamp'] = $param['timestamp'] = $timestamp;
        $data['nonceStr'] = $param['noncestr'] = $noncestr;
        $param['accesstoken'] = session('oauth_access_token'.$token);
        if(empty($param['accesstoken'])){
            exit(json_encode(array('error'=>1)));
        }
        
        $data['addrSign'] = $this->genSha1Sign($param);

        exit(json_encode($data));
    }
    //创建签名SHA1
    private function genSha1Sign($parameters){
        $signPars = '';
        ksort($parameters);
        foreach($parameters as $k => $v) {
            if("" != $v && "sign" != $k) {
                if($signPars == '')
                    $signPars .= $k . "=" . $v;
                else
                    $signPars .=  "&". $k . "=" . $v;
            }
        }
        $sign = SHA1($signPars);
        return $sign;
    }
    private function get_oath_code($callback){
        $info = get_token_appinfo ();
        $param ['appid'] = $info ['appid'];
        $param ['redirect_uri'] = $callback;
        $param ['response_type'] = 'code';
        $param ['scope'] = 'snsapi_base';
        $param ['state'] = $info ['public_id'];
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query ( $param ) . '#wechat_redirect';
        redirect ( $url );
        // $content = file_get_contents ( $url );
    }
    private function get_oath_accesstoken($token,$code){
        if(empty($token) || empty($code)){
            return null;
        }
        $info = get_token_appinfo ($token);
        $param ['appid'] = $info ['appid'];
        $param ['secret'] = $info ['secret'];
        $param ['code'] = $code;
        $param ['grant_type'] = 'authorization_code';
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query ( $param );
        $content = file_get_contents ( $url );
        $content = json_decode ( $content, true );
        return $content;
    }
    /*change end */
    /**
    *   @author AliceXT
    *   2015-12-16 订单page
    **/
    public function Page($count = 100){
        $map['by_from_openid'] = $openid = array('in',I('id'));
        $map['order_status'] = array('in','2,3,7,10');
        $page = I('page') ? I('page') : 1;
        $orders = M('shop_order')->field("id,title,total_fee,pid,user_id,timestamp")->where($map)->page($page,$count)->select();
        $Dis = M('shop_product_dis');
        if(I('type') == "zhijie")
            $type = true;
        else
            $type = false;
        foreach($orders as &$order){
            $pdis = $Dis->where(array('pid'=>$order['pid']))->find();
            $deduct = $type ? $pdis['seller_deduct'] : $pdis['manager_deduct'];
            $order['deduct'] = number_format($order['total_fee']*$deduct/100,2);
            // $order['deduct'] = $deduct;
            $order['total_fee'] = number_format($order['total_fee']/100,2);
            $order['owner'] = get_wx_nickname($order['user_id']);
            $order['timestamp'] = time_format($order['timestamp']);
        }
        $res['info'] = $orders;
        $res['state'] = 1;
        exit(json_encode($res));
    }
}
