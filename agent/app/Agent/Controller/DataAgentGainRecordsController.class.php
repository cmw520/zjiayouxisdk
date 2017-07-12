<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataAgentGainRecordsController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_data_base_obj = new \Huosdk\Data\Base();
        $fields = array(
            "时间", "订单号", "玩家帐号", "充值游戏", "充值".C('CURRENCY_NAME'), "充值方式",
            "实付金额", "二级渠道", "一级渠道", "二级渠道收益", "一级渠道收益");
        $table_header = $hs_data_base_obj->generate_table_header($fields);
        $this->assign("table_header", $table_header);
        $agent_id = $this->agid;
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->GetMeAndMySubAgentIDs();
        $ids_txt = join(",", $ids);
        $where = array();
//        $where['_string']="gc.admin_id IN ($ids_txt)";
        $count = M('gm_charge')
            ->field(
                "gc.order_id,gc.create_time,gc.admin_id,gc.gm_cnt as coin_cnt,gc.payway,gc.real_amount as real_pay,"
                ."u.mobile,m.username as mem_name,g.name as game_name"
            )
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=gc.admin_id) AND (ag.app_id = gc.app_id))"
            )
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")
                ."agent_game_rate agr ON ((agr.agent_id=gc.admin_id) AND (agr.app_id = gc.app_id))"
            )
            ->where($where)
            ->count();
        $page = new \Think\Page($count, 10);
        $items = M('gm_charge')
            ->field(
                "gc.order_id,gc.create_time,gc.admin_id,gc.gm_cnt as coin_cnt,gc.payway,gc.real_amount as real_pay,"
                ."u.mobile,m.username as mem_name,g.name as game_name"
            )
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=gc.admin_id) AND (ag.app_id = gc.app_id))"
            )
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")
                ."agent_game_rate agr ON ((agr.agent_id=gc.admin_id) AND (agr.app_id = gc.app_id))"
            )
            ->where($where)
            ->order("gc.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($items as $k => $v) {
            $profits = $this->get_agent_gain($v['admin_id'], $v['app_id'], $v['mem_id'], $v['order_id']);
            $items[$k]['sub_agent_profit'] = $profits['sub_agent_profit'];
            $items[$k]['agent_profit'] = $profits['agent_profit'];
            $items[$k]['sub_agent_name'] = $profits['sub_agent_name'];
            $items[$k]['agent_name'] = $profits['agent_name'];
            $items[$k]['create_time_txt'] = date("Y-m-d H:i:s", $v['create_time']);
            $items[$k]['payway_txt'] = $this->payway_txt($v['payway']);
        }
        $db_fields = array(
            "create_time_txt", "order_id", "mem_name", "game_name", "coin_cnt", "payway_txt",
            "real_pay", "sub_agent_name", "agent_name", "sub_agent_profit", "agent_profit");
        $table_content = $hs_data_base_obj->generate_table_content($items, $db_fields);
        $this->assign("table_content", $table_content);
        $this->assign("Page", $page->show());
        $this->display();
    }

    public function payway_txt($k) {
        $data = array("alipay" => "支付宝", "ptb" => C('CURRENCY_NAME'));
        return $data[$k];
    }

    public function get_agent_gain($agent_id, $app_id, $mem_id, $order_id) {
        $result = array();
        if ($this->is_agent($agent_id)) {
            $result['sub_agent_name'] = '';
            $result['agent_name'] = $this->get_user_name($agent_id);
            $result['sub_agent_profit'] = '';
            $result['agent_profit'] = M('agent_order')->where(array("order_id" => $order_id))->getField("agent_gain");
        }
        if ($this->is_sub_agent($agent_id)) {
            $result['sub_agent_profit'] = M('agent_order')->where(array("order_id" => $order_id))->getField(
                "agent_gain"
            );
            $ownerid = $this->get_parent_agent_id($agent_id);
            $result['sub_agent_name'] = $this->get_user_name($agent_id);
            $result['agent_name'] = $this->get_parent_agent_name($ownerid);
            $result['agent_profit'] = M('agent_order')->where(array("order_id" => $order_id))->getField("agent_gain");
        }
        return $result;
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

    public function getUserType($user_id) {
        $role_id = M('role_user')->where(array("user_id" => $user_id))->getField("role_id");
        if ($role_id) {
            $name = M('role')->where(array("id" => $role_id))->getField("name");
            return $name;
        }
    }

    public function get_user_name($id) {
        return M('users')->where(array("id" => $id))->getField("user_login");
    }

    public function get_parent_agent_id($subid) {
        return M('users')->where(array("id" => $subid))->getField("ownerid");
    }

    public function get_parent_agent_name($subid) {
        return M('users')->where(array("id" => $subid))->getField("user_login");
    }
}

