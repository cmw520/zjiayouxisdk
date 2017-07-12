<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class GameZoneController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function edit() {
        $app_id = $_GET['id'];
        $data = M('game')->where(array("id" => $app_id))->find();
        $imgs = M('game_subsite')->where(array("app_id" => $app_id))->find();
        $this->assign("imgs", $imgs);
        $this->assign("data", $data);
        $this->display();
    }

    public function edit_post_old() {
        $app_id = I('app_id');
        $upload_fp = SITE_PATH.'access/upload/mobile/zone/';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 10 * 1024 * 1024;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = $upload_fp; // 设置附件上传根目录
//        $upload->savePath  =      $app_id; // 设置附件上传（子）目录
        $upload->subName = $app_id;
        $upload->autoSub = true;
        // 上传文件 
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        } else {// 上传成功 获取上传文件信息
            $i = 0;
            foreach ($info as $file) {
                echo "<p> $i ".$file['savepath']." : ".$file['savename']."</p>";
                $i++;
            }
        }
    }

    private function handle_file($upload, $app_id, $name) {
        $arr = $_FILES["$name"];
        if (isset($arr) && $arr['name']) {
            $upload->saveName = $name;
            $info = $upload->uploadOne($arr);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                $fp = $info['savepath'].$info['savename'];
                $model = M('game_subsite');
                $where = array("app_id" => $app_id);
                $model->where($where)->setField("$name", $fp);
            }
        }
    }

    public function edit_post() {
        $app_id = I('app_id');
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 10 * 1024 * 1024;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = SITE_PATH.'access/upload/mobile/zone/'; // 设置附件上传根目录
//        $upload->savePath  =      $app_id; // 设置附件上传（子）目录
        $upload->subName = $app_id;
        $upload->autoSub = true;
        $upload->replace = true;
        // 上传文件 
        $model = M('game_subsite');
        $where = array("app_id" => $app_id);
        $exists = $model->where($where)->find();
        if (!$exists) {
            $model->where($where)->add(array("app_id" => $app_id));
        }
        $this->handle_file($upload, $app_id, "banner");
        $this->handle_file($upload, $app_id, "banner2");
        $this->handle_file($upload, $app_id, "download_btn");
        $this->handle_file($upload, $app_id, "gift_btn");
        $this->handle_file($upload, $app_id, "background");
//        $this->success("上传成功",U('Mobile/Mobile/game'));
        $this->success("上传成功");
    }
}

