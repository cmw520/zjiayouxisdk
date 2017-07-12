<?php

/**
 * Share.php UTF-8
 * 获取分享信息
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

class Share extends Base
{
    function _initialize() {
        parent::_initialize();
    }

    function read(){
        $data['title'] = "畅玩乐园分享title";
        $data['url'] = "http://www.baidu.com";
        $data['sharetext'] = "分享内容";

        return hs_api_responce(200, '请求成功', $data);
    }
}