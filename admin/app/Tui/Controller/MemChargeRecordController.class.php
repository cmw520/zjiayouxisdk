<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class MemChargeRecordController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("memname_input", $hs_ui_filter_obj->memname_input());
        $this->assign("order_id_input", $hs_ui_filter_obj->order_id_input());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $this->assign("payway_select", $hs_ui_filter_obj->payway_select2());
        $this->assign("pay_from", $hs_ui_filter_obj->pay_from());
        $this->getList();
        $this->display();
    }

    public function getList() {
        $model = M('gm_charge');
//        $where = array();
//        $where[''] = $v;
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gc.create_time");
        $hs_where_obj->get_simple($where, "order_id", "gc.order_id");
        $hs_where_obj->get_simple($where, "app_id", "gc.app_id");
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_where_obj->get_simple($where, "payway", "gc.payway");
        $hs_where_obj->get_simple($where, "pay_from", "gc.flag");
        $count = $model
            ->field("gc.*,g.name as game_name,m.username as member_name")
            ->alias("gc")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->count();
        $page = $this->page($count, 10);
        $items = $model
            ->field("gc.*,g.name as game_name,m.username as member_name")
            ->alias("gc")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("gc.id desc")
            ->select();
        \Huosdk\Data\FormatRecords::mem_charge_from($items);
        \Huosdk\Data\FormatRecords::pay_status($items);
        \Huosdk\Data\FormatRecords::payway($items);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
    }
}

