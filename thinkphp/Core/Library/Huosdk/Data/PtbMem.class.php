<?php
namespace Huosdk\Data;

use Huosdk\Data\Base;

class PtbMem extends Base {
    public function get_table_header_admin() {
        $data = array(
            "时间", "订单号", "玩家帐号", "充值游戏", "充值".C('CURRENCY_NAME'), "充值方式",
            "实付金额", "二级渠道", "一级渠道", "二级渠道收益", "一级渠道收益");
        return $this->generate_table_header($data);
    }

    public function get_table_header_agent() {
        $data = array(
            "时间", "订单号", "玩家帐号", "充值游戏", "充值".C('CURRENCY_NAME'), "充值方式",
            "实付金额", "二级渠道", "一级渠道", "二级渠道收益", "一级渠道收益");
        return $this->generate_table_header($data);
    }

    public function get_table_header_sub() {
        $data = array(
            "时间", "订单号", "玩家帐号", "充值游戏", "充值游戏币", "充值方式",
            "实付金额", "二级渠道", "二级渠道收益");
        return $this->generate_table_header($data);
    }

    public function fields() {
        $data = array(
            "create_time_txt", "order_id", "mem_name", "game_name", "coin_cnt", "payway_txt",
            "real_pay", "sub_agent_name", "agent_name", "sub_agent_profit", "agent_profit");
        return $data;
    }

    public function get_table_content($data = array(), $fields = array()) {
        if (!$data) {
            $data = $this->get_items();
        }
        if (!$fields) {
            $fields = $this->fields();
        }
        $txt = '';
        foreach ($data as $k => $v) {
            $txt .= '<tr>';
            foreach ($fields as $field) {
                $txt .= "<td>".$v[$field]."   </td>";
            }
            $txt .= "</tr>";
        }
        return $txt;
    }

    public function get_admin_count() {
        $data = $this->get_items();
        return count($data);
    }

    public function get_admin_table($start = 0, $limit = 0) {
        $data = $this->get_items(array(), $start, $limit);
        return $this->get_table_content($data);
    }

    public function get_agent_count($agent_id) {
        $data = $this->get_agent_items($agent_id);
        return count($data);
    }

    public function get_agent_items($agent_id, $start, $limit) {
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->GetMeAndMySubAgentIDs();
        $ids_txt = join(",", $ids);
        $where = array();
        $where['_string'] = "gc.admin_id IN ($ids_txt)";
        $data = $this->get_items($where, $start, $limit);
        return $data;
    }

    public function get_agent_table($agent_id, $start = 0, $limit = 0) {
        $data = $this->get_agent_items($agent_id, $start, $limit);
        $fields = array(
            "create_time_txt", "order_id", "mem_name", "game_name", "coin_cnt", "payway_txt",
            "real_pay", "sub_agent_name", "agent_name", "sub_agent_profit", "agent_profit");
        return $this->get_table_content($data, $fields);
    }

    public function get_sub_count($agent_id) {
        $where = array();
        $where['gc.admin_id'] = $agent_id;
        $data = $this->get_items($where);
        return count($data);
    }

    public function get_sub_table($agent_id, $start = 0, $limit = 0) {
        $where = array();
        $where['gc.admin_id'] = $agent_id;
        $data = $this->get_items($where, $start, $limit);
        $fields = array(
            "create_time_txt", "order_id", "mem_name", "game_name", "coin_cnt", "payway_txt",
            "real_pay", "sub_agent_name", "sub_agent_profit");
        return $this->get_table_content($data, $fields);
    }

    public function get_items($where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $where['gc.status'] = 2;
//            if(isset($_GET['agent'])&&$_GET['agent']!=''){
//                $where["ac.admin_id"]=array('eq',$_GET['agent']);
//            }
//
//            if(isset($_GET['memname'])&&$_GET['memname']!=''){
//
//                $mem_id=get_memid_by_name($_GET['memname']);
//                $where["ac.mem_id"]= array('eq',$mem_id) ;
//            }
//
//            if(isset($_GET['orderid'])&&$_GET['orderid']!=''){
//                $where["ac.order_id"]=array('eq',$_GET['orderid']);
//            }
//
//            if(isset($_GET['start_time'])&&$_GET['start_time']!=''){
//                $where["ac.create_time"]=array('gt',strtotime($_GET['start_time']));
//            }
//
//            if(isset($_GET['end_time'])&&$_GET['end_time']!=''){
//                $where["ac.create_time"]=array('lt',strtotime($_GET['end_time']));
//            }
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
            ->where($where_extra)
            ->order("gc.id desc")
            ->limit($start, $limit)
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
        return $items;
    }

    public function payway_txt($k) {
        $data = array("alipay" => "支付宝", "ptb" => "游戏币");
        return $data[$k];
    }

    public function get_total_count() {
    }

    public function get_sum_admin($where) {
        $sumitems = M('gm_charge')
            ->field("sum(gc.gm_cnt) as sum_coin_cnt,sum(gc.real_amount) as sum_real_pay")
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
            ->select();
        return $sumitems[0];
    }

    public function get_sum_admin_txt($where) {
        $data = $this->get_sum_admin($where);
        $fields = array(
            "汇总", "--", "--", "--", $data['sum_coin_cnt'], "--",
            $data['sum_real_pay'], "--", "--", "--", "--");
        return $this->generate_table_sum($fields);
    }

    public function get_sum_sub($agent_id) {
        $where = array();
        $where['gc.admin_id'] = $agent_id;
        $sumitems = M('gm_charge')
            ->field("sum(gc.gm_cnt) as sum_coin_cnt,sum(gc.real_amount) as sum_real_pay")
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
            ->select();
        return $sumitems[0];
    }

    public function get_sum_sub_txt($agent_id) {
        $data = $this->get_sum_sub($agent_id);
        $fields = array(
            "汇总", "--", "--", "--", $data['sum_coin_cnt'], "--",
            $data['sum_real_pay'], "--", "--");
        return $this->generate_table_sum($fields);
    }

    public function get_sum_agent($agent_id) {
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->GetMeAndMySubAgentIDs();
        $ids_txt = join(",", $ids);
        $where = array();
        $where['_string'] = "gc.admin_id IN ($ids_txt)";
        $sumitems = M('gm_charge')
            ->field("sum(gc.gm_cnt) as sum_coin_cnt,sum(gc.real_amount) as sum_real_pay")
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
            ->select();
        return $sumitems[0];
    }

    public function get_sum_agent_txt($agent_id) {
        $data = $this->get_sum_agent($agent_id);
        $fields = array(
            "汇总", "--", "--", "--", $data['sum_coin_cnt'], "--",
            $data['sum_real_pay'], "--", "--", "--", "--");
        return $this->generate_table_sum($fields);
    }

    public function getUserType($user_id) {
        $role_id = M('role_user')->where(array("user_id" => $user_id))->getField("role_id");
        if ($role_id) {
            $name = M('role')->where(array("id" => $role_id))->getField("name");
            return $name;
        }
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

    public function get_user_name($id) {
        return M('users')->where(array("id" => $id))->getField("user_login");
    }

    public function get_parent_agent_id($subid) {
        return M('users')->where(array("id" => $subid))->getField("ownerid");
    }

    public function get_parent_agent_name($subid) {
        return M('users')->where(array("id" => $subid))->getField("user_login");
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
}

