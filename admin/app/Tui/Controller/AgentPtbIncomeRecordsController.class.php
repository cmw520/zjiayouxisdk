<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentPtbIncomeRecordsController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    /**
     * 渠道的平台币收入来源有
     * 1 官方发放，对应的表格是ptb_given
     * 2 上级代理转账，对应的表格是ptb_agentcharge
     * 3 自己用第三方支付充值，对应的表格是ptb_agentcharge
     * 4 自己用账户余额充值, 对应的表格是ptb_agentcharge
     *
     */
    public function index2() {
        $agent_id = I("agent_id");
        $model = M('ptb_agentcharge');
        $where = array();
        $where['pac.agent_id'] = $agent_id;
        $count = $model
            ->alias("pac")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $field = "pac.*,'上级代理转账' as `from`,pac.ptb_cnt as coin_cnt";
        $items = $model
            ->field($field)
            ->alias("pac")
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order("pac.id desc")
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function index() {
        $agent_id = I("agent_id");
        $sql = ""
               ."SELECT create_time,ptb_cnt as coin_cnt,'官方发放' as `from` From c_ptb_given "
               ."WHERE (agent_id = $agent_id) AND (status=2) "
               .""
               ."UNION ALL "
               .""
               ."SELECT create_time,ptb_cnt as coin_cnt,'在线充值' as `from` From c_ptb_agentcharge "
               ."WHERE (agent_id=$agent_id) AND (status=2) "
               ."AND (payway='bank-pay' OR payway='wxpay' OR payway='alipay' OR payway='ptb' ) "
               .""
               .""
               ."UNION ALL "
               .""
               ."SELECT create_time,ptb_cnt as coin_cnt,'官方发放' as `from` From c_ptb_agentcharge "
               ."WHERE (agent_id=$agent_id) AND (status=2) "
               ."AND ((payway IS NULL ) ) "
               .""
               ."UNION ALL "
               .""
               ."SELECT create_time,ptb_cnt as coin_cnt,'账户余额充值' as `from` From c_ptb_agentcharge "
               ."WHERE (agent_id=$agent_id) AND (status=2) "
               ."AND (payway='ab' OR payway='account_ba' ) "
               .""
               ."ORDER BY create_time desc ";
        $all_items = M()->query($sql);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $items = M()->query($sql."LIMIT $page->firstRow , $page->listRows ");
        $total_income = 0;
        foreach ($all_items as $key => $value) {
            $total_income += $value['coin_cnt'];
        }
        $this->assign("total_income", $total_income);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
}

