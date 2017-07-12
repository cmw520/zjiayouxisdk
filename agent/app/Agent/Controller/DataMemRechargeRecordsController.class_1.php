<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemRechargeRecordsController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function mem_game_filter(&$where) {
        if (isset($_GET['mem_name']) && $_GET['mem_name'] != '') {
            $v = $_GET['mem_name'];
            $where["m.username"] = array("like", "%$v%");
        }
        if (isset($_GET['game_name']) && $_GET['game_name'] != '') {
            $gamename = $_GET['game_name'];
            $where["g.name"] = array("like", "%$gamename%");
        }
    }

    public function index() {
        $where = array();
        $this->mem_game_filter($where);
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "p.create_time");
        $hs_where_obj->order_id($where, "p.order_id");
        $all_items = $this->getMemChargeItems($where);
        $count = count($all_items);
        $page = new \Think\Page($count, 10);
        $items = $this->getMemChargeItems($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function getList() {
        $where = array();
        $this->mem_game_filter($where);
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "p.create_time");
        $hs_where_obj->order_id($where, "p.order_id");
        $hs_agent_obj = new \Huosdk\Agent($this->agid);
        $ids_txt = $hs_agent_obj->getMeAndMySubIdsTxt();
        $where['_string'] = "p.agent_id IN ($ids_txt) ";
        $where['p.status'] = 2;
        $all_items = $this->getMemChargeItems($where);
        $count = count($all_items);
        $page = new \Think\Page($count, 10);
        $items = $this->getMemChargeItems($where, $page->firstRow, $page->listRows);
        $sum_items = M('pay')
            ->field("sum(p.amount) as sum_amount")
            ->alias('p')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=p.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=p.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=p.app_id")
            ->order("p.id desc")
            ->where($where)
            ->select();
        $this->assign("sum_items", $sum_items[0]);
        $this->assign("num_of_records", $count);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function getMemChargeItems($where_extra = array(), $start = 0, $limit = 0) {
//        $hs_agent_obj=new \Huosdk\Agent($this->agid);
//        $ids_txt=$hs_agent_obj->getMeAndMySubIdsTxt();
//        $where=array();
//        $where['_string']="p.agent_id IN ($ids_txt) ";
//        $where['p.status']=2;
        $items = M('pay')
            ->field(
                "p.create_time,p.amount,p.status,p.order_id,p.payway,p.agent_id,"
                ."u.user_login as agent_name,m.username as mem_name,g.name as game_name,u.user_type"
            )
            ->alias('p')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=p.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=p.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=p.app_id")
            ->order("p.id desc")
//                ->where($where)
            ->where($where_extra)
            ->limit($start, $limit)
            ->select();
        $payway_data = M('payway')->getField("payname,realname", true);
        foreach ($items as $k => $v) {
            $items[$k]['payway_txt'] = $payway_data[$v['payway']];
            $profit_data = $this->get_agent_gain($v['agent_id'], $v['order_id']);
//            $items[$k]=array_merge($items[$k],$profit_data);     
            $items[$k]['profits'] = $profit_data;
        }
        $hs_itemsfield_obj = new \Huosdk\Data\ItemsFields();
        $hs_itemsfield_obj->agent_level($items);
        return $items;
    }

    public function get_agent_gain($agent_id, $order_id) {
        $hs_account_obj = new \Huosdk\Account();
        $result = array();
        if ($hs_account_obj->is_agent_prev($agent_id)) {
            $agent_rate = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))->getField(
                "agent_rate"
            );
            $agent_gain = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))->getField(
                "agent_gain"
            );
            $result['subagent_name'] = '';
            $result['subagent_profit'] = '';
            $result['agent_name'] = '('.round($agent_rate, 2).')'.$hs_account_obj->getUserName($agent_id);
//            $result['agent_name']='('.round($agent_rate,2).')';   
            $result['agent_profit'] = $agent_gain;
            $result['real_amount'] = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))
                                                     ->getField("real_amount");
            $result['rebate_cnt'] = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))
                                                    ->getField("rebate_cnt");
            if ($result['real_amount']) {
                if ($result['rebate_cnt'] != 0) {
                    $result['benefit_type'] = "返利";
                } else {
                    $result['benefit_type'] = "折扣";
                }
            }
        }
        if ($hs_account_obj->is_subagent_prev($agent_id)) {
            $subagent_gain = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))->getField(
                "agent_gain"
            );
            $subagent_rate = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))->getField(
                "agent_rate"
            );
            $result['subagent_name'] = '('.round($subagent_rate, 2).')'.$hs_account_obj->getUserName($agent_id);
            $result['subagent_profit'] = $subagent_gain;
            $ownerid = $hs_account_obj->get_parent_agent_id($agent_id);
            $agent_gain = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $ownerid))->getField(
                "agent_gain"
            );
            $agent_rate = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $ownerid))->getField(
                "agent_rate"
            );
            $result['agent_name'] = '('.round($agent_rate, 2).')'.$hs_account_obj->getUserName($ownerid);
            $result['agent_profit'] = $agent_gain;
            $result['real_amount'] = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))
                                                     ->getField("real_amount");
            $result['rebate_cnt'] = M('agent_order')->where(array("order_id" => $order_id, "agent_id" => $agent_id))
                                                    ->getField("rebate_cnt");
            if ($result['real_amount']) {
                if ($result['rebate_cnt'] != 0) {
                    $result['benefit_type'] = "返利";
                } else {
                    $result['benefit_type'] = "折扣";
                }
            }
        }
        return $result;
    }
}

