<?php
/**
 * Memsign.php UTF-8
 * 玩家签到
 *
 * @date    : 2017/2/6 15:53
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\player;

use huosdk\integral\Memitg;
use think\Db;

class Memsign {
    private $mem_id;
    private $defualt; /* 默认当日积分 */
    /**
     * Memsign constructor.
     *
     * @param int $mem_id
     */
    public function __construct($mem_id = 0) {
        if (!empty($mem_id)) {
            $this->mem_id = $mem_id;
        }
        $this->defualt = 0;
    }

    /**
     *  获取签到列表
     */
    public function getList() {
        //获取当前年月
        $_default_map['date'] = '0000-00';
        $_days = date('t');
        $_default_sign_reword = Db::name('sign_reward')
                                  ->where($_default_map)
                                  ->cache(86400)
                                  ->order('sign_days ASC')
                                  ->column('give_integral', 'sign_days');
        $_map['date'] = date('Ym', time());
        $_sign_reword = Db::name('sign_reward')
                          ->where($_map)
                          ->cache(86400)
                          ->order('sign_days ASC')
                          ->column('give_integral', 'sign_days');
        $_rdata['count'] = $_days;
        $_mem_sign = $this->getMemsignDays();
        $_rdata['signdays'] = $_mem_sign[0];
        $_rdata['signdesc'] = $this->getSigndisc();
        $_list = array();
        for ($_i = 1; $_i <= $_days; $_i++) {
            if ($_i <= $_rdata['signdays']) {
                $_for_list['signed'] = 2;
            } else {
                $_for_list['signed'] = 1;
            }
            $_for_list['day'] = $_i;
            if (empty($_sign_reword[$_i])) {
                if (empty($_default_sign_reword[$_i])) {
                    $_for_list['integral'] = $this->defualt;
                } else {
                    $_for_list['integral'] = $_default_sign_reword[$_i];
                }
            } else {
                $_for_list['integral'] = $_sign_reword[$_i];
            }
            $_list[] = $_for_list;
        }
        $_rdata['list'] = $_list;

        return $_rdata;
    }

    /**
     * 玩家签到数据
     *
     * @return array
     * 0 表示签到天数
     * 1 表示最后签到日期时间戳
     * 2 表示是否已经签到 0 未签到  1 已签到
     */
    public function getMemsignDays() {
        if (empty($this->mem_id)) {
            $_rdata[0] = 0;
            $_rdata[1] = 0;
        }
        $_map['mem_id'] = $this->mem_id;
        $field = [
            'sign_days',
            'last_sign_date'
        ];
        $_days = Db::name('mem_ext')->field($field)->where($_map)->find();
        if (empty($_days)) {
            $_rdata[0] = 0;
            $_rdata[1] = 0;
        } else {
            $_rdata[0] = $_days['sign_days'];
            $_rdata[1] = $_days['last_sign_date'];
        }
        $_rdata[2] = 0;
        //判断今日是否已经签到
        if (date('Ymd', $_rdata[1]) == date('Ymd')) {
            $_rdata[2] = 1;
        }

        return $_rdata;
    }

    /**
     * 获取签到说明
     *
     * @return int 返回玩家签到天数
     */
    public function getSigndisc() {
        $_map['act_code'] = 'sign';
        $_desc = Db::name('integral_activity')->where($_map)->value('act_desc');
        if (empty($_desc)) {
            return '';
        }

        return $_desc;
    }

    /**
     * @param int $signdays 签到天数
     *
     * @return bool|int|mixed 签到失败 或者 签到后的积分
     */
    public function save($signdays = 0) {
        if (empty($this->mem_id)) {
            return false; //签到失败
        }
        $_mem_sign = $this->getMemsignDays();
        /* 签到时间为签到次数+1 */
        if ($signdays != $_mem_sign[0] + 1) {
            return false; //签到失败
        }
        if ($_mem_sign[1] > time()) {
            return false; //签到失败
        }
        //判断今日是否已经签到
        if (date('Ymd', $_mem_sign[1]) == date('Ymd')) {
            return -1; //今日已签到
        }
        $_ext_data['mem_id'] = $this->mem_id;
        $_ext_data['sign_days'] = $signdays;
        $_ext_data['last_sign_date'] = time();
        $_rs = Db::name('mem_ext')->update($_ext_data);
        $_memitg_class = new Memitg($this->mem_id);
        if ($_rs) {
            /* BEGIN 获取积分 ITG_SIGN */
            $_rs = $_memitg_class->addbyAction(ITG_SIGN, $signdays);
            if (false == $_rs) {
                return $_memitg_class->get();
            }
            /* END 获取积分 ITG_SIGN */
            //更新签到天数
            $this->updateSign($signdays);
        }

        return $_memitg_class->get();
    }

    /**
     * @param int $signdays 签到第几天
     *
     * @return bool 更新成功OR失败
     */
    public function updateSign($signdays = 0) {
        if (empty($signdays)) {
            return false;
        }
        $_map['mem_id'] = $this->mem_id;
        $_ext_model = Db::name('mem_ext');
        $_ext_info['mem_id'] = $this->mem_id;
        $_ext_info['sign_days'] = $signdays;
        $_ext_info['last_sign_date'] = time();
        $_rs = $_ext_model->update($_ext_info);
        if (false === $_rs) {
            return false;
        }

        return true;
    }
}