<?php
namespace Huosdk;
class Account {
    public $agentRoldId;
    public $subAgentRoldId;

    public function __construct() {
        $this->agentRoldId = $this->getAgentRoleId();
        $this->subAgentRoldId = $this->getSubAgentRoleId();
    }

    public function getAgentIdByUserLogin($user_login) {
        return M('users')->where(array("user_login" => $user_login))->getField("id");
    }

    public function get_parent_agent_id($agent_id) {
        return M('users')->where(array("id" => $agent_id))->getField("ownerid");
    }

    public function getUserName($agent_id) {
        return M('users')->where(array("id" => $agent_id))->getField("user_nicename");
    }

    public function getAgentInfoByUserLogin($user_login) {
        return M('users')->where(array("user_login" => $user_login))->find();
    }

    public function getMemIdByName($name) {
        return M('members')->where(array("username" => $name))->getField("id");
    }

    public function getAgentRoleId() {
        return M('role')->where(array("name" => "渠道专员"))->getField("id");
    }

    public function getSubAgentRoleId() {
        return M('role')->where(array("name" => "公会渠道"))->getField("id");
    }

    public function allAgents() {
        $rs = M('users')->where(array("user_type" => $this->agentRoldId))->select();

        return $rs;
    }

    public function allAgentIds() {
        $rs = M('users')->where(array("user_type" => $this->agentRoldId))->getField("id", true);

        return $rs;
    }

    public function allSubAgents() {
        $rs = M('users')->where(array("user_type" => $this->subAgentRoldId))->select();

        return $rs;
    }

    public function allSubAgentIds() {
        $rs = M('users')->where(array("user_type" => $this->subAgentRoldId))->getField("id", true);

        return $rs;
    }

    public function createAgent($phone, $pass) {
        $encrypt_pass = sp_password($pass);
        $pay_pass = pay_password($pass);
        $time = date("Y-m-d H:i:s");
        $model = M('users');
        $data = array(
            "user_login"  => $phone,
            "user_pass"   => $encrypt_pass,
            "pay_pwd"     => $pay_pass,
            "user_type"   => $this->agentRoldId,
            "user_status" => "1", //刚注册的用户处于待审核的状态
            "mobile"      => $phone,
            "create_time" => $time
        );
        $result = $model->data($data)->add();
        return $result;
    }

    public function createAgent2($user_login, $user_nicename, $pass) {
        $encrypt_pass = sp_password($pass);
        $time = date("Y-m-d H:i:s");
        $model = M('users');
        $data = array(
            "user_login"    => $user_login,
            "user_pass"     => $encrypt_pass,
            "user_nicename" => $user_nicename,
            "user_type"     => $this->agentRoldId,
            "user_status"   => "2", //刚注册的用户处于待审核的状态
            "create_time"   => $time
        );
        $result = $model->data($data)->add();
        return $result;
    }

    public function userLoginInUse($user_login) {
        $exist = M('users')->where(array("user_login" => $user_login))->find();
        return $exist;
    }

    public function userNicenameInUse($user_nicename) {
        $exist = M('users')->where(array("user_nicename" => $user_nicename))->find();
        return $exist;
    }

    public function AgentPhoneInUse($phone) {
        $where = array();
        $where['mobile'] = $phone;
        $where['_string'] = "(( user_type = $this->agentRoldId ) OR ( user_type = $this->subAgentRoldId ))";
        return M('users')->where($where)->find();
    }

    public function checkMemberBelongToAgent($name, $agid) {
        return M('members')->where(array("username" => $name, "agent_id" => $agid))->count();
    }

    public function createSubAgent($ownerId, $phone, $pass) {
        $encrypt_pass = sp_password($pass);
        $en_pay_pwd = pay_password($pass);
        $time = date("Y-m-d H:i:s");
        $model = M('users');
        $data = array(
            "user_login"  => $phone,
            "user_pass"   => $encrypt_pass,
            "pay_pwd"     => $en_pay_pwd,
            "user_type"   => $this->subAgentRoldId,
            "user_status" => "1", //刚注册的用户处于待审核的状态
            "mobile"      => $phone,
            "create_time" => $time,
            "ownerid"     => $ownerId
        );
        $result = $model->data($data)->add();
        return $result;
    }

    public function resetPwdByPhone($phone, $pass) {
        $encrypt_pass = sp_password($pass);
        $model = M('users');
        $data = array(
            "user_pass" => $encrypt_pass
        );
        $condition = array(
            "mobile"    => $phone,
            "user_type" => array('in', array($this->agentRoldId, $this->subAgentRoldId)),
        );
        $result = $model->where($condition)->save($data);
        return $result;
    }

    public function setUserPwdById($user_id, $pass) {
        $encrypt_pass = sp_password($pass);
        $model = M('users');
        $data = array(
            "user_pass" => $encrypt_pass
        );
        $result = $model->where(array("id" => $user_id))->save($data);
        return $result;
    }

    public function setUserPwd($id, $pre, $pwd) {
        $model = M('users');
        $en_pre = sp_password($pre);
        $exist = $model->where(array("id" => "$id", "user_pass" => $en_pre))->find();
        if (!$exist) {
            return "原密码不正确";
        }
        $en_new = sp_password($pwd);
        $model->where(array("id" => "$id"))->setField("user_pass", $en_new);
        return "1";
    }

