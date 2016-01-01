<?php
namespace Addons\Shop\Controller;
use User\Api\UserApi;
use Vendor\PHPMailer;
class WebController extends BaseController {

    var $UserApi;
    var $index_url;
    public function _initialize(){
        parent::_initialize();
        $this->index_url = addons_url("Shop://Web/index");
        $this->assign("index_url",$this->index_url);
        $this->UserApi = new UserApi();

        $controller = strtolower(_CONTROLLER);

        $res['title'] = '网页配置';
        $res['url'] = addons_url('Shop://Web/config');
        $res['class'] = $controller == 'web' ? 'cur' : '';
        $nav[] = $res;

        $res['title'] = '秒杀配置';
        $res['url'] = addons_url('Shop://Seckill/config');
        $res['class'] = $controller == 'seckill' ? 'cur' : '';
        $nav[] = $res;

        $this->assign('sub_nav', $nav);
    }

    public function index(){
        $param['token'] = session('token');
        $url = addons_url("Shop://Shop/index",$param);
        redirect($url);
    }

    public function config(){
        if(IS_POST){
            $flag = D("Common/AddonConfig")->set("ShopWeb",I('config'));
            if($flag !== false){
                $this->success("保存成功",Cookie('__forward__'));
            }else{
                $this->error("保存失败");
            }
        }else{
            $addon['config'] = $this->web_fields();
            $db_config = D('Common/AddonConfig')->get("ShopWeb");
            if($db_config){
                foreach($addon['config'] as $key=>$value){
                    !isset($db_config[$key]) || $addon['config'][$key]['value']=$db_config[$key];
                }
            }
            $this->assign('data',$addon);
            $this->display();
        }
    }

    public function web_fields(){
        return array (
            'title' => array (
                    'title' => '网站标题:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '标签title的内容'
            ),
            'icon' => array (
                    'title' => '网站图标:',
                    'type' => 'picture',
                    'value' => '',
                    'tip' => 'jpg或png格式图标文件'
            ),
            'keywords' => array (
                    'title' => '网站关键词:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '用于优化搜索的网站关键词'
            ),
            'decription' => array (
                    'title' => '网站描述:',
                    'type' => 'textarea',
                    'value' => '',
                    'tip' => '用于优化搜索网站的详细描述'
            ),
            'index' => array (
                    'title' => '商城首页链接:',
                    'type' => 'text',
                    'value' => '',
                    'tip' => '首页链接url'
            ),
        );
    }

    public function login(){
        if(IS_POST){
            $result = $this->UserApi->login($_POST['username'], $_POST['password'], $type = 2);
            if($result > 0){
                $map['id'] = $result;
                $user = M('ucenter_member')->where($map)->find();
                // session('openid',$user['openid']);
                // session('token',$user['token']);
                get_token($user['token']);
                get_openid($user['openid']);

                // 如果该账号不在follow表中，添加记录
                // $follow = M('follow')->where($map)->find();
                // empty($follow) || 

                exit(json_encode(array("state"=>"登录成功","success"=>"1","url"=>$this->index_url)));
            }else{
                if($result == -2){
                    exit(json_encode(array("state"=>"密码错误")));
                }else{
                    exit(json_encode(array("state"=>"用户不存在或被禁用")));
                }
            }
        }else{
            $db_config = D('Common/AddonConfig')->get("ShopWeb");

            $this->assign("web",$db_config);

            $param['token'] = get_token();
            $register_url = addons_url("Shop://Web/register",$param);
            $this->assign("register_url",$register_url);
            $this->display(T ( 'Addons://Shop@Web/login' ));
        }
    }

    public function logout(){
        session("openid",null);
        $this->success("账号退出成功！",$this->index_url);
    }

