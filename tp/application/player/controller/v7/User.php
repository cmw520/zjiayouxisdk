<?php
/**
 * User.php UTF-8
 * 玩家接口
 *
 * @date    : 2016年8月18日下午9:47:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\player\controller\v7;

use app\common\controller\Basehuo;
use huosdk\common\Simplesec;
use huosdk\log\Memlog;
use huosdk\player\Meminfo;
use huosdk\sms\Verify;
use think\Config;
use think\Session;

class User extends Basehuo {
    protected $m_class;

    function _initialize() {
        parent::_initialize();
        $this->m_class = new \huosdk\player\Member();
    }

    /**
     *
     * 注册登陆后,获得返回数据
     *
     * @param        $mem_info
     * @param string $agentgame
     * @param int    $flag
     *
     * @return mixed
     */
    protected function getReturn($mem_info, $agentgame = '', $flag = 0) {
        $_rdata['user_token'] = $this->se_class->setUsertoken($mem_info['id'], $this->rq_data);
        Session::set('agentgame', $agentgame, 'user');
        //登陆处理
        $_login_data['mem_id'] = Session::get('id', 'user');
        $_login_data['app_id'] = Session::get('app_id', 'app');
        $_login_data['agentgame'] = $agentgame;
        $_login_data['imei'] = Session::get('device_id', 'device');
        $_login_data['deviceinfo'] = Session::get('deviceinfo', 'device');
        $_login_data['userua'] = Session::get('userua', 'device');
        $_login_data['from'] = Session::get('from', 'device');
        $_login_data['flag'] = $flag;
        $_login_data['reg_time'] = $mem_info['reg_time'];
        $_login_data['login_time'] = time();
        $_login_data['agent_id'] = Session::get('agent_id', 'user');
        $_login_data['login_ip'] = $this->request->ip();
        $_login_data['ipaddrid'] = Session::get('ipaddrid', 'device');
        $_login_data['open_cnt'] = Session::get('open_cnt', 'device');
        $_login_class = new Memlog('login_log');
        $_login_class->login($_login_data);
        $_rdata['mem_id'] = $_login_data['mem_id'];
        $_ss_class = new Simplesec();
        $_rdata['cp_user_token'] = $_ss_class->encode(session_id(), Config::get('config.CPAUTHCODE'));
        $_rdata['agentgame'] = $agentgame;

        return $_rdata;
    }

    protected function checkMobile() {
        $_mobile = $this->getVal($this->rq_data, 'mobile', '');
        $_smscode = $this->getVal($this->rq_data, 'smscode', '');
        $_sv_class = new Verify($_mobile, $_smscode, 1);
        $_check_data = $_sv_class->check();
        if ('200' != $_check_data['code']) {
            return hs_player_responce($_check_data['code'], $_check_data['msg']);
        }

        return true;
    }

    protected function logout() {
        Session::clear('user');
        Session::clear('order');
        Session::clear('role');
    }

    /**
     * http://doc.1tsdk.com/43?page_id=691
     * 【内】获取用户信息( user/detail )
     * portrait
     * nickname
     * myintegral
     * couponcnt
     * giftcnt
     * gmgamecnt
     * newmsg
     */
    public function read() {
        $this->isUserLogin();
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rdata = $_mi_class->read();
        if (empty($_rdata)) {
            return hs_huosdk_responce('400', '获取信息失败');
        }

        return hs_huosdk_responce('200', '请求成功', $_rdata, $this->auth_key);
    }

    /**
     * 【内】读取玩家地址(user/address/detail)
     * http://doc.1tsdk.com/43?page_id=698
     *
     * @return $this 返回地址信息
     */
    public function redaddress() {
        $this->isUserLogin();
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rdata = $_mi_class->getAddress();
        return hs_huosdk_responce('200', '请求成功', $_rdata, $this->auth_key);
    }

    /**
     * 【【内】修改玩家地址(user/address/update)
     * http://doc.1tsdk.com/43?page_id=701
     *
     */
    public function setaddress() {
        $this->isUserLogin();
        $_key_arr = [
            'consignee',
            'mobile',
            'province',
//            'city',
//            'town',
            'address'
        ];
        $_data = $this->getParams($_key_arr);
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rdata = $_mi_class->setAddress($_data);
        if (false == $_rdata) {
            return hs_huosdk_responce('400', '修改地址信息失败');
        }

        return hs_huosdk_responce('200', '修改成功');
    }

    /**
     * http://doc.1tsdk.com/43?page_id=693
     * 【内】找回密码( user/passwd/find )
     */
    public function setpwd() {
        $this->checkMobile();
        $_mobile = $this->getVal($this->rq_data, 'mobile', '');
        $_new_pwd = get_val($this->rq_data, 'password', '');
        $_mi_class = new Meminfo();
        $_rs = $_mi_class->setPwdByMobile($_mobile,$_new_pwd);
        if (false === $_rs) {
            return hs_huosdk_responce(400, '修改失败');
        }

        return hs_huosdk_responce(200, '修改成功', null, $this->auth_key);
    }

    /**
     * 【内】修改密码( user/passwd/update )
     * http://doc.1tsdk.com/43?page_id=657
     */
    public function modpwd() {
        $this->isUserLogin();
        $_old_pwd = get_val($this->rq_data, 'old_pwd', '');
        $_new_pwd = get_val($this->rq_data, 'new_pwd', '');
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rs = $_mi_class->modPwd($_old_pwd, $_new_pwd);
        if (200 != $_rs) {
            return hs_huosdk_responce($_rs, '修改失败');
        }

        return hs_huosdk_responce(200, '修改成功', null, $this->auth_key);
    }

    /**
     * 【内】修改玩家信息(user/info/update)
     * http://doc.1tsdk.com/43?page_id=700
     */
    public function set() {
        $this->isUserLogin();
        $_nicename = get_val($this->rq_data, 'nicename', '');
        if (empty($_nicename)) {
            return hs_huosdk_responce(400, '请填写昵称');
        }
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rs = $_mi_class->setNicename($_nicename);
        if (false === $_rs) {
            return hs_huosdk_responce(400, '修改失败');
        }

        return hs_huosdk_responce(200, '修改成功');
    }

    /**
     * 【内】绑定手机( user/phone/bind )
     * http://doc.1tsdk.com/43?page_id=658
     *
     * @return $this
     */
    public function bindmobile() {
        $this->isUserLogin();
        $this->checkMobile();
        $_mobile = get_val($this->rq_data, 'mobile', '');
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rs = $_mi_class->setMobile($_mobile);
        if (false == $_rs) {
            return hs_huosdk_responce(400, '绑定手机已存在');
        }

        $_rdata['status'] = 2;
        return hs_huosdk_responce(200, '绑定手机成功', $_rdata, $this->auth_key);
    }

    /**
     * 【内】验证手机( user/phone/verify )
     * http://doc.1tsdk.com/43?page_id=803
     *
     * @return $this
     */
    public function verify() {
        $this->isUserLogin();
        $this->checkMobile();
        $_mobile = get_val($this->rq_data, 'mobile', '');
        $_mem_id = Session::get('id', 'user');
        $_mi_class = new Meminfo($_mem_id);
        $_rs = $_mi_class->verifyMobile($_mobile);
        if (false === $_rs) {
            return hs_huosdk_responce(400, '手机验证不通过');
        }

        $_rdata['status'] = 2;
        return hs_huosdk_responce(200, '验证成功', $_rdata, $this->auth_key);
    }
}