<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemChargeController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agent_id = $this->agid;
        $model = M("pay");
        $where = array();
//        $where['gp.agent_id']=$agent_id;
        if (isset($_GET['mem_id'])) {
//            $where['gp.mem_id']=$_GET['mem_id'];
        }
        $where = array();
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
            $where['u.user_nicename'] = array("like", "%$v%");
        }
        if (isset($_GET['order_id']) && ($_GET['order_id'])) {
            $v = $_GET['order_id'];
            $where['gp.order_id'] = $v;
        }
        $count = $model
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->where($where)->count();
        $page = new \Think\Page($count, 20);
        $records = $model
            ->field("gp.*,g.name as game_name,m.username as mem_name,u.user_login as agent_name")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->where($where)
            ->order("gp.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $payway_data = $this->payway_txt();
        foreach ($records as $key => $value) {
            $records[$key]['payway_txt'] = $payway_data[$value['payway']];
        }
        $this->assign("page", $page->show());
        $this->assign("items", $records);
        $this->assign("formget", $_GET);
        $this->assign("n", $count);
        $this->display();
    }
}
