<?php
/**
 * Alipay.php UTF-8
 * 支付宝对外函数
 *
 * @date    : 2016年11月18日下午4:25:29
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月18日下午4:25:29
 */
namespace app\pay\controller;

use app\common\controller\Base;
use think\Log;

class Alipay extends Base {
    function _initialize() {
        parent::_initialize();
    }

    public function notifyurl() {
        $_ali_class = new \huosdk\pay\Alipay();
        $_ali_class->notifyUrl();
    }

    public function returnurl() {
    }
}