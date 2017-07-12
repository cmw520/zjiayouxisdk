<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class GameCateController extends AdminbaseController {
    private $game_obj;
    private $model;

    function _initialize() {
        parent::_initialize();
        Vendor('HuoShu.Game');
        $this->game_obj = new \HuoShu\Game();
        $this->model = M('type');
    }

    public function index() {
        $result = $this->game_obj->all_game_category();
        $this->assign("items", $result['items']);
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
        $this->success("成功");
    }

    public function add_post() {
        $name = $_POST['name'];
        $exists = $this->model->where(array("name" => $name, "obj" => "1"))->find();
        if ($exists) {
            $this->error("分类已经存在");

            return;
        }
        $image_fp = $this->image_upload();
        $this->model->add(array("name" => $name, "image" => $image_fp));
        $this->success("添加成功");
    }

    private function image_upload() {
        $image_fp = '';
        if (isset($_FILES['image']) && ($_FILES['image']['name'])) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 10 * 1024 * 1024;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = SITE_PATH.'access/upload/category/'; // 设置附件上传根目录
            $upload->autoSub = false;
            $upload->replace = true;
            $info = $upload->uploadOne($_FILES['image']);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                $image_fp = $info['savename'];
            }
        }

        return $image_fp;
    }

    public function sort_post() {
//        print_r($_POST['order']);
        $orders = $_POST['order'];
        $model = M('type');
        foreach ($orders as $key => $order) {
            $model->where(array("id" => $key))->setField("order", $order);
        }
        $this->success("排序成功");
    }

    public function edit_post() {
        $id = I('id');
        $name = I('name');
        $image_fp = $this->image_upload();
        if ($image_fp) {
            $this->model->where(array("id" => $id))->save(array("name" => $name, "image" => $image_fp));
        } else {
            $this->model->where(array("id" => $id))->save(array("name" => $name));
        }
        $this->success("修改成功");
    }
}