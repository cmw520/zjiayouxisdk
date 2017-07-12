<?php
namespace huosdk\agent;

use think\Log;
use think\Db;

class Agent {
    private $agent_id;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'agent\Agent Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $agentid   INT 代理ID
     * @param $agentgame string 代理游戏名称
     */
    public function __construct($agent_id = 0, $agentgame = '') {
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
    function setAgentidfromAgid($downid = 0) {
        if (0 == $downid) {
            return false;
        }
        $_map['id'] = $downid;
        $_ag_info = \think\Db::name('agent_game')->cache('agid_'.$downid)->where($_map)->find();
        if (empty($_ag_info)) {
            return false;
        }
        return $_ag_info;
    }
}