<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataMemConsumeController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agent_id = $this->agid;
        $model = M("pay");
        $where = array();
//        $where['gp.agent_id']=$agent_id;
        if (isset($_GET['mem_id'])) {
            $where['gp.mem_id'] = $_GET['mem_id'];
        }
        $count = $model
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->where($where)->count();
        $page = $this->page($count, 10);
        $records = $model
            ->field("gp.*,g.name as game_name,m.username as mem_name")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign("page", $page->show('Admin'));
        $this->assign("items", $records);
        $this->display();
    }
}
