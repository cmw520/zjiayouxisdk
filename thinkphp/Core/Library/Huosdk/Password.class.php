<?php
namespace Huosdk;
class Password {
    public function CheckAgentPaypwd($agent_id, $payPwd) {
        $sp_pw = $this->pay_password_algorithm($payPwd);
        return M('users')->where(array("id" => $agent_id, "pay_pwd" => $sp_pw))->find();
    }

    public function checkAdminPaypwd($agent_id, $payPwd) {
        $sp_pw = $this->pay_password_algorithm($payPwd);
        return M('users')->where(array("id" => $agent_id, "pay_pwd" => $sp_pw))->find();
    }

    function pay_password_algorithm($pw, $authcode = '') {
        if (empty($authcode)) {
            $authcode = C("AUTHCODE");
        }
        $result = md5(md5($authcode.$pw).$pw);
        return $result;
    }

    function member_password($pw) {
        $authcode = C("AUTHCODE");
        $result = md5(md5($authcode.$pw).$pw);
        return $result;
    }
}

