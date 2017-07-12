<?php
/*
 *  @time 2017-1-22 17:55:08
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class FeedbackController extends AdminbaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->model = M('guest_book');
    }

    public function index() {
        $this->_game();
        $where = array();
        $count = $this->getCnt($where);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = $this->model
            ->alias("gb")
            ->field("gb.*,m.username as mem_name")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gb.mem_id")
            ->where($where)
            ->limit($start, $limit)
            ->order('id desc')
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
        }
        $this->formatReplyStatus($items);

        return $items;
    }

    public function getCnt($where = array()) {
        $_cnt = $this->model
            ->alias("gb")
            ->field("gb.*,m.username as mem_name")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gb.mem_id")
            ->where($where)
            ->count();

        return $_cnt;
    }

    public function formatReplyStatus(&$items) {
        $data = array("1" => "未处理", "2" => "已处理", "3" => "删除");
        foreach ($items as $key => $value) {
            $items[$key]['status_txt'] = $data[$value['status']];
        }
    }

    public function edit() {
        $id = I('id');
        $data = $this->getList(array("gb.id" => $id));
        $this->assign("data", $data[0]);
        $this->display();
    }

    public function editPost() {
        $id = I('id/d');
        $data = array();
        $data['status'] = '2';
        $data['update_time'] = time();
        $_remark = I('remark/s');
        if (empty($_remark)) {
            $this->error('未处理');
        }
        $data['remark'] = html_entity_decode($_remark);
        $edit_result = $this->model
            ->where(array("id" => $id))
            ->save($data);
        if (false === $edit_result) {
            $this->error("编辑失败");
        } else {
            $this->success("编辑成功", U('Feedback/index'));
        }
    }
}