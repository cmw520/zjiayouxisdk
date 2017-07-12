<?php
namespace Huosdk\Benefit;
class SubForMem {
    public function getParentAgentGainDiscount($subagent_id, $app_id, $amount) {
        $sub_agentrate = M('agent_game_rate')->where(array("agent_id" => $subagent_id, "app_id" => $app_id))->getField(
            "agent_rate"
        );
        $parent_agentrate = $this->getParentAgentGameRate($subagent_id, $app_id);
        $parent_agent_gain = ($sub_agentrate - $parent_agentrate) * $amount;
        if ($parent_agent_gain < 0) {
            $parent_agent_gain = 0;
        }
        return $parent_agent_gain;
    }

    public function getParentAgentGameRate($sub_agentid, $app_id) {
        $parent_agentid = M('users')->where(array("id" => $sub_agentid))->getField("ownerid");
        $parent_agentrate = M('agent_game_rate')->where(array("agent_id" => $parent_agentid, "app_id" => $app_id))
                                                ->getField("agent_rate");
        return $parent_agentrate;
    }

    public function addAgentOrder($mem_id, $app_id, $subagent_id, $amount, $real_amount) {
        $parent_agentid = M('users')->where(array("id" => $subagent_id))->getField("ownerid");
        $parent_agent_gain = $this->getParentAgentGainDiscount($subagent_id, $app_id, $amount);
        $data = array();
        $data['order_id'] = setorderid();
        $data['mem_id'] = $mem_id;
        $data['agent_id'] = $parent_agentid;
        $data['app_id'] = $app_id;
        $data['amount'] = $amount;
        $data['real_amount'] = $real_amount;
//        $data['agent_rate'];
        $data['agent_gain'] = $parent_agent_gain;
        $data['from'] = 2;
        $data['status'] = 2;
        $data['create_time'] = time();
        $data['update_time'] = time();
//        $data['remark']='sub_for_mem_parent_gain';
        $data['remark'] = $subagent_id;
        M('agent_order')->add($data);
        /**
         * 增加上级渠道的账户余额
         */
        $hs_settle_obj = new \Huosdk\Settle();
        $hs_settle_obj->IncAgentAccountBalance($parent_agentid, $parent_agent_gain);
    }
}

