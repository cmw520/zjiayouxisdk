<?php
/**
 * Driver.php UTF-8
 * 支付处理
 *
 * @date    : 2016年11月16日下午3:13:29
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午3:13:29
 */
namespace huosdk\pay;

use think\Config;
use think\App;
use think\Log;

class Driver {
    /**
     * 初始化实例
     *
     * @access public
     *
     * @param array       $config 配置数组
     * @param bool|string $name   缓存连接标识 true 强制重新初始化
     *
     * @return \wallet\Gm \wallet\Ptb \wallet\Ptbgm
     */
    public static function init($payway) {
        $_class = false !== strpos($payway, '\\') ? $payway : '\\huosdk\\pay\\'.ucwords($payway);
        // 记录初始化信息
        App::$debug && Log::record('[ PAYDRIVER ] INIT '.$payway, 'info');
        return new $_class();
    }
}