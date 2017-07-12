<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentPtbOutcomeRecordsController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agent_id = I("agent_id");
        $model = M('gm_charge');
        $where = array();
        $where['gc.admin_id'] = $agent_id;
        $where['gc.status'] = 2;
        $count = $model
            ->alias("gc")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->count();
        $page = $this->page($count, 10);
        $field = "gc.*,g.name as game_name,m.username as to_account,gc.money as coin_cnt,'给玩家充值' as `way`";
        $items = $model
            ->field($field)
            ->alias("gc")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("gc.id desc")
            ->select();
        $sumitems = $model
            ->alias("gc")
            ->field("sum(gc.money) as sum_coin_cnt")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->select();
        $this->assign("total_outcome", $sumitems[0]['sum_coin_cnt']);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function index2() {
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

