<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class IndexPageServerListController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->assign("items", $this->getList());
        $this->display();
    }

    public function getList() {
        $model = M('options');
        $data = $model->where(array("option_name" => "inde_page_serverlist"))->getField("option_value");
        if (!$data) {
            return array();
        }
        $items = json_decode($data, true);

//        print_r($items);
        return $items;
    }

    public function add() {
        $data = $_POST;
        if (!(isset($_POST['game_name']) && ($_POST['game_name']))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "游戏名称不能为空"));
            exit;
        }
        if (!(isset($_POST['server_name']) && ($_POST['server_name']))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "新服名称不能为空"));
            exit;
        }
        if (!(isset($_POST['start_time']) && ($_POST['start_time']))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "开服时间不能为空"));
            exit;
        }
        $prev_items = $this->getList();
        if (count($prev_items) >= 8) {
            $this->ajaxReturn(array("error" => "1", "msg" => "最多只能添加8个"));
            exit;
        }
        $new_item = array("item" => $data);
        array_push($prev_items, $new_item);
        $new_list = json_encode($prev_items);
        $model = M('options');
        $model->where(array("option_name" => "inde_page_serverlist"))->setField("option_value", $new_list);
        $this->ajaxReturn(array("error" => "0", "msg" => "添加成功"));
    }

    public function edit() {
    }

    public function delete() {
        $k = I('k');
        $prev_items = $this->getList();
        unset($prev_items[$k - 1]);
        $new_list = json_encode($prev_items);
        $model = M('options');
        $model->where(array("option_name" => "inde_page_serverlist"))->setField("option_value", $new_list);
        $this->ajaxReturn(array("error" => "0", "msg" => "删除成功"));
    }
}

