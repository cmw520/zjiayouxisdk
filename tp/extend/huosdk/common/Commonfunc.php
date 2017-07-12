<?php
namespace huosdk\common;

use think\Config;

/**
 * Commonfunc.php UTF-8
 * 公共函数
 *
 * @date    : 2016年11月9日下午11:29:44
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月9日下午11:29:44
 */
class Commonfunc {
    //生成订单号
    public static function setOrderid($mem_id, $agent_id = 1) {
        list($usec, $sec) = explode(" ", microtime());
        // 取微秒前3位+再两位随机数+玩家ID后四位+渠道ID后四位
        $orderid = $sec.substr($usec, 2, 3).rand(10, 99).sprintf("%04d", $mem_id % 10000).sprintf(
                "%04d", $agent_id % 10000
            );

        return $orderid;
    }

    //生成用户支付token
    public static function setPaytoken($order_id) {
        $time = time();
        $pay_token = md5(md5($order_id).$time);

        return $pay_token;
    }

    //参数排序
    public static function argSort(array $para) {
        ksort($para);
        reset($para);

        return $para;
    }

    public static function createLinkstring(array $para) {
        $arg = "";
        while (list($key, $val) = each($para)) {
            $arg .= $key."=".urlencode($val)."&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 判断是否为app
     *
     * @param $app_id
     *
     * @return bool 是app返回true, 不是app返回fasle
     */
    public static function isApp($app_id) {
        if (!in_array($app_id, Config::get('config.HUOAPP'))) {
            return false;
        }

        return true;
    }

    /**
     * 获取安卓app id
     *
     * @param string $from android 或 ios
     *
     * @return bool|string 返回app_id 错误则返回false
     */
    public static function getAndAppid($from = 'and') {
        $_app_arr = Config::get('config.HUOAPP');
        if ('and' == $from) {
            $_app_id = $_app_arr['APP_APPID'];
        } else {
            $_app_id = $_app_arr['IOS_APP_APPID'];
        }
        if (empty($_app_id)) {
            return false;
        }

        return $_app_id;
    }
}