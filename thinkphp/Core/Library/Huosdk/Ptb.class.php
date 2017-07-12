<?php
namespace Huosdk;
class Ptb {
    public function addIncRecord() {
    }

    public function giveList($where_extra = array(), $start = 0, $limit = 0) {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $items = M('ptb_given')
            ->field("u.user_nicename as agent_name,pg.create_time,pg.ptb_cnt,u.user_type,pg.remark")
            ->alias("pg")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pg.agent_id")
            ->where($where_extra)
            ->order("pg.create_time desc")
            ->limit($start, $limit)
            ->select();
        foreach ($items as $k => $v) {
            if ($v['user_type'] == $agent_roleid) {
                $items[$k]['agent_type'] = "一级代理";
            } else if ($v['user_type'] == $subagent_roleid) {
                $items[$k]['agent_type'] = "二级代理";
            }
        }
        return $items;
    }
}

