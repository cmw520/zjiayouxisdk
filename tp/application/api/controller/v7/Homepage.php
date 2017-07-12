<?php
/**
 * Homepage.php UTF-8
 * app首页
 *
 * @date    : 2017/1/21 19:33
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace app\api\controller\v7;

use app\common\controller\Baseapi;
use huosdk\game\Gamelist;
use huosdk\slide\Slide;

class Homepage extends Baseapi {
    function _initialize() {
        parent::_initialize();
    }

    /**
     * @return $this
     */
    public function index() {
        /* 获取广告图 广告文字 */
        $_slide_tyle = 'home';
        $_slide_class = new Slide($_slide_tyle);
        $_rdata = $_slide_class->getList();
        $_texthome = $_slide_class->getList('texthome');
        if (!empty($_texthome)) {
            $_rdata = array_merge($_rdata, $_texthome);
        }
        /* 来源信息 1-WEB、2-WAP、3-Android、4-IOS、5-WP */
        $_from = $this->request->param('from');
        if (3 == $_from) {
            $_map['g.classify'] = [
                ['=', 3],
                ['between', '300,399'],
                'or'
            ];
        } elseif (4 == $_from) {
            $_map['g.classify'] = 4;
        }
        $_map['g.is_app'] = 2; /* app中上线的游戏 */
        $_map['g.is_delete'] = 2; /* 伪删除游戏不显示 */
        $_page = 1;
        $_offset = 5;
        $_gl_class = new Gamelist();
        /* 新游首发 */
        $_new_map = $_map;
        $_new_map['g.is_new'] = 2;
        $_rdata['newrmd'] = $_gl_class->gameList($_new_map, $_page, $_offset);
        /* 新游推荐 */
        $_newrmd_map = $_map;
        $_newrmd_map['g.is_new'] = 2;
        $_rdata['newgame'] = $_gl_class->remdList($_newrmd_map, $_page, $_offset);
        /* 测试表 */
        $_test_map = $_map;
        unset($_test_map['g.is_app']);
        $_rdata['testgame'] = $_gl_class->testList($_test_map, $_page, $_offset);
        /* 新服表 */
        $_rdata['newserver'] = $_gl_class->serverList($_map, $_page, $_offset);
        /* 手游风向标 */
        $_hot_map = $_map;
        $_hot_map['is_hot'] = 2;
        $_rdata['hotgame'] = $_gl_class->gameList($_hot_map, $_page, $_offset);
        /*猜你喜欢 */
        $_offset = 20;
        $_rdata['likegame'] = $_gl_class->gameList($_map, $_page, $_offset, 1);

        return hs_api_responce(200, '请求成功', $_rdata);
    }
}