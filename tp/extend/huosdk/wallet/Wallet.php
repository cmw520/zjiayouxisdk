<?php
/**
 * Wallet.php UTF-8
 * 钱包 或 游戏币 处理
 *
 * @date    : 2016年11月16日下午3:13:29
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午3:13:29
 */
namespace huosdk\wallet;

use think\Config;
use think\App;
use think\Log;

class Wallet {
    protected static $instance = [];
    protected static $rate     = 0;
    /**
     * 操作句柄
     *
     * @var object
     * @access protected
     */
    protected static $handler;

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
    public static function init(array $config = [], $name = false) {
        $_config = $config;
        if (empty($config)) {
            $_config = Config::get('config.wallet');
        }
        /*
         * 默认支付类型为游戏币
         */
        if (empty($_config['type'])) {
            $_config['type'] = 'gm';
        }
        /*
         * 默认比例为1:1
         */
        if (empty($_config['rate'])) {
            $_config['rate'] = '1';
        }
        self::$rate = $_config['rate'];
        if (true === $name || !isset(self::$instance[$name])) {
            $_class = false !== strpos($_config['type'], '\\')
                ? $_config['type']
                : '\\huosdk\\wallet\\'.ucwords(
                    $_config['type']
                );
            // 记录初始化信息
            App::$debug && Log::record('[ WALLET ] INIT '.$_config['type'], 'info');
            if (true === $name) {
                return new $_class($_config['rate']);
            } else {
                self::$instance[$name] = new $_class($_config['rate']);
            }
        }
        self::$handler = self::$instance[$name];
        return self::$handler;
    }

    /**
     * 切换币类型
     *
     * @access public
     *
     * @param string $name 缓存币名称
     *
     * @return \wallet\Gm \wallet\Ptb \wallet\Ptbgm
     */
    public static function change($name) {
        self::init(array(), $name);
        return self::$handler;
    }

    /**
     * 游戏消费
     *
     * @access public
     *
     * @param array $paydata 充值数据
     * @param int   $appid   游戏ID
     *
     * @return false|int
     */
    public static function pay(array $paydata, $appid = 0) {
        self::init();
        return self::$handler->pay($paydata);
    }

    /**
     * SDK充值返利
     *
     * @access public
     *
     * @param array $paydata 充值数据
     * @param int   $appid   游戏ID
     *
     * @return false|int
     */
    public static function rebate(array $paydata, $appid = 0) {
        self::init();
        if (empty($appid)) {
            $appid = isset($paydata['app_id']) ? $paydata['app_id'] : 0;
        }
        return self::$handler->rebate($paydata, $appid);
    }

    /**
     * 获取币余额
     *
     * @access public
     *
     * @param INT $mem_id 玩家ID
     * @param INT $appid  游戏ID
     *
     * @return double 余额
     */
    public static function getRemain($mem_id, $appid = 0) {
        self::init();
        return self::$handler->getRemain($mem_id, $appid);
    }

    /**
     * 获取人民币与平台币比例
     *
     * @return 比例
     */
    public static function getRate() {
        self::init();
        return self::$rate;
    }

    public static function setRemain() {
        self::init();
        return self::$rate;
    }
}