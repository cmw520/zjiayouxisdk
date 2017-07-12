<?php
/**
 * Meminfo.php UTF-8
 * 玩家信息
 *
 * @date    : 2017/1/21 15:04
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\player;

use huosdk\common\HuoImage;
use huosdk\coupon\Coupon;
use huosdk\gift\Gift;
use huosdk\integral\Memitg;
use huosdk\wallet\Gm;
use think\Config;
use think\Db;
use think\Log;

class Meminfo {
    private $mem_id;

    /**
     *
     * 自定义错误处理
     *
     * @param        $msg   输出的信息
     * @param string $level 输出等级
     */
    private function _error($msg, $level = 'error') {
        $_info = 'player\Meminfo Error:'.$msg;
        Log::record($_info, $level);
    }

    /**
     * Meminfo constructor.
     *
     * @param int $mem_id 玩家ID
     */
    public function __construct($mem_id = 0) {
        if (!empty($mem_id)) {
            $this->mem_id = $mem_id;
        }
    }

    /**
     * 设置mem_id
     *
     * @param $mem_id INT 玩家ID
     */
    public function setMemid($mem_id) {
        $this->mem_id = $mem_id;
    }

    /**
     * 读取玩家信息
     */
    public function read() {
        $_field = [
            'username' => 'username',
            'email'    => 'email',
            'mobile'   => 'mobile',
            'nickname' => 'nickname',
            'portrait' => 'portrait'
        ];
        $_map['id'] = $this->mem_id;
        $_mem_info = Db::name('members')->field($_field)->where($_map)->find();
        if (empty($_mem_info)) {
            return null;
        }
        $_itg_class = new Memitg($this->mem_id);
        $_mem_info['myintegral'] = $_itg_class->get();
        $_coupon_class = new Coupon();
        $_mem_info['couponcnt'] = $_coupon_class->getMemCouponCnt($this->mem_id);
        $_gift_class = new Gift();
        $_mem_info['giftcnt'] = $_gift_class->getMemgiftcnt($this->mem_id);
        $_gm_class = new Gm(1);
        $_mem_info['gmgamecnt'] = $_gm_class->getCnt($this->mem_id);
        $_mem_info['newmsg'] = 1;
        $_msg_class = new Memmsg($this->mem_id);
        if ($_msg_class->hasNew()) {
            $_mem_info['newmsg'] = 2;
        }
        $_mem_info['portrait'] = Config::get('domain.STATICSITE').$_mem_info['portrait'];

        return $_mem_info;
    }

    /**
     * 查询是否已上传头像
     *
     * @return bool
     */
    public function getPortrait() {
        if (empty($this->mem_id)) {
            return false;
        }
        $_map['id'] = $this->mem_id;
        $_portrait = Db::name('members')->where($_map)->value('portrait');
        if (empty($_portrait)) {
            return false;
        }

        return true;
    }

    public function setPortrait($file) {
        if (empty($this->mem_id)) {
            return false;
        }
        $_hi_class = new HuoImage();
        $_path = $_hi_class->savePortrait($file);
        if (empty($_path)) {
            return false;
        }
        $_map['id'] = $this->mem_id;
        $_portrait = Db::name('members')->where($_map)->value('portrait');
        if (empty($_portrait)) {
            //表示第一次上传, 获取积分
            /* BEGIN 获取积分 ITG_UPPORTRAIT */
            $_mitg_class = new Memitg($this->mem_id);
            $_mitg_class->addbyAction(ITG_UPPORTRAIT);
            /* END 获取积分 ITG_UPPORTRAIT */
        }
        $_m_data['portrait'] = $_path;
        $_rs = Db::name('members')->where($_map)->update($_m_data);
        if (false === $_rs) {
            return false;
        }

        return Config::get('domain.STATICSITE').$_path;
    }

    /**
     * 获取玩家地址信息
     *
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getAddress() {
        $_map['mem_id'] = $this->mem_id;
        if (empty($_map['mem_id'])) {
            return array();
        }
        $_map['is_default'] = 2;
        $_map['is_delete'] = 2;
        $_field = [
            'consignee' => 'consignee',
            'mobile'    => 'mobile',
            'country'   => 'country',
            'province'  => 'province',
            'city'      => 'city',
            'district'  => 'district',
            'town'      => 'town',
            'address'   => 'address',
            'zipcode'   => 'zipcode',
        ];
        $_ad_info = Db::name('mem_address')
                      ->field($_field)
                      ->where($_map)
                      ->find();
        if (empty($_ad_info)) {
            return array();
        }
        $_address_list = Db::name('region')->cache(86400)->column('name', 'id');
        $_address_list[0] = '';
        $_ad_info['topaddress'] = '中国'.$_address_list[$_ad_info['province']].$_address_list[$_ad_info['city']]
                                  .$_address_list[$_ad_info['district']].$_address_list[$_ad_info['town']];

        return $_ad_info;
    }

    /**
     * 修改玩家地址信息
     *
     * @param $data
     *
     * @return bool
     */
    public function setAddress($data) {
        if (empty($this->mem_id)) {
            return false;
        }
        $_ad_data['consignee'] = get_val($data, 'consignee', '');
        $_ad_data['mobile'] = get_val($data, 'mobile', '');
        $_ad_data['country'] = get_val($data, 'country', 0);
        $_ad_data['province'] = get_val($data, 'province', 0);
        $_ad_data['city'] = get_val($data, 'city', 0);
        $_ad_data['district'] = get_val($data, 'district', 0);
        $_ad_data['town'] = get_val($data, 'town', 0);
        $_ad_data['address'] = get_val($data, 'address', '');
        $_ad_data['zipcode'] = get_val($data, 'zipcode', '');
        $_ad_data['is_default'] = 2;
        $_ad_data['mem_id'] = $this->mem_id;
        $_ad_data['is_delete'] = 2;
        $_id = Db::name('mem_address')->insertGetId($_ad_data);
        if ($_id) {
            $_map['mem_id'] = $this->mem_id;
            $_map['id'] = ['neq', $_id];
            //设置其他地址失效
            Db::name('mem_address')->where($_map)->setField('is_default', 1);

            return true;
        }

        return false;
    }

    public function modPwd($old_pwd, $new_pwd) {
        if (empty($this->mem_id)) {
            return 1000; //服务器内部错误
        }
        if (empty($old_pwd) || empty($new_pwd)) {
            return 412; //密码错误
        }
        $_map['id'] = $this->mem_id;
        $_password_in_db = Db::name('members')->where($_map)->value('password');
        $_m_class = new Member();
        $_rs = $_m_class->comparePwd($old_pwd, $_password_in_db);
        if (false == $_rs) {
            return 412; //密码错误
        }
        $_rs = $this->setPwd($new_pwd);
        if (false == $_rs) {
            return 1000; //密码修改内部错误
        }

        return 200; //修改成功
    }

    /**
     * 设置密码
     *
     * @param        $pwd
     * @param string $authcode
     *
     * @return bool
     */
    public function setPwd($pwd, $authcode = '') {
        if (empty($pwd)) {
            return false;
        }
        $_m_class = new Member();
        $_mem_data['id'] = $this->mem_id;
        if (empty($this->mem_id)) {
            return false;
        }
        $_mem_data['password'] = $_m_class->authPwd($pwd, $authcode);
        $_rs = Db::name('members')->update($_mem_data);
        if (false === $_rs) {
            return false;
        }

        return true;
    }

    /**
     * 通过用户名设置密码
     *
     * @param string $username
     * @param string $pwd
     * @param string $authcode
     *
     * @return bool
     */
    public function setPwdByusername($username, $pwd, $authcode = '') {
        if (empty($username)) {
            return false;
        }
        $_m_class = new Member(0, $username);
        $this->mem_id = $_m_class->getMemid();

        return $this->setPwd($pwd, $authcode);
    }

    /**
     * 通过手机号设置密码
     *
     * @param string $_mobile 手机
     * @param string $pwd
     * @param string $authcode
     *
     * @return bool
     */
    public function setPwdByMobile($_mobile, $pwd, $authcode = '') {
        if (empty($_mobile)) {
            return false;
        }
        $this->mem_id = Db::name('members')->where('mobile', $_mobile)->value('id');

        return $this->setPwd($pwd, $authcode);
    }

    /**
     * 绑定手机号
     *
     * @param $mobile 手机号
     *
     * @return bool
     */
    public function setMobile($mobile) {
        $_mem_id = $this->mem_id;
        if (empty($mobile) || empty($_mem_id)) {
            return false;
        }
        $_mem_data['mobile'] = $mobile;
        //查重
        $_cnt = Db::name('members')->where($_mem_data)->count();
        if ($_cnt > 0) {
            return false;
        }
        /* BEGIN 获取积分 ITG_BINDMOBILE */
        $_mitg_class = new \huosdk\integral\Memitg($_mem_id);
        $_mitg_class->addbyAction(ITG_BINDMOBILE);
        /* END 获取积分 ITG_BINDMOBILE */
        $_mem_data['id'] = $_mem_id;
        $_rs = Db::name('members')->update($_mem_data);
        if (false === $_rs) {
            return false;
        }

        return true;
    }

    /**
     * 验证手机号
     *
     * @param $mobile 手机号
     *
     * @return bool
     */
    public function verifyMobile($mobile) {
        $_mem_id = $this->mem_id;
        if (empty($mobile) || empty($_mem_id)) {
            return false;
        }
        $_map['id'] = $_mem_id;
        $_veri_mobile = Db::name('members')->where($_map)->value('mobile');
        if ($_veri_mobile != $mobile) {
            return false;
        }

        return true;
    }

    /**
     * 修改昵称
     *
     * @param string $nicename 昵称
     *
     * @return bool 修改成功 OR 失败
     *
     */
    public function setNicename($nicename) {
        if (empty($nicename) || empty($nicename)) {
            return false;
        }
        $_mem_data['id'] = $this->mem_id;
        $_mem_data['nickname'] = $nicename;
        $_rs = Db::name('members')->update($_mem_data);
        if (false === $_rs) {
            return false;
        }

        return true;
    }

    public function getAppmoney() {
        $_map['mem_id'] = $this->mem_id;
        if (empty($_map['mem_id'])) {
            return 0;
        }
        $_app_sum_money = Db::name('mem_ext')->where($_map)->value('app_sum_money');
        if (empty($_app_sum_money)) {
            return 0;
        }

        return $_app_sum_money;
    }

    /**
     * 设置app中充值总额
     *
     * @param int $_app_sum_money
     *
     * @return bool|int|true
     *
     */
    public function setAppmoney($_app_sum_money = 0) {
        if (empty($_app_sum_money)) {
            return false;
        }
        $_map['mem_id'] = $this->mem_id;
        if (empty($_map['mem_id'])) {
            return false;
        }
        $_rs = Db::name('mem_ext')->where($_map)->setInc('app_sum_money', $_app_sum_money);
        if (false === $_rs) {
            return false;
        }

        return true;
    }
}