<?php
/**
 * Shangxun.php UTF-8
 * 商讯短信发送
 *
 * @date    : 2017年03月01日
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\sms;

use think\Log;

class Shangxun {
    protected static $template = "您的验证码是：#code#。请不要把验证码泄露给其他人。";
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Shangxun Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        // 商讯短信配置信息
        if (file_exists(CONF_PATH."extra/sms/shangxun.php")) {
            $sx_config = include CONF_PATH."extra/sms/shangxun.php";
        } else {
            return false;
        }
        if (empty($sx_config)) {
            return false;
        }
        $msg  = urlencode(self::content($sms_code));
        $name = $sx_config['SMS_ACC'];
        $pwd  = $sx_config['SMS_PWD'];
        $url = $sx_config['SMS_URL'];
        $ret = file_get_contents($url."?name=$name&pwd=$pwd&dst=$mobile&msg=$msg");
        $result = explode("&",$ret);
        $num = explode("=",$result[0]);
        if ($num[1] == 0) {
            $_rdata['code'] = '0';
            $_rdata['msg'] = "短信发送失败";
        } else {
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        }
        return $_rdata;
    }

    protected static function content($capcha){
        return str_replace("#code#", $capcha, self::auto_read(self::$template,"GBK"));
    }

    /**
     * 自动解析编码读入文件
     * @param string $str 字符串
     * @param string $charset 读取编码
     * @return string 返回读取内容
     */
    private static function auto_read($str, $charset='UTF-8') {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }
}