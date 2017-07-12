<?php
/**
 * Agentgame.php UTF-8
 * 渠道游戏处理
 *
 * @date    : 2017/1/16 21:14
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\agent;

use think\Log;
use think\Db;

class Agentgame extends Agent {
    private $agent_id;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'agent\Agentgame Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $agentid   INT 代理ID
     * @param $agentgame string 代理游戏名称
     */
    public function __construct($ag_id = 0, $agentgame = '') {
        if (empty($agent_id)) {
            $this->setAgentidfromAg($agentgame);
        } else {
            $this->agent_id = $agent_id;
        }
    }

    /**
     * 设置agentid
     *
     * @param $agentid INT 代理ID
     */
    public function setAgentid($agentid) {
        $this->agent_id = $agentid;
    }

    /**
     * 通过agentgame设置agentid
     *
     * @param $agentid INT 代理ID
     */
    public function setAgentidfromAg($agentgame) {
        if (empty($agentgame) || 'default' == $agentgame) {
            $this->agent_id = 0;
            return;
        }
        $_map['agentgame'] = $agentgame;
        $_agent_id = DB::name('agent_game')->where($_map)->value('agent_id');
        if (empty($_agent_id)) {
            $this->agent_id = 0;
            return;
        }
        $this->agent_id = $_agent_id;
    }

    /**
     * 获取agentid
     */
    public function getAgentid() {
        return $this->agent_id;
    }
}