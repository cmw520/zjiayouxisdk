<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentGamePackageController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "app_id", "gg.app_id");
        $hs_where_obj->get_simple($where, "agent_id", "gg.agent_id");
        $model = M('agent_game');
        $app_appid = C("APP_APPID");
        $where['_string'] = "gg.url IS NOT NULL AND (gg.app_id != $app_appid )";
//        $where['gg.app_id'] = array("neq",C("APP_APPID"));
        $count = $model
            ->field("gg.*,u.user_nicename as agent_name,g.name as game_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gg.agent_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("gg.*,u.user_nicename as agent_name,g.name as game_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gg.agent_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("gg.id desc")
            ->select();
        \Huosdk\Data\FormatRecords::package_generate_status($items);
        $this->package_fp($items);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function package_generate_status2(&$items) {
        foreach ($items as $key => $value) {
            if (!$value['url']) {
                $items[$key]['url'] = "出包中";
            }
        }
    }

    public function package_fp(&$items) {
        foreach ($items as $key => $value) {
            if ($value['url']) {
                $items[$key]['package_fp'] = DOWNSITE.$value['url'];
            }
        }
    }

    public function delete() {
        $agid = I('agid');
        M('agent_game')->where(array("id" => $agid))->setField("url", "");
        M('agent_game')->where(array("id" => $agid))->setField("status", "1");
        $this->ajaxReturn(array("error" => "0", "msg" => "删除成功"));
    }

    public function update() {
        $agid = I('agid');
        $hs_package_obj = new \Huosdk\Package();
        $result = $hs_package_obj->pack($agid);
        if ($result['error'] == 1) {
            $this->ajaxReturn(array("error" => "1", "msg" => $result['msg']));
            exit;
        }
        M('agent_game')->where(array("id" => $agid))->setField("status", "2");
        $this->ajaxReturn(array("error" => "0", "msg" => "更新成功"));
    }
}
