<?php
/**
 * 后台Controller
 */
namespace Common\Controller;
class AdminbaseController extends AppframeController {
    protected $role_type, $agentwhere, $row;

    public function __construct() {
        $admintpl_path = C("SP_ADMIN_TMPL_PATH").C("SP_ADMIN_DEFAULT_THEME")."/";
        C("TMPL_ACTION_SUCCESS", $admintpl_path.C("SP_ADMIN_TMPL_ACTION_SUCCESS"));
        C("TMPL_ACTION_ERROR", $admintpl_path.C("SP_ADMIN_TMPL_ACTION_ERROR"));
        parent::__construct();
        $time = time();
        $this->assign("js_debug", APP_DEBUG ? "?v=$time" : "");
    }

    function _initialize() {
        parent::_initialize();
        $this->load_app_admin_menu_lang();
        if (isset($_SESSION['ADMIN_ID'])) {
            $users_obj = M("Users");
            $id = get_current_admin_id();
            $user = $users_obj->where("id=$id")->find();
            if (!$this->check_access($id)) {
                $this->error("您没有访问权限！");
                exit();
            }
            $this->role_type = sp_get_current_roletype();
            $this->agentwhere = " >=0 ";
            if (3 == $this->role_type) {
                $userids = $this->_getOwnerAgents();
                $this->agentwhere = " in (".$userids.") ";
            } elseif (4 == $this->role_type) {
                $this->agentwhere = "=".sp_get_current_admin_id();
            }
            $this->row = 10;
            $this->assign("admin", $user);
        } else {
            //$this->error("您还没有登录！",U("admin/public/login"));
            if (IS_AJAX) {
                $this->error("您还没有登录！", U("admin/public/login"));
            } else {
                header("Location:".U("admin/public/login"));
                exit();
            }
        }
    }

    /**
     * 初始化后台菜单
     */
    public function initMenu() {
        $Menu = F("Menu");
        if (!$Menu) {
            $Menu = D("Common/Menu")->menu_cache();
        }

        return $Menu;
    }

    /**
     * 消息提示
     *
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax
     */
    public function success($message = '', $jumpUrl = '', $ajax = false) {
        $this->insertLog(1, $message);
        parent::success($message, $jumpUrl, $ajax);
    }

    /**
     * 消息提示
     *
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax
     */
    public function error($message = '', $jumpUrl = '', $ajax = false) {
        $this->insertLog(2, $message);
        parent::error($message, $jumpUrl, $ajax);
    }

    public function ajaxReturn($data, $type = '', $json_option = 0) {
        $_type = $data['status'] ? 1 : 2;
        if (isset($data['msg'])) {
            $this->insertLog($_type, $data['msg']);
        } else {
            $this->insertLog($_type, json_encode($data));
        }
        parent::ajaxReturn($data, $type, $json_option);
    }

