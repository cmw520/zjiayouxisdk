<?php
/*
 *  @time 2017-1-20 20:42:33
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class GoodsController extends AdminbaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->model = M('goods');
    }

    public function index() {
        $where = array();
        $h_obj = new \Huosdk\Where();
        $h_obj->get_simple_like($where, "goods_name", "g.goods_name");
        $allitems = $this->getList($where);
        $count = count($allitems);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = $this->model
            ->alias("g")
            ->field("g.*")
            ->where($where)
            ->order("g.goods_id desc")
            ->limit($start, $limit)
            ->select();
        $real_type_data = array("1" => "虚拟", "2" => "实物");
        $on_sale_data = array("1" => "下架", "2" => "上架");
        foreach ($items as $key => $value) {
            $items[$key]['is_real_txt'] = $real_type_data[$value['is_real']];
            $items[$key]['last_update'] = date('Y-m-d H:i:s', $value['last_update']);
            $items[$key]['on_time'] = date('Y-m-d H:i:s', $value['on_time']);
            $items[$key]['on_sale_txt'] = $on_sale_data[$value['is_on_sale']];
        }

        return $items;
    }

    public function setOnSale() {
        $id = I('id');
        $is_on_sale = I('is_on_sale');
        if ($is_on_sale != "1" && $is_on_sale != "2") {
            $this->error("参数有误");
        }
        $this->model
            ->where(array("goods_id" => $id))
            ->setField("is_on_sale", $is_on_sale);
        $this->success("编辑成功");
    }

    public function add() {
        $this->display();
    }

    public function addPost() {
        $this->commonFilter();
        $data = array();
        $data['goods_name'] = I('goods_name');
        $data['integral'] = I('integral');
        $data['goods_intro'] = htmlspecialchars(I('goods_intro'));
        $data['goods_content'] = html_entity_decode(I('goods_content'));
        $data['last_update'] = time();
        $data['is_real'] = I('is_real');
        $data['original_img'] = I('original_img');
        $data['store_count'] = I('store_count');
        $data['remain_count'] = I('store_count');
        $data['market_price'] = I('market_price');
        $data['on_time'] = strtotime(I('on_time'));
        $data['create_time'] = time();
        $data['is_on_sale'] = I('is_on_sale');
        $id = $this->model->add($data);
        if (!$id) {
            $this->error("商品创建失败");
        }
        $this->success("商品创建成功", U('Newapp/Goods/index'));
    }

    public function commonFilter() {
        if (!I('goods_content')) {
            $this->error("商品内容不能为空");
        }
        if (!I('goods_intro')) {
            $this->error("商品简介不能为空");
        }
        if (!I('goods_name')) {
            $this->error("商品名称不能为空");
        }
        if (!I('market_price')) {
            $this->error("市场价格不能为空");
        }
        if (I('market_price') <= 0) {
            $this->error("市场价格必须大于0");
        }
        if (!I('on_time')) {
            $this->error("上架时间不能为空");
        }
        if (!I('integral')) {
            $this->error("积分价格不能为空");
        }
        if (I('integral') <= 0) {
            $this->error("积分价格必须大于0");
        }
        if (!I('original_img')) {
            $this->error("商品展示图不能为空");
        }
        $count = I('store_count');
        if ($count < 0 || !is_numeric($count)) {
            $this->error("库存必须为正整数");
        }
    }

    public function delete() {
        $id = I('id');
        $result = $this->model->where(array("goods_id" => $id))->delete();
        if (!$result) {
            $this->error("删除失败");
        }
        $this->success("删除成功");
    }

    public function edit() {
        $id = I('id');
        $info = $this->getList(array("g.goods_id" => $id), 0, 1);
        $this->assign("data", $info[0]);
        $this->display();
    }

    public function editPost() {
        $id = I('id');
        $prev_dataset = $this->getList(array("g.goods_id" => $id), 0, 1);
        $prev_data = $prev_dataset[0];
        $this->commonFilter();
        $data = array();
        $data['goods_name'] = I('goods_name');
        $data['goods_content'] = html_entity_decode(I('goods_content'));
        $data['last_update'] = time();
        $data['is_real'] = I('is_real');
        $data['original_img'] = I('original_img');
        $data['store_count'] = I('store_count');
        $data['market_price'] = I('market_price');
        $data['on_time'] = strtotime(I('on_time'));
        $data['integral'] = I('integral');
        $data['goods_intro'] = htmlspecialchars(I('goods_intro'));
        $data['is_on_sale'] = I('is_on_sale');
        if ($prev_data['store_count'] != I('store_count')) {
            $data['remain_count'] = $prev_data['remain_count'] - ($prev_data['store_count'] - I('store_count'));
        }
        $result = $this->model->where(array("goods_id" => $id))->save($data);
        if (!$result) {
            $this->error("消息更新失败或无变化");
        }
        $this->success("保存成功");
    }
}
