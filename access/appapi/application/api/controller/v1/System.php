<?php

/**
 * System.php UTF-8
 * 系统修复
 * @date: 2016年8月18日下午9:47:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\api\controller\v1;

use app\common\controller\Base;
use think\Db;
use think\Controller;

class System extends Base
{
    function _initialize() {
        parent::_initialize();
    }

    function repair(){
        $url = "https://www.huosdk.com/ios/index.html";
//         $url = "https://www.baidu.com";
        $this->redirect($url);
    }
}