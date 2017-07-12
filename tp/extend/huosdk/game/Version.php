<?php
/**
 * Version.php UTF-8
 * 游戏版本处理
 *
 * @date    : 2016年11月11日下午6:04:09
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午6:04:09
 */
namespace huosdk\game;

use think\Log;
use think\Db;

class Version {
    private $app_id;
    private $client_id;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'game\Version Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $rsa_pri_path string rsa私钥地址
     */
    public function __construct($app_id = 0, $client_id = 0) {
        if (!empty($app_id)) {
            $this->app_id = $app_id;
        }
        if (!empty($client_id)) {
            $this->client_id = $client_id;
        }
    }

    /**
     * 获取最新版本信息
     *
     * @param $app_id int 游戏ID
     *
     * @return 最新游戏版本信息
     */
    public function getLastinfo($app_id = 0) {
        $_app_id = $app_id;
        if (empty($_app_id)) {
            $_app_id = $this->app_id;
        }
        if (empty($_app_id)) {
            return false;
        }
        /* 获取最新版本信息 */
        $_map['app_id'] = $_app_id;
        $_map['status'] = 2;
        $_gv_info = Db::name('game_version')->where($_map)->order('id')->limit(1)->find();
        if (empty($_gv_info)) {
            return false;
        }
        return $_gv_info;
    }
}