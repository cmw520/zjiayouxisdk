<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class AgentGainFromSubController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $where = array();
        $where['aor.agent_id'] = $this->agid;
        $hs_agfsfm_obj = new \Huosdk\Data\GainFromSubForMem();
        $all_items = $hs_agfsfm_obj->getList($where);
        $total_rows = count($all_items);
        $page = new \Think\Page($total_rows, 10);
        $items = $hs_agfsfm_obj->getList($where, $page->firstRow, $page->listRows);
        $sums = $hs_agfsfm_obj->getSum($where);
//        print_r($items);
        $this->assign("sums", $sums);
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->assign("formget", $_GET);
        $this->display();
    }
}

