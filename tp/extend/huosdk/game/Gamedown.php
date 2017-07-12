<?php
/**
 * Gamedown.php UTF-8
 * 游戏下载地址
 *
 * @date    : 2017/1/16 21:08
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\game;

use think\Config;
use think\Db;
use think\Log;

class Gamedown {
    private $app_id;
    private $client_id;

    /**
     * 自定义错误处理
     *
     * @param 输出的文件  $msg
     * @param string $level
     *
     * @internal param 输出的文件 $msg
     */
    private function _error($msg, $level = 'error') {
        $_info = 'game\Gamedown Error:'.$msg;
        Log::record($_info, $level);
    }

    public function down($rq_data, $downid = 0, $agent_id = 0, $app_id = 0) {
        $_downdata['app_id'] = $app_id;
        if (empty($downid) && empty($agent_id)) {
            $_downdata['agent_id'] = 0;
            $_downdata['agentname'] = 'default';
        } else {
            if (!empty($downid)) {
                $_map['id'] = $downid;
            } else {
                $_map['agent_id'] = $agent_id;
                $_map['app_id'] = $app_id;
            }
            /* 查询agentid */
            $_agent_info = Db::name('agent_game')->cache(60)->where($_map)->find();
            if (false == $_agent_info || $_agent_info['app_id'] != $app_id) {
                $_downdata['agent_id'] = 0;
                $_downdata['agentname'] = 'default';
            } else {
                $_downdata['app_id'] = $app_id;
                $_downdata['agent_id'] = $_agent_info['agent_id'];
                $_downdata['agentname'] = $_agent_info['agentgame'];
                $_downurl = $_agent_info['url'];
            }
        }
        if (empty($_downurl)) {
            $_gv_map['app_id'] = $app_id;
            $_gv_map['status'] = 2;
            $_gv_info = Db::name('game_version')->where($_gv_map)->order('id desc')->limit(1)->select();
            if (!empty($_gv_info)) {
                $_downurl = $_gv_info[0]['packageurl'];
            }
        }
        if (empty($_downurl)) {
            return null;
        } else {
            if (strpos($_downurl, 'sdkgame')) {
                $_downurl = str_replace($_downurl, 'sdkgame', '');
            }
            $_downurl = Config::get('domain.DOWNSITE').DS.$_downurl;
        }
        // 默认下载app
        $_downdata['id'] = get_val($rq_data, 'mem_id', 0);
        $_downdata['mem_id'] = get_val($rq_data, 'mem_id', 0);
        $_downdata['ver_id'] = get_val($rq_data, 'ver_id', 0);
        $_downdata['app_id'] = get_val($rq_data, 'app_id', 0);
        $_downdata['agent_id'] = get_val($rq_data, 'agent_id', 0);
        $_downdata['agentname'] = get_val($rq_data, 'agentname', '');
        $_downdata['openudid'] = get_val($rq_data, 'openudid', '');
        $_downdata['deviceid'] = get_val($rq_data, 'deviceid', '');
        $_downdata['idfa'] = get_val($rq_data, 'idfa', '');
        $_downdata['idfv'] = get_val($rq_data, 'idfv', '');
        $_downdata['mac'] = get_val($rq_data, 'mac', '');
        $_downdata['resolution'] = get_val($rq_data, 'resolution', '');
        $_downdata['devicetype'] = get_val($rq_data, 'devicetype', '');
        $_downdata['deviceinfo'] = get_val($rq_data, 'deviceinfo', '');
        $_downdata['network'] = get_val($rq_data, 'network', '');
        $_downdata['userua'] = get_val($rq_data, 'userua', '');
        $_downdata['create_time'] = get_val($rq_data, 'create_time', '');
        $_downdata['ip'] = get_val($rq_data, 'ip', '');
        $_downdata['local_ip'] = get_val($rq_data, 'local_ip', '');
        Db::name('game_downlog')->insert($_downdata);

        return $_downurl;
    }

    public function getDowncnt($game_id = 0) {
        if (empty($game_id)) {
            return 0;
        }
        $_map['app_id'] = $game_id;
        $_downcnt = Db::name('game_ext')->where($_map)->value('down_cnt');
        if (empty($_downcnt)) {
            return 0;
        }

        return $_downcnt;
    }
}