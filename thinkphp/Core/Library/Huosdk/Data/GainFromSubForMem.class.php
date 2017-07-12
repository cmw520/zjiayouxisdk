<?php
namespace Huosdk\Data;
class GainFromSubForMem {
    public function getList($where_extra = array(), $start = 0, $limit = 0) {
        $model = M('agent_order');
        $where = array();
        $where['aor.from'] = 2;
        $items = $model
            ->field(
                "aor.*,"
                ."g.name as game_name,u.user_nicename as sub_agent_name,m.username as mem_name,u2.user_nicename as parent_agent_name"
            )
            ->alias("aor")
            ->where($where)
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.remark")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->limit($start, $limit)
            ->order("aor.id desc")
            ->select();
        return $items;
    }

    public function getSum($where_extra = array()) {
        $model = M('agent_order');
        $where = array();
        $where['aor.from'] = 2;
        $sums = $model
            ->field("sum(aor.agent_gain) as sum_gain,sum(aor.amount) as sum_amount")
            ->alias("aor")
            ->where($where)
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->select();
        return $sums;
    }
}

