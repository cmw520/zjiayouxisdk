<?php
namespace huosdk\game;

use think\Log;
use think\Config;
use think\Db;

class Gameserver {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'game\Gameserver Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function getSeverlist($app_id = 0) {
        $_map['is_delete'] = 2;
        $_game_field = [
            'app_id' => 'gameid'
        ];
        if (!empty($app_id)) {
            $_map['app_id'] = $app_id;
            $_game_field = array();

        }
        $_own_field = [
            "id"       => 'serid',
            "ser_name" => 'sername',
            'ser_desc' => 'serdesc',
            "status"     => 'status',
            'start_time' => 'starttime'
        ];
        $_field = array_merge($_game_field,$_own_field);

        $_serlist = Db::name('game_server')->field($_field)->where($_map)->select();
        return $_serlist;
    }
}