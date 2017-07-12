<?php
namespace Huosdk\Money;
class AgentOutcome {
    public static function getList($agent_id, $start = 0, $limit = 0) {
        $model = M('settle');
        $where = array();
        $where['se.status'] = 3;
        $where['se.agent_id'] = $agent_id;
        $items = $model
            ->field("se.*,u.user_login as agent_name")
            ->alias("se")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=se.agent_id")
            ->limit($start, $limit)
            ->order("se.id desc")
            ->select();
        $hs_fr_obj = new \Huosdk\Data\FormatRecords();
        $hs_fr_obj->settle_status($items);
        self::times($items);
        return $items;
    }

    public function times(&$items) {
        foreach ($items as $key => $value) {
            if ($value['settle_time']) {
                $items[$key]['settle_time'] = date("Y-m-d H:i:s", $value['settle_time']);
            } else {
                $items[$key]['settle_time'] = '';
            }
            if ($value['check_time']) {
                $items[$key]['check_time'] = date("Y-m-d H:i:s", $value['check_time']);
            } else {
                $items[$key]['check_time'] = '';
            }
            if ($value['create_time']) {
                $items[$key]['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
            } else {
                $items[$key]['create_time'] = '';
            }
        }
    }
}

