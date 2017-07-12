<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataAgentGainFromSubController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "aor.create_time");
        $hs_where_obj->get_simple($where, "app_id", "aor.app_id");
//        $hs_where_obj->get_simple($where, "agent_id", "u2.id");
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_agfsfm_obj = new \Huosdk\Data\GainFromSubForMem();
        $all_items = $hs_agfsfm_obj->getList($where);
        $total_rows = count($all_items);
        $page = $this->page($total_rows, 10);
        $items = $hs_agfsfm_obj->getList($where, $page->firstRow, $page->listRows);
        $sums = $hs_agfsfm_obj->getSum($where);
        $this->assign("sums", $sums);
        $this->assign("items", $items);
        $this->assign("page", $page->show('Admin'));
        $this->assign("formget", $_GET);
        $this->display();
    }
}

