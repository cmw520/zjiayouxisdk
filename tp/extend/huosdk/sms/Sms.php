<?php
/**
 * Sms.php UTF-8
 * 短信
 *
 * @date    : 2016年11月11日下午10:40:54
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午10:40:54
 */
namespace huosdk\sms;

use think\Log;
use think\Session;

class Sms {
    private $sms_config;
    private $expaire_diff;

    public function __construct($expaire_diff = 120) {
        if (file_exists(CONF_PATH."extra/sms/setting.php")) {
            $_config = include CONF_PATH."extra/sms/setting.php";
        } else {
            $_config = array();
        }
        foreach ($_config as $_key => $_val) {
            if ($_val > 0) {
                $this->sms_config[$_key] = $_val;
            }
        }
        $this->expaire_diff = $expaire_diff;
    }

    /**
     * 自定义错误处理
     *
     * @param string $msg 输出的信息
     * @param string $level
     */
    private function _error($msg = '', $level = 'error') {
        $_info = 'sms\Alidayu Error:'.$msg;
        Log::record($_info, $level);
    }

    /**
     * 检查手机号码正确性
     *
     * @param $mobile string 手机号
     *
     * @return bool|手机号返回
     */
    public function checkMoblie($mobile) {
        $checkExpressions = "/^[1][34578][0-9]{9}$/";
        if (false == preg_match($checkExpressions, $mobile)) {
            return false;
        }

        return true;
    }

    /**
     * 发送短信验证码
     *
     * @param $mobile   string 手机号
     * @param $type     INT 手机号 1注册 2登陆 3修改密码 4信息变更
     * @param $sms_code string 验证码
     *
     * @return 返回 code 与 msg
     */
    public function send($mobile, $type, $sms_code = '') {
        /* 检查手机号格式是否正确 */
        $_rs = $this->checkMoblie($mobile);
        if (false == $_rs) {
            $_rdata['code'] = '413';
            $_rdata['msg'] = '手机号格式不正确';

            return $_rdata;
        }
        $_rs = $this->isSend($mobile);
        if ($_rs) {
            $_rdata['code'] = '416';
            $_rdata['msg'] = '验证码已发送,请稍后再试';

            return $_rdata;
        }
        $_sms_code = $sms_code;
        if (empty($_sms_code)) {
            $_sms_code = rand(1000, 9999);
        }
        $_rdata = $this->smsSelect($mobile, $type, $_sms_code);
        if (200 == $_rdata['code']) {
            $_expairte_time = time() + $this->expaire_diff;
            Session::set('mobile', $mobile, 'sms');
            Session::set('smstype', $type, 'sms');
            Session::set('smscode', $_sms_code, 'sms');
            Session::set('expaire_time', $_expairte_time, 'sms');
        }

        return $_rdata;
    }

    /**
     * 判断是否已发送过验证码
     *
     * @param $mobile string 手机号
     *
     * @return bool 已发送返回true 未发送返回false
     */
    private function isSend($mobile) {
        $_session_mobile = Session::get('mobile', 'sms');
        $_session_extime = Session::get('expaire_time', 'sms');
        if ($mobile == $_session_mobile && time() < $_session_extime) {
            return true;
        }

        return false;
    }

    /**
     * 短信服务商选取
     *
     * @param $mobile   string 手机号
     * @param $type     INT 手机号 1注册 2登陆 3修改密码 4信息变更
     * @param $sms_code string 验证码
     *
     * @return bool 返回 code 与 msg
     */
    private function smsSelect($mobile, $type, $sms_code) {
        $_data = false;
        foreach ($this->sms_config as $_key => $_val) {
            $_class = "\\huosdk\\sms\\".ucwords($_key);
            if (!class_exists($_class)) {
                $this->_error('短信不存在'.$_class);
                continue;
            }
            $_sms_class = new $_class();
            $_data = $_sms_class->send($mobile, $type, $sms_code);
            if (1 == $_data['code']) {
                break;
            }
        }
        $_rdata = $_data;
        if (false == $_data || empty($_data)) {
            $_rdata['code'] = 1000;
            $_rdata['msg'] = '服务器内部错误';
        }

        return $_rdata;
    }
}