    public function resetUserPayPwd($id, $pwd = '123456') {
        $model = M('users');
        $en = pay_password($pwd);
        $model->where(array("id" => "$id"))->setField("pay_pwd", $en);
        return "1";
    }

    public function setUserPayPwd($id, $pre, $pwd) {
        $model = M('users');
        $en_pre = pay_password($pre);
        $exist = $model->where(array("id" => "$id", "pay_pwd" => $en_pre))->find();
        if (!$exist) {
            return "原密码不正确";
        }
        $en_new = pay_password($pwd);
        $model->where(array("id" => "$id"))->setField("pay_pwd", $en_new);
        return "1";
    }

    public function check_paypwd($pass, $uid) {
        $model = M('users');
        $en_pass = pay_password($pass);
        $exist = $model->where(array("id" => "$uid", "pay_pwd" => $en_pass))->find();
        return $exist;
    }

    public function setUserPayPwdWithoutPre($id, $pwd) {
        $model = M('users');
        $en_new = pay_password($pwd);
        $model->where(array("id" => "$id"))->setField("pay_pwd", $en_new);
        return "1";
    }

    public function AgentPhoneAlreadyRegisterd($phone) {
        $model = M('users');
        $data = $model->where(array("mobile" => "$phone", "user_type" => $this->agentRoldId))->find();
        return $data;
    }

    public function AgentPhonePassMatch($phone, $pass) {
        $en_pass = sp_password($pass);
        $model = M('users');
        $n = $model->where(array("mobile" => "$phone", "user_type" => $this->agentRoldId, "user_pass" => $en_pass))
                   ->count();
        return $n;
    }

    public function AgentOrSubPhonePassMatch($phone, $pass) {
        $model = M('users');
        $_map['user_login'] = $phone;
        $_map['user_type'] = array('in', "$this->agentRoldId,$this->subAgentRoldId");
        $user = $model->where($_map)->find();
        if (!sp_compare_password($pass, $user['user_pass'])) {
            return array("error" => "1", "msg" => "用户名或密码错误");
        }
        $user_status = $user['user_status'];
        if (!$user_status) {
            return array("error" => "1", "msg" => "用户名或密码错误");
        } else if ($user_status == 3) {
            return array("error" => "1", "msg" => "此帐号已被禁用");
        } else {
            return array("error" => "0", "msg" => $user);
        }
    }

    public function MarkAgentOrSubLoggedIn($id) {
        $_SESSION['logged_in'] = true;
        $info = $this->get_user_info_by_id($id);
        $phone = $info['mobile'];
        $_SESSION['phone'] = $phone;
        $_SESSION['agent_id'] = $id;
        $_SESSION['roleid'] = $info['user_type'];
        $_SESSION['user_activation_key'] = $info['user_activation_key'];
    }

    public function MarkAgentLoggedIn($phone) {
        $_SESSION['logged_in'] = true;
        $_SESSION['phone'] = $phone;
        $info = $this->get_agent_info_by_phone($phone);
        $_SESSION['agent_id'] = $info['id'];
        $_SESSION['roleid'] = $info['user_type'];
    }

    public function get_agent_info_by_phone($phone) {
        $model = M('users');
        $result = $model->where(array("user_type" => $this->agentRoldId, "mobile" => "$phone"))->find();
        return $result;
    }

    public function get_agent_info_by_id($id) {
        $model = M('users');
        $result = $model->where(array("user_type" => $this->agentRoldId, "id" => "$id"))->find();
        return $result;
    }

    public function get_user_info_by_id($id) {
        $model = M('users');
        $result = $model->where(array("id" => "$id"))->find();
        return $result;
    }

    public function getUserPhone($id) {
        $data = $this->get_user_info_by_id($id);
        $phone = $data['mobile'];
        return $phone;
    }

    public function getUserEmail($id) {
        $data = $this->get_user_info_by_id($id);
        $r = $data['user_email'];
        return $r;
    }

    public function getUserType($user_id) {
        $role_id = M('role_user')->where(array("user_id" => $user_id))->getField("role_id");
        if ($role_id) {
            $name = M('role')->where(array("id" => $role_id))->getField("name");
            return $name;
        }
    }

    public function getUserType_prev($user_id) {
        return M('users')->where(array("id" => $user_id))->getField("user_type");
    }

    public function is_agent($user_id) {
        $type_name = $this->getUserType($user_id);
        if ($type_name == "渠道专员") {
            return true;
        }
    }

    public function is_sub_agent($user_id) {
        $type_name = $this->getUserType($user_id);
        if ($type_name == "公会渠道") {
            return true;
        }
    }

    private function createUser() {
    }

    public function is_agent_prev($user_id) {
        $type = $this->getUserType_prev($user_id);
        if ($type == $this->agentRoldId) {
            return true;
        }
    }

    public function is_subagent_prev($user_id) {
        $type = $this->getUserType_prev($user_id);
        if ($type == $this->subAgentRoldId) {
            return true;
        }
    }
}