    /**
     * 模板显示
     *
     * @param type   $templateFile 指定要调用的模板文件
     * @param type   $charset      输出编码
     * @param type   $contentType  输出类型
     * @param string $content      输出内容
     *                             此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
     */
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType);
    }

    /**
     * 获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     *
     * @access protected
     *
     * @param string $templateFile 指定要调用的模板文件
     *                             默认为空 由系统自动定位模板文件
     * @param string $content      模板输出内容
     * @param string $prefix       模板缓存前缀*
     *
     * @return string
     */
    public function fetch($templateFile = '', $content = '', $prefix = '') {
        $templateFile = empty($content) ? $this->parseTemplate($templateFile) : '';

        return parent::fetch($templateFile, $content, $prefix);
    }

    public function verifyPaypwd($password) {
        $user_model = M('users');
        $uid = get_current_admin_id();
        $admin = $user_model->where(array("id" => $uid))->find();
        $password = pay_password($password, C('AUTHCODE'));
        if ($admin['pay_pwd'] != $password) {
            $this->error("密码错误,发放失败.");
            exit;
        }

        return true;
    }

    /**
     * 自动定位模板文件
     *
     * @access protected
     *
     * @param string $template 模板文件规则
     *
     * @return string
     */
    public function parseTemplate($template = '') {
        $tmpl_path = C("SP_ADMIN_TMPL_PATH");
        define("SP_TMPL_PATH", $tmpl_path);
        // 获取当前主题名称
        $theme = C('SP_ADMIN_DEFAULT_THEME');
        if (is_file($template)) {
            // 获取当前主题的模版路径
            define('THEME_PATH', $tmpl_path.$theme."/");

            return $template;
        }
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        // 获取当前模块
        $module = MODULE_NAME."/";
        if (strpos($template, '@')) { // 跨模块调用模版文件
            list($module, $template) = explode('@', $template);
        }
        // 获取当前主题的模版路径
        define('THEME_PATH', $tmpl_path.$theme."/");
        // 分析模板文件规则
        if ('' == $template) {
            // 如果模板文件名为空 按照默认规则定位
            $template = CONTROLLER_NAME.$depr.ACTION_NAME;
        } elseif (false === strpos($template, '/')) {
            $template = CONTROLLER_NAME.$depr.$template;
        }
        C("TMPL_PARSE_STRING.__TMPL__", __ROOT__."/");
        C('SP_VIEW_PATH', $tmpl_path);
        C('DEFAULT_THEME', $theme);
        define("SP_CURRENT_THEME", $theme);
        $file = sp_add_template_file_suffix(THEME_PATH.$module.$template);
        $file = str_replace("//", '/', $file);
        if (!file_exists_case($file)) {
            E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
        }

        return $file;
    }

    /**
     *  排序 排序字段为listorders数组 POST 排序字段为：listorder
     */
    protected function _listorders($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); //获取主键名称
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where(array($pk => $key))->save($data);
        }

        return true;
    }

    /**
     * 后台分页
     *
     */
    protected function page(
        $total_size = 1, $page_size = 0, $current_page = 1, $listRows = 6, $pageParam = '', $pageLink = '',
        $static = false
    ) {
        if ($page_size == 0) {
            $page_size = C("PAGE_LISTROWS");
        }
        if (empty($pageParam)) {
            $pageParam = C("VAR_PAGE");
        }
        $Page = new \Page($total_size, $page_size, $current_page, $listRows, $pageParam, $pageLink, $static);
        $Page->SetPager(
            'Admin', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}',
            array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页",
                  "list"     => "*", "disabledclass" => "")
        );

        return $Page;
    }

    private function check_access($uid) {
        //如果用户角色是1，则无需判断
        if ($uid == 1) {
            return true;
        }
        $rule = MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
        $no_need_check_rules = array("AdminIndexindex", "AdminMainindex");
        if (!in_array($rule, $no_need_check_rules)) {
            return sp_auth_check($uid);
        } else {
            return true;
        }
    }

    //支付密码
    public function pay_password($pw, $pre) {
        $decor = md5($pre);
        $mi = md5($pw);

        return substr($decor, 0, 12).$mi.substr($decor, -4, 4);
    }

    function _game(
        $option = true, $status = null, $is_delete = null, $is_sdk = null, $is_app = null, $classfy = 3,
        $game_flag = false
    ) {
        $cates = array(
            "0" => "选择游戏",
        );
        if ($status) {
            $where['status'] = 2;
        }
        if ($is_delete) {
            $where['is_delete'] = 2;
        }
        if ($is_sdk) {
            $where['is_own'] = 2;
        }
        if ($is_app) {
            $where['is_app'] = 2;
        }
        if ($classfy) {
            if (3 == substr($classfy, 0, 1)) {
                $where['_string'] = " classify=3 OR (classify BETWEEN 300 AND 399)";
            } else {
                $where['classify'] = $classfy;
            }
        }
        if (3 <= $this->role_type) {
            $agents = $this->_getOwnerAgents();
            $apparr = M('agent_game')->where(array('agent_id' => array('in', $agents)))->getField('app_id', true);
            if ($apparr) {
                $where['id'] = array('in', implode(',', $apparr));
            }
        }
        if ($game_flag) {
            $_group = "game_id";
            $games = M('game')->where($where)->group($_group)->getField("game_id id,name gamename", true);
        } else {
            $games = M('game')->where($where)->getField("id id,name gamename");
        }
        if ($option && $games) {
            $games = $cates + $games;
        }
        $this->assign("games", $games);
    }

    function _game_from($option = true) {
        $cates = array(
            "3" => "CP"
        );
        $where['status'] = 2;
        $where['_string'] = " id BETWEEN 300 AND 399";
        $_g_class = M('game_class')->where($where)->getField("id,realname name", true);
        if (!empty($_g_class)) {
            $_g_class = $cates + $_g_class;
        } else {
            $_g_class = $cates;
        }
        $this->assign("gamefrom", $_g_class);
    }

    function _game_type($option = true) {
        $cates = array(
            "0" => "全部类型",
        );
        $where['status'] = 2;
        $gametypes = M('game_type')->where($where)->getField("id,name gametype", true);
        if ($option && $gametypes) {
            $gametypes = $cates + $gametypes;
        }
        $this->assign("gametypes", $gametypes);
    }

    function _agents($agent_id = 0, $option = true) {
        $cates = array(
            "0" => "全部渠道"
        );
        if (empty($agent_id)) {
            $agent_id = sp_get_current_admin_id();
        }
        $where['user_type'] = array('GT', '1');
        $roletype = $this->_get_role_type($agent_id);
        if (3 <= $this->role_type) {
            $aidstr = $this->_getOwnerAgents($agent_id);
            $where['id'] = array('in', $aidstr);
        }
        $agents = M('users')->where($where)->getField("id,user_nicename agentname", true);
        if ($option && $agents) {
            $agents = $cates + $agents;
        }
        $this->assign("agents", $agents);
    }

    function _roles($type = null, $option = true) {
        $cates = array(
            "0" => "全部"
        );
        $where = "status=1";
        if ($type) {
            $where .= " AND role_type >= ".$type;
        }
        $roles = M('role')->where($where)->getField("id,name", true);
        if ($option && $roles) {
            $roles = $cates + $roles;
        }
        $this->assign("roles", $roles);
    }

    function _roletypes($type = null, $option = true) {
        $cates = array(
            "0" => "全部",
            "1" => "超级管理员",
        );
        $roletypes = array(
            "2" => "平台人员",
            "3" => "渠道市场",
            "4" => "渠道"
        );
        if ($option) {
            $roletypes = $cates + $roletypes;
        }
        $this->assign("roletypes", $roletypes);
    }

    function _getOwnerAgents($userid = null) {
        if (empty($userid)) {
            $userid = sp_get_current_admin_id();
        }
        $usersids = M('users')->where("ownerid=$userid OR id=$userid")->getField("id", true);
        $idstr = implode(',', $usersids);

        return $idstr;
    }

    function _get_role_type($userid = null) {
        if (empty($userid)) {
            $userid = sp_get_current_admin_id();
        }
        $role_user_model = M("RoleUser");
        $role_user_join = C('DB_PREFIX').'role as b on a.role_id =b.id';
        $role_type = $role_user_model->alias("a")->join($role_user_join)->where(
            array("user_id" => $userid, "status" => 1)
        )->getField("min(role_type)");

        return $role_type;
    }

    function _mem_status() {
        $cates = array(
            1 => "试玩",
            2 => "正常",
            3 => "冻结"
        );
        $this->assign("memstatus", $cates);
    }

    private function load_app_admin_menu_lang() {
        if (C('LANG_SWITCH_ON', null, false)) {
            $admin_menu_lang_file = SPAPP.MODULE_NAME."/Lang/".LANG_SET."/admin_menu.php";
            if (is_file($admin_menu_lang_file)) {
                $lang = include $admin_menu_lang_file;
                L($lang);
            }
        }
    }

    /**
     * 图片上传类
     *
     * @date  : 2016年4月9日上午11:26:50
     *
     * @param NULL
     *
     * @return NULL
     * @since 1.0
     */
    public function upload($up_info, $savePath, $name) {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = C('UPLOADPATH').$savePath.'/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        $upload->saveName = $name;
        $upload->autoSub = false;
        $upload->replace = true;
        $info = $upload->uploadOne($up_info);
        /* 上传错误提示错误信息 */
        if (!$info) {
            $return['status'] = 0;
            $return['msg'] = $upload->getError();
        } else {
            /* 上传成功 */
            $return['status'] = 1;
            $return['msg'] = $info['savepath'].$info['savename'];
        }

        return $return;
    }

    /**
     * 图片上传类
     *
     * @date  : 2016年4月9日上午11:26:50
     *
     * @param NULL
     *
     * @return NULL
     * @since 1.0
     */
    public function uploads($up_info, $savePath, $name) {
        $name_array = array('uniqid');
        foreach ($up_info['name'] as $key => $val) {
            $saveName = $name.'_'.time().'_';
            array_push($name_array, $saveName);
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = C('UPLOADPATH').$savePath.'/'; // 设置附件上传根目录
        $upload->savePath = ""; // 设置附件上传（子）目录
        $upload->saveName = $name_array;
        $upload->replace = true;
        $upload->autoSub = false;
        $info = $upload->upload($up_info);
        /* 上传错误提示错误信息 */
        if (!$info) {
            $return['status'] = 0;
            $return['msg'] = $upload->getError();
        } else {
            /* 上传成功 */
            $return['status'] = 1;
            $return['msg'] = '';
            foreach ($info as $file) {
                $return['msg'] = $return['msg'].','.$file['savename'];
            }
            $return['msg'] = substr($return['msg'], 1);
        }

        return $return;
    }

    function _authPaypwd($repwd) {
        if (empty($repwd)) {
            $repwd = I('post.repwd');
        }
        if (empty($repwd)) {
            $this->error("请输入二级密码！");
            exit;
        }
        $user_obj = D("Common/Users");
        $uid = get_current_admin_id();
        $admin = $user_obj->where(array("id" => $uid))->find();
        $repwd = sp_password($repwd);
        if ($admin['pay_pwd'] != $repwd) {
            $this->error("二级密码错误,操作失败！");
            exit;
        }
    }

    //操作类型，1操作成功, 2 操作失败
    protected function insertLog($type = 0, $remark) {
        $userid = sp_get_current_admin_id();
        if (empty($userid)) {
            $userid = 0;
        }
        $data['user_id'] = $userid;
        $data['username'] = $_SESSION['name'];
        $data['action'] = MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
        $data['create_time'] = time();
        $data['type'] = $type;
        $data['ip'] = get_client_ip();
        $data['addr'] = get_ip_attribution($data['ip']);
        $data['param'] = 'GET:'.json_encode($_GET).'; POST:'.json_encode($_POST);
        $data['remark'] = $remark;
        $result = M('admin_operate_log')->add($data); // 写入数据到数据库
        return $result;
    }

    //type 0 表示登陆 1表示已经登陆,1输入网址再次登陆,2表示登出
    protected function admin_login_log($type = 0, $last_login_ip = null) {
        if (empty($last_login_ip)) {
            $last_login_ip = get_client_ip();
        }
        $adminlog['type'] = $type;
        $adminlog['user_id'] = get_current_admin_id();
        $adminlog['ip'] = $last_login_ip;
        $adminlog['deviceinfo'] = $_SERVER["HTTP_USER_AGENT"];
        $adminlog['login_time'] = time();
        $adminlog['addr'] = get_ip_attribution($last_login_ip);
        M('admin_login_log')->add($adminlog);
    }

    /*
	**xls导出
	*/
    public function exportExcel($expTitle, $expCellName, $expTableData) {
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel");
        vendor("PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
                          'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ',
                          'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY',
                          'AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum - 1].'1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue(
                    $cellName[$j].($i + 3), $expTableData[$i][$expCellName[$j][0]]
                );
            }
        }
        ob_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /************自定义方法********/
    /**
     *  删除提示信息
     *
     * @access protected
     *
     * @param bool   $res 判断结果
     * @param string $url 跳转地址
     */
    protected function my_delete_msg($res, $url = '') {
        if ($res) {
            $this->success('删除成功', $url);
        } else {
            $this->error('删除失败');
        }
    }

    /**
     *  添加提示信息
     *
     * @access protected
     *
     * @param bool   $res 判断结果
     * @param string $url 跳转地址
     */
    protected function my_add_msg($res, $url = '') {
        if ($res) {
            $this->success('添加成功', $url);
        } else {
            $this->error('添加失败');
        }
    }

    /**
     *  修改提示信息
     *
     * @access protected
     *
     * @param bool   $res 判断结果
     * @param string $url 跳转地址
     */
    protected function my_edit_msg($res, $url = '') {
        if ($res) {
            $this->success('修改成功', $url);
        } else {
            $this->error('修改失败');
        }
    }

    /**
     *  数据库操作方法 获取某一条数据 my_find
     *
     * @access protected
     *
     * @param object       $object 数据表对象
     * @param string       $field  要获取的字段名
     * @param string|array $where  查询条件
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_find($object, $where = '', $field = true) {
        $data = $object->field($field)->where($where)->find();

        return $data;
    }

    /**
     *  数据库操作方法 获取多条数据 my_select
     *
     * @param object       $object 数据表对象
     * @param string       $field  要获取的字段名
     * @param string|array $where  查询条件
     * @param string       $order  排序方法
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_select($object, $field = true, $where = '', $order = '') {
        $data = $object->field($field)->where($where)->order($order)->select();

        return $data;
    }

    /**
     *  数据库操作方法 获取多条数据 my_join_select
     *
     * @param object       $object 数据表对象
     * @param string       $alias  数据表别名
     * @param string       $field  要获取的字段名
     * @param string       $join   要连接的数据表
     * @param string|array $where  查询条件
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_join_select($object, $alias = '', $field = true, $join = '', $where = '', $order = '') {
        $data = $object->alias($alias)->field($field)->join($join)->where($where)->order($order)->select();

        return $data;
    }

    /**
     *  数据库操作方法 获取多条数据 my_join_select
     *
     * @param object       $object 数据表对象
     * @param string       $join   要连接的数据表
     * @param string       $alias  数据表别名
     * @param string       $field  要获取的字段名
     * @param string|array $where  查询条件
     * @param string       $group  分组字段
     * @param string       $have   分组后刷选字段
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_group_select(
        $object, $join = '', $alias = '', $field = true, $where = '', $group = '', $have = '', $order = ''
    ) {
        if (!empty($join) && !empty($object)) {
            $data = $object->alias($alias)->field($field)->join($join)->where($where)->group($group)->having($have)
                           ->order($order)->select();
        } else {
            $this->error('$join 不能为空');
        }

        return $data;
    }

    /**
     *  获取某个字段的值 my_getfield 这个数据可以将要查询的信息放到一个关联数组下面去。例如游戏的信息全部放到游戏的ID下的数组下。
     *
     * @param object       $object 数据表对象
     * @param string       $field  要获取的字段名
     * @param string       $alias  数据表别名
     * @param string       $join   要连接的数据表
     * @param string|array $where  查询条件
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_getfield($object, $field = '', $where = '', $alias = '', $join = '') {
        $data = $object->alias($alias)->join($join)->where($where)->getField($field, true);

        return $data;
    }

    /**
     *  分页查询获取数组 my_page_data()
     *
     * @param object       $object 数据表对象
     * @param string       $alias  数据表别名
     * @param string       $field  要获取的字段名
     * @param string       $join   要连接的数据表
     * @param array        $map    搜索查询条件
     * @param string|array $where  查询限定条件（例如状态，是否删除等）
     * @param object       $page   分页类
     * @param string       $order  排序字符串
     *
     * @return array       $data   查询得到的数据
     */
    protected function my_page_data(
        $object, $alias = '', $field = true, $join = '', $map = array(), $where = '', $page = '', $order = ''
    ) {
        //$where = empty($where) ? $map : $where;
        $map = empty($map) ? $where : $map;
        $data = $object->alias($alias)->field($field)->join($join)
                       ->where($map)->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)
                       ->select();

        return $data;
    }

    /**
     *  处理提交的数据 my_handle_data()
     *
     * @access protected
     *
     * @param array $fields     需要查询的数据
     * @param array $where_ands 需要查询的条件
     *
     * @return $where_ands
     */
    protected function my_handle_data($fields, $where_ands = array()) {
        if (IS_POST) {
            foreach ($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_POST[$param]);
                    $_GET[$param] = $get;
                    if ('start_time' == $param || 'end_time' == $param) {
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach ($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_GET[$param]);
                    if ('start_time' == $param || 'end_time' == $param) {
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $map = join(" AND ", $where_ands);

        return $map;
    }

    /**
     *  实例化分页类
     *
     * @access protected
     *
     * @param  int $count 数据总条数
     *
     * @return object $page  分页类
     */
    protected function my_page($count) {
        $row = C('PAGE_SIZE') ? C('PAGE_SIZE') : 10;
        $page = $this->page($count, $row);

        return $page;
    }

    /**
     *  设定某个字段
     *
     * @access protected
     *
     * @param object       $object 数据库对象
     * @param string|array $where  修改条件
     * @param array        $data   修改的数据
     *
     * @return bool        $res
     */
    protected function my_setField($object, $where, $data) {
        $res = $object->where($where)->setField($data);

        return $res;
    }

    /**
     *  修改数据
     *
     * @access protected
     *
     * @param object $object 数据库对象
     *
     * @return bool  $res
     */
    protected function my_save($object, $data = array()) {
        if ($data['id']) {
            $res = $object->data($data)->save();
        } else {
            $this->error('请输入需要修改的ID');
        }

        return $res;
    }

    /**
     *  添加数据
     *
     * @access protected
     *
     * @param object $object 数据库对象
     * @param array  $data   保存的数据
     *
     * @return bool  $res
     */
    protected function my_data_add($object, $data) {
        if (!empty($data)) {
            $res = $object->data($data)->add();
        } else {
            $this->error('请填写需要保存的数据');
        }

        return $res;
    }

    /**
     *  用create添加数据
     *
     * @access protected
     *
     * @param  object $object 数据库对象
     *
     * @return bool   $res    添加结果
     */
    protected function my_data_create($object) {
        if ($object->create()) {
            $res = $object->add();
        } else {
            $this->error('创建数据对象失败');
        }

        return $res;
    }

    /**
     *  删除数据
     *
     * @access protected
     *
     * @param object $object 数据库对象
     * @param object $where  删除的条件
     * @param bool   $res    删除结果
     */
    protected function my_delete($object, $where) {
        $res = $object->where($where)->delete();

        return $res;
    }

    /**
     *  获取缩略图
     *
     * @access public
     *
     * @param string $path      原图片路径
     * @param string $imagename 原图片名称
     * @param string $thumbname 缩略图名称
     * @param int    $width     宽度
     * @param int    $height    高度
     * @param string $attr      缩略图的方式
     *
     * @return void
     */
    function get_thumb($imagename, $thumbname = '', $attr = \Think\Image::IMAGE_THUMB_SCALE, $width = 320, $height = 240
    ) {
        /* 获取缩略图 */
        $image = new \Think\Image();
        if (empty($thumbname)) {
            $thumbname = 'th_'.pathinfo($imagename, PATHINFO_BASENAME);
        }
        // 打开图片
        $image->open($imagename);
        // 获取缩略图并保存
        $image->thumb($width, $height, $attr)->save($thumbname);
    }
}