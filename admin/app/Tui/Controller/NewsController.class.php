<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class NewsController extends AdminbaseController {
    private $model;
    private $where;

    function _initialize() {
        parent::_initialize();
        $this->model = M('posts');
        $this->where = array("post_type" => 4);
    }

    public function index() {
        $count = $this->model->count();
        $page = $this->page($count, $this->row);
        $news = $this->model
            ->field('tn.*')
            ->alias('tn')
            ->order("tn.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->where($this->where)
            ->select();
        $this->assign("news", $news);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function add_post() {
        $data = array(
            "post_title"   => I('title'),
            "post_content" => htmlspecialchars_decode(I('content')),
            "post_date"    => time(),
            "post_status"  => "2",
            "post_type"    => "4"
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
        $this->model->where(array("id" => I('id')))->delete();
        $this->success("删除成功", U('tui/news/index'));
    }

    public function edit_post() {
        $data = array(
            "post_title"   => I('title'),
            "post_content" => htmlspecialchars_decode(I('content'))
        );
        $this->model->where(array("id" => I('id')))->save($data);
        $this->success("编辑成功", U('tui/news/index'));
    }

    public function CheckApply() {
        $model = M('agent_game');
        $count = $model->count();
        $page = $this->page($count, $this->row);
        $users = $model
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
        $model = M('users');
        $c_results = $model->where("user_type= $subagent_roleid ")->count();
        $count = count($c_results);
        $row = isset($_POST['row']) ? $_POST['row'] : $this->row;
        $page = $this->page($count, $row);
        $users = $model
            ->field('u.*,am.*')
            ->alias('u')
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_man am ON u.id=am.agent_id")
            ->where("u.user_type= $subagent_roleid ")
            ->order("id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
//        print_r($page);
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
        $model = M('agent_game');
        $c_results = $model->where("id=$id")->setField("check_status", $status);
        if ($c_results) {
            $this->success("审核成功");
        } else {
            $this->error("审核失败，内部错误");
        }
    }

    public function notpass() {
        $this->set_check_status(I('id'), 3);
    }
}

