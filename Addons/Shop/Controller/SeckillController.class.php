<?php
namespace Addons\Shop\Controller;
use Addons\Shop\Model\ProductModel;
class SeckillController extends WebController{

    var $config;
    public function _initialize(){
        parent::_initialize();

        $config = D('Common/AddonConfig')->get("ShopSeckill");

        $this->config = $config;

        $page_config = D('Common/AddonConfig')->get("ShopWeb");
        $this->assign("web",$page_config);

        // 检查时间，超过付款期限时，取消未付款订单
        $start_time = strtotime($this->config['start_time']);
        $end_time = $start_time + intval($this->config['time']);

        $map['id'] = $config['pid'];
        $pd = M('shop_product')->where($map)->find();
        $now = time();
        if($end_time < $now || $pd['stock_count'] < 0){
            // $this->_expire();
            $this->_changeShangjia('下架');
            // $this->_changeStock(0,true);
        }

        if($now < $end_time && $now > $start_time){
            $this->_changeUsed();
        }

        // 检查库存，为0时取消未付款订单
        // if($config)$this->check_stock();
    }

    public function config(){
        $param ['token'] = get_token ();
        $normal_tips = '通过地址访问秒杀等待：' . addons_url ( 'Shop://Shop/waiting', $param ) . ' ，也可点击<a target="_blank" href="' . U ( 'waiting', $param ) . '">这里</a>在预览';
        $this->assign ( 'normal_tips', $normal_tips );
        if(IS_POST){
            $flag = D("Common/AddonConfig")->set("ShopSeckill",I('config'));
            if($flag !== false){

                // dump($flag);
                $this->config = D('Common/AddonConfig')->get("ShopSeckill");
                $this->_changeStock();
                // dump(M('shop_product')->getLastSql());

                $this->_changeShangjia('下架');

                // dump(M('shop_product')->getLastSql());

                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->seckill_fields();
            $db_config = D('Common/AddonConfig')->get("ShopSeckill");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display(T("Addons://Shop@Seckill/config"));
        }
    }

    public function seckill_fields(){
        return array (
            'pid' => array (
                    'title' => '商品ID:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '请输入要进行秒杀的商品的ID'
            ),
            'num' => array (
                    'title' => '通过码名额:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '通过码总共分配的名额'
            ),
            'password' => array (
                    'title' => '通过码:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '用于验证用户是否有购买资格'
            ),
            'second' => array (
                    'title' => '通过码分配时间:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '单位：秒，如300表示对开始前5分钟进入等待页面的用户随机分配通过码,建议不要设太大'
            ),
            'start_time' => array (
                    'title' => '开始抢购时刻:',
                    'type' => 'datetime',
                    'value' => '',
                    'tip' => ''
            ),
            'time' => array (
                    'title' => '抢购时间:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '单位：秒，如60表示有1分钟的抢购时间,建议不要设太大'
            ),
            'stock_count' => array (
                    'title' => '抢购数量:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '提供抢购的商品数量'
            ),
            'next_activity' => array (
                    'title' => '预告日期',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '下一次秒杀活动的日期，格式10月1日'
            ),
        );
    }

	//添加有通过码的用户
	private function _add(){
		$Model = M('shop_seckill');
		$data['pid'] = $this->config['pid'];
		$data['openid'] = get_openid();
        $data['ctime'] = time();
        $data['token'] = get_token();
		if($Model->create($data)){
            $result = $Model->add();
			return $result;
		}else{
			return false;
		}
	}

    // 修改上架
    private function _changeShangjia($str = "上架"){
        $Model = M('shop_product');
        $map['id'] = $this->config['pid'];
        $map['token'] = get_token();

        if(empty($map['id'])) return false;

        $data['shangjia'] = $str;
        $result = $Model->where($map)->setField($data);
        return $result;
    }

    // 修改库存
    private function _changeStock($stock_count,$flag = false){
        $Model = M('shop_product');
        $map['id'] = $this->config['pid'];
        $map['token'] = get_token();

        if(empty($map['id'])) return false;

        $data['stock_count'] = $flag ? $stock_count : $this->config['stock_count'];
        $result = $Model->where($map)->setField($data);
        return $result;
    }

    // 修改使用状态
    public function _changeUsed(){
        $map['pid'] = $this->config['pid'];
        $map['token'] = get_token();
        $seckills = M('shop_seckill')->where($map)->select();

        foreach($seckills as $s){
            $s_map['openid'] = $s['openid'];
            $info = M('shop_user')->where($s_map)->find();
            $order_map['user_id'] = $info['id'];
            $order_map['pid'] = $this->config['pid'];
            $order = M('shop_order')->where($order_map)->find();
            if($order){
                M('shop_seckill')->where($s_map)->setField('used','1');
            }
        }
    }

    // 进入购买页面
    public function seckill(){
        $Model = M('shop_seckill');
        $password = session('password');
        if($password == $this->config['password']){
            $map['pid'] = $this->config['pid'];
            $map['openid'] = get_openid();
            $map['token'] = get_token();
            $data = $Model->where($map)->find();
            $this->_changeShangjia();
            if($data && $data['used'] == 0){

                // $Model->where($map)->setField('used','1');

                $param['count'] = "1";
                $param['id'] = $this->config['pid'];
                $param['express'] = "in";
                $param['back'] = 'false';
                session('back',$pasword);
                $url = addons_url("Shop://Order/buyNowShow",$param);
                redirect($url);
            }else{
                $url = addons_url("Shop://Shop/index");
                $this->error("您已经购买过该商品，请到“我的订单”进行结算",$url);
            }
        }else{
            $url = addons_url("Shop://Shop/index");
            $this->error("该产品已经被抢购一空",$url);
        }
    }

    // 测试页面
    public function test(){
        $access_token = get_access_token();
        dump($access_token);
    }

    // 输出产品信息
    public function _product_info($pid){
        $pd_m = new ProductModel();
        $map['id'] = $pid;
        $map['token'] = get_token();
        $info = $pd_m->productDetail($map);
        $this->assign("pd",$info);
    }

    // 等待秒杀的倒数页面
    public function waiting(){
        $stime = strtotime($this->config['start_time']);
        $time = intval($this->config['time']);
        $stock_count = $this->config['stock_count'];
        $this->assign("stock_count",$stock_count);
        $this->assign("start_time",$stime);
        $this->assign("time",$time);
        $this->assign("next_activity",$this->config['next_activity']);
        $this->assign("server_time",time());

        // 输出产品信息
        $this->_product_info($this->config['pid']);

        // 时间是秒杀结束之前,尝试获得名额
        if(time() < $stime + $time && time() > ($stime - $this->config['second'])){
            // session('password',null);//清空通过码
            $this->_give_password();
        }

        $this->display(T("Addons://Shop@Seckill/waiting"));
    }

    // 给予通过码
    private function _give_password(){
        // 查重
        $re_map['pid'] = $this->config['pid'];
        $re_map['openid'] = get_openid();
        $re_map['token'] = get_token();
        $exist = M('shop_seckill')->where($re_map)->find();
        // dump($exist);
        if($exist){
            session("password",$this->config['password']);
            return ;
        }

        $map['pid'] = $this->config['pid'];
        $map['token'] = get_token();
        // 已经发放的通过码个数
        $deliver = count(M('shop_seckill')->where($map)->select());
        // 剩下通过码的个数
        $num = intval($this->config['num']) - $deliver;

        if($num <= 0) return ;

        // $second = intval($this->config['second']);
        // 现在剩下的时间
        $second = strtotime($this->config['start_time']) - time();
        //每一小节的时间间隔
        $section = ceil($second / $num);

        // 取出shop_seckill表最后一条数据，取得其最后的时间
        $map['pid'] = $this->config['pid'];
        $map['token'] = get_token();
        $last = M('shop_seckill')->where($map)->order('id desc')->find();
        $time = $last['ctime'];
        // dump($time);
        // 最后时间+时间间隔 跟当前时间进行比较
        if($time + $section < time()){
            $this->_add();
            session("password",$this->config['password']);
        }else{
            return ;
        }
    }

    // 返回当前库存
    public function check_stock(){
        $Model = M('shop_product');
        $map['pid'] = $this->config['pid'];
        $map['token'] = get_token();
        $data = $Model->where($map)->find();
        if($data){
            $return_data['stock_count'] = $data['stock_count'];
            $return_data['success'] = "1";

            //库存为0时将其他订单失效
            if($data['stock_count'] == "0"){
                $this->_expire();
            }
        }
        // exit(json_encode($return_data));
    }

    // 使未支付订单失效,订单状态为4为取消订单
    private function _expire(){
        $Model = M('shop_order');
        $map['pid'] = $this->config['pid'];
        $map['order_status'] = '1';
        $map['token'] = get_token();
        $data['order_status'] = '4';
        $result = $Model->where($map)->setField($data);
        return $result;
    }

}