<?php
/**
 * Qixintong.php UTF-8
 * 企信通短信发送
 *
 * @date    : 2017年03月01日
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\sms;

use think\Log;

class Qixintong {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Qixintong Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        // 商讯短信配置信息
        if (file_exists(CONF_PATH."extra/sms/qixintong.php")) {
            $qxtconfig = include CONF_PATH."extra/sms/qixintong.php";
        } else {
            return false;
        }
        if (empty($qxtconfig)) {
            return false;
        }

        $usr=$qxtconfig['USR'];  //用户名
        $pw=$qxtconfig['PW'];  //密码
        $tem=$qxtconfig['TEM'];  //模板类型
        $mob=$mobile;  //手机号,只发一个号码：13800000001。发多个号码：13800000001,13800000002,...N 。使用半角逗号分隔。

        $mt="验证码".$sms_code."，您正在注册哔哔游戏，请妥善保管验证码";  //要发送的短信内容，特别注意：签名必须设置，网页验证码应用需要加添加【图形识别码】。

        $mt = urlencode($mt);//执行URLencode编码  ，$content = urldecode($content);解码

        $sendstring = "usr=".$usr."&pw=".$pw."&mob=".$mob."&mt=".$mt;
        $url = $qxtconfig['URL'];
        $sendline = $url."?".$sendstring;
        $result = @file_get_contents($sendline);
        if ($result=="00" || $result == "01") {
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        } else {
            $_rdata['code'] = '0';
            $_rdata['msg'] = "短信发送失败";
        }
        return $_rdata;
    }
}