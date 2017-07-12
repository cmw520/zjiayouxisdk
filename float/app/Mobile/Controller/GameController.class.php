<?php
/**
 * UserController.class.php UTF-8
 * 用户中心控制
 *
 * @date    : 2016年7月8日下午2:54:46
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class GameController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
        $this->assign('title', '我玩过的游戏');
    }

    /**
     * 玩过的游戏
     */
    function myPlayed() {
        $myplayed = $this->playdata();
        $this->assign('myplayed', $myplayed);
        $this->display('myPlayed');
    }

    //获取玩过的游戏
    private function playdata() {
        $userid = get_current_userid();
        $field = "mg.app_id,g.name,g.type,g.icon icon";
        $map['mem_id'] = $userid;
        $myplayed = M('mem_game')
            ->alias('mg')
            ->field($field)
            ->join('left join '.C("DB_PREFIX").'game g on g.id = mg.app_id')
            ->where($map)
            ->order('mg.create_time desc')
            ->select();
        //获取类型
// 	    foreach($myplayed as $key => $val){
// 	        $myplayed[$key]['type'] = getGametype($val['type']);
// 	    }
        return $myplayed;
    }
}