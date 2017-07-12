<?php
/**
 * CouponController.class.php UTF-8
 * 代金卷后台管理
 *
 * @date    : 2017/1/18 11:05
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class CouponController extends AdminbaseController {
    protected $coupon_model;

    function _initialize() {
        parent::_initialize();
        $this->coupon_model = M('coupon');
    }

    /**
     * 代金卷列表
     */
    public function index() {
//        $this->_game();
        $this->get_rate();
        $this->_cList();
        $this->display();
    }

    /**
     * 代金卷列表计算页
     */
    public function _cList() {
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $title = trim(I('title/s', ''));
        $_money = I('money/d', 0);
        $result = array();
        $where = "is_delete = 2";
        $_formdata = array();
        if (isset($title) && $title != '') {
            $_formdata['title'] = $title;
            $where .= " AND title like '%".$title."%'";
        }
        if ($_money > 0) {
            $_formdata['money'] = $_money;
            $where .= " AND money=".$_money;
        }
        $result["total"] = $this->coupon_model->where($where)->count();
        $page = $this->page($result["total"], $rows);
        $couponlist = $this->coupon_model
            ->where($where)
            ->order("isrcmd DESC,id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign('formdata', $_formdata);
        $this->assign('coupons', $couponlist);
        $this->assign("Page", $page->show('Admin'));
    }

    /**
     *
     * 删除代金卷
     */
    public function del() {
        $coupon_id = I('id/d', 0);
        if ($coupon_id > 0) {
            $_map['id'] = $coupon_id;
            //伪删除信息
            $rs = $this->coupon_model->where($_map)->setField('is_delete', 1);
            if (false !== $rs) {
                $this->success("删除成功", U("Coupon/index"));
                exit;
            } else {
                $this->error("删除失败");
                exit;
            }
        }
        $this->error("参数错误");
    }

    /**
     * 修改使用代金卷最大比例
     */
    public function edit_rate() {
        if (!$_POST['max_rate']) {
            $this->ajaxReturn(array("error" => "1", "msg" => "比例填写错误"));
            exit;
        }
        $_max_rate = I('post.max_rate/d', 0);
        if ($_max_rate < 0 || $_max_rate > 100) {
            $this->ajaxReturn(array("error" => "1", "msg" => "比例填写错误"));
            exit;
        }
        $_map['option_name'] = 'appcoupongmrate';
        $_rs = M('options')->where($_map)->setField('option_value', $_max_rate / 100);
        if (false !== $_rs) {
            $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "设置失败"));
        }
    }

    /**
     * 获取最大比例
     */
    public function get_rate() {
        $_map['option_name'] = 'appcoupongmrate';
        $_max_rate = M('options')->where($_map)->getField('option_value');
        if (empty($_max_rate)) {
            $_data = $_map;
            $_data['option_value'] = 0;
            M('options')->add($_data);
            $_max_rate = 0;
        }
        $_max_rate = 100 * $_max_rate;
        $this->assign('max_rate', $_max_rate);
    }

    /**
     *
     * 上线代金卷
     * 下线代金卷
     */
    public function recommend() {
        $coupon_id = I('id/d', 0);
        $_recommend = I('isrcmd/d', 0);
        if (empty($_recommend)) {
            $this->error("参数错误");
        }
        if (2 == $_recommend) {
            /* 最多设置五款 */
            $_check_map['isrcmd'] = 2;
            $_check_map['is_delete'] = 2;
            $_check_map['end_time'] = array(array('gt', time()), 0, 'or');
            $_cnt = $this->coupon_model->where($_check_map)->count();
            if ($_cnt >= 5) {
                $this->error('已超过5款推荐,请先取消推荐几款');
            }
        }
        if ($coupon_id > 0) {
            $_map['id'] = $coupon_id;
            //伪删除信息
            $rs = $this->coupon_model->where($_map)->setField('isrcmd', $_recommend);
            if (false !== $rs) {
                $this->success("修改成功", U("Coupon/index"));
                exit;
            } else {
                $this->error("修改失败");
                exit;
            }
        }
        $this->error("参数错误");
    }

    /**
     *
     * 上线代金卷
     * 下线代金卷
     */
    public function setStatus() {
        $coupon_id = I('id/d', 0);
        $_status = I('status/d');
        if (empty($_status)) {
            $this->error("参数错误");
        }
        if ($coupon_id > 0) {
            $_map['id'] = $coupon_id;
            //伪删除信息
            $rs = $this->coupon_model->where($_map)->setField('status', $_status);
            if (false !== $rs) {
                $this->success("修改成功", U("Coupon/index"));
                exit;
            } else {
                $this->error("修改失败");
                exit;
            }
        }
        $this->error("参数错误");
    }

    /**
     * 添加代金卷
     */
    public function add() {
        $this->display();
    }

    /**
     * 添加代金卷处理函数
     */
    public function add_post() {
        if (IS_POST) {
            //获取数据
            $c_data['title'] = I('title/s', '');/* 代金卷标题 */
            $c_data['money'] = I('money/d', 0);/* 代金卷金额 */
            $c_data['total_num'] = I('total_num/d', 0); /* 代金卷发放数量 */
            $c_data['condition'] = I('condition/d', 0); /* 领取所需积分 */
            $c_data['content'] = I('content/s', ''); /* 使用说明 */
            $c_data['start_time'] = strtotime(I('starttime'));
            $c_data['end_time'] = strtotime(I('endtime'));
            $c_data['create_time'] = time();
            $c_data['send_start_time'] = time(); /* 默认开始发放时间从添加起开始 */
            $c_data['app_id'] = 0; /* 默认不限制游戏 */
            $c_data['status'] = I('status/d', 1); /* 默认不限制游戏 */
            if (empty($c_data['title']) || empty($c_data['money']) || empty($c_data['condition'])) {
                $this->error("请填写完数据后再提交");
            }
            $_rs = $this->coupon_model->add($c_data);
            if ($_rs) {
                $this->success("添加成功!", U("Coupon/index"));
                exit;
            } else {
                $this->error("添加失败");
            }
        }
        $this->error("参数错误");
    }

    public function edit() {
        $id = I("get.id/d", 0);
        if (empty($id)) {
            $this->error("参数错误");
        }
        $map['id'] = $id;
        $couponlist = $this->coupon_model->where($map)->find();
        $this->assign($couponlist);
        $this->display();
    }

    /**
     * 修改代金卷
     */
    public function edit_post() {
        $c_id = I('post.id/d', 0);
        if (empty($c_id)) {
            $this->error("参数错误");
        }
        //获取数据
        $c_data['id'] = $c_id;
        $c_data['title'] = I('title/s', '');/* 代金卷标题 */
//        $c_data['money'] = I('money/d', 0);/* 代金卷金额 */
        $c_data['total_num'] = I('total_num/d', 0); /* 代金卷发放数量 */
        $c_data['condition'] = I('condition/d', 0); /* 领取所需积分 */
        $c_data['content'] = I('content/s', ''); /* 使用说明 */
        $c_data['start_time'] = strtotime(I('starttime'));
        $c_data['end_time'] = strtotime(I('endtime'));
        $c_data['update_time'] = time();
        $c_data['status'] = I('status/d', 1); /* 默认未上线 */
        $_rs = $this->coupon_model->save($c_data);
        if (false !== $_rs) {
            $this->success("更新成功!", U("Conpon/index"));
        } else {
            $this->error("修改失败");
        }
    }
}