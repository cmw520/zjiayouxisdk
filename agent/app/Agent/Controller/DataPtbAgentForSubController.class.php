<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataPtbAgentForSubController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $model = M('ptb_agentcharge');
        $where = array();
        $where['pac.status'] = 2;
        $where['pac.admin_id'] = $this->agid;
        $where['pac.agent_id'] = array("neq", $this->agid);
        if (isset($_GET['sub_agent_name']) && ($_GET['sub_agent_name'])) {
            $v = trim($_GET['sub_agent_name']);
            $where['u.user_nicename'] = array("like", "%$v%");
        }
        if (isset($_GET['give_agent_name']) && ($_GET['give_agent_name'])) {
            $v = trim($_GET['give_agent_name']);
            $where['u2.user_nicename'] = array("like", "%$v%");
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "pac.create_time");
        $count = $model
            ->field("pac.*")
            ->alias("pac")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=pac.admin_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("pac.*,u.user_nicename as sub_agent_name,u2.user_nicename as give_agent_name")
            ->alias("pac")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=pac.admin_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("pac.id desc")
            ->select();
        $payway_data = $this->payway_txt();
        foreach ($items as $key => $value) {
            $items[$key]['payway_txt'] = $payway_data[$value['payway']];
        }
        $this->assign("total_rows", $count);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }
}

