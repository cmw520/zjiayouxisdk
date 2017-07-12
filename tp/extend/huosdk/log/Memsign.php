<?php
/**
 * Memsign.php UTF-8
 * 玩家签到
 *
 * @date    : 2017/2/6 17:05
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\log;
class Memsign extends Huolog {
    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct($table_name = '') {
        if (empty($table_name)) {
            $table_name = 'mem_sign_log';
        }
        parent::__construct($table_name);
    }

    /**
     * 插入签到数据
     *
     * @param int $mem_id
     * @param int $sign_days
     * @param int $give_integral
     * @param int $coupon_id
     * @param int $goods_id
     *
     * @return bool|int 错误返回false 否则返回签到ID
     *
     */
    public function sign($mem_id = 0, $sign_days = 0, $give_integral = 0, $coupon_id = 0, $goods_id = 0) {
        $_data['mem_id'] = $mem_id;
        $_data['sign_days'] = $sign_days;
        $_data['give_integral'] = $give_integral;
        $_data['coupon_id'] = $coupon_id;
        $_data['goods_id'] = $goods_id;
        $_data['create_time'] = time();
        $_id = parent::insertGetId($_data);
        if (!$_id) {
            return false;
        }

        return $_id;
    }
}