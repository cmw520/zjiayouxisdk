<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataAgentForMemController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $where = array();
//        $where['gc.admin_id']=$this->agid;
        if (isset($_GET['mem_id'])) {
            $where['gc.mem_id'] = $_GET['mem_id'];
        }
        $count = M('gm_charge')
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 15);
        $items = M('gm_charge')
            ->field(
                "gc.order_id,gc.create_time,gc.admin_id,gc.gm_cnt as coin_cnt,gc.payway,gc.money as real_pay,"
                ."u.mobile,m.username as mem_name,g.name as game_name,u.user_login as agent_name"
            )
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->order("gc.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign("items", $items);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
}
