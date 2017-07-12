<?php
/**
 * Chuanglan.php UTF-8
 * 创蓝短信发送
 *
 * @date    : 2016年11月14日下午1:55:34
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2017年1月4日
 */
namespace huosdk\sms;

use think\Log;

class Chuanglan {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Chuanglan Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        include EXTEND_PATH."msg/ChuanglanSmsApi.class.php";
        $_c = new \ChuanglanSmsApi();
        $result = $_c->sendSMS($mobile, $sms_code, true);
        $result = $_c->execResult($result);
        if (0 == $result[1]) {
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        } else {
            $_rdata['code'] = 0;
            $_rdata['msg'] = "短信发送失败";
        }
        return $_rdata;
    }
}