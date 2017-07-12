<?php
/**
 * News.php UTF-8
 * 新闻 消息接口
 *
 * @date    : 2016年12月10日上午12:05:28
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月10日上午12:05:28
 */
namespace app\api\controller\v7;

use app\common\controller\Baseapi;

class News extends Baseapi {
    function _initialize() {
        parent::_initialize();
    }

    /* 
     * 获取资讯列表（news/list）
     * http://doc.1tsdk.com/12?page_id=425
     */
    public function index() {
    }

    /*
     * WEB资讯详情页(news/webdetail/[newsid])
     * http://doc.1tsdk.com/12?page_id=427
     */
    public function webread() {
    }

    /* 
     * BBS资讯列表（bbs/news/list）
     * http://doc.1tsdk.com/12?page_id=452
     */
    public function bbsindex() {
    }

    /* 
     * 获取资讯详情(news/getdetail)
     * http://doc.1tsdk.com/12?page_id=426
     */
    public function read() {
    }
}