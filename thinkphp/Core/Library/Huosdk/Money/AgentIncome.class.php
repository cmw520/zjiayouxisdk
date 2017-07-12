<?php
namespace Huosdk\Money;
class AgentIncome {
    public static function getList($agent_id, $start = 0, $limit = 0) {
        $model = M('agent_order');
        $where = array();
        $where['aor.agent_id'] = $agent_id;
        $items = $model
            ->field("aor.*,g.name as game_name,m.username as mem_name")
            ->alias("aor")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->limit($start, $limit)
            ->order("aor.id desc")
            ->select();
        return $items;
    }
}

