<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class IndexPageHotGameController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->assign("games", $this->get_all_games());
        $this->assign("items", $this->get_tui_games());
        $this->display();
    }

    private function get_all_games() {
        $items = $this->getHotGameLists();
        $lists = join(",", $items);
        $model = M('game');
        $where = array();
        if ($lists) {
            $where["_string"] = "id NOT IN ($lists)";
        }
        $where['is_delete'] = 2;
        $where['is_own'] = 2;
        $results = $model->where($where)->order("id desc")->select();

        return $results;
    }

    public function getHotGameLists() {
        $model = M('options');
        $data = $model->where(array("option_name" => "index_page_hot_games"))->getField("option_value");
        if (!$data) {
            return array();
        }
        $items = json_decode($data, true);

        return $items;
    }

    private function get_tui_games() {
        $items = $this->getHotGameLists();
        $lists = join(",", $items);
        if ($lists) {
            $records = M('game')->where("id IN ($lists)")->select();
        }

        return $records;
    }

    private function get_tui_games_count() {
        $items = $this->getHotGameLists();
        $n = count($items);

        return $n;
    }

    public function hot_post() {
        $gameid = I('gameid');
        if ($this->get_tui_games_count() >= 9) {
            $this->ajaxReturn(array("error" => "1", "msg" => "最多只能添加9个"));
            exit;
        }
        if ($this->tui_game_already_has($gameid)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "已经添加过了，不能重复添加"));
            exit;
        }
        $prev_items = $this->getHotGameLists();
        if (is_array($prev_items)) {
            array_push($prev_items, $gameid);
        } else {
            $prev_items = array($gameid);
        }
        $new_lists = json_encode($prev_items);
        $aa = M('options')->where(array("option_name" => "index_page_hot_games"))->find();
        if (M('options')->where(array("option_name" => "index_page_hot_games"))->find()) {
            M('options')->where(array("option_name" => "index_page_hot_games"))->setField("option_value", $new_lists);
        } else {
            $adddata["option_name"] = "index_page_hot_games";
            $adddata["option_value"] = $new_lists;
            M('options')->add($adddata);
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "添加成功 "));
    }

    private function tui_game_already_has($gameid) {
        $items = $this->getHotGameLists();
        if (in_array($gameid, $items)) {
            return true;
        }

        return false;
    }

    public function delete() {
        $gameid = I('id');
        $items = $this->getHotGameLists();
        foreach ($items as $key => $value) {
            if ($value == $gameid) {
                unset($items[$key]);
            }
        }
        $new_lists = json_encode($items);
        M('options')->where(array("option_name" => "index_page_hot_games"))->setField("option_value", $new_lists);
        $this->ajaxReturn(array("error" => "0", "msg" => "删除成功"));
    }
}

