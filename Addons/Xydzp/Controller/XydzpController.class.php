<?php
namespace Addons\Xydzp\Controller;
use Home\Controller\AddonsController;
class XydzpController extends AddonsController
{
    protected $model;
    protected $option;
    protected $jplist;
    public function __construct()
    {
        parent::__construct();
        $this->model = M('Model')->getByName($_REQUEST['_controller']);
        $this->model || $this->error('模型不存在！');
        
        $this->assign('model', $this->model);
        
        $this->option = M('Model')->getByName('xydzp_option');
        $this->assign('option', $this->option);
        
        $this->jplist = M('Model')->getByName('xydzp_jplist');
        $this->assign('jplist', $this->jplist);
    }
    /**
     * 显示指定模型列表数据
     */
    public function lists()
    {
        $page = I('p', 1, 'intval'); // 默认显示第一页数据
        
        // 解析列表规则
        $fields = array();
        $grids  = preg_split('/[;\r\n]+/s', $this->model['list_grid']);
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val   = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array(
                'field' => $field,
                'title' => $val[1]
                );
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use (&$fields)
                {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array    = explode('|', $val);
                $fields[] = $array[0];
            }
        }
        // 过滤重复字段信息
        $fields       = array_unique($fields);
        // 关键字搜索
        $map['token'] = get_token();
        $key          = $this->model['search_key'] ? $this->model['search_key'] : 'title';
        if (isset($_REQUEST[$key])) {
            $map[$key] = array(
                'like',
                '%' . htmlspecialchars($_REQUEST[$key]) . '%'
                );
            unset($_REQUEST[$key]);
        }
        // 条件搜索
        foreach ($_REQUEST as $name => $val) {
            if (in_array($name, $fields)) {
                $map[$name] = $val;
            }
        }
        $row = empty($this->model['list_row']) ? 20 : $this->model['list_row'];
        
        // 读取模型数据列表     
        empty($fields) || in_array('id', $fields) || array_push($fields, 'id');
        $name = parse_name(get_table_name($this->model['id']), true);
        
        $data  = M($name)->field(empty($fields) ? true : $fields)->where($map)->order('id DESC')->page($page, $row)->select();
        /* 查询记录总数 */
        $count = M($name)->where($map)->count();
        
