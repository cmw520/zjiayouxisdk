<?php
/**
 * IndexController.class.php UTF-8
 *
 * @date    : 2016年3月30日上午11:44:20
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 *
 */
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class AboutController extends AgentPublicController {
    function _initialize() {
        parent::_initialize();
    }

    public function get_about_content($code) {
        $result = M('tui_about')->where(array("short_code" => "$code"))->find();
        return $result;
    }

    public function aboutus() {
        $data = $this->get_about_content("aboutus");
        $this->assign("data", $data);
        $this->display();
    }

    public function Cooperation_process() {
        $data = $this->get_about_content("cooperation");
        $this->assign("data", $data);
        $this->display();
    }

    public function business() {
        $data = $this->get_about_content("business");
        $this->assign("data", $data);
        $this->display();
    }

    public function notice() {
        $id = I('path.2');
        $model = M('posts');
        if ($id) {
            $data = $model->where(array("id" => $id))->find();
            $this->assign("data", $data);
            $this->display();
        } else {
            $records = $model->where(array("post_type" => "4"))->order("id desc")->limit(10)->select();
            $this->assign("items", $records);
            $this->display("About/notice_all");
        }
    }
}