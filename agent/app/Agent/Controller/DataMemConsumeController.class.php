<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemConsumeController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agent_id = I('agent_id');
        $model = M("pay");
        $where = array();
        $where['gp.status'] = 2;
        $where['gp.agent_id'] = $agent_id;
        if (isset($_GET['mem_id'])) {
            $where['gp.mem_id'] = $_GET['mem_id'];
        }
        $count = $model
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->where($where)->count();
        $page = new \Think\Page($count, 10);
        $records = $model
            ->field("gp.*,g.name as game_name,m.username as mem_name")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign("page", $page->show());
        $this->assign("items", $records);
        $this->display();
    }
}