        // 分页
        if ($count > $row) {
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->meta_title = $this->model['title'] . '列表';
        // $this->display(T('Addons://Xydzp@Xydzp/lists'));
        $this->display('lists');
    }
    public function add()
    {
        if (IS_POST) {
            // 自动补充token
            $_POST['token'] = get_token();
            $Model          = D(parse_name(get_table_name($this->model['id']), 1));
            // 获取模型的字段信息
            $Model          = $this->checkAttr($Model, $this->model['id']);
            if ($Model->create() && $xydzp_id = $Model->add()) {
                // 保存关键词
                D('Common/Keyword')->set(I('keyword'), 'Xydzp', $xydzp_id);
                
                $this->success('添加' . $this->model['title'] . '成功！', U('lists?model=' . $this->model['name']));
            } else {
                $this->error($Model->getError());
            }
        } else {

            $xydzp_fields = get_model_attribute($this->model['id']);
            $this->assign('fields', $xydzp_fields);
            // 奖品表
            $option_fields = get_model_attribute($this->option['id']);
            $this->assign('option_fields', $option_fields);
            
            $this->meta_title = '新增' . $this->model['title'];
            $this->display($this->model['template_add'] ? $this->model['template_add'] : T('Addons://Xydzp@Xydzp/add'));
        }
    }
    public function del()
    {
        $ids = I('id', 0);
        if (empty($ids)) {
            $ids = array_unique(( array ) I('ids', 0));
        }
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        
        $Model = M(get_table_name($this->model['id']));
        $map   = array(
            'id' => array(
                'in',
                $ids
                )
            );
        if ($Model->where($map)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    public function edit()
    {
        // 获取模型信息
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $Model = D(parse_name(get_table_name($this->model['id']), 1));
            // 获取模型的字段信息
            $Model = $this->checkAttr($Model, $this->model['id']);
            if ($Model->create() && $Model->save()) {
                // 保存关键词
                D('Common/Keyword')->set(I('keyword'), 'Xydzp', $id);
                
                $this->success('保存' . $this->model['title'] . '成功！', U('lists'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($this->model['id']);
            
            // 获取数据
            $data = M(get_table_name($this->model['id']))->find($id);
            $data || $this->error('数据不存在！');
            
            $option_list = M('xydzp_option')->where('xydzp_id=' . $id)->select();
            $this->assign('option_list', $option_list);
            
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = '编辑' . $this->model['title'];
            $this->display(T('Addons://Xydzp@Xydzp/edit'));
        }
    }
    
    /**
     * **************************中奖记录************************************
     */
    public function zjloglists()
    {
        $page = I('p', 1, 'intval'); // 默认显示第一页数据
        $xydzp_id = I('get.id', 0, "intval");
        $zjlog = M('Model')->getByName('xydzp_log');
        $grids  = preg_split('/[;\r\n]+/s', $zjlog['list_grid']);
        // 解析列表规则
        $fields = array();
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val   = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array(
                'field' => $field,
                'title' => $val[1]
                );
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use (&$fields)
                {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array    = explode('|', $val);
                $fields[] = $array[0];
            }
        }
        $fix     = C("DB_PREFIX");
        // 过滤重复字段信息
        $fields       = array_unique($fields);
        $row = empty($zjlog['list_row']) ? 20 : 10000;//$zjlog['list_row'];
         //添加奖品关联
        $fields[]        = $fix . "xydzp_option.title";
        $key = array_search('id', $fields);
        if (false === $key) {
            array_push($fields, "{$fix}xydzp_log.id as id");
        } else {
            $fields[$key] = "{$fix}xydzp_log.id as id";
        }
        
        $key = array_search('openid', $fields);
        if (false === $key) {
            array_push($fields, "{$fix}ucenter_member.openid as openid");
        } else {
            $fields[$key] = "{$fix}ucenter_member.openid as openid";
        }
        
        $key = array_search('username', $fields);
        if (false === $key) {
            array_push($fields, "{$fix}ucenter_member.username as username");
        } else {
            $fields[$key] = "{$fix}ucenter_member.username as username";
        }
        
        $key = array_search('mobile', $fields);
        if (false === $key) {
            array_push($fields, "{$fix}ucenter_member.mobile as mobile");
        } else {
            $fields[$key] = "{$fix}ucenter_member.mobile as mobile";
        }
        
        $proname = I('post.proname','全部');//条件查询


        if(empty($proname) || $proname=="全部" ){
            $sarr = array('xydzp_id' => $xydzp_id,$fix . 'ucenter_member.token' => get_token());
        }else{
            $sarr = array('xydzp_id' => $xydzp_id,$fix . 'xydzp_option.title' => $proname,$fix . 'ucenter_member.token' => get_token());
        }
        
        //分类筛选
        
        $str = 'wp_xydzp_jplist.xydzp_id='.$xydzp_id;
        $xlssql = M('xydzp_option')
        ->join('RIGHT Join ' . $fix . 'xydzp_jplist on ' . $fix . 'xydzp_jplist.xydzp_option_id=' .$fix .'xydzp_option.id')
        ->where($str)
        ->select();


            // echo M('xydzp_option')->getlastsql();
            // exit();   


        //中奖列表
        $sqlqu = M('xydzp_log')
        ->join('left join ' . $fix . 'ucenter_member on ' . $fix . 'xydzp_log.uid=' . $fix . 'ucenter_member.openid')
        ->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_log.xydzp_option_id=' . $fix . 'xydzp_option.id')
        ->field(empty($fields) ? true : $fields)
        ->where($sarr);
        $data = $sqlqu->order("zjdate desc")
        ->page($page, $row)
        ->select();

            // echo M('xydzp_log')->getlastsql();
            // exit();       


        /* 查询记录总数 */
        $count = $sqlqu->count();
        
        // 分页
        if ($count > $row) {
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign('xydzp_id', $xydzp_id);
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->assign('ptype',$proname);
        $this->assign('typename',$xlssql);

        $this->display(T('Addons://Xydzp@Xydzp/zjloglists'));
    }
    
    function ylingqu(){
        // 获取模型信息
        $id = I('id', 0, 'intval');
        M("xydzp_log")
        ->where(array('id' => $id))
        ->data(array("state"=>1))
        ->save();
        $xydzp_log = M("xydzp_log")->where(array('id' => $id))->find();
        $this->success('已标记为已领取状态！', U('zjloglists?id='.$xydzp_log["xydzp_id"]));           
    }
    
    function wlingqu(){
        // 获取模型信息
        $id = I('id', 0, 'intval');
        M("xydzp_log")
        ->where(array('id' => $id))
        ->data(array("state"=>0))
        ->save();
        $xydzp_log = M("xydzp_log")->where(array('id' => $id))->find();
        $this->success('已标记为未领取状态！', U('zjloglists?id='.$xydzp_log["xydzp_id"]));    
    }
    /**
     * **************************奖品库设置功能************************************
     */
    public function jpoplists()
    {
        $grids  = preg_split('/[;\r\n]+/s', $this->option['list_grid']);
        // 解析列表规则
        $fields = array();
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val   = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array(
                'field' => $field,
                'title' => $val[1]
                );
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use (&$fields)
                {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array    = explode('|', $val);
                $fields[] = $array[0];
            }
        }
        // 过滤重复字段信息
        $fields       = array_unique($fields);
        // 条件
        $map['token'] = get_token();
        
        $data = M("xydzp_option")->field(empty($fields) ? true : $fields)->where($map)->order('id DESC')->select();
        
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->display(T('Addons://Xydzp@Xydzp/jpoplists'));
    }
    public function jpopadd()
    {
        if (IS_POST) {
            // 自动补充token
            $_POST['token'] = get_token();
            $Model          = D(parse_name(get_table_name($this->option['id']), 1));
            // 获取模型的字段信息
            $Model          = $this->checkAttr($Model, $this->option['id']);
            if ($Model->create() && $xydzp_option_id = $Model->add()) {
                if ($_POST['isjx'] == "1") {
                    $this->success('添加' . $this->option['title'] . '成功！<br/>将继续进行新增奖品', U('jpopadd'));
                } else {
                    $this->success('添加' . $this->option['title'] . '成功！', U('jpoplists'));
                }
            } else {
                $this->error($Model->getError());
            }
        } else {
            $xydzp_option_fields = get_model_attribute($this->option['id']);
            $this->assign('fields', $xydzp_option_fields);
            $this->meta_title = '新增奖品';
            $this->display($this->model['template_add'] ? $this->model['template_add'] : T('Addons://Xydzp@Xydzp/jpopadd'));
        }
    }
    public function jpopedit()
    {
        // 获取模型信息
        $id = I('id', 0, 'intval');
        
        if (IS_POST) {
            $Model = D(parse_name(get_table_name($this->option['id']), 1));
            // 获取模型的字段信息
            $Model = $this->checkAttr($Model, $this->option['id']);
            if ($Model->create() && $Model->save()) {
                $this->success('保存' . $this->option['title'] . '成功！', U('jpoplists'));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($this->option['id']);
            
            // 获取数据
            $data = M(get_table_name($this->option['id']))->find($id);
            $data || $this->error('数据不存在！');
            
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = '编辑' . $this->option['title'];
            $this->display(T('Addons://Xydzp@Xydzp/jpopedit'));
        }
    }
    public function jpopdel()
    {
        $ids = I('id', 0);
        if (empty($ids)) {
            $ids = array_unique(( array ) I('ids', 0));
        }
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        
        $Model = M(get_table_name($this->option['id']));
        $map   = array(
            'id' => array(
                'in',
                $ids
                )
            );
        if ($Model->where($map)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    
    
    
    /**
     * **************************活动奖品设置************************************
     */
    public function jplists()
    {
        $xydzp_id = I('get.xydzp_id', 0, "intval");
        $grids    = preg_split('/[;\r\n]+/s', $this->jplist['list_grid']);
        // 解析列表规则
        $fields   = array();
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val   = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array(
                'field' => $field,
                'title' => $val[1]
                );
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use (&$fields)
                {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array    = explode('|', $val);
                $fields[] = $array[0];
            }
        }
        
        // 过滤重复字段信息
        $fields          = array_unique($fields);
        // 条件
        $map['xydzp_id'] = $xydzp_id;
        $fix             = C("DB_PREFIX");
        //添加奖品关联
        $fields[]        = $fix . "xydzp_option.title";
        array_unshift($grids, array(
            "field" => array(
                "title"
                ),
            "title" => "奖品名称"
            ));
        
        $key = array_search('id', $fields);
        if (false === $key) {
            array_push($fields, "{$fix}xydzp_jplist.id as id");
        } else {
            $fields[$key] = "{$fix}xydzp_jplist.id as id";
        }
        
        $key = array_search('gailv_str', $fields);
        if (false != $key) {
            $fields[$key] = "(case when type='0' then gailv else gailv_str end) as gailv_str";
        }
        
        $data = M('xydzp_jplist')->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_jplist.xydzp_option_id=' . $fix . 'xydzp_option.id')->field(empty($fields) ? true : $fields)->where($map)->order('type asc,gailv asc')->select();
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->assign('xydzp_id', $xydzp_id);
        $this->display(T('Addons://Xydzp@Xydzp/jplists'));
    }
    public function jpadd()
    {
        $xydzp_id = I('get.xydzp_id', 0, "intval");
        if (IS_POST) {
            $Model = D(parse_name(get_table_name($this->jplist['id']), 1));
            // 获取模型的字段信息
            $Model = $this->checkAttr($Model, $this->jplist['id']);
            if ($Model->create() && $xydzp_jplist_id = $Model->add()) {
                if ($_POST['isjx'] == "1") {
                    $this->success('添加成功！<br/>将继续进行新增奖品', U('jpadd?xydzp_id=' . $xydzp_id));
                } else {
                    $this->success('添加成功！', U('jplists?xydzp_id=' . $xydzp_id));
                }
            } else {
                $this->error($Model->getError());
            }
        } else {
            $jplist_fields = get_model_attribute($this->jplist['id']);
            $this->assign('fields', $jplist_fields);
            // 过滤重复字段信息
            $fields       = array_unique($fields);
            $map['token'] = get_token();
            $jpdata       = M("xydzp_option")->field(array(
                "title",
                "id"
                ))->where($map)->order('id DESC')->select();
            $this->assign('jpdata', $jpdata);
            $this->assign('xydzp_id', $xydzp_id);
            $this->meta_title = '新增奖品';
            $this->display($this->model['template_add'] ? $this->model['template_add'] : T('Addons://Xydzp@Xydzp/jpadd'));
        }
    }
    public function jpedit()
    {
        // 获取模型信息
        $id       = I('id', 0, 'intval');
        $xydzp_id = I('get.xydzp_id', 0, "intval");
        
        if (IS_POST) {
            $Model = D(parse_name(get_table_name($this->jplist['id']), 1));
            // 获取模型的字段信息
            $Model = $this->checkAttr($Model, $this->jplist['id']);
            if ($Model->create() && $Model->save()) {
                $this->success('保存成功！', U('jplists?xydzp_id=' . $xydzp_id));
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($this->jplist['id']);
            
            // 获取数据
            $data = M(get_table_name($this->jplist['id']))->find($id);
            $data || $this->error('数据不存在！');
            $xydzp_id = $data["xydzp_id"];
            $jpdata   = M("xydzp_option")->field(array(
                "title",
                "id"
                ))->where($map)->order('id DESC')->select();
            $this->assign('jpdata', $jpdata);
            $this->assign('seljpdata', $data["xydzp_option_id"]);
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->assign('xydzp_id', $xydzp_id);
            $this->meta_title = '编辑';
            $this->display(T('Addons://Xydzp@Xydzp/jpedit'));
            
        }
    }
    public function jpdel()
    {
        $ids = I('id', 0);
        if (empty($ids)) {
            $ids = array_unique(( array ) I('ids', 0));
        }
        if (empty($ids)) {
            $this->error('请选择要操作的数据!');
        }
        
        $Model = M(get_table_name($this->jplist['id']));
        $map   = array(
            'id' => array(
                'in',
                $ids
                )
            );
        if ($Model->where($map)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    /**
     * **************************微信上的操作功能************************************
     */
    function show()
    {
        $xydzp_id = I('id', 0, 'intval');
        $openid   = get_openid();
        $token    = get_token();
        
        $this->assign('openid', $openid);       
        $this->assign('token', $token); 
        $this->assign('xydzp_id', $xydzp_id); 
        
        //是否已经结束 
        $isend = $this->_is_overtime($xydzp_id);
        $isfan = $this->_is_fan($openid);
        if($isend){
            $this->assign('isend', $isend);
        }elseif(!$isfan){
            $this->assign('notfan',!$isfan);
            $appinfo = get_token_appinfo($token);
            $focus_str = $appinfo['public_name'];
            $config = get_addon_config('Xydzp');
            $this->assign('qrcode',$config['qrcode']);
            $this->assign('focus_str',$focus_str);
        }else{
            //查询是否绑定了信息
            $user = M('ucenter_member')->where(array(
                "openid" => $openid,
                "token" => $token
                ))->find();         
            
            $param['token']  = $token;
            $param['openid'] =$openid;
            $param['id']     =  $xydzp_id ;     
            $url="";
            if (($user["username"] == "" || $user["mobile"] == "") && $openid != "" && $openid != "-1") {           
                $url = addons_url('Xydzp://Xydzp/info', $param);            
            }
            $this->assign('isinfo_url', $url);
            
            $joinurl = addons_url('Xydzp://Xydzp/join', $param);
            $info    = $this->_getXydzpInfo($xydzp_id);
            $cjnum   = ($this->is_Maxjoin($xydzp_id, $openid, $token));
            $canJoin = !empty($openid) && !empty($token) && $cjnum != 0;   

                //查询奖品列表        
            $fix     = C("DB_PREFIX");
            $jplist  = M('xydzp_jplist')
            ->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_jplist.xydzp_option_id=' . $fix . 'xydzp_option.id')
            ->field($fix . "xydzp_option.title,pic," . $fix . "xydzp_option.miaoshu,". $fix . "xydzp_option.isdf")
            ->where(array('xydzp_id' => $xydzp_id))
            ->order("type asc,gailv asc")
            ->select();

            $this->assign('jplist', urldecode(json_encode($jplist)));
            $this->assign('jplists', $jplist);


// 活动数据
            $data               = M(get_table_name($this->model['id']))->find($xydzp_id);
                // $data["start_date"] = date('Y-m-d H:i:s', $data["start_date"]);
                // $data["end_date"]   = date('Y-m-d H:i:s', $data["end_date"]);
            $this->assign('hddata', $data);



//抽奖页中奖列表
            $zjuserlist = M('xydzp_log')
            ->join('left join ' . $fix . 'ucenter_member on ' . $fix . 'xydzp_log.uid=' . $fix . 'ucenter_member.openid')
            ->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_log.xydzp_option_id=' . $fix . 'xydzp_option.id')
            ->field($fix . "xydzp_log.id," . $fix . "ucenter_member.username," . $fix . "xydzp_option.title,zjdate")
            ->where(array('xydzp_id' => $xydzp_id,$fix . 'ucenter_member.token' => $token ))
            ->order("zjdate desc")
                    //->limit(10)
            ->select();
            

            $this->assign('zjuserlist', $zjuserlist);            
            $this->assign('canJoin', $canJoin);
            $this->assign('cjnum', $cjnum);
            $this->assign('joinurl', $joinurl);

                // 分享的链接消息
            $this->_set_share();

                //签名配置
            $this->set_jsapi_config();
        }
        $this->display(T('Addons://Xydzp@Xydzp/show'));
    }
    // 设置签名信息
    function set_jsapi_config(){
        $ticket = get_jsapi_ticket();

        $param['jsapi_ticket'] = $ticket['ticket'];

        $param['noncestr'] = "123456789";
        $param['timestamp'] = $ticket['timestamp'];
        $param['url'] = "http://".$_SERVER['SERVER_NAME'].__SELF__;

        $string = $this->toVarString($param);
        $sha = sha1($string);

        $appinfo = get_token_appinfo();

        $config['timestamp'] = $param['timestamp'];
        $config['noncestr'] = $param['noncestr'];
        $config['appid'] = $appinfo['appid'];
        $config['signature'] = $sha;

        $this->assign('config',$config);
    }
    // 生成签名字符串
    function toVarString($arr){
        $string = null;
        foreach($arr as $key=>$value){
            $string .= $key."=".$value."&";
        }
        $len = strlen($string);
        return substr($string, 0, $len-1);
    }
    // 是否已经关注公众号
    function _is_fan($openid)
    {
        $token = get_token();
        $data = getWeixinUserInfo($openid,$token);
        if($data['subscribe'] == 0){
            return false;
        }
        return true;
    }
    //创建分享内容
    function _set_share(){
        $config = get_addon_config('Xydzp');
        
        $share['title'] = $config['title'];
        $share['desc'] = $config['desc'];
        $share['link'] = $config['link'];
        $share['pic'] = $config['cover'];
        $this->assign('share',$share);
    }

    function _getXydzpInfo($id)
    {
        // 检查ID是否合法
        if (empty($id) || 0 == $id) {
            $this->error("错误的幸运大转盘ID");
        }
        
        $map['id'] = $map2['xydzp_id'] = intval($id);
        $info      = M('xydzp')->where($map)->find();
        // dump(M ( 'xydzp' )->getLastSql());
        $this->assign('info', $info);
        
        // dump($info);
        $opts = M('xydzp_jplist')->where($map2)->select();
        
        // dump($opts);
        $this->assign('opts', $opts);
        $this->assign('num_total', $total);
        return $info;
    }
    
    //我的中奖信息
    function zjinfo(){
        $xydzp_id = I('id', 0, 'intval');
        $openid   = get_openid();
        $token    = get_token();
        $fix     = C("DB_PREFIX");
        //中奖列表
        $zjuserlist = M('xydzp_log')
        ->join('left join ' . $fix . 'ucenter_member on ' . $fix . 'xydzp_log.uid=' . $fix . 'ucenter_member.openid')
        ->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_log.xydzp_option_id=' . $fix . 'xydzp_option.id')
        ->field($fix . "xydzp_log.id," .$fix . "xydzp_log.state," . $fix . "ucenter_member.username," . $fix . "xydzp_option.title,zjdate,miaoshu")
        ->where(array('xydzp_id' => $xydzp_id,$fix . 'ucenter_member.token' => $token, $fix . 'xydzp_log.uid'=>$openid ))
        ->order("zjdate desc")              
        ->select();

                //echo M('xydzp_log')->getlastsql();
                //dump($zjuserlist);
                //exit();
        $this->assign('now',time());
        $this->assign('zjuserlist', $zjuserlist);

        // 分享的链接消息
        $this->_set_share();
        //签名配置
        $this->set_jsapi_config();  
        // 填写的收货信息接收地址
        $this->assign('receiver_url',addons_url('Xydzp://Share/receive'));
        $this->assign('modifyurl',addons_url('Xydzp://Share/modify'));
        $this->display(T('Addons://Xydzp@Xydzp/zjinfo'));
    }
    
    //[Ajax]保存用户信息
    function info()
    {
        $data['openid'] = get_openid();
        $data['token']  = get_token();      

        // 保存用户信息
        $username = I('truename');
        if (!empty($username)) {
            $ucenter_member['username'] = $username;
        }
        $mobile = I('mobile');
        if (!empty($mobile)) {
            $ucenter_member['mobile'] = $mobile;
        }
        if (!empty($ucenter_member)) {
            M('ucenter_member')->where($data)->save($ucenter_member);
        }
        $array=array("result"=>0);
        $json = json_encode($array);
        echo urldecode($json);
    }

//查询该用户是否已经中奖过  
    private function getuserzj(){
       $uid = get_openid();
       $utoken = get_token(); 
       $sql['uid'] = $uid;
       $sql['xydzp_id'] = I('id');
       $rs = M('xydzp_log')->where($sql)->count();
       if($rs > 0){
        return true;
    }else{
        return false;       
    }


}  

    //[Ajax]开始转
function join()
{
    $xydzp_id = I('get.id', 0, 'intval');
    if($this->_is_starttime($xydzp_id)){
        if($this->_is_overtime($xydzp_id)){
            $json = json_encode(array("type"=>4));          
            echo urldecode($json);
        }
            //关闭抽中后不可抽
        else if($this->getuserzj()){ 
            $json = json_encode(array("type"=>200));            
            echo urldecode($json);      
        }else
        {
            $openid    = I('get.openid');
            $token    = I('get.token');
            $fix      = C("DB_PREFIX");
                //查询奖品列表        
            $jplist   = M('xydzp_jplist')->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_jplist.xydzp_option_id=' . $fix . 'xydzp_option.id')->field($fix . "xydzp_option.title,gailv,xydzp_id,xydzp_option_id,isdf," . $fix . "xydzp_jplist.gailv_maxnum," . $fix . "xydzp_option.num as kcnum," . $fix . "xydzp_option.miaoshu")->where("xydzp_id=$xydzp_id")->order('type asc,gailv asc')->select();
                //按奖品等级计算概率


            echo $this->getResult($jplist, $xydzp_id, $openid, $token);
        }
    }else{
        $json = json_encode(array("type"=>3));          
        echo urldecode($json);
    } 
}

    //获得中奖结果
    /*
    $result['type'] 奖品回调类型  
    0: 抽奖次数用完
    1：奖品库存为0
    2：成功    
    3: 活动时间未到
    4: 活动已经结束
    */
    private function getResult($jplist, $xydzp_id, $openid, $token)
    {

        $row = $this->is_Maxjoin($xydzp_id, $openid, $token);
        

        if ($row != 0) {
            $arr = array();
            //按概率计算
            $ls = 0;
            foreach ($jplist as $key => $val) {

                //查询奖品库存状态，如果没有库存则中奖率改为0
                $xydzp_option_id = $m['id'] = $val['xydzp_option_id'];
                $dd = M('xydzp_option')->where( $m )->find();

                // 总量小于1或今日发放量大于等于上限
                if($dd['num']<1 || $this->isTodayMax($val)){
                    $arr[] = 0; 
                }else{
                    $arr[] = $val['gailv'];
                    $ls = $ls + 1;  //用来判定奖品是否已经全部发放完毕
                }
            }
            //$arr[] = $val['gailv'];
             //每个奖品的概率
            $rid  = $this->getRand($arr); //根据概率获取奖项id      
            $res  = $jplist[$rid]; //中奖项
            
            if($res['isdf']==1){
                $vl = 444;  
            }else{
                $vl = 2;    
            }
            
            $num = $row;
            if(isset($ls)!=0){
                $num  = $row - 1;   
            }
            
            //用户抽奖次数减1
            //是否第一次参加
            $list = M("xydzp_userlog")->where("xydzp_id=$xydzp_id AND uid='$openid' ")->find();
            if ($list["id"] > 0) {
                M("xydzp_userlog")->where(array(
                    'xydzp_id' => $res['xydzp_id'],
                    'uid' => $openid
                    ))->data(array(
                    'num' => $list["num"] + 1,
                    'cjdate' => time()
                    ))->save();
                } else {
                    M("xydzp_userlog")->add(array(
                        'uid' => $openid,
                        'xydzp_id' => $xydzp_id,
                        'cjdate' => time(),
                        'num' => 1
                        ));
                } 


                if ($res['kcnum'] == 0 || $this->isTodayMax($res)) {
                    $result['type'] = 1;
                }else {
                //用户抽取的那个奖项库存减1         
                    M("xydzp_option")->where(array(
                        'id' => $res['xydzp_option_id']
                        ))->save(array(
                        'num' => ($res['kcnum'] - 1)
                        ));

                //保存用户的中奖信息(排除谢谢惠顾)
                        if ($res["isdf"] != 1) {                
                            M("xydzp_log")->add(array(                    
                                'xydzp_id' => $xydzp_id,
                                'uid' => $openid,
                                'xydzp_option_id' => $res['xydzp_option_id'],
                                'zjdate' => time(),
                                'state' => 0
                                ));
                        }
                    }

                    $result['type'] = $vl;
                    $result['num']        = $num;
            //计算中奖角度的位置             
                    $result['angle']      = 360 - (360 / sizeof($jplist) / 2) - (360 / sizeof($jplist) * ($rid)) - 90;
                    $result['praisename'] = $res["title"];          


                } else {
                    $result['type']       = 0;
                    $result['num']        = $num;
            //计算中奖角度的位置             
                    $result['angle']      = -90;
                    $result['praisename'] = "";
                }

        if(!empty($row) && $ls < 1){ //判断所有奖品是否已经全部发放完毕
            $result['type']       = 404;
            $result['num']        = $num;
            //计算中奖角度的位置             
            $result['angle']      = $ls;
            $result['praisename'] = "";     
        }
        
        
        return $this->json($result);
       // return $this->json($result);
        //exit();
    }

    // 今日大于等于上限，返回true
    private function isTodayMax($res){
        $xydzp_option_id = $res['xydzp_option_id'];
        $today = M('xydzp_log')->where("xydzp_option_id=".$xydzp_option_id." And  FROM_UNIXTIME(zjdate, '%Y/%m/%d')=DATE_FORMAT(CURRENT_DATE,'%Y/%m/%d')")->select();
        $today_count = count($today);
        return $today_count >= $res['gailv_maxnum']; 
    }
    private function getRand($proArr)
    {
        $result = '';
        
        //概率数组的总概率精度 
        $proSum = array_sum($proArr);
        
        //概率数组循环 
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        
        unset($proArr);
        return $result;
    }
    private function json($array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
    //对数组中所有元素做处理
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
    }
    /**
     * **************************公用功能************************************
     */
    // 查询参加次数是否超标 反回剩余次数
    private function is_Maxjoin($xydzp_id, $user_id, $token)
    {
        $huodong = M("xydzp")->where("id=$xydzp_id And token='$token'")->find();
        $list    = M("xydzp_userlog")->where("xydzp_id=$xydzp_id AND uid='$user_id' And FROM_UNIXTIME(cjdate, '%Y/%m/%d')=DATE_FORMAT(CURRENT_DATE,'%Y/%m/%d') ")->find();
        if ($list["id"] > 0) {
            if ($list["num"] > $huodong["choujnum"]) {
                return 0;
            } else {
                return ($huodong["choujnum"] - $list["num"]);
            }
        }else{
            $data['num'] = 0;
            M('xydzp_userlog')->where("xydzp_id=$xydzp_id AND uid='$user_id'")->setField($data);
            return $huodong["choujnum"];
        }
    }

    //活动期限过期与否
    private function _is_overtime($xydzp_id)
    {
        $the_vote = M("xydzp")->where("id=$xydzp_id")->find();       
        if ($the_vote['end_date'] <= time()) {
            return true;
        }
        return false;
    }
    
    //活动是否开始
    private function _is_starttime($xydzp_id)
    {
        $the_vote = M("xydzp")->where(array("id"=>$xydzp_id))->find();      
        if ($the_vote['start_date'] <= time()) {
            return true;
        }
        return false;
    }
    protected function checkAttr($Model, $model_id)
    {
        $fields   = get_model_attribute($model_id, false);
        $validate = $auto = array();
        foreach ($fields as $key => $attr) {
            if ($attr['is_must']) { // 必填字段
                $validate[] = array(
                    $attr['name'],
                    'require',
                    $attr['title'] . '必须!'
                    );
            }
            // 自动验证规则
            if (!empty($attr['validate_rule'])) {
                $validate[] = array(
                    $attr['name'],
                    $attr['validate_rule'],
                    $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误',
                    0,
                    $attr['validate_type'],
                    $attr['validate_time']
                    );
            }
            // 自动完成规则
            if (!empty($attr['auto_rule'])) {
                $auto[] = array(
                    $attr['name'],
                    $attr['auto_rule'],
                    $attr['auto_time'],
                    $attr['auto_type']
                    );
            } elseif ('checkbox' == $attr['type']) { // 多选型
                $auto[] = array(
                    $attr['name'],
                    'arr2str',
                    3,
                    'function'
                    );
            } elseif ('datetime' == $attr['type']) { // 日期型
                $auto[] = array(
                    $attr['name'],
                    'strtotime',
                    3,
                    'function'
                    );
            }
        }
        return $Model->validate($validate)->auto($auto);
    }
    public function printxls(){

        $xydzp_id = I("get.id",0);
        $ptype = I('get.ptype','全部');//条件查询
        $fix = C("DB_PREFIX");
        
        if(empty($ptype) || $ptype=='全部' ){
            $parr = array('xydzp_id' => $xydzp_id,$fix .'ucenter_member.token' => get_token());
        }else{
            $parr = array('xydzp_id' => $xydzp_id,$fix . 'xydzp_option.title' => $ptype,$fix . 'ucenter_member.token' => get_token());
                //$parr = array('xydzp_id' => $xydzp_id,$fix .'ucenter_member.token' => get_token());
        }
        
        
        $als = M('xydzp_log')
                    ->join('left join ' . $fix . 'ucenter_member on ' . $fix . 'xydzp_log.uid=' . $fix . 'ucenter_member.openid')
                    ->join('left join ' . $fix . 'xydzp_option on ' . $fix . 'xydzp_log.xydzp_option_id=' . $fix . 'xydzp_option.id')
                    ->field($fix."xydzp_log.id,".$fix."ucenter_member.username,".$fix."ucenter_member.mobile,".$fix ."xydzp_log.receiver,".$fix."xydzp_log.iphone,".$fix."xydzp_log.address,".$fix."xydzp_option.title,"."FROM_UNIXTIME(".$fix."xydzp_log.zjdate,'%Y年%m月%d')")
                    ->where($parr)
                    ->order($fix."xydzp_log.id asc")
                    ->select();
        //for($i=0;$i<=count($als);$i++){   
        //  $als[$i]['zjdate'] = date("Y-m-d",$als[$i]['zjdate']);
        //}
        
        
        //dump(implode("\n",$als));
        //exit();
        
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");  
        header("Content-Disposition:attachment;filename=".I("get.ptype",'默认xls文件').".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $title = array("编号","用户姓名","用户电话","收件人姓名","收件人电话","收货地址","奖品名","中奖日期");
        
        if (!empty($title)){
            foreach ($title as $k => $v) {
                $title[$k]=iconv("utf-8", "gb2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }

        

        if (!empty($als)){
            foreach($als as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $als[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                }
                $als[$key]=implode("\t", $als[$key]);
                
                
            }
            
            
            echo implode("\n",$als);
        }

    }
    
    
}
