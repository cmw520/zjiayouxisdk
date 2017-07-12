<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemChargeController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agent_id = $this->agid;
        //让一级代理能看到自己所有下级代理的信息
        //2016年12月17日 16:20:44 严旭
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->GetMeAndMySubAgentIDs();
        $model = M("pay");
        $where = array();
        $where['gp.agent_id'] = array("in", $ids);
        if (isset($_GET['mem_id'])) {
            $where['gp.mem_id'] = $_GET['mem_id'];
        }
        $where['gp.status'] = 2;
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gp.create_time");
        if (isset($_GET['mem_name']) && ($_GET['mem_name'])) {
            $v = $_GET['mem_name'];
            $where['m.username'] = array("like", "%$v%");
        }
        if (isset($_GET['game_name']) && ($_GET['game_name'])) {
            $v = $_GET['game_name'];
            $where['g.name'] = array("like", "%$v%");
        }
        if (isset($_GET['agent_name']) && ($_GET['agent_name'])) {
            $v = $_GET['agent_name'];
            $where['u.user_login'] = array("like", "%$v%");
        }
        if (isset($_GET['order_id']) && ($_GET['order_id'])) {
            $v = $_GET['order_id'];
            $where['gp.order_id'] = $v;
        }
        if (isset($_GET['payway']) && (!empty($_GET['payway']))) {
            if (2 == $_GET['payway']) {
                $where['gp.payway'] =  array('in', "ptbpay,gamepay");
            } else {
                $where['gp.payway'] = array('not in', "ptbpay,gamepay,0");
            }
        }
        $count = $this->getCnt($where);
        $page = new \Think\Page($count, 20);
        $records = $this->getList($where, $page->firstRow, $page->listRows);
        /**
         ** 加入汇总
         ** 2016-12-17 16:12:27
         ** 严旭
         **/
        $records_sum = $model
            ->field("sum(gp.amount) as sum_amount,sum(gp.real_amount) as real_amount")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->where($where)
            ->select();
        $this->assign("items_sum", $records_sum);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "玩家充值明细";
            $expCellName = array(
                array("agent_name", "渠道名称"),
                array("create_time", "充值时间"),
                array("order_id", "订单号"),
                array("mem_name", "玩家账号"),
                array("game_name", "充值游戏"),
                array("amount", "充值金额"),
                array("real_amount", "实收金额"),
                array("payway_txt", "充值方式"),
            );
            $expTableData = $this->getList($where);
            foreach ($expTableData as $k => $v){
                $expTableData[$k]["order_id"] = " ".$v["order_id"];
                $expTableData[$k]["create_time"] = date('Y-m-d H:i:s',$v["create_time"]);
            }
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("page", $page->show());
        $this->assign("items", $records);
        $this->assign("formget", $_GET);
        $this->assign("n", $count);
        $this->display();
    }

    public function getList($where_extra = array(), $start = 0, $limit = 0) {
        $model = M("pay");
        $records = $model
            ->field("gp.*,g.name as game_name,m.username as mem_name,u.user_login as agent_name")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->where($where_extra)
            ->order("gp.id desc")
            ->limit($start, $limit)
            ->select();
        $payway_data = $this->payway_txt();
        foreach ($records as $key => $value) {
            $records[$key]['payway_txt'] = $payway_data[$value['payway']];
            if (!$value['agent_name']) {
                $records[$key]['agent_name'] = "官方";
            }
        }

        return $records;
    }

    public function getCnt($where_extra = array()) {
        $_cnt = M("pay")
            ->field("gp.*,g.name as game_name,m.username as mem_name,u.user_login as agent_name")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->where($where_extra)
            ->count();

        return $_cnt;
    }
}
