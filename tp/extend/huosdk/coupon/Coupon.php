<?php
/**
 * Coupon.php UTF-8
 * 代金卷处理类
 *
 * @date    : 2017/1/19 17:34
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\coupon;

use huosdk\integral\Memitg;
use think\Config;
use think\Db;
use think\Session;

class Coupon {
    private $coupon_id;
    private $app_id;

    /**
     * Gift constructor.
     *
     * @param int $app_id    游戏ID
     * @param int $coupon_id 礼包ID
     */
    public function __construct($app_id = 0, $coupon_id = 0) {
        if (!empty($app_id)) {
            $this->app_id = $app_id;
        }
        if (!empty($coupon_id)) {
            $this->coupon_id = $coupon_id;
        }
    }

    private function getListfield() {
        $_field = [
            'c.id'                   => 'couponid',
            "c.title"                => 'couponname',
            'c.app_id'               => 'gameid',
            'ROUND(c.money)'         => 'money',
            'c.total_num'            => 'total',
            "c.total_num-c.send_num" => 'remain',
            '""'                     => 'icon',
            'c.send_start_time'      => 'starttime',
            "c.end_time"             => 'enttime',
            "c.condition"            => 'integral',
            "'通用'"                   => 'scope',
            "c.content"              => 'func',
        ];

        return $_field;
    }

    public function getDetail($coupon_id) {
        if (empty($coupon_id)) {
            return null;
        }
        $_field = $this->getListfield();
        $_map['c.id'] = $coupon_id;
        $_rdata = Db::name('coupon')
                    ->alias('c')
                    ->field($_field)
                    ->where($_map)
                    ->find();
        if (empty($_rdata)) {
            return null;
        }

        return $_rdata;
    }

    /**
     * @param array $map    输入参数
     * @param int   $page   页码
     * @param int   $offset 每页展示数量
     *
     * @return array 如果为空返回null,如果不为空返回count,list
     */
    public function getList(array $map, $page = 1, $offset = 10) {
        $_map['c.start_time'] = [
            ['=', 0],
            ['<', time()],
            'or'
        ];
        $_map['c.is_delete'] = 2;
        $_map['c.status'] = 2;
        if (!empty($map['app_id'])) {
            /* 指定游戏的代金卷 */
            $_map['c.app_id'] = $map['app_id'];
        }
        if (!empty($map['isrcmd']) && 2 == $map['isrcmd']) {
            /* 指定游戏的代金卷 */
            $_map['c.isrcmd'] = 2;
        }
        /* 限定时间代金卷 */
        if (!empty($map['limittime'])) {
            $_map['c.end_time'] = ['gt', 0];
        } else {
            $_map['c.end_time'] = [
                ['=', 0],
                ['>', time()],
                'or'
            ];
        }
        $_map['c.total_num'] = ['gt', 0]; /* 剩余数量大于0 */
        $_order = "id DESC";
        $_field = $this->getListfield();
        $_rdata['count'] = Db::name('coupon')
                             ->alias('c')
                             ->where($_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_page = $page." , ".$offset;
            $_list = Db::name('coupon')
                       ->alias('c')
                       ->field($_field)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            }
            $_rdata['list'] = $_list;
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    /**
     * @param int $mem_id 玩家ID
     * @param int $page   第几页
     * @param int $offset 每页显示数量
     *
     * @return null
     */
    public function getMemlist($mem_id, $page = 1, $offset = 10) {
        if (empty($mem_id)) {
            return null;
        }
        $_map['cm.mem_id'] = $mem_id;
        $_map['c.is_delete'] = 2;
        $_map['c.status'] = 2;
//        $_map['c.end_time'] = [
//            ['=', 0],
//            ['>', time()],
//            'or'
//        ];
        $_field = $this->getListfield();
        $_own_field = [
            'cm.total'  => 'mytotal',
            'cm.remain' => 'myremain'
        ];
        $_field = array_merge($_field, $_own_field);
        $_join = [
            [
                Config::get('database.prefix').'coupon c',
                'c.id =cm.c_id AND mem_id='.$mem_id,
                'LEFT'
            ]
        ];
        $_rdata['count'] = Db::name('coupon_mem')
                             ->alias('cm')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_page = $page." , ".$offset;
            $_list = Db::name('coupon_mem')
                       ->alias('cm')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    /**
     *
     * @param $mem_id
     * @param $couponcontent
     *
     * @return bool|int
     */
    public function getMoneybyString($mem_id, $couponcontent) {
        if (empty($couponcontent)) {
            return 0;
        }
        $_cc_array = explode(',', $couponcontent);
        $_map['mem_id'] = $mem_id;
        $_mem_coupon = Db::name('coupon_mem')->where($_map)->column('remain', 'c_id');
        $_coupon_data = Db::name('coupon')->column('money', 'id');
        $_money = 0;
        foreach ($_cc_array as $_val) {
            list($_cid, $_cnt) = explode(':', $_val);
            if (empty($_cid)) {
                return false;
            }
            if (empty($_cnt)) {
                continue;
            }
            if ($_cnt > $_mem_coupon[$_cid]) {
                return false;
            }
            $_money += $_cnt * $_coupon_data[$_cid];
        }

        return $_money;
    }

    /**
     *
     * @param $mem_id
     * @param $couponcontent
     *
     * @return bool|int
     */
    public function setMoneybyString($mem_id, $couponcontent) {
        if (empty($couponcontent) || empty($mem_id)) {
            return 0;
        }
        $_cc_array = explode(',', $couponcontent);
        $_map['mem_id'] = $mem_id;
        $_mem_coupon = Db::name('coupon_mem')->where($_map)->column('remain', 'c_id');
        foreach ($_cc_array as $_val) {
            list($_cid, $_cnt) = explode(':', $_val);
            $_cm_map['mem_id'] = $mem_id;
            $_cm_map['c_id'] = $_cid;
            $_update_data['update_time'] = time();
            if ($_mem_coupon[$_cid] < $_cnt) {
                return false;
            }
            $_update_data['remain'] = $_mem_coupon[$_cid] - $_cnt;
            $_rs = Db::name('coupon_mem')->where($_cm_map)->update($_update_data);
            if (false === $_rs) {
                return false;
            }
        }

        return true;
    }

    /**
     * 积分兑换代金卷
     *
     * @param int $coupon_id 代金卷ID
     *
     * @return array|false|null|\PDOStatement|string|\think\Model
     */
    public function memGetcoupon($coupon_id) {
        $_mem_id = (int)Session::get('id', 'user');
        if (empty($coupon_id) || empty($_mem_id)) {
            return null;
        }
        $_c_data = $this->getDetail($coupon_id);
        if (empty($_c_data)) {
            return null;
        }
        $_coupon_remain = $this->getRemain($coupon_id);
        if (empty($_coupon_remain)) {
            return null;
        }
        /* 先扣取玩家积分 */
        $_itg_class = new  Memitg($_mem_id);
        $_rs = $_itg_class->decrease($_mem_id, $_c_data['integral']);
        if (false == $_rs) {
            /* 扣除玩家积分失败 */
            return null;
        }
        $this->doAfterget($_mem_id, $coupon_id, $_c_data['money']);
        $_c_data['remain'] -= 1;
        $_c_data['myintegral'] = $_itg_class->get();

        return $_c_data;
    }

    /**
     *
     * 代金卷剩余数量
     *
     * @param int $coupon_id 代金卷ID
     * @param int $app_id    游戏ID  0表示不限制游戏
     *
     * @return int 返回代金卷剩余数量
     */
    public function getRemain($coupon_id, $app_id = 0) {
        if (empty($coupon_id)) {
            return 0;
        }
        $_map['id'] = $coupon_id;
        $_map['is_delete'] = 2;
        $_map['status'] = 2;
        $_field = "total_num,send_num";
        $_c_info = Db::name('coupon')->field($_field)->where($_map)->find();
        if (empty($_c_info)) {
            return 0;
        }
        if ($_c_info['total_num'] <= $_c_info['send_num']) {
            return 0;
        }

        return $_c_info['total_num'] - $_c_info['send_num'];
    }

    public function setSendnum($couponid) {
        Db::name('coupon')->where('id', $couponid)->setInc('send_num');
    }

    public function doAfterget($mem_id, $couponid, $money = 0) {
        /* 代金卷已领取数量增加 */
        $this->setSendnum($couponid);
        $_map['mem_id'] = $mem_id;
        $_map['c_id'] = $couponid;
        $_cm_model = Db::name('coupon_mem');
        $_cm_data = $_cm_model->where($_map)->find();
        if (empty($_cm_data)) {
            $_cm_data = $_map;
            $_cm_data['total'] = 1;
            $_cm_data['remain'] = 1;
            $_cm_data['create_time'] = time();
            $_rs = $_cm_model->insert($_cm_data);
        } else {
            $_cm_data['total'] += 1;
            $_cm_data['remain'] += 1;
            $_cm_data['update_time'] = time();
            $_rs = $_cm_model->update($_cm_data);
        }
        if (false !== $_rs) {
            /* 更新log */
            // TODO: 2017/1/21 生成兑换订单 wuyonghong
            $_cml_data = $_map;
            $_cml_data['order_id'] = 0;
            $_cml_data['money'] = $money;
            $_cml_data['order_id'] = time();
            Db::name('coupon_mem_log')->insert($_cml_data);
        }
    }

    /**
     * @param int $mem_id
     * @param int $coupon_id
     *
     * @return int
     */
    public function getMemCouponCnt($mem_id = 0, $coupon_id = 0) {
        if (empty($mem_id)) {
            return 0;
        }
        $_map['mem_id'] = $mem_id;
        if (!empty($coupon_id)) {
            $_map['c_id'] = $coupon_id;
        }
        $_sum = Db::name('coupon_mem')->where($_map)->sum('remain');
        if (empty($_sum)) {
            return 0;
        }

        return ceil($_sum);
    }

    /**
     * 获取代金卷使用最大比例
     *
     * @return int|mixed
     */
    public function getMaxrate() {
        $_map['option_name'] = 'appcoupongmrate';
        $_max_rate = Db::name('options')->where($_map)->value('option_value');
        if (empty($_max_rate)) {
            $_data = $_map;
            $_data['option_value'] = 0;
            M('options')->add($_data);
            $_max_rate = 0;
        }

        return $_max_rate;
    }

    public function getMemCouponWeal($mem_id) {
        $_max_offset = 1000000000;
        $_data = $this->getMemlist($mem_id, 0, $_max_offset);
        $_weal = 0;
        if (empty($_data['list'])) {
            return $_weal;
        }
        foreach ($_data['list'] as $_key => $_val) {
            $_weal += $_val['myremain'] * $_val['money'];
        }

        return $_weal;
    }
}