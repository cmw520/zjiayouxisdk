<?php
/**
 * Alidayu.php UTF-8
 * 阿里大鱼短信发送
 *
 * @date    : 2016年11月14日下午1:55:34
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月14日下午1:55:34
 */
namespace huosdk\sms;

use think\Log;

class Alidayu {
    /**
     * 自定义错误处理
     *
     * @param string $msg 输出的文件
     * @param string $level
     *
     * @internal param  $msg
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Alidayu Error:'.$msg;
        Log::record($_info, $level);
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        include EXTEND_PATH."taobao/TopSdk.php";
        include EXTEND_PATH."taobao/top/TopClient.php";
        include EXTEND_PATH."taobao/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
        // 获取阿里大鱼配置信息
        if (file_exists(CONF_PATH."extra/sms/alidayu.php")) {
            $_config = include CONF_PATH."extra/sms/alidayu.php";
        } else {
            $_config = array();
        }
        if (empty($_config)) {
            $this->_error("配置信息错误");

            return false;
        }
        $_content = array(
            "code"    => ''.$sms_code,
            "product" => ''.$_config['PRODUCT']
        );
        $_smstemp = 'SMSTEMPAUTH';
        if (1 == $type) {
            $_smstemp = 'SMSTEMPREG';
        }
        $_c = new \TopClient();
        $_c->appkey = $_config['APPKEY'];
        $_c->secretKey = $_config['APPSECRET'];
        $_req = new \AlibabaAliqinFcSmsNumSendRequest();
        $_req->setExtend($_config['SETEXTEND']);
        $_req->setSmsType($_config['SMSTYPE']);
        $_req->setSmsFreeSignName($_config['SMSFREESIGNNAME']);
        $_req->setSmsParam(json_encode($_content));
        $_req->setRecNum($mobile);
        $_req->setSmsTemplateCode($_config[$_smstemp]);
        $_resp = $_c->execute($_req);
        $_resp = (array)$_resp;
        if (!empty($_resp['result'])) {
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        } else {
            $this->_error("手机号:".$mobile."|| 类型:".$type."|| 阿里大鱼发送失败".json_encode($_resp));
//            $_rdata['code'] = (int)$_resp['code'];
            $_rdata['code'] = 400;
            $_rdata['msg'] = "短信发送失败";
        }

        return $_rdata;
    }
}