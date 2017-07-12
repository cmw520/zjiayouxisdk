<?php
/*
 *  @time 2017-1-19 21:26:47
 *  @author 严旭
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class SlideBaseController extends AdminbaseController {
    public $slide_model;
    public $slidecat_model;
    public $slidecat_type_id;

    function _initialize() {
        parent::_initialize();
        $this->slide_model = D("Common/Slide");
        $this->slidecat_model = D("Common/SlideCat");
    }

    public function setSelectAreas($type_id = 0, $target_id = 0) {
        if ($type_id == 2) {
            $_GET['app_id'] = $target_id;
        } else if ($type_id == 3) {
            $_GET['gift_id'] = $target_id;
        } else if ($type_id == 4) {
            $_GET['coupon_id'] = $target_id;
        }
        $this->assign("app_select_area", $this->getAppSelect());
        $this->assign("gift_select_area", $this->getGiftSelect());
        $this->assign("coupon_select_area", $this->getcouponSelect());
    }

    public function _cates() {
        $cates = array(
            array("cid" => "0", "cat_name" => "全部类别"),
        );
        $categorys = $this->slidecat_model
            ->field("cid,cat_name")
            ->where("cat_status!=0")
            ->where("cat_type_id = $this->slidecat_type_id ")
            ->select();
        if ($categorys) {
            $categorys = array_merge($cates, $categorys);
        } else {
            $categorys = $cates;
        }
        $this->assign("categorys", $categorys);
    }

    public function _status() {
        $data = array("1" => "隐藏", "2" => "显示");
        $this->assign("status", $data);
    }

    //隐藏
    function ban() {
        $id = intval($_GET['id']);
        $data['slide_status'] = 1;
        if ($id) {
            $rst = $this->slide_model->where("slide_id in ($id)")->save($data);
            if ($rst) {
                $this->success("轮播图隐藏成功！");
            } else {
                $this->error('轮播图隐藏失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    //显示
    function cancelban() {
        $id = intval($_GET['id']);
        $data['slide_status'] = 2;
        if ($id) {
            $result = $this->slide_model->where("slide_id in ($id)")->save($data);
            if ($result) {
                $this->success("轮播图启用成功！");
            } else {
                $this->error('轮播图启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function delete() {
        if (isset($_POST['ids'])) {
            $ids = implode(",", $_POST['ids']);
            $data['slide_status'] = 1;
            if ($this->slide_model->where("slide_id in ($ids)")->delete() !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        } else {
            $id = intval(I("get.id"));
            if ($this->slide_model->delete($id) !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    function toggle() {
        if (isset($_POST['ids']) && $_GET["display"]) {
            $ids = implode(",", $_POST['ids']);
            $data['slide_status'] = 2;
            if ($this->slide_model->where("slide_id in ($ids)")->save($data) !== false) {
                $this->success("显示成功！");
            } else {
                $this->error("显示失败！");
            }
        }
        if (isset($_POST['ids']) && $_GET["hide"]) {
            $ids = implode(",", $_POST['ids']);
            $data['slide_status'] = 1;
            if ($this->slide_model->where("slide_id in ($ids)")->save($data) !== false) {
                $this->success("隐藏成功！");
            } else {
                $this->error("隐藏失败！");
            }
        }
    }

    //排序
    public function listorders() {
        $status = parent::_listorders($this->slide_model);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function getAppData() {
        $items = M('game')->getField("id,name", true);

        return $items;
    }

    public function getGiftData() {
        $items = M('gift')->getField("id,title", true);

        return $items;
    }

    public function getCouponData() {
        $items = M('coupon')->getField("id,title", true);

        return $items;
    }

    public function getAppSelect() {
        $txt = $this->select_common($this->getAppData(), "app_id", $_GET['app_id']);

        return $txt;
    }

    public function getGiftSelect() {
        $txt = $this->select_common($this->getGiftData(), "gift_id", $_GET['gift_id']);

        return $txt;
    }

    public function getCouponSelect() {
        $txt = $this->select_common($this->getCouponData(), "coupon_id", $_GET['coupon_id']);

        return $txt;
    }

    public function select_common($data, $name, $current, $select2 = '') {
        $txt = "<select class='select_2' name='$name' select2='$select2' >";
        $txt .= "<option value='0' >请选择</option>";
        foreach ($data as $k => $v) {
            $select = '';
            if ($current == $k) {
                $select = ' selected ';
            }
            $txt .= "<option value='$k' $select >$v</option>";
        }
        $txt .= "</select>";

        return $txt;
    }

    public function formatTargetObject(&$items) {
        foreach ($items as $key => $value) {
            if ($value['type_id'] == '1') {
                $items[$key]['target_object'] = "【链接】 ".$value['slide_url'];
            } else if ($value['type_id'] == '2') {
                $a_data = $this->getAppData();
                $items[$key]['target_object'] = "【游戏】 ".$a_data[$value['target_id']];
            } else if ($value['type_id'] == '3') {
                $gift_data = $this->getGiftData();
                $items[$key]['target_object'] = "【礼包】 ".$gift_data[$value['target_id']];
            } else if ($value['type_id'] == '4') {
                $c_data = $this->getCouponData();
                $items[$key]['target_object'] = "【代金券】 ".$c_data[$value['target_id']];
            }
        }
    }
}