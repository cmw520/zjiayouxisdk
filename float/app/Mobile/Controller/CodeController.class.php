<?php
/**
 * CodeController.class.php UTF-8
 * 邀请码填写
 *
 * @date    : 2017年1月5日下午11:42:37
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2017年1月5日下午11:42:37
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class CodeController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
    }

    // 浮点找回密码首页,输入找回密码的账号
    public function index() {
        $this->assign("title", '邀请码');
        $this->display();
    }

    // 检查用户名
    public function check() {
        $accode = I('post.accode/s', '');
        if (empty($accode)) {
            $this->error("邀请码输入错误");
        }
        $map['user_activation_key'] = $accode;
        $agent_id = M('users')->where($map)->getField('id');
        if (empty($agent_id)) {
            $this->error("该邀请码不存在，请核对后输入");
        }
        /* 通过agent_id 获取agentgame */
        $_info = 'default';
        $_ag_map['agent_id'] = $agent_id;
        $_ag_map['app_id'] = $_SESSION['app']['app_id'];
        $_ag = M('agent_game')->where($_ag_map)->getField('agentgame');
        if (!empty($_ag)) {
            $_info = $_ag;
            /*玩家渠道绑定*/
            $_mem_data['agentgame'] = $_info;
            $_mem_data['agent_id'] = $agent_id;
            $_mem_data['update_time'] = time();
            $_mem_data['id'] = sp_get_current_userid();

            $_SESSION['user']['agent_id'] = $agent_id;

            M('members')->save($_mem_data);
        }
        $this->success($_info);
    }
}