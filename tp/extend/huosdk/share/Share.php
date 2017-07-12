<?php
/**
 * Share.php UTF-8
 * 分享
 *
 * @date    : 2017/2/7 22:30
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\share;

use huosdk\common\Commonfunc;
use huosdk\game\Game;
use huosdk\game\Gamepack;
use think\Config;
use think\Db;
use think\Session;

class Share {
    /**
     * 玩家ID与游戏ID为为空时 表示分享app
     *
     * @param int $mem_id
     * @param int $game_id
     *
     * @return string
     */
    public function getGame($mem_id = 0, $game_id = 0) {
        $_app_id = $game_id;
        $_mem_id = $mem_id;
        if (empty($_mem_id)) {
            return false;
        }
//        $_url = Url::build('wap/Game/read', ['gameid' => $_app_id, 'agentid' => $_mem_agent_id]);
//        $_url = Config::get('domain.SDKSITE').$_url;
        if (in_array($_app_id, Config::get('config.HUOAPP'))) {
            //分享app, 则分享每个app的下载地址
            $_gp_class = new Gamepack($mem_id);
            $_url = $_gp_class->getMemAppurl($mem_id);
        } else {
            //分享游戏 则分享官方游戏
            $_g_class = new  Game();
            $_url = $_g_class->getDownlink($_app_id);
        }
        //生成分享信息

        $_title = Db::name('game_info')->where('app_id', $_app_id)->value('publicity');
        $_content = Db::name('game_info')->where('app_id', $_app_id)->value('description');
        $_share_id = $this->genLog($_mem_id, $_app_id, $_url, $_title, $_content);
        $_rdata['shareid'] = $_share_id;
        $_rdata['title'] = $_title;
        $_rdata['url'] = $_url;
        $_rdata['sharetext'] = $_content;

        return $_rdata;
    }

    /**
     * 生成分享ID
     *
     * @param int    $mem_id
     * @param int    $app_id
     * @param string $url
     * @param string $title
     * @param string $content
     *
     * @return bool|int|string
     */
    public function genLog($mem_id = 0, $app_id = 0, $url = '', $title = '', $content = '') {
        $_share_data['mem_id'] = $mem_id;
        $_share_data['app_id'] = $app_id;
        $_share_data['imei'] = Session::get('device_id', 'device');
        $_share_data['deviceinfo'] = Session::get('deviceinfo', 'device');
        $_share_data['userua'] = Session::get('userua', 'device');
        $_share_data['from'] = Session::get('from', 'device');
        $_share_data['create_time'] = time();
        $_share_data['title'] = $title;
        $_share_data['content'] = $content;
        $_share_data['url'] = $url;
        $_id = Db::name('share_log')->insertGetId($_share_data);
        if (false === $_id) {
            return false;
        }

        return $_id;
    }
}