<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class NewsController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function getlist() {
        $type = 4;
        $income_type = I('type');
        if ($income_type) {
            $type = $income_type;
        }
        $model = M('posts');
        $where = array();
        $where['post_type'] = $type;
        $count = $model
            ->alias("p")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $field = "";
        $items = $model
            ->field($field)
            ->alias("p")
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order("p.id desc")
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display("News/getlist");
    }
//    public function getlist() {
//        $model = M('tui_news');
//        $count = $model->count();
//        $page = $this->page($count, 20);
//        $_field = "id,title post_title,create_time post_date";
//        $news = $model
//            ->alias('tn')
//            ->field($_field)
//            ->order("tn.id desc")
//            ->limit($page->firstRow.','.$page->listRows)
//            ->select();
//        $this->assign("items", $news);
//        $this->assign("formget", $_GET);
//        $this->assign("page", $page->show('Admin'));
//        $this->display("News/getlist");
//    }
//    public function viewItem() {
//        $id = I('id');
//        $_field = "title post_title,create_time post_date,content post_content";
//        $data = M('tui_news')->field($_field)->where(array("id" => $id))->find();
//        $this->assign("data", $data);
//        $this->display();
//    }
    public function viewItem() {
        $id = I('id');
        $data = M('posts')->where(array("id" => $id))->find();
        $this->assign("data", $data);
        $this->display();
    }
}

