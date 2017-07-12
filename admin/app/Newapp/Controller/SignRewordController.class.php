<?php
/*
 *  @time 2017-1-23 10:04:57
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class SignRewordController extends AdminbaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->assign("admin_module_name", "签到奖励管理");
        $this->model = M('sign_reward');
    }

    public function index() {
        $month = date("Ym");
        if (isset($_GET['year']) && ($_GET['year']) && isset($_GET['month']) && ($_GET['month'])) {
            $month = $_GET['year']."".$_GET['month'];
        }
        $this->assign("year_select", $this->getYearSelect());
        $this->assign("month_select", $this->getMonthSelect());
        $items = $this->getList($month);
        $this->assign("items", $items);
        $this->display();
    }

    public function getList($month) {
        $days = date('t', strtotime($month));
        $where = array("sr.date" => $month);
        $items = $this->model
            ->alias("sr")
            ->field("sr.*")
            ->where($where)
            ->order("sr.sign_days ASC")
            ->getField("sign_days,give_integral", true);
        $_default_map['sr.date'] = '0000-00';
        $_default_items = $this->model
            ->alias("sr")
            ->field("sr.*")
            ->where($_default_map)
            ->order("sr.sign_days ASC")
            ->getField("sign_days,give_integral", true);
        $result = array();
        for ($i = 1; $i <= $days; $i++) {
            if ($items[$i]) {
                $result[] = array("date"              => $month,
                                  "sign_days"         => $i,
                                  "give_integral"     => $items[$i],
                                  "give_integral_txt" => $items[$i]);
            } elseif ($_default_items[$i]) {
                $result[] = array("date"              => $month,
                                  "sign_days"         => $i,
                                  "give_integral"     => $_default_items[$i],
                                  "give_integral_txt" => $_default_items[$i]);
            } else {
                $result[] = array("date"              => $month,
                                  "sign_days"         => $i,
                                  "give_integral"     => '',
                                  "give_integral_txt" => '未设置');
            }
        }

        return $result;
    }

    public function getYearSelect() {
        $txt = "<select name='year'>";
        $current_year = date("Y");
        if (isset($_GET['year'])) {
            $current = $_GET['year'];
        }
        for ($i = 2016; $i <= 2050; $i++) {
            if ($i == $current_year) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $txt .= "<option value='$i' $selected >$i</option>";
        }
        $txt .= "</select>";

        return $txt;
    }

    public function getMonthSelect() {
        $txt = "<select name='month'>";
        $current = date("m");
        if (isset($_GET['month'])) {
            $current = $_GET['month'];
        }
        $data = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
        for ($i = 1; $i <= 12; $i++) {
            $v = $data[$i - 1];
            if ($v == $current) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $txt .= "<option value='$v' $selected >$v</option>";
        }
        $txt .= "</select>";

        return $txt;
    }

    public function edit() {
        $date = I('date');
        $sign_days = I('sign_days');
        $v = I('give_integral');
        if (!$v || !is_numeric($v)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "积分数量不正确"));
        }
        $exist = $this->model->where(array("date" => $date, "sign_days" => $sign_days))->find();
        if ($exist) {
            $this->model->where(array("date" => $date, "sign_days" => $sign_days))->setField("give_integral", $v);
        } else {
            $data = array(
                "date"          => $date,
                "sign_days"     => $sign_days,
                "give_integral" => $v
            );
            $this->model->add($data);
        }
        /* 设置默认签到积分 */
        $_default_map['date'] = '0000-00';
        $_default_map['sign_days'] = $sign_days;
        $this->model->where($_default_map)->setField("give_integral", $v);
        $this->ajaxReturn(array("error" => "0", "msg" => "编辑成功"));
    }
}