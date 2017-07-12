<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class FindPwdController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
        if (is_logged_in()) {
            redirect(U('Agent/Money/recharge_member'));
            exit;
        }
    }

    public function index() {
        $this->display();
    }

    public function check_userlogin() {
        $user_login = I('user_login');
        if (!trim($user_login)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请输入帐号"));
            exit;
        }
        $hs_account_obj = new \Huosdk\Account();
        $exist = $hs_account_obj->userLoginInUse($user_login);
        if (!$exist) {
            $this->ajaxReturn(array("error" => "1", "msg" => "帐号不存在"));
            exit;
        }
        $info = $hs_account_obj->getAgentInfoByUserLogin($user_login);
        $this->ajaxReturn(array("error" => "0", "msg" => "帐号存在", "phone" => $info['mobile']));
    }

    public function setpwd() {
        $pass1 = I('pwd');
        $pass2 = I('confirm');
        if (!(isset($_SESSION['phoneVerifyCodeMatch']) && $_SESSION['phoneVerifyCodeMatch'])) {
            $this->ajaxReturn(array("error" => "1", "msg" => "需要通过手机号验证"));
            exit;
        }
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
        $hs_account_obj = new \Huosdk\Account();
        $phone = $_SESSION['mobile'];
        $hs_account_obj->resetPwdByPhone($phone, $pass1);
        $this->ajaxReturn(array("error" => "0", "msg" => "密码重设成功，请牢记"));
    }
}
