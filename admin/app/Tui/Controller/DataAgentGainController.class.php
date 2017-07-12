<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataAgentGainController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("only_agent_select", $hs_ui_filter_obj->parent_agent_select());
        $this->assign("only_subagent_select", $hs_ui_filter_obj->only_subagent_select());
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $this->assign("orderid_input", $hs_ui_filter_obj->order_id_input());
        $hs_data_base_obj = new \Huosdk\Data\Base();
        $fields = array(
            "时间", "订单号", "玩家帐号", "游戏", "金额", "充值方式",
            "实付金额", "二级渠道", "一级渠道", "二级渠道收益", "一级渠道收益");
        $table_header = $hs_data_base_obj->generate_table_header($fields);
        $this->assign("table_header", $table_header);
        $agent_id = $this->agid;
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->GetMeAndMySubAgentIDs();
        $ids_txt = join(",", $ids);
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "aor.create_time");
        $hs_where_obj->get_simple($where, "app_id", "aor.app_id");
        $hs_where_obj->get_simple($where, "agent_id", "aor.agent_id");
        $hs_where_obj->get_simple($where, "parent_agent_id", "aor.parent_id|aor.agent_id");
        $hs_where_obj->get_simple($where, "mem_name", "m.username");
        $hs_where_obj->get_simple($where, "order_id", "aor.order_id");
        $count = $this->getListCnt($where);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $db_fields = array(
            "create_time_txt", "order_id", "mem_name", "game_name", "coin_cnt", "payway_txt",
            "real_pay", "sub_agent_name", "agent_name", "sub_agent_profit", "agent_profit");
        $table_content = $hs_data_base_obj->generate_table_content($items, $db_fields);
        $this->assign("table_content", $table_content);
        $this->assign("page", $page->show('Admin'));
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "收益明细";
            $expCellName = array(
                array("create_time_txt", "时间",),
                array("order_id", "订单号",),
                array("mem_name", "玩家帐号",),
                array("game_name", "游戏",),
                array("coin_cnt", "金额",),
                array("payway_txt", "充值方式",),
                array("real_pay", "实付金额",),
                array("sub_agent_name", "二级渠道",),
                array("agent_name", "一级渠道",),
                array("sub_agent_profit", "二级渠道收益",),
                array("agent_profit", "一级渠道收益")
            );
            $expTableData = $this->getList($where);
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->display();
    }

    /**
     * 获取列表数量
     *
     * @param array $where
     *
     * @return mixed
     * 2017/2/11  wuyonghong
     */
    public function getListCnt($where = array()) {
        $_cnt = M('agent_order')
            ->alias('aor')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=aor.parent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->where($where)
            ->count();

        return $_cnt;
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $where_extra = array();
        //$where_extra['p.status']=2;
        //$where_extra['aor.agent_gain']=array("neq",0);
        $items = M('agent_order')
            ->field(
                "aor.create_time,aor.amount as coin_cnt,aor.real_amount as real_pay,aor.status,aor.order_id,aor.payway,"
                ."m.username as mem_name,g.name as game_name,u.user_type,"
                ."aor.agent_id,u.user_nicename as agent_name,aor.agent_gain as agent_profit,"
                ."u2.id as parent_agent_id,u2.user_nicename as parent_agent_name,aor.parent_gain as parent_agent_profit"
            )
            ->alias('aor')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=aor.parent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->where($where)
            ->order("aor.id desc")
            ->limit($start, $limit)
            ->select();
        $payway_data = M('payway')->getField("payname,realname", true);
        foreach ($items as $k => $v) {
            $items[$k]['payway_txt'] = $payway_data[$v['payway']];
            //$profit_data=$this->get_gain_v2($v['order_id']);
            //$profit_data=$this->get_agent_gain($v['agent_id'], $v['order_id']);
            //$items[$k]['profits']=$profit_data;
            $items[$k]['create_time_txt'] = date("Y-m-d H:i:s", $v['create_time']);
            /**
             * 如果上级代理为1，证明自己就是一级代理，下级代理就不会有收益了
             */
            if ($v['parent_agent_id'] == 1) {
                $items[$k]['sub_agent_name'] = '--';
                $items[$k]['sub_agent_profit'] = '--';
                $items[$k]['agent_name'] = $v['agent_name'];
                $items[$k]['agent_profit'] = $v['agent_profit'];
            } else {
                /**
                 * 如果上级代理不为1，证明自己就是二级代理，
                 */
                $items[$k]['sub_agent_name'] = $v['agent_name'];
                $items[$k]['sub_agent_profit'] = $v['agent_profit'];
                $items[$k]['agent_name'] = $v['parent_agent_name'];
                $items[$k]['agent_profit'] = $v['parent_agent_profit'];
            }
        }
//        $hs_itemsfield_obj=new \Huosdk\Data\ItemsFields();
//        $hs_itemsfield_obj->agent_level($items);
//        $items=M('gm_charge')
//                ->field("gc.order_id,gc.create_time,gc.admin_id,gc.gm_cnt as coin_cnt,gc.payway,gc.real_amount as real_pay,"
//                        . "u.mobile,m.username as mem_name,g.name as game_name")
//                ->alias('gc')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=gc.admin_id) AND (ag.app_id = gc.app_id))")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON ((agr.agent_id=gc.admin_id) AND (agr.app_id = gc.app_id))")
//                ->where($where)
//                ->order("gc.id desc")
//                ->limit($start,$limit)
//                ->select();      
//            
//        foreach($items as $k=>$v){
//            $profits=$this->get_agent_gain($v['admin_id'], $v['order_id']);
//            $items[$k]['sub_agent_profit']=$profits['sub_agent_profit'];
//            $items[$k]['agent_profit']=$profits['agent_profit'];
//
//            $items[$k]['sub_agent_name']=$profits['sub_agent_name'];
//            $items[$k]['agent_name']=$profits['agent_name'];
//
//            $items[$k]['create_time_txt']=date("Y-m-d H:i:s",$v['create_time']);
//
//            $items[$k]['payway_txt']=$this->payway_txt($v['payway']);
//        }
        return $items;
    }

    public function payway_txt($k) {
        $data = array("alipay" => "支付宝", "ptb" => C('CURRENCY_NAME'));

        return $data[$k];
    }

