<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AboutController extends AdminbaseController {
    private $model;

    function _initialize() {
        parent::_initialize();
        $this->model = M('web_aboutus');
    }

    public function index() {
        $data = $this->model->where(array("title" => '合作联系'))->find();
        $this->assign("data", $data);
//        $items = $this->model->select();
//
//        $this->assign("items", $items);
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function add_post() {
        $this->model = M('tui_news');
        $data = array(
            "title"       => I('title'),
            "content"     => htmlspecialchars_decode(I('content')),
            "create_time" => time(),
            "is_delete"   => "2"
        );
        $this->model->add($data);
        $this->success("添加成功", U('tui/news/index'));
    }

    public function edit() {
        $data = $this->model->where(array("id" => I('id')))->find();
        $this->assign("data", $data);
        $this->display();
    }

    public function delete_post() {
        $this->model = M('tui_news');
        $this->model->where(array("id" => I('id')))->delete();
        $this->success("删除成功", U('tui/news/index'));
    }

    public function edit_post() {
        $data = array(
//            "title" => I('title'),
"content" => htmlspecialchars_decode(I('content'))
        );
        $this->model->where(array("id" => I('id')))->save($data);
        $this->success("编辑成功", U('tui/about/index'));
    }

    public function CheckApply() {
        $this->model = M('agent_game');
        $count = $this->model->count();
        $page = $this->page($count, $this->row);
        $users = $this->model
            ->field('ag.id,ag.create_time,ag.check_status,g.name as gamename,u.mobile,u.user_nicename as username')
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ag.agent_id")
            ->order("ag.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("users", $users);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function checkinfo() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $this->model = M('users');
        $c_results = $this->model->where("user_type= $subagent_roleid ")->count();
        $count = count($c_results);
        $row = isset($_POST['row']) ? $_POST['row'] : $this->row;
        $page = $this->page($count, $row);
        $users = $this->model
            ->field('u.*,am.*')
            ->alias('u')
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_man am ON u.id=am.agent_id")
            ->where("u.user_type= $subagent_roleid ")
            ->order("id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("users", $users);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function checkwithdraw() {
        $this->show("check");
    }

    public function withdrawRecord() {
        $this->display();
    }

    public function pass() {
        $this->set_check_status(I('id'), 2);
    }

    private function set_check_status($id, $status) {
        $this->model = M('agent_game');
        $c_results = $this->model->where("id=$id")->setField("check_status", $status);
        if ($c_results) {
            $this->success("审核成功");
        } else {
            $this->error("审核失败，内部错误");
        }
    }

    public function notpass() {
        $this->set_check_status(I('id'), 3);
    }

    public function ads() {
        $data = M('web_media')->where(array("name" => "agent_site_pics"))->select();
        $this->assign("data", $data);
        $this->display();
    }

    public function ads_post() {
        $id = I('id');
        $url = I('url');
        M('web_media')->where(array("id" => $id))->setField("url", $url);
        $obj = new \Huosdk\Upload();
        $fp = $obj->image_upload("img");
        if ($fp) {
            M('web_media')->where(array("id" => $id))->setField("icon", $fp);
        }
        $this->success("修改成功");
    }
}
