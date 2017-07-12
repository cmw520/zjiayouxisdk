<?php
/*
 *  @time 2017-1-21 16:02:21
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class DeliveryController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
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

    public function getMemData() {
        return M('members')->getField("id,username", true);
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = M('integral_order')
            ->alias("io")
            ->field("io.*,g.original_img,g.goods_name,g.is_real")
            ->join('LEFT JOIN '.C("DB_PREFIX")."goods g ON g.goods_id = io.goods_id")
            ->where($where)
            ->order("io.id desc")
            ->limit($start, $limit)
            ->select();
        $this->formatShippingStatus($items);
        $this->formatSumPrice($items);
        $this->formatBuyerName($items);
        $this->formatFlag($items);

        return $items;
    }

    public function formatBuyerName(&$items) {
        $data = $this->getMemData();
        foreach ($items as $key => $value) {
            $items[$key]['buyer_name'] = $data[$value['mem_id']];
        }
    }

    public function formatSendStatus(&$items) {
        $data = array("0" => "未发货", "1" => "已发货", "2" => "已换货", "3" => "已退货");
        foreach ($items as $key => $value) {
            $items[$key]['send_status_txt'] = $data[$value['is_send']];
        }
    }

    public function formatSumPrice(&$items) {
        foreach ($items as $key => $value) {
            $items[$key]['sum_price'] = $value['goods_num'] * $value['market_price'];
        }
    }

    public function formatShippingStatus(&$items) {
        $data = array("1" => "未发货", "2" => "已发货", "3" => "发货失败");
        foreach ($items as $key => $value) {
            $items[$key]['shipping_status_txt'] = $data[$value['shipping_status']];
        }
    }

    public function send() {
        $region_data = $this->regionData();
        $data = M('integral_order')
            ->alias("io")
            ->field("io.*,g.original_img,g.goods_name,g.is_real")
            ->join('LEFT JOIN '.C("DB_PREFIX")."goods g ON g.goods_id = io.goods_id")
            ->where(array("io.order_id" => I("order_id")))
            ->find();
//        $data['sum_price'] = $data['goods_num'] * $data['market_price'];
        $this->assign("order_id", I('order_id'));
        $data['province'] = $region_data[$data['province']];
        $data['city'] = $region_data[$data['city']];
        $data['district'] = $region_data[$data['district']];
        $data['town'] = $region_data[$data['town']];
        $this->assign("data", $data);
        $this->display();
    }

    public function sendPost() {
        $order_id = I('order_id');
        $exist = M('integral_order')->where(array("order_id" => I("order_id")))->find();
        if ($exist) {
            M('integral_order')->where(array("order_id" => I("order_id")))->save($_POST);
        } else {
            $data = $_POST;
            $data['create_time'] = time();
            M('integral_order')->add($data);
        }
        M('integral_order')->where(array("order_id" => I("order_id")))->setField("is_send", "1");
        $this->success("发货成功");
    }

    public function regionData() {
        $data = M('region')->getField("id,name", true);

        return $data;
    }

    public function getMemAddr($mem_id) {
        $data = M('mem_address')->where(array("mem_id" => $mem_id))->find();

        return $data;
    }

    public function getMemAddrFormat($mem_id) {
        $data = $this->getMemAddr($mem_id);
        $region_data = $this->regionData();
        $data['province'] = $region_data[$data['province']];
        $data['city'] = $region_data[$data['city']];
        $data['district'] = $region_data[$data['district']];

        return $data;
    }

    public function formatFlag(&$items) {
        $data = array(
            "1" => "购买代金卷", "2" => "购买礼包", "3" => "购买礼品卡", "4" => "购买实物"
        );
        foreach ($items as $key => $value) {
            $items[$key]['flag_txt'] = $data[$value['flag']];
        }
    }
}
