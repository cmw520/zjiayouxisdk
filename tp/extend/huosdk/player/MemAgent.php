<?php
/**
 * MemAgent.php UTF-8
 * 玩家渠道处理
 *
 * @date    : 2017/2/7 22:43
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\player;

use think\Db;

class MemAgent extends Member {
    /**
     * Member constructor.
     *
     * @param int    $mem_id   玩家ID
     * @param string $username 玩家名称
     */
    public function __construct($mem_id = 0, $username = '') {
        parent::__construct($mem_id, $username);
    }

    /**
     * @return int|mixed 返回渠道ID
     */
    public function getAgentid() {
        $_mem_id = $this->getMemid();
        if (empty($_mem_id)) {
            return 0;
        }
        $_map['mem_id'] = $_mem_id;
        $_mem_agent_id = Db::name('users')->where($_map)->value('id');
        if (empty($_mem_agent_id)) {
            $_mem_agent_id = $this->genAgent();
        }
        if (empty($_mem_agent_id)) {
            return 0;
        }

        return $_mem_agent_id;
    }

    /**
     * 判断是否是官方玩家
     *
     * @return bool true 是官方玩家 false 非官方玩家
     *
     */
    public function isOfficeMem() {
        $_map['id'] = $this->getMemid();
        if (empty($_map['id'])) {
            return false;
        }
        $_parent_mem_id = Db::name('members')->where($_map)->value('parent_mem_id');
        if (empty($_parent_mem_id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 玩家生成渠道
     *
     * @return bool|int|mixed|string
     */
    public function genAgent() {
        $_mem_id = $this->getMemid();
        if (empty($_mem_id)) {
            return false;
        }
        $_user_map['mem_id'] = $_mem_id;
        $_id = Db::name('users')->where($_user_map)->value('id');
        if (!empty($_id)) {
            return $_id;
        }
        /* 生成渠道数据 */
        $_u_data['user_login'] = $_mem_id.'_'.uniqid().md5($_mem_id);
        $_u_data['user_pass'] = $_mem_id.'_'.uniqid().md5($_mem_id);
        $_u_data['user_nicename'] = $_u_data['user_login'];
        $_u_data['create_time'] = time();
        $_u_data['user_status'] = 3;
        $_u_data['mem_id'] = $_mem_id;
        $_id = Db::name('users')->insertGetId($_u_data);
        if (false !== $_id) {
            return 0;
        }

        return $_id;
    }

    /**
     * 玩家设定上级玩家
     *
     * @param int $agent_id 设定的渠道ID
     *
     * @return bool|mixed 返回上级玩家ID
     */
    public function setParent($agent_id = 0) {
        if (empty($agent_id)) {
            return true;
        }
        $_mem_id = $this->getMemid();
        if (empty($_mem_id)) {
            return false;
        }
        $_u_map['id'] = $agent_id;
        $_parent_mem_id = Db::name('users')->where($_u_map)->value('mem_id');
        /* 非玩家传播 */
        if (empty($_parent_mem_id)) {
            return true;
        }
        $_mem_data['parent_mem_id'] = $_parent_mem_id;
        $_mem_data['id'] = $_mem_id;
        $_rs = Db::name('members')->update($_mem_data);
        if (false === $_rs) {
            return false;
        }

        return $_parent_mem_id;
    }
}