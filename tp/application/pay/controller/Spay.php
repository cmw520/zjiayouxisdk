<?php
/**
 * Spay.php UTF-8
 * 微付通对外函数
 *
 * @date    : 2016年11月18日下午4:25:52
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月18日下午4:25:52
 */
namespace app\pay\controller;

use app\common\controller\Base;

class Spay extends Base {
    function _initialize() {
        parent::_initialize();
    }

    public function notifyurl() {
        $_class = new \huosdk\pay\Spay();
        $_class->notifyUrl();
    }


    public function returnurl() {
    }
}