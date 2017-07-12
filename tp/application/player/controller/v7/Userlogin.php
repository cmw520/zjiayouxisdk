<?php
/**
 * Userlogin.php UTF-8
 * 玩家登陆接口
 *
 * @date    : 2016年8月18日下午9:47:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\player\controller\v7;
class Userlogin extends User {
    function _initialize() {
        parent::_initialize();
    }

    /*
     * 普通登陆
     */
    function login() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'device_id',
            'userua',
            'username',
            'password'
        );
        $_rdata = $this->_login($_key_arr);

        return hs_player_responce(200, '登陆成功', $_rdata, $this->auth_key);
    }

    /*
     * 手机登陆
     */
    function loginMobile() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'device_id',
            'userua',
            'mobile',
            'password',
            'smscode',
            'smstype'
        );
        /* 验证手机短信 */
        $this->checkMobile();
        $this->rq_data['username'] = $this->rq_data['mobile'];
        $_rdata = $this->_login($_key_arr);

        return hs_player_responce(200, '登陆成功', $_rdata, $this->auth_key);
    }

    /*
     * 第三方登陆
     */
    function loginOauth() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'device_id',
            'userua',
            'openid',
            'access_token',
            'userfrom'
        );
        $_data = $this->getParams($_key_arr);
        $_agentgame = $this->getVal($_data, 'agentgame', '');
        $_data['agentgame'] = $this->getAgentgame($_agentgame);
        $_data['ip'] = $this->request->ip();
        $_mem_info = $this->m_class->loginOauth($_data);
        if (-411 == $_mem_info['id']) {
            return hs_player_responce('411', '用户不存在');
        }
        if (-412 == $_mem_info['id']) {
            return hs_player_responce('412', '密码错误');
        }
        if (-3 == $_mem_info['id']) {
            return hs_player_responce('411', '用户已禁用');
        }
        if (0 > $_mem_info['id']) {
            return hs_player_responce(0 - $_mem_info['id'], '登陆失败');
        }
        $_flag = 0;
        if (!empty($_mem_info['flag'])) {
            $_flag = $_mem_info['flag'];
        }
        $_rdata = $this->getReturn($_mem_info, $_data['agentgame'], $_flag);

        return hs_player_responce(200, '登陆成功', $_rdata, $this->auth_key);
    }

    /*
     * 登陆函数实体
     */
    private function _login($_key_arr) {
        $_data = $this->getParams($_key_arr);
        $_agentgame = $this->getVal($_data, 'agentgame', '');
        $_data['agentgame'] = $this->getAgentgame($_agentgame);
        $_mem_info = $this->m_class->loginMem($_data);
        if (-411 == $_mem_info['id']) {
            return hs_player_responce('411', '用户不存在');
        }
        if (-412 == $_mem_info['id']) {
            return hs_player_responce('412', '密码错误');
        }
        if (-3 == $_mem_info['id']) {
            return hs_player_responce('411', '用户已禁用');
        }
        if (0 > $_mem_info['id']) {
            return hs_player_responce(0 - $_mem_info['id'], '用户名错误');
        }
        $_rdata = $this->getReturn($_mem_info, $_data['agentgame'], 0);

        return $_rdata;
    }

    /*
     * 登出接口
     */
    function logout() {
        parent::logout();
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'user_token',
            'device_id',
            'userua'
        );
        $this->getParams($_key_arr);
        $_rdata['title'] = 'title';
        $_rdata['url'] = 'http://www.baidu.com';
        $_rdata['content'] = '内容';

        return hs_player_responce(200, '登出成功', $_rdata, $this->auth_key);
    }
}