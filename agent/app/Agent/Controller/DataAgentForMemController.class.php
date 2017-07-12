<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataAgentForMemController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $where = array();
        $where['gc.admin_id'] = $this->agid;
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
        $page = new \Think\Page($count, 5);
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
        $this->assign("page", $page->show());
        $this->display();
    }

    public function all() {
        $where = array();
        $where['gc.admin_id'] = $this->agid;
        if (isset($_GET['mem_id'])) {
//            $where['gc.mem_id']=$_GET['mem_id'];
        }
//        if(isset($_GET['agent_name'])&&($_GET['agent_name'])){
//            $v=trim($_GET['agent_name']);
//            $where['u.user_nicename'] = array("like","%$v%");
//        }
        if (isset($_GET['mem_name']) && ($_GET['mem_name'])) {
            $v = trim($_GET['mem_name']);
            $where['m.username'] = array("like", "%$v%");
        }
        if (isset($_GET['game_name']) && ($_GET['game_name'])) {
            $v = trim($_GET['game_name']);
            $where['g.name'] = array("like", "%$v%");
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gc.create_time");
        $count = M('gm_charge')
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->count();
        $page = new \Think\Page($count, 20);
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
        $this->assign("totalrows", $count);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }
}
