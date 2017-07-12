<?php
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class PlayerRoleController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->_game(true, null, null, null, null, null);
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
//        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
//        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "app_id", "mr.app_id");
        $hs_where_obj->get_simple_like($where, "username", "m.username");
        $hs_where_obj->get_simple($where, "server", "mr.server");
        $hs_where_obj->get_simple($where, "role", "mr.role");
        $hs_where_obj->time($where, "mr.update_time");

        $_cnt = $this->getCnt($where);
        $page = $this->page($_cnt, 10);
        $_item = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign('items', $_item);
        $this->assign('formget', $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function getCnt($where) {
        $_cnt = M('mg_role')
            ->alias("mr")
            ->join("left join ".C('DB_PREFIX')."game g ON mr.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."members m ON mr.mem_id = m.id")
            ->where($where)
            ->count();

        return $_cnt;
    }

    public function getList($where, $start = 0, $limit = 0) {
        $field = "mr.mem_id,mr.app_id,mr.server,mr.role,mr.level,mr.money,mr.update_time "
                 .",m.username,g.name";
        $items = M('mg_role')
            ->alias("mr")
            ->field($field)
            ->join("left join ".C('DB_PREFIX')."game g ON mr.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."members m ON mr.mem_id = m.id")
            ->where($where)
            ->order("mr.id DESC")
            ->limit($start, $limit)
            ->select();

        return $items;
    }
}

