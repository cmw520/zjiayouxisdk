<?php
/**
 * ApiController.class.php UTF-8
 * 发送短信
 *
 * @date    : 2016年7月21日下午8:22:55
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class ApiController extends AgentPublicController {
    function _initialize() {
        parent::_initialize();
    }

    public function logout() {
        $redirect_url = U("Front/Index/index");
        end_session();
        redirect($redirect_url);
    }

    public function sendPhoneCode() {
        $phone = I('phone');
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
        } else if ($sendtype == 7) {
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

    public function VerifyPhoneCode() {
        $PhoneVerifyCode = I('PhoneVerifyCode');
        if (empty($PhoneVerifyCode)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "验证码不正确"));
            exit;
        }
        if ($PhoneVerifyCode != $_SESSION['sms_code']) {
            $_SESSION['phoneVerifyCodeMatch'] = false;
            $this->ajaxReturn(array("error" => "1", "msg" => "验证码不正确"));
            exit;
        }
        $_SESSION['phoneVerifyCodeMatch'] = true;
        $this->ajaxReturn(array("error" => "0", "msg" => "验证成功"));
    }

    public function changePhone() {
        if (!isset($_SESSION['phoneVerifyCodeMatch']) || $_SESSION['phoneVerifyCodeMatch'] == false) {
            $this->ajaxReturn(array("error" => "1", "msg" => "必须先验证手机号才能修改"));
            exit;
        }
        $id = I('id');
        $phone = I('phone');
        $hs_validate_obj = new \Huosdk\Validate();
        $phone_ok = $hs_validate_obj->phone($phone);
        if (!$phone_ok) {
            $this->ajaxReturn(array("error" => "1", "msg" => "手机号格式不正确"));
            exit;
        }
        M('users')->where(array("id" => $id))->setField("mobile", $phone);
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }
}