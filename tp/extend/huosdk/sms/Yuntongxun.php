<?php
/**
 * Yuntongxun.php UTF-8
 * 云通讯短信发送
 *
 * @date    : 2017年02月22日下午1:55:34
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\sms;

use think\Log;

class Yuntongxun {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Yuntongxun Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        include EXTEND_PATH."yuntongxun/Rest.class.php";
        // 获取容联云配置信息
        if (file_exists(CONF_PATH."extra/sms/yuntongxun.php")) {
            $ytx_config = include CONF_PATH."extra/sms/yuntongxun.php";
        } else {
            return false;
        }
        if (empty($ytx_config)) {
            return false;
        }
        // 请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';
        // 请求端口
        $serverPort = '8883';
        // REST版本号
        $softVersion = '2013-12-26';
        // 主帐号
        $accountSid = $ytx_config['RONGLIAN_ACCOUNT_SID'];
        // 主帐号Token
        $accountToken = $ytx_config['RONGLIAN_ACCOUNT_TOKEN'];
        // 应用Id
        $appId = $ytx_config['RONGLIAN_APPID'];
        // 模板Id
        $templetId = $ytx_config['RONGLIAN_TEMPLATE_ID'];
        $rest = new \Rest($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $result = $rest->sendTemplateSMS(
            $mobile, array(
            $sms_code,
            5
        ), $templetId
        );
        if ($result == null) {
            $_rdata['code'] = '0';
            $_rdata['msg'] = "短信发送失败";
        }
        if ($result->statusCode != 0) {
            $_rdata['code'] = '0';
            $_rdata['msg'] = "短信发送失败";
        } else {
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        }
        return $_rdata;
    }
}