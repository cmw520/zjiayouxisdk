<?php

namespace app\common\controller;

use think\Db;
use think\Loader;
use think\Controller;
use think\Request;
use think\Log;
use think\Config;

class Base7881 extends OutBase
{
    protected function _initialize() {
        //记录请求记录
        $postdata =   file_get_contents("php://input");
        Log::write($postdata,'error');

        $huosdk_config['conf_7881'] = include SITE_PATH . 'conf/store/7881/config.php';
        Config::set($huosdk_config);
    }
}