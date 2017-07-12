<?php
namespace Common\Controller;

use Think\Controller;

class AppframeController extends Controller {
    function _initialize() {
        $this->assign("waitSecond", 3);
        $time = time();
        $this->assign("js_debug", APP_DEBUG ? "?v=$time" : "");
        if (APP_DEBUG) {
        }
    }

    /**
     * Ajax方式返回数据到客户端
     *
     * @access protected
     *
     * @param mixed  $data 要返回的数据
     * @param String $type AJAX返回数据格式
     *
     * @return void
     */
    protected function ajaxReturn($data, $type = '', $json_option = 0) {
        $data['referer'] = $data['url'] ? $data['url'] : "";
        $data['state'] = $data['status'] ? "success" : "fail";
        $_o_type = $data['status'] ? 1 : 2;
        if (isset($data['msg'])) {
            $this->insertLog($_o_type, $data['msg']);
        } else {
            $this->insertLog($_o_type, json_encode($data));
        }
        if (empty($type)) {
            $type = C('DEFAULT_AJAX_RETURN');
        }
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data, $json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')])
                    ? $_GET[C('VAR_JSONP_HANDLER')]
                    : C(
                        'DEFAULT_JSONP_HANDLER'
                    );
                exit($handler.'('.json_encode($data, $json_option).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            case 'AJAX_UPLOAD':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:text/html; charset=utf-8');
                exit(json_encode($data, $json_option));
            default :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return', $data);
        }
    }

    //分页
    protected function page(
        $Total_Size = 1, $Page_Size = 0, $Current_Page = 1, $listRows = 6, $PageParam = '', $PageLink = '',
        $Static = false
    ) {
        import('Page');
        if ($Page_Size == 0) {
            $Page_Size = C("PAGE_LISTROWS");
        }
        if (empty($PageParam)) {
            $PageParam = C("VAR_PAGE");
        }
        $Page = new \Page($Total_Size, $Page_Size, $Current_Page, $listRows, $PageParam, $PageLink, $Static);
        $Page->SetPager(
            'default', '{first}{prev}{liststart}{list}{listend}{next}{last}',
            array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页",
                  "list"     => "*", "disabledclass" => "")
        );
        return $Page;
    }

    //空操作
    public function _empty() {
        $this->error('该页面不存在！');
    }

    /**
     * 检查操作频率
     *
     * @param int $duration 距离最后一次操作的时长
     */
    protected function check_last_action($duration) {
        $action = MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
        $time = time();
        if (!empty($_SESSION['last_action']['action']) && $action == $_SESSION['last_action']['action']) {
            $mduration = $time - $_SESSION['last_action']['time'];
            if ($duration > $mduration) {
                $this->error("您的操作太过频繁，请稍后再试~~~");
            } else {
                $_SESSION['last_action']['time'] = $time;
            }
        } else {
            $_SESSION['last_action']['action'] = $action;
            $_SESSION['last_action']['time'] = $time;
        }
    }

    //操作类型，1操作成功, 2 操作失败
    protected function insertLog($type = 0, $remark) {
        $userid = sp_get_current_agent_id();
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
}