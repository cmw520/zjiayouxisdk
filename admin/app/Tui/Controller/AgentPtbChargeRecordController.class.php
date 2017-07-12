<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentPtbChargeRecordController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index2() {
        $model = M('');
        $where = array();
//        $where[''] = $v;
        $count = $model
            ->field("")
            ->alias("ll")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ll.app_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("ll.login_time,g.name as game_name")
            ->alias("ll")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ll.app_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("ll.id desc")
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function payway_txt() {
        $data = M('payway')->getField("payname,realname");

        return $data;
    }

    public function index3() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("memname_input", $hs_ui_filter_obj->memname_input());
        $this->assign("order_id_input", $hs_ui_filter_obj->order_id_input());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $this->assign("payway_select", $hs_ui_filter_obj->payway_select2());
        $this->assign("pay_from", $hs_ui_filter_obj->pay_from());
        $sql = "SELECT '代理充值' as `from`,create_time,payway,ptb_cnt,agent_id FROM c_ptb_agentcharge WHERE status=2 "
               ."UNION ALL "
               ."SELECT '官方发放' as `from`, create_time,payway,ptb_cnt,agent_id FROM c_ptb_given WHERE status=2 "
               //                . "LEFT JOIN c_users u ON u.id=agent_id "
               ."ORDER BY create_time desc";
        $all_items = M('')->query($sql);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $sql2
            = "(SELECT '代理充值' as `from`,create_time,payway,ptb_cnt,agent_id,status,order_id,money FROM c_ptb_agentcharge WHERE status=2) "
              ."UNION ALL "
              ."(SELECT '官方发放' as `from`, create_time,payway,ptb_cnt,agent_id,status,order_id,money FROM c_ptb_given WHERE status=2) "
              ."ORDER BY create_time desc LIMIT $page->firstRow,$page->listRows ";
        $items = M('')->query($sql2);
        $payway_data = $this->payway_txt();
        foreach ($items as $key => $value) {
            $items[$key]['payway_txt'] = $payway_data[$value['payway']];
            if ($value['from'] == '代理充值') {
                if (($value['payway'] == 'alipay' || $value['payway'] == 'bankpay' || $value['payway'] == 'wxpay')) {
                    $items[$key]['from'] = "在线充值";
                } else if ($value['payway'] == 'account_balance') {
                    $items[$key]['from'] = "账户余额充值";
                } else if ($value['payway'] == 'ptb') {
                    $items[$key]['from'] = "代理转账";
                }
            }
        }
        $hs_fr_obj = new \Huosdk\Data\FormatRecords();
        $hs_fr_obj->pay_status($items);
        $hs_fr_obj->payway($items);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("memname_input", $hs_ui_filter_obj->memname_input());
        $this->assign("order_id_input", $hs_ui_filter_obj->order_id_input());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $this->assign("payway_select", $hs_ui_filter_obj->payway_select2());
        $this->assign("pay_from", $hs_ui_filter_obj->pay_from());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_where_obj->get_simple($where, "order_id", "pa.order_id");
        $hs_where_obj->get_simple($where, "agent_id", "pa.agent_id");
        $hs_where_obj->time($where, "pa.create_time");
        $all_items = $this->getList($where);
        $count = count($all_items);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        foreach ($items as $key => $value) {
            if (($value['payway'] == 'alipay' || $value['payway'] == 'bankpay' || $value['payway'] == 'wxpay')) {
                $items[$key]['from'] = "在线充值";
            } else if ($value['payway'] == 'account_balance') {
                $items[$key]['from'] = "账户余额充值";
            } else if ($value['payway'] == 'ptb') {
                $items[$key]['from'] = "代理转账";
            }
        }
        $hs_fr_obj = new \Huosdk\Data\FormatRecords();
        $hs_fr_obj->pay_status($items);
        $hs_fr_obj->payway($items);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getList($where_extra = array(), $start = 0, $limit = 0) {
        $model = M('ptb_agentcharge');
        $where = array();
//        $where['pa.status'] = 2;
        $items = $model
            ->field("pa.*,u.user_nicename as agent_name")
            ->alias("pa")
            ->where($where)
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pa.agent_id")
            ->limit($start, $limit)
            ->order("pa.id desc")
            ->select();

        return $items;
    }
}


