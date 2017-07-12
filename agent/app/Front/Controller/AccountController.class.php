<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class AccountController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
        if (is_logged_in()) {
            redirect(U('Agent/money/recharge_member'));
            exit;
        }
    }

    public function register() {
        $this->display();
    }

    public function register2() {
        $this->display();
    }

    public function login() {
        redirect(U('Front/Index/index2'));
       exit;
        // 获取渠道登录界面的背景图片
        //$ads_image = M('web_footer')->where(array('title'=>'ads_image'))->getField('content');
        $this->assign('ads_image', $ads_image);

        // 获取渠道登录的底部信息
        $where['id'] = array('neq', 5);
        //$foot_info = M('web_footer')->where($where)->getField('title,content', true);
	
        //$this->assign($foot_info);
        $this->display();
    }

    public function do_login() {
        $phone = I('userName');
        $pass = I('userPass');
        $checkCode = I('checkCode');
        if (!$checkCode) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请输入验证码"));
            exit;
        }
        $verify = new \Think\Verify();
        if (!$verify->check($checkCode)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "验证码错误"));
            exit;
        }
        $user_result = $this->huoshu_account->AgentOrSubPhonePassMatch($phone, $pass);
        if ($user_result['error'] == "1") {
            $this->ajaxReturn(array("error" => "1", "msg" => $user_result['msg']));
            exit;
        }
        $user = $user_result['msg'];
        $this->huoshu_account->MarkAgentOrSubLoggedIn($user['id']);
        //每次登录，记录渠道登录信息
        $hs_all_obj = new \Huosdk\AgentLoginLog();
        $hs_all_obj->add($user['id']);
        $this->ajaxReturn(array("error" => "0", "msg" => "登陆成功"));
    }

    public function checkPhone() {
        $phone = I('phone');
        if (!$this->is_valide_phone_number($phone)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "手机号码格式不正确，请重新输入"));
            exit;
        }
        if ($this->huoshu_account->AgentPhoneAlreadyRegisterd($phone)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "此号码已经注册过，请选择其他手机号"));
            exit;
        }
        //手机验证码发送后，只有status为1的时候是正确的，其他的都是异常的
        $phone_send_result = $this->sendPhoneVerifyCode($phone);
        if ($phone_send_result['status'] == 1) {
            $this->ajaxReturn(array("error" => "0", "msg" => "验证码发送成功，请尽快输入"));
            exit;
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => $phone_send_result['msg']));
            exit;
        }
    }

    public function VerifyPhoneCode() {
        $PhoneVerifyCode = I('PhoneVerifyCode');
        if ($PhoneVerifyCode != $_SESSION['sms_code']) {
            $_SESSION['phoneVerifyCodeMatch'] = false;
            $this->ajaxReturn(array("error" => "1", "msg" => "验证码不正确"));
            exit;
        }
        $_SESSION['phoneVerifyCodeMatch'] = true;
        $this->ajaxReturn(array("error" => "0", "msg" => "验证成功"));
    }

    public function forgetPWDVerifyPhoneCode() {
        $this->VerifyPhoneCode();
    }

    public function checkPass() {
        $pass1 = I('pass1');
        $pass2 = I('pass2');
        //进一步操作之前，我们需要知道用户是否真的通过了手机号码验证
        if (!(isset($_SESSION['phoneVerifyCodeMatch']) && $_SESSION['phoneVerifyCodeMatch'])) {
            $this->ajaxReturn(array("error" => "1", "msg" => "需要通过手机号验证"));
            exit;
        }
        if ($pass1 !== $pass2) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次密码输入不一致"));
            exit;
        }
        if (!$this->is_valide_password($pass1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码不符合要求，密码需要由字母和数字构成，长度在6-20位"));
            exit;
        }
        if (!$this->huoshu_account->createAgent($_SESSION['phone'], $pass1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "用户创建失败，请重试"));
            exit;
        }
        mark_user_logged_in();
        $this->ajaxReturn(array("error" => "0", "msg" => "注册成功！"));
    }

    public function forgetPWDcheckPass() {
        $pass1 = I('pass1');
        $pass2 = I('pass2');
        //进一步操作之前，我们需要知道用户是否真的通过了手机号码验证
        if (!(isset($_SESSION['phoneVerifyCodeMatch']) && $_SESSION['phoneVerifyCodeMatch'])) {
            $this->ajaxReturn(array("error" => "1", "msg" => "需要通过手机号验证"));
            exit;
        }
        if ($pass1 !== $pass2) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次密码输入不一致"));
            exit;
        }
        if (!$this->is_valide_password($pass1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码不符合要求，密码需要由字母和数字构成，长度在6-20位"));
            exit;
        }
        if (!$this->huoshu_account->resetPwdByPhone($_SESSION['phone'], $pass1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "修改密码失败，请重试"));
            exit;
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "重设密码成功！"));
    }

    private function is_valide_password($pass) {
        //密码必须由字母和数字组成，长度在6到20位之间
        //2016-08-06 10:25:33 严旭
        return preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/", $pass);
    }

    private function sendPhoneVerifyCode($phone) {
        $_SESSION['phone'] = $phone;
        $result = sendMsg_alidayu($phone);
//        $result= \HuoShu\Sms\Alidayu::send($phone,'SMSTEMPREG');
        return $result;
    }

    private function is_valide_phone_number($phone) {
        return preg_match("/^1[34578]\d{9}$/", $phone);
    }

    public function userTerms() {
        $this->display();
    }

    public function forgetPWD() {
        $this->display();
    }

    public function forgetPWDCheckPhone() {
        $phone = I('phone');
        if (!$this->is_valide_phone_number($phone)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "手机号码格式不正确，请重新输入"));
            exit;
        }
        $where = array();
        $where['_string'] = "( user_type = $this->agent_roleid ) OR ( user_type = $this->subagent_roleid )";
        $where['mobile'] = $phone;
        $user = M('users')->where($where)->find();
        if (!$user) {
            $this->ajaxReturn(array("error" => "1", "msg" => "帐号不存在，请核对后再输入"));
            exit;
        }
        $_SESSION['phone'] = $phone;
        $phone_send_result = sendMsg_alidayu($phone, "您正在重置密码，验证码为");
        if ($phone_send_result['status'] == 1) {
            $this->ajaxReturn(array("error" => "0", "msg" => "验证码发送成功，请尽快输入"));
            exit;
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => $phone_send_result['msg']));
            exit;
        }
    }
}
