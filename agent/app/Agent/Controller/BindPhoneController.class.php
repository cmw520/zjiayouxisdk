<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class BindPhoneController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        if (!$this->user_info['mobile']) {
            redirect(U('Agent/BindPhone/prev_none'));
        }
        $this->display();
    }

    public function prev_none() {
//        $hs_account_obj=new \Huosdk\Account();
//        echo $hs_account_obj->agentRoldId." ". $hs_account_obj->subAgentRoldId;
        if ($this->user_info['mobile']) {
            redirect(U('Agent/BindPhone/index'));
        }
        $this->display();
    }

    public function sendPhoneCode() {
        $phone = I('phone');
        if (empty($phone)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请填写手机号"));
            exit;
        }
        $hs_account_obj = new \Huosdk\Account();
        $inuse = $hs_account_obj->AgentPhoneInUse($phone);
        if ($inuse) {
            $this->ajaxReturn(array("error" => "1", "msg" => "此号码已经被注册，请使用其他手机号"));
            exit;
        }
        // 获取短信发送配置信息
        if (file_exists(SITE_PATH."conf/sms/setting.php")) {
            $setconfig = include SITE_PATH."conf/sms/setting.php";
            $i = 1;
            foreach ($setconfig as $k => $v) {
                if ($v > 0) {
                    $sendtype = $i;
                    break;
                }
                $i += 1;
            }
        } else {
            $sendtype = 1;
        }
        if ($sendtype == 1) {
            $result = sendMsg_alidayu($phone);
        } else if ($sendtype == 2) {
            $result = sendMsg_ytx($phone);
        } else if ($sendtype == 3) {
            $result = sendMsg_shangxun($phone);
        } else if ($sendtype == 4) {
            $result = sendMsg_juhe($phone);
        } else if ($sendtype == 5) {
            $result = sendMsg_chuanglan($phone);
        } else if ($sendtype == 6) {
            $result = sendMsg_qixintong($phone);
        }else if ($sendtype == 7) {
            $result = sendMsg_aliyun($phone);
        } else {
            //没设置则为阿里大鱼
            $result = sendMsg_alidayu($phone);
        }
        if ($result['status'] == 1) {
            $this->ajaxReturn(array("error" => "0", "msg" => "验证码发送成功，请尽快输入"));
            exit;
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => $result['msg']));
            exit;
        }
    }
}

