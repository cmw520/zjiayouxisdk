<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class PostTagController extends AdminbaseController {
    private $game_obj;
    private $model;

    function _initialize() {
        parent::_initialize();
        Vendor('HuoShu.Game');
        $this->game_obj = new \HuoShu\Game();
        $this->model = M('tag');
    }

    public function index() {
        $items = $this->game_obj->all_news_tag();
        $this->assign("items", $items);
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function edit() {
        $id = I('id');
        $data = $this->model->where(array("id" => $id))->find();
        $this->assign("data", $data);
        $this->display();
    }

    public function del() {
        $id = I('id');
        $this->model->where(array("id" => $id))->delete();
        $this->ajaxReturn(array("error" => "0", "msg" => "成功"));
    }

    public function add_post() {
        $name = $_POST['name'];
        if ($name == '') {
            $this->ajaxReturn(array("error" => "1", "msg" => "标签名称不能为空"));

            return;
        }
        $exists = $this->model->where(array("name" => $name, "obj" => "2"))->find();
        if ($exists) {
            $this->ajaxReturn(array("error" => "1", "msg" => "已经存在"));

            return;
        }
        $this->model->add(array("name" => $name, "obj" => "2"));
        $this->ajaxReturn(array("error" => "0", "msg" => "添加成功"));
    }

    public function edit_post() {
        $id = I('id');
        $name = I('name');
        $this->model->where(array("id" => $id))->save(array("name" => $name));
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }
}