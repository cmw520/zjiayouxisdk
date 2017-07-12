<?php
/**
 * GameController.class.php UTF-8
 * wap游戏页面
 *
 * @date    : 2017年1月6日下午3:33:11
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2017年1月6日下午3:33:11
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class GameController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
    }

    function sub() {
        $_app_id = I('get.appid/d', 0);
        $_agent_id = I('get.agent/d', 0);
        if (empty($_app_id)) {
            redirect(WEBSITE);
            $this->error("参数错误",WEBSITE);
        }
        $_ac_code = null;
        if (!empty($_agent_id)) {
            $_u_map['id'] = $_agent_id;
            $_ac_code = M('users')->where($_u_map)->getField('user_activation_key');
        }
        
        $this->assign('accode', $_ac_code);
        $_g_map['id'] = $_app_id;
        $_g_data = M('game')->where($_g_map)->find();
        if (empty($_g_data)) {
            redirect(WEBSITE);
            $this->error("游戏不存在",WEBSITE);
        }
        $_gi_data = array();
        if (!empty($_g_data['game_id'])) {
            $_gi_map['app_id'] = $_g_data['game_id'];
            $_gi_data = M('game_info')->where($_gi_map)->find();
        }
        $_gv_map['app_id'] = $_app_id;
        $_gv_map['status'] = 2;
        $_gv_data = M('game_version')->where($_gv_map)->order('id desc')->select();
        if (empty($_gi_data['size'])){
            $_gi_data['size'] = format_file_size($_gv_data[0]['size']);
        }
        $this->assign('gameversion', $_gv_data[0]);
        $this->assign('game', $_g_data);
        $this->assign('gameinfo', $_gi_data);

        $images = json_decode($_gi_data['image'],true);
        $this->assign('images',$images);
        if (4 == $_g_data['classify']) {
            $this->display('Game/ios');
        } else {
            $this->display('Game/android');
        }
    }

    /**
     * IOS WAP游戏 子站页面
     * @method:
     *
     * @return  :
     * @author  : wuyonghong <wyh@huosdk.com>
     * @date    : 2017年1月6日下午3:35:36
     * @since   7.0
     * @modified:
     */
    function trust() {
        $this->display();
    }
}

