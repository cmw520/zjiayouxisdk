<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class AgentPtbBalanceController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function get() {
        $sub_agent_name = I("subagent");
        $hs_account_obj = new \Huosdk\Account();
        $agent_id = $hs_account_obj->getAgentIdByUserLogin($sub_agent_name);
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $balance = $hs_pb_obj->getBalance($agent_id);
        $this->ajaxReturn(array("error" => "0", "msg" => $balance));
    }
}

