<?php
/**
 * Gamepack.php UTF-8
 * 游戏分包
 *
 * @date    : 2017/2/21 15:49
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\game;

use huosdk\common\Commonfunc;
use huosdk\player\MemAgent;
use huosdk\request\Request;
use think\Config;
use think\Db;

class Gamepack {
    /**
     * 分包
     *
     * @param $app_id
     * @param $agent_id
     *
     * @return array
     */
    function pack($app_id, $agent_id) {
        if (empty($agent_id)) {
            return array(
                "error" => "1",
                "msg"   => "参数错误"
            );
        }
        $_app_id = $app_id;
        $_ver_id = 0;
        $_is_app = Commonfunc::isApp($_app_id);
        if (empty($_app_id) || $_is_app) {
            $_app_id = Commonfunc::getAndAppid();
            $_gv_map['status'] = 2;
            $_gv_map['app_id'] = $_app_id;
            $_ver_id = Db::name('game_version')->where($_gv_map)->order('id desc')->value('id');
        }
        $_g_map['id'] = $_app_id;
        $_game_info = Db::name('game')->where($_g_map)->find();
        $_map['app_id'] = $_app_id;
        $_map['agent_id'] = $agent_id;
        $_init = $_game_info['initial'];
        if (!empty($_ver_id)) {
            $_init = $_init.DS.$_ver_id;
        }
        $_ag = Db::name('agent_game')->where($_map)->value('agentgame');
        if (empty($_ag)) {
            //插入信息
            $_ag_data['agent_id'] = $agent_id;
            $_ag_data['app_id'] = $_app_id;
            $_ag_data['agentgame'] = $_game_info['initial'].'_'.$agent_id;
            $_ag_data['create_time'] = time();
            $_ag_id = Db::name('agent_game')->insertGetId($_ag_data);
            if (false === $_ag_id) {
                return array(
                    "error" => "1",
                    "msg"   => "内部错误"
                );
            }
            $_ag = $_ag_data['agentgame'];
        }
        $opt = md5(md5($_init.$_ag).'resub');
        $initial = base64_encode($_init);
        $agentgame = base64_encode($_ag);
        $opt = base64_encode($opt);
        $data_string = array(
            'p' => $initial,
            'a' => $agentgame,
            'o' => $opt
        );
        $_data_string = json_encode($data_string);
        $_url = DOWNIP."/sub.php";
        $cnt = 0;
        $return_content = -100;
        while (1) {
            $return_content = base64_decode(Request::httpJsonpost($_url, $_data_string));
            if (0 < $return_content || 3 == $cnt) {
                break;
            }
            $cnt++;
        }
        if (0 <= $return_content) {
            $_update_data['url'] = $_init.'/'.$_ag.".apk";;
            $_update_data['update_time'] = time();
            $_update_data['status'] = 2;
            $_rs = Db::name('agent_game')->where($_map)->update($_update_data);
            if (false !== $_rs) {
                return array(
                    "error" => "1",
                    "msg"   => "分包记录添加失败",
                    'url'   => Config::get('domain.DOWNSITE').DS.$_update_data['url']
                );
            }

            return array(
                "error" => "0",
                "msg"   => "分包成功"
            );
        } else if (-6 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "拒绝访问"
            );
        } else if (-4 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "验证错误"
            );
        } else if (-3 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "请求数据为空"
            );
        } else if (-2 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "分包失败"
            );
        } else if (-1 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "无法创建文件,打包失败."
            );
        } else if (-5 == $return_content) {
            return array(
                "error" => "1",
                "msg"   => "游戏原包不存在"
            );
        } else {
            return array(
                "error" => "1",
                "msg"   => "请求数据失败"
            );
        }
    }

    /**
     * 获取app下载地址
     *
     * @param int $app_id
     * @param int $agent_id
     *
     * @return array|string
     */
    public function getAppurl($app_id = 0, $agent_id = 0) {
        if (empty($app_id)) {
            return '';
        }
        $_g_class = new Game();
        $_downurl = $_g_class->getAgDownlink($app_id, $agent_id);
        if (!empty($_downurl)) {
            return $_downurl;
        } else if (empty($_downurl) && empty($agent_id)) {
            return $_g_class->getDownlink($app_id);
        }
        $_rdata = $this->pack($app_id, $agent_id);
        if (empty($_rdata['url'])) {
            return '';
        }

        return $_rdata['url'];
    }

    /**
     * 玩家获取自己app的下载地址
     *
     * @param int $mem_id
     *
     * @return array|string
     */
    public function getMemAppurl($mem_id = 0) {
        $_m_class = new MemAgent($mem_id);
        $_mem_agent_id = $_m_class->getAgentid();
        if (empty($_mem_agent_id)) {
            return '';
        }
        $_app_arr = Config::get('config.HUOAPP');
        $_app_id = $_app_arr['APP_APPID'];

        return $this->getAppurl($_app_id, $_mem_agent_id);
    }
}