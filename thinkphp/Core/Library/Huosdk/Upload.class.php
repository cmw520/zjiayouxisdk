<?php
namespace Huosdk;
class Upload {
    public function __construct() {
    }

    public function image_upload($name) {
        $image_fp = '';
        $f = $_FILES["$name"];
        if (isset($f) && ($f['name'])) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 10 * 1024 * 1024;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = SITE_PATH.'access/upload/'; // 设置附件上传根目录
            $upload->autoSub = false;
            $upload->replace = true;
            $info = $upload->uploadOne($f);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                $image_fp = $info['savename'];
            }
        }
        $full_fp = '/upload/'.$image_fp;
        return $full_fp;
    }
}

