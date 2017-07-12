<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataAgentReportController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
//        $hs_where_obj = new \Huosdk\Where();
//        $where = array();
//        $hs_where_obj->time($where, "dag.create_time");
//        $hs_where_obj->get_simple($where, "app_id", "dag.app_id");
//        $where = join(' AND ', $where);
        $where = ' ';
        if (isset($_GET["start_time"]) && $_GET["start_time"]) {
            $v = $_GET['start_time'];
            $where .= " AND (dag.date >= '$v') ";
        }
        if (isset($_GET["end_time"]) && $_GET["end_time"]) {
            $v = $_GET['end_time'];
            $where .= " AND (dag.date <= '$v') ";
        }
        if (isset($_GET['app_id']) && $_GET['app_id']) {
            $v = $_GET['app_id'];
            $where .= " AND (dag.app_id = '$v') ";
        }
        if (isset($_GET['agent_id']) && $_GET['agent_id']) {
            $v = $_GET['agent_id'];
            $where .= " AND (dag.agent_id = '$v') ";
        }
        $all_items = $this->getCnt($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $sumitems = $this->getTotal($where);
        $this->assign("sumitems", $sumitems);
        $this->assign("items", $items);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "全部渠道统计";
            $expCellName = array(
                array("date", "时间"),
                array("agent_name", "渠道名称"),
                array("game_name", "游戏"),
                array("sum_new_user_cnt", "新增用户"),
                array("sum_active_user_cnt", "活跃用户"),
                array("sum_charge_amount", "充值金额"),
                array("sum_pay_user_cnt", "充值人数"),
                array("sum_pay_rate", "付费率"),
            );
            $expTableData = $this->getList($where, 0, $count);
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget", $_GET);
        $this->display();
    }

    public function test() {
        $r = $this->getList();
        print_r($r);
    }

    public function getCnt($where_extra) {
        $sql = "SELECT count(*) FROM c_day_agentgame dag "
               ."LEFT JOIN c_users u ON u.id=dag.agent_id "
               ."LEFT JOIN c_game g ON g.id=dag.app_id "
               ."WHERE (agent_id > 0) ".$where_extra
               ." GROUP BY agent_id,date ";
        return M()->query($sql);
    }

    public function getTotal($where_extra) {
        $_field = ""
                  ."sum(dag.user_cnt) as sum_active_user_cnt,"
                  ."sum(dag.sum_money) as sum_charge_amount,"
                  ."sum(dag.reg_cnt) as sum_new_user_cnt,"
                  ."sum(dag.pay_user_cnt) as sum_pay_user_cnt";
        $total = M('day_agentgame')
            ->alias("dag")
            ->field($_field)
            ->join("left join c_users u ON u.id=dag.agent_id ")
            ->join("LEFT JOIN c_game g ON g.id=dag.app_id ")
            ->where("(agent_id IS NOT NULL) AND (agent_id !=0)".$where_extra)
//            ->where(array("dag.agent_id" => $agent_id, "dag.date" => $date))
            ->find();

        return $total;
    }

    public function getList($where_extra = '', $start = 0, $limit = 0) {
        $result = array();
        $sql = "SELECT agent_id,date,u.user_nicename as agent_name,g.name as game_name FROM c_day_agentgame dag "
               ."LEFT JOIN c_users u ON u.id=dag.agent_id "
               ."LEFT JOIN c_game g ON g.id=dag.app_id "
               ."WHERE (agent_id IS NOT NULL) AND (agent_id !=0) ".$where_extra
               ." GROUP BY agent_id,date "
               ." ORDER BY date desc"
               ." limit $start, $limit";
        $agents = M()->query($sql);
//        print_r($agents);
        foreach ($agents as $key => $v) {
            $agent_id = $v['agent_id'];
            $date = $v['date'];
//            echo $agent_id;
            $all_app_data = M('day_agentgame')
                ->alias("dag")
                ->field(
                    ""
                    ."sum(dag.user_cnt) as sum_active_user_cnt,"
                    ."sum(dag.sum_money) as sum_charge_amount,"
                    ."sum(dag.reg_cnt) as sum_new_user_cnt,"
                    ."sum(dag.pay_user_cnt) as sum_pay_user_cnt,"
                    ."CONCAT(format((sum(dag.pay_user_cnt)/sum(dag.user_cnt))*100,2),'%') as sum_pay_rate"
                )
                ->where(array("dag.agent_id" => $agent_id, "dag.date" => $date))
                ->find();
            $all_app_data['agent_name'] = $v['agent_name'];
            $all_app_data['date'] = $v['date'];
//            $all_app_data[0]['game_name']=$v['game_name'];
//            $all_app_data[0]['game_name'] = '全部游戏';
            $result[] = $all_app_data;
        }
//        print_r($result);
        return $result;
    }

    public function getList_app($where_extra = '', $start = 0, $limit = 0) {
        $result = array();
        $sql = "SELECT agent_id,date,u.user_nicename as agent_name,g.name as game_name FROM c_day_agentgame dag "
               ."LEFT JOIN c_users u ON u.id=dag.agent_id "
               ."LEFT JOIN c_game g ON g.id=dag.app_id "
               ."WHERE (agent_id IS NOT NULL) AND (agent_id !=0) ".$where_extra
               ." GROUP BY agent_id,date "
               ." ORDER BY date desc";
        $agents = M()->query($sql);
//        print_r($agents);
        foreach ($agents as $key => $v) {
            $agent_id = $v['agent_id'];
            $date = $v['date'];
//            echo $agent_id;
            $all_app_data = M('day_agentgame')
                ->alias("dag")
                ->field(
                    ""
                    ."sum(dag.user_cnt) as sum_active_user_cnt,"
                    ."sum(dag.sum_money) as sum_charge_amount,"
                    ."sum(dag.reg_cnt) as sum_new_user_cnt,"
                    ."sum(dag.pay_user_cnt) as sum_pay_user_cnt,"
                    ."CONCAT(format((sum(dag.pay_user_cnt)/sum(dag.user_cnt))*100,2),'%') as sum_pay_rate"
                )
                ->where(array("dag.agent_id" => $agent_id, "dag.date" => $date))
                ->select();
            $all_app_data[0]['agent_name'] = $v['agent_name'];
            $all_app_data[0]['date'] = $v['date'];
//            $all_app_data[0]['game_name']=$v['game_name'];
//            $all_app_data[0]['game_name'] = '全部游戏';
            $result[] = $all_app_data;
        }
//        print_r($result);
        return $result;
    }
}


