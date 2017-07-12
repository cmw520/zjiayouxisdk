<?php
/**
 * User.php UTF-8
 * 用户中心
 *
 * @date    : 2016年11月10日下午2:24:51
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月10日下午2:24:51
 */
namespace app\wap\controller\v7;

use app\common\controller\Basewap;
use think\Config;

class User extends Basewap {
    function _initialize() {
        parent::_initialize();
    }

    /*
     * 玩家中心
     */
    function index() {
        $_se_id = session_id();
        $site = Config::get('domain.SDKSITE');
        $site = $site.'/float.php/Mobile/User/index'.'?session_id='.$_se_id;
        $this->redirect($site);
//         return hs_player_responce(201, '上传成功');
    }

    /*
     * 【内】打开WEB-修改密码(user/passwd/webupdate)
     * http://doc.1tsdk.com/12?page_id=459
     * 
     */
    function uppwd() {
    }

    /*
     * 【内】打开WEB-绑定手机(user/phone/webadd)
     * http://doc.1tsdk.com/12?page_id=460
     */
    function mobile() {
    }

    /*
     * 【内】打开WEB-绑定邮箱（user/email/webadd）
     * http://doc.1tsdk.com/12?page_id=461
     */
    function email() {
    }
}