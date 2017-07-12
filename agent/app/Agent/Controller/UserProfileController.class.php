<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class UserProfileController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $data = M('agent_man')->where(array("agent_id" => $this->agid))->find();
        $this->assign("data", $data);
        $this->assign("bank_select", $this->_bank($data['bankname']));
        $this->display();
    }

    public function _bank($current) {
        $data = array(
            "请选择银行",
            "中国工商银行         ",
            "中国建设银行         ",
            "中国农业银行         ",
            "招商银行             ",
            "交通银行             ",
            "中国银行             ",
            "中国邮政储蓄银行     ",
            "中国光大银行         ",
            "兴业银行             ",
            "中信银行             ",
            "中国民生银行         ",
            "华夏银行             ",
            "青岛银行             ",
            "平安银行             ",
            "渤海银行             ",
            "浦发银行             ",
            "广发银行             ",
            "上海银行             "
        );
        $txt = '';
        foreach ($data as $key => $value) {
            if ($value == $current) {
                $selected = "selected='selected'";
            } else {
                $selected = "";
            }
            $txt .= "<option value='$value' $selected >$value</option>";
        }
        return $txt;
    }

    public function edit_post() {
        if (!isset($_SESSION['sms_code'])) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请先验证手机号"));
            exit;
        }
        if (I('bankname') == "请选择银行") {
            $this->ajaxReturn(array("error" => "1", "msg" => "请选择银行"));
            exit;
        }
        $exist = M('agent_man')->where(array("agent_id" => I('agent_id')))->find();
        if ($exist) {
            $data = $_POST;
            unset($data['agent_id']);
            M('agent_man')->where(array("agent_id" => I('agent_id')))->save($_POST);
        } else {
            M('agent_man')->add($_POST);
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "保存成功".json_encode($_POST)));
    }
}

