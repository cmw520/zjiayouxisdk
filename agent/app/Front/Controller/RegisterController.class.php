<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class RegisterController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
        if (is_logged_in()) {
            redirect(U('Agent/Money/recharge_member'));
            exit;
        }
    }

    public function index() {
        /* 1667-5 关闭推广渠道注册功能，一级渠道由后台创建，二级渠道由一级渠道创建*/
        redirect(U('Agent/Money/recharge_member'));
        /* 1667-5 end */
        $this->display();
    }

    public function do_register() {
        $pass1 = I('pwd');
        $pass2 = I('confirm');
        $user_login = I('user_login');
        $user_nicename = I('user_nicename');
        if (!trim($pass1) || !trim($pass2)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码不能为空"));
            exit;
        }
        if ($pass1 !== $pass2) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次密码输入不一致"));
            exit;
        }
        $hs_validate_obj = new \Huosdk\Validate();
        if (!$hs_validate_obj->password2($pass1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码不符合要求，密码需要由字母和数字构成，长度在6-20位"));
            exit;
        }
        if (!$hs_validate_obj->userLogin($user_login)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "登录帐号不符合要求，需要由字母和数字构成，长度在6-20位"));
            exit;
        }
        $hs_account_obj = new \Huosdk\Account();
        if ($hs_account_obj->userLoginInUse($user_login)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "登录帐号已经被注册，请尝试其他帐号"));
            exit;
        }
        if ($hs_account_obj->userNicenameInUse($user_nicename)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "渠道名称已经被使用，请尝试其他名称"));
            exit;
        }
        $agent_id = $hs_account_obj->createAgent2($user_login, $user_nicename, $pass1);
        if (!$agent_id) {
            $this->ajaxReturn(array("error" => "1", "msg" => "用户创建失败，请重试"));
            exit;
        }
        $this->mark_user_logged_in($agent_id);
        $this->ajaxReturn(array("error" => "0", "msg" => "注册成功，欢迎您的加入^_^"));
    }

    function mark_user_logged_in($agent_id) {
        $_SESSION['logged_in'] = true;
        $_SESSION['agent_id'] = $agent_id;
        $hs_account_obj = new \Huosdk\Account();
        $info = $hs_account_obj->get_agent_info_by_id($agent_id);
        $_SESSION['roleid'] = $info['user_type'];
    }
}
