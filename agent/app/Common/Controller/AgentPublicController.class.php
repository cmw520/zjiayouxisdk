<?php
/**
 * 后台Controller
 */
namespace Common\Controller;

class AgentPublicController extends AppframeController {
    public $agent_roleid;
    public $subagent_roleid;
    public $huoshu_account;
    public $login_url;
    public $site_type;
    public $current_site_domain;
    public $company_info;
    public $current_roleid;

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
        $urls = array(
            "index"       => U('Agent/Money/recharge_member'),
            "front_index" => U('Front/Index/index')
        );
        $this->assign("urls", $urls);
        $v_str = M('options')->where("`option_name` = 'company_info'")->getField("option_value");
        $data = json_decode($v_str, true);
        $this->company_info = $data;
        $this->assign("company_info", $data);
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $this->agent_roleid = $agent_roleid;
        $this->subagent_roleid = $subagent_roleid;
        $this->login_url = U('front/account/login');
        $this->huoshu_account = new \Huosdk\Account();
        if (is_logged_in()) {
            $this->assign("logged_in", "yes");
            $user_info = get_agent_info_by_id($_SESSION['agent_id']);
            $this->assign("user", $user_info);
            $this->current_roleid = $_SESSION['roleid'];
            $this->assign("current_roleid", $this->current_roleid);
            if ($_SESSION['roleid'] == $this->agent_roleid) {
                $role = "agent";
            } else if ($_SESSION['roleid'] == $this->subagent_roleid) {
                $role = "subagent";
            }
            $this->assign("role", $role);
        }
        //判断当前网站类型
        $domain = $_SERVER['SERVER_NAME'];
        $domain_now = HTTP.$domain;
        $this->current_site_domain = $domain_now;
        if ($domain_now == AGENTSITE) {
            $this->site_type = 'agent';
        } else if ($domain_now == SUBAGENTSITE) {
            $this->site_type = 'subagent';
        }
        $this->assign("site_type", $this->site_type);
//        $this->assign("page_title",C('BRAND_NAME'));
    }

    public function is_agent_site() {
        if ($this->site_type == 'agent') {
            return true;
        }
    }

    public function is_subagent_site() {
        if ($this->site_type == 'subagent') {
            return true;
        }
    }

    public function inCaseBalanceNotEnough($amount) {
        $real_pay = $amount;
        $total_balance = get_agent_balance_ptb();
        if (($total_balance < $real_pay)) {
            $this->error("您的账户余额不足，请充值");
            exit;
        }
    }

    /**
     * 消息提示
     *
     * @param type $message
     * @param type $jumpUrl
     * @param type $ajax
     */
    public function success($message = '', $jumpUrl = '', $ajax = false) {
        parent::success($message, $jumpUrl, $ajax);
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
        C("TMPL_PARSE_STRING.__TMPL__", __ROOT__."/".THEME_PATH);
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
     * 排序 排序字段为listorders数组 POST 排序字段为：listorder
     */
    protected function _listorders($model) {
        if (!is_object($model)) {
            return false;
        }
        $pk = $model->getPk(); // 获取主键名称
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            $model->where(
                array(
                    $pk => $key
                )
            )->save($data);
        }

        return true;
    }

    /**
     * 后台分页
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
            'Admin', '{first}{prev}&nbsp;{liststart}{list}{listend}&nbsp;{next}{last}', array(
                       "listlong"      => "9",
                       "first"         => "首页",
                       "last"          => "尾页",
                       "prev"          => "上一页",
                       "next"          => "下一页",
                       "list"          => "*",
                       "disabledclass" => ""
                   )
        );

        return $Page;
    }

    private function check_access($uid) {
        // 如果用户角色是1，则无需判断
        if ($uid == 1) {
            return true;
        }
        $rule = MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
        $no_need_check_rules = array(
            "AdminIndexindex",
            "AdminMainindex"
        );
        if (!in_array($rule, $no_need_check_rules)) {
            return sp_auth_check($uid);
        } else {
            return true;
        }
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

    public function get_code_image($data, $name, $size = '4', $level = 'L', $padding = 2, $logo = true) {
        vendor("phpqrcode.phpqrcode");
        $fileurl = C('UPLOADPATH').C('CODEATH')."/".$name.".png";
        \QRcode::png($data, $fileurl, $level, $size);

        return $fileurl;
    }
}
