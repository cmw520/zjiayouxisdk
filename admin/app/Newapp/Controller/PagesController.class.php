<?php
/*
 *  @time 2017-1-22 17:55:08
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class PagesController extends AdminbaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->model = M('slide');
    }

    public function index() {
        $items = $this->getList();
        $this->assign("items", $items);
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = $this->model
            ->alias("sl")
            ->field("sl.slide_id as id,sc.cat_name as title,sl.slide_content as content")
            ->join("LEFT JOIN ".C("DB_PREFIX")."slide_cat sc ON sc.cid=sl.slide_cid")
            ->where(array("sc.cat_idname" => array("IN", "regagreement,platagreement,rightagreement")))
            ->where($where)
            ->limit($start, $limit)
            ->select();

        return $items;
    }

    public function edit() {
        $slide_id = I('id');
        $data = $this->getList(array("sl.slide_id" => $slide_id));
        $this->assign("data", $data[0]);
        $this->display();
    }

    public function editPost() {
        $id = I('id/d', 0);
        $data['slide_name'] = I('title/s', '');
        $data['slide_content'] = html_entity_decode(I('content'));
        if (empty($id) || empty($data['slide_name']) || empty($data['slide_content'])) {
            $this->error('参数错误');
        }
        $edit_result = $this->model
            ->where(array("slide_id" => $id))
            ->save($data);
        if (false === $edit_result) {
            $this->error("编辑失败，或者无变化");
        }
        $this->success("编辑成功", U('Pages/index'));
    }
}