<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class MemController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
    }

    public function reg() {
        $agent_id = I('agent_id');
        $this->assign("agent_id", $agent_id);
        $agent_name = M('users')->where(array("id" => $agent_id))->getField("user_nicename");
        $this->assign("agent_name", $agent_name);
        $this->display();
    }

    public function memberNameExists($name) {
        return M('members')->where(array("username" => $name))->find();
    }

    public function reg_post() {
        if (!isset($_SESSION['last_reg_cnt'])) {
            $_SESSION['last_reg_cnt'] = 0;
        }
        if (isset($_SESSION['last_reg_time']) && ($_SESSION['last_reg_cnt'] >= 3)) {
            $time_now = time();
            $time_prev = $_SESSION['last_reg_time'];
            if (($time_now - $time_prev) <= 300) {
                $this->ajaxReturn(array("error" => "1", "msg" => "您的注册太频繁，请5分钟后再尝试"));
                exit;
            }
        }
        $name = I('name');
        $pwd = I('pwd');
        $confirm = I('confirm');
        $agent_id = I('agent_id');
        if (empty($name) || empty($pwd) || empty($confirm) || empty($agent_id)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "参数不能为空"));
            exit;
        }
        if ($pwd !== $confirm) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次密码输入不一致"));
            exit;
        }
        if ($this->memberNameExists($name)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "用户名已经被使用"));
            exit;
        }
        $hs_validate_obj = new \Huosdk\Validate();
        $is_valide_pass = $hs_validate_obj->password($pwd);
        if (!$is_valide_pass) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码格式不正确"));
            exit;
        }
        if (!$hs_validate_obj->username($name)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "用户名格式不正确"));
            exit;
        }
        $mid = M('members')->add(
            array(
                "username" => $name,
                "nickname" => $name,
                "password" => $this->member_password($pwd),
                "agent_id" => $agent_id,
                "status"   => "2",
                "reg_time" => time()
            )
        );
        $_SESSION['last_reg_time'] = time();
        $_SESSION['last_reg_cnt'] += 1;
//        $_SESSION['mid']=$mid;
//        $_SESSION['username']=$name;
        $this->ajaxReturn(array("error" => "0", "msg" => "注册成功"));
    }

    function member_password($pw) {
        $authcode = C("AUTHCODE");
        $result = md5(md5($authcode.$pw).$pw);
        return $result;
    }
}

