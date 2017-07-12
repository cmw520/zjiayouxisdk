<?php
namespace Huosdk;
class Exist {
    private $agent_roleid, $subagent_roleid;

    public function __construct() {
        $obj = new \Huosdk\Account();
        $this->agent_roleid = $obj->getAgentRoleId();
        $this->subagent_roleid = $obj->getSubAgentRoleId();
    }

    public function AgentUserLogin($value) {
        return M('users')->where(array("user_login" => $value, "user_type" => $this->agent_roleid))->find();
    }

    public function SubAgentUserLogin($value) {
        return M('users')->where(array("user_login" => $value, "user_type" => $this->subagent_roleid))->find();
    }
}