//    public function get_agent_gain($agent_id,$order_id){
//        $result=array();
//        if($this->is_agent($agent_id)){
//            $result['sub_agent_name']='';
//            $result['agent_name']=$this->get_user_name($agent_id);
//            $result['sub_agent_profit']='';
//            $result['agent_profit']=M('agent_order')->where(array("order_id"=>$order_id,"agent_id"=>$agent_id))->getField("agent_gain");
//        }
//        
//        if($this->is_sub_agent($agent_id)){                      
//            $result['sub_agent_profit']=M('agent_order')->where(array("order_id"=>$order_id,"agent_id"=>$agent_id))->getField("agent_gain");
//            $ownerid=$this->get_parent_agent_id($agent_id);
//            
//            $result['sub_agent_name']=$this->get_user_name($agent_id);
//            $result['agent_name']=$this->get_parent_agent_name($ownerid);
//            
//            $result['agent_profit']=M('agent_order')->where(array("order_id"=>$order_id,"agent_id"=>$ownerid))->getField("agent_gain");
//        }
//   
//        return $result; 
//    }
    public function get_gain_v2($order_id) {
        $data = array();
        $items = M('agent_order')
            ->field("aor.agent_id,aor.agent_gain,u.user_nicename")
            ->alias("aor")
            ->where(array("order_id" => $order_id))
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->select();
        $n = count($items);
        if ($n == 1) {
            $data['agent_profit'] = $items[0]['agent_gain'];
            $data['agent_name'] = $items[0]['user_nicename'];
        } else if ($n == 2) {
            $data['agent_profit'] = $items[0]['agent_gain'];
            $data['agent_name'] = $items[0]['user_nicename'];
            $data['subagent_profit'] = $items[1]['agent_gain'];
            $data['subagent_name'] = $items[1]['user_nicename'];
        }

        return $data;
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

