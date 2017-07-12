<?php
/**
 * Devicelog.php UTF-8
 * 设备渠道
 *
 * @date    : 2016年11月11日下午4:26:51
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午4:26:51
 */
namespace huosdk\log;

use think\Log;
use think\Db;

class Devicelog extends Huolog {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'log\Devicelog Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct() {
    }

    /*
     * 从device_agent中查询 agentgame
     */
    public function getAgentgame($device_id, $app_id) {
        if (empty($device_id) || empty($app_id)) {
            return '';
        }
        $_map['device_id'] = $device_id;
        $_agentgame = DB::name('agent_device')->where($_map)->value('agentgame');
        if (!empty($_agentgame)) {
            return $_agentgame;
        }
        return '';
    }
}