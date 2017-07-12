<?php
/**
 * HuoSession.php UTF-8
 * 火速session 设置
 *
 * @date    : 2016年11月17日下午7:40:53
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月17日下午7:40:53
 */
namespace huosdk\common;

use think\Config;
use think\Db;
use think\Session;

class HuoSession {
    protected $session_config
        = array(
            'expire'     => "60 * 60 * 24 * 3",
            'auto_start' => 1
        );

    /**
     * 设置用户相关session
     *
     * @param  int  $mem_id   玩家ID
     * @param array $mem_info 玩家信息
     *
     * @return bool true 设置成功  false 设置失败
     */
    public function setUsersession($mem_id, array $mem_info) {
        if (empty($mem_info) || empty($mem_id) || $mem_id != $mem_info['id']) {
            return false;
        }
        $_user_info['id'] = get_val($mem_info, 'id');
        $_user_info['username'] = get_val($mem_info, 'username');
        $_user_info['email'] = get_val($mem_info, 'email');
        $_user_info['mobile'] = get_val($mem_info, 'mobile');
        $_user_info['nickname'] = get_val($mem_info, 'nickname');
        $_user_info['agent_id'] = get_val($mem_info, 'agent_id');
        $_user_info['agentgame'] = get_val($mem_info, 'agentgame');
        $_user_info['status'] = get_val($mem_info, 'status');
        $_user_info['reg_time'] = get_val($mem_info, 'reg_time');
        Session::set('user', $_user_info);
        $_ss_class = new Simplesec();
        $_re_token = $_ss_class->encode(session_id(), Config::get('config.HSAUTHCODE'));

        return $_re_token;
    }

    /**
     * 设置设备session
     *
     * @param array $rq_data
     *
     * @return bool
     */
    public function setRqsession(array $rq_data) {
        if (empty($rq_data)) {
            return false;
        }
        $_open_cnt = isset($rq_data['open_cnt']) ? $rq_data['open_cnt'] : 1;
        Session::set('device', $rq_data['device']);
        Session::set('from', $rq_data['from'], 'device');
        Session::set('open_cnt', $_open_cnt, 'device');
        Session::set('ip', $rq_data['ip'], 'device');
        Session::set('app_id', $rq_data['app_id'], 'app');
        Session::set('client_id', $rq_data['client_id'], 'app');

        return true;
    }

    public function setUser($name, $value) {
        $this->set($name, $value, 'user');
    }

    public function setGmremain($gm_remain) {
        $this->set('gmremain', $gm_remain, 'user');
    }

    public function setOrder($name, $value) {
        $this->set($name, $value, 'order');
    }

    public function setRole($name, $value) {
        $this->set($name, $value, 'role');
    }

    /**
     * session设置
     *
     * @param string      $name   session名称
     * @param mixed       $value  session值
     * @param string|null $prefix 作用域（前缀）
     *
     * @return void
     */
    public function set($name, $value = '', $prefix = null) {
        Session::set($name, $value, $prefix);
    }

    /**
     * session获取
     *
     * @param string      $name   session名称
     * @param string|null $prefix 作用域（前缀）
     *
     * @return mixed
     */
    public function get($name = '', $prefix = null) {
        return Session::get($name, $prefix);
    }

    /**
     * @param array $rq_data 请求的信息
     *
     * @return bool
     */
    public function setStartsession(array $rq_data) {
        if (!empty(Session::get('device'))) {
            Session::destroy();
        }
        Session::start();
        $this->setRqsession($rq_data);

        return true;
    }

    /**
     * @param int   $mem_id  玩家ID
     * @param array $rq_data 请求数组
     *
     * @param bool  $uptoken
     *
     * @return string 返回当前存放的session_id
     */
    public function setUsertoken($mem_id, $rq_data, $uptoken = true) {
        if (!in_array($rq_data['app_id'], Config::get('config.HUOAPP'))) {
            $_uptoken = false;
        } else {
            $_uptoken = $uptoken;
        }
        /* APP设置玩家session 先重置session_id 保留原来的session */
        if ($_uptoken) {
            Session::regenerate(false);
        }
        $_session_open_cnt = Session::get('open_cnt', 'device');
        if (!empty($_session_open_cnt)) {
            $rq_data['open_cnt'] = $_session_open_cnt;
        }
        Session::clear();
        $this->setRqsession($rq_data);
        $_me_data['user_token'] = session_id();
        $_i = 0;
        while ($_i < 5 && $_uptoken) {
            $cnt = Db::name('mem_ext')->where($_me_data)->count();
            if (0 == $cnt) {
                break;
            }
            $_i++;
            Session::regenerate(true);
            $_me_data['user_token'] = session_id();
        }
        if (5 != $_i) {
            if ($_uptoken) {
                $_me_data['mem_id'] = $mem_id;
                Db::name('mem_ext')->update($_me_data);
            }
            $_mem_info = Db::name('members')->where('id', $mem_id)->find();
            $_re_token = $this->setUsersession($mem_id, $_mem_info);
        } else {
            /* sessionid 获取错误 */
            return false;
        }

        return $_re_token;
    }

    /**
     * 初始化session_id
     *
     * @param string $rq_user_token 传入的请求token
     *
     * @return bool|String false 为初始化session失败  成功则返回session_id
     */
    public function initSession($rq_user_token) {
        if (empty($rq_user_token)) {
            return false;
        }
        /* 启用session */
        $_ss_class = new Simplesec();
        $_session_id = $_ss_class->decode($rq_user_token, Config::get('config.HSAUTHCODE'));
        if ($_session_id) {
            $this->session_config['id'] = $_session_id;
            Config::set('session', $this->session_config);
            Session::init($this->session_config);
        } else {
            return false;
        }

        return $_session_id;
    }

    /**
     * @param array $rq_data 请求信息
     *
     * @return bool|mixed 返回信息
     */
    public function setSession(array $rq_data) {
        /* 先读取session_id,配置session */
        $_rq_user_token = get_val($rq_data, 'user_token', '');
        $_session_id = $this->initSession($_rq_user_token);
        if (false === $_session_id) {
            //session初始化失败 重新设置session
            $this->setStartsession($rq_data);
            if (empty($rq_data['app_id']) || !in_array($rq_data['app_id'], Config::get('config.HUOAPP'))) {
                return session_id();
            } else {
                return false;
            }
        }
        $_session_mem_id = Session::get('id', 'user');
        /* 非APP 的游戏SDK 不需要根据user_token 判断是否合法 */
        if (empty($rq_data['app_id']) || !in_array($rq_data['app_id'], Config::get('config.HUOAPP'))) {
            if ($_session_mem_id) {
                return $_session_mem_id;
            }

            return false;
        }
        /* 从session_id读取mem_id, 并设置session */
        $_me_map['user_token'] = $_session_id;
        $_mem_id = Db::name('mem_ext')->where($_me_map)->value('mem_id');
        /* 玩家未登陆 */
        if (empty($_mem_id)) {
            return false;
        }
        if (!empty($_session_mem_id) && $_mem_id == $_session_mem_id) {
            /* 玩家已登陆 不需要再处理 */
            return $_session_mem_id;
        }
        $_rs = $this->setUsertoken($_mem_id, $rq_data, false);
        if (!$_rs) {
            return false;
        }

        return $_mem_id;
    }
}