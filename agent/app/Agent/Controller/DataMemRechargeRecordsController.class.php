<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemRechargeRecordsController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function getList() {
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "aor.create_time");
        $hs_where_obj->order_id($where, "aor.order_id");
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_where_obj->get_simple_like($where, "game_name", "g.name");
        $hs_agent_obj = new \Huosdk\Agent($this->agid);
        $ids_txt = $hs_agent_obj->getMeAndMySubIdsTxt();
        $where['_string'] = "aor.agent_id IN ($ids_txt) ";
        $all_items = $this->getMemChargeItems($where);
        $count = count($all_items);
        $page = new \Think\Page($count, 10);
        $items = $this->getMemChargeItems($where, $page->firstRow, $page->listRows);
        $sum_items = $this->getMemChargeItems_sum($where);
        $this->assign("sum_items", $sum_items[0]);
        $this->assign("num_of_records", $count);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function getMemChargeItems($where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $where['aor.agent_gain'] = array("neq", "0");
        //$where['aor.status']=2;
        $items = M('agent_order')
            ->field("aor.*,g.name as game_name,m.username as mem_name,u.user_nicename as agent_name")
            ->alias('aor')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->order("aor.id desc")
            ->where($where)
            ->where($where_extra)
            ->limit($start, $limit)
            ->select();
        $payway_data = M('payway')->getField("payname,realname", true);
        foreach ($items as $k => $v) {
            $items[$k]['payway_txt'] = $payway_data[$v['payway']];
        }
        $hs_itemsfield_obj = new \Huosdk\Data\ItemsFields();
        $hs_itemsfield_obj->agent_level($items);
        return $items;
    }

    public function getMemChargeItems_sum($where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $where['aor.agent_gain'] = array("neq", "0");
        //$where['aor.status']=2;
        $items = M('agent_order')
            ->field(
                "sum(aor.agent_gain) as sum_agent_gain,"
                ."sum(aor.amount) as sum_amount,"
                ."sum(aor.real_amount) as sum_real_amount,"
                ."sum(aor.rebate_cnt) as sum_rebate_cnt"
            )
            ->alias('aor')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->order("aor.id desc")
            ->where($where)
            ->where($where_extra)
            ->select();
        return $items;
    }
}