    public function register(){ 
        $db_config = D('Common/AddonConfig')->get("ShopWeb");

        if(IS_POST){
            $verify = I('verify');
            /* 检测验证码 TODO: */
            if(!check_verify($verify)){
                // $this->error('验证码输入错误！');
                exit(json_encode(array("state"=>"验证码输入错误！")));
            }

            $token = get_token();
            $openid = md5($token.time().$_POST['password']);
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $mobile = $_POST['mobile'];

            M()->startTrans();
            $map['id'] = $this->UserApi->register($username, $password, $email, $mobile, $openid, $token);
            // dump($map['id']);
            if($map['id'] > 0){
                $param['verify'] = $openid;
                $url = addons_url("Shop://Web/active",$param);
                $check_email = $this->checkEmail($_POST['email'],$openid,$db_config['title'],$url);
                // 设置用户状态为0
                $data['status'] = "0";
                $data['mobile'] = $mobile;
                $data['token'] = $token;
                $data['openid'] = $openid;
                $result = M('ucenter_member')->where($map)->setField($data);

                if($result && $check_email){
                    M()->commit();
                    // $this->success("注册成功，请登录注册邮箱进行邮箱验证",$this->index_url,15);
                    exit(json_encode(array("success"=>"1","state"=>"注册成功，请登录注册邮箱进行邮箱验证","url"=>$this->index_url)));
                }elseif($result && !$check_email){
                    M()->rollback();
                    exit(json_encode(array("state"=>"邮件发送失败")));
                }elseif(!$result && $check_email){
                    M()->rollback();
                    exit(json_encode(array("state"=>"用户注册失败")));
                }
            }else{
                //这是错误信息
                M()->rollback();
                switch ($map['id']) {   
                    case -1:
                        exit(json_encode(array("state"=>"昵称长度不合法")));
                        break;
                    case -2:
                        exit(json_encode(array("state"=>"昵称禁止注册")));
                        break;
                    case -3:
                        exit(json_encode(array("state"=>"昵称被占用")));
                        break;
                    case -4:
                        exit(json_encode(array("state"=>"密码长度不合法")));
                        break;
                    case -5:
                        exit(json_encode(array("state"=>"邮箱格式不正确")));
                        break;
                    case -6:
                        exit(json_encode(array("state"=>"邮箱长度不合法")));
                        break;
                    case -7:
                        exit(json_encode(array("state"=>"邮箱禁止注册")));
                        break;
                    case -8:
                        exit(json_encode(array("state"=>"邮箱被占用")));
                        break;
                    case -9:
                        exit(json_encode(array("state"=>"手机格式不正确")));
                        break;
                    case -10:
                        exit(json_encode(array("state"=>"手机禁止注册")));
                        break;
                    case -11:
                        exit(json_encode(array("state"=>"手机号被占用")));
                        break;
                    
                    default:
                        exit(json_encode(array("state"=>"未知错误")));
                        break;
                }
                

            }
        }else{
            
            $this->assign("web",$db_config);

            $this->display( T ( 'Addons://Shop@Web/register' ) );
        }
    }

    private function checkEmail($mail,$token,$sender,$url){
        $content = "您好，这是一份系统邮件，用于验证您的邮箱是可用的:),<br>以下是你的验证链接：<br><a href='".$url."' target= '_blank'>".$url."</a><br/> 如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。";

        // $r = think_send_mail('要发送的邮箱','发送人名称，即你的名称','文件标题','邮件内容');
        $r = think_send_mail($mail,$sender,'邮箱验证',$content);
        return $r;
    }
    private function findPassword($mail,$token,$sender,$url){
        $content = "您好，这是一份系统邮件，用于重设您的密码:),<br>以下是你的验证链接：<br><a href='".$url."' target= '_blank'>".$url."</a><br/> 如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，重设的密码为88888888。";

        // $r = think_send_mail('要发送的邮箱','发送人名称，即你的名称','文件标题','邮件内容');
        $r = think_send_mail($mail,$sender,'邮箱验证',$content);
        return $r;
    }

    public function active(){
        $verify = stripslashes(trim($_GET['verify'])); 

        $map['openid'] = $verify;
        $map['status'] = "0";

        $row = M("ucenter_member")->where($map)->find();

        if($row){ 
            if(time() > $row['reg_time'] + 60*60*48)//注册超过48小时验证邮箱
            {
                $msg = '您的激活有效期已过，请登录您的帐号重新发送激活邮件.'; 
                $this->error($msg,$this->index_url);
            }
            $data['status'] = "1";

            $result = M('ucenter_member')->where('id='.$row['id'])->setField($data);

            if($result){
                session('openid',$row['openid']);
                $this->success("邮箱验证成功",$this->index_url);
            }else{
                $this->error("用户状态更改失败，请联系客服");
            }
        }else{
            $msg = '该用户不存在'; 
            $this->error($msg,$this->index_url);
        } 
    }

    public function forget(){
        $map['email'] = stripslashes(trim($_POST['email']));
        if(empty($map['email'])){
            echo "邮箱不能为空";
            return ;
        }
        $user = M('ucenter_member')->field('openid')->where($map)->find();
        $param['verify'] = $user['opendid'];
        $r = $this->findPassword($map['email'],$user['openid'],C('PUBLIC_NAME'),addons_url("Shop://Web/reset",$param));
        if($r){
            // $this->success("邮件成功，请登录邮箱进行重设密码验证");
            echo "邮件发送成功，请登录邮箱进行重设密码验证";
        }else{
            // $this->error("这是无效邮箱");
            echo "这是无效邮箱";
        }
        
    }
    public function reset(){
        $verify = stripslashes(trim($_GET['verify'])); 
        $map['openid'] = $verify;
        $data['password'] = md5("88888888");
        $r = M('ucenter_member')->where($map)->setField($data);
        if($r){
            $this->success("密码重设成功",$this->index_url);
        }else{
            $this->error("密码重设失败",$this->index_url);
        }
    }

}