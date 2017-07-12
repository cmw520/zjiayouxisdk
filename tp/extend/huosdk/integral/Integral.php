<?php
/**
 * Integral.php UTF-8
 * 积分
 *
 * @date    : 2017/2/6 15:21
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\integral;

use think\Db;

class Integral {
    private $mitg_class;
    private $mem_id;

    public function __construct($mem_id = 0) {
        $this->mem_id = $mem_id;
        $this->mitg_class = new Memitg($mem_id);
    }

    /**
     * 获取积分活动列表
     *
     */
    public function getActList() {
        /* 选取不过期的积分活动 */
        $_map['ia.end_time'] = [
            ['gt', time()],
            ['=', '0'],
            'or'
        ];
        $_map['ia.start_time'] = ['lt', time()]; /* 选取已经开始的的积分活动 */
        $_map['ia.is_delete'] = 2; /* 未删除的积分活动 */
        $_field = [
            'ia.id'            => 'actid',
            "ia.act_name"      => 'actname',
            "ia.act_code"      => 'actcode',
            'ia.act_desc'      => 'actdesc',
            'ia.give_integral' => 'integral',
            'ia.start_time'    => 'starttime',
            "ia.end_time"      => 'enttime',
            "ia.type"          => 'typeid',
        ];
        $_rdata['count'] = Db::name('integral_activity')
                             ->alias('ia')
                             ->where($_map)
                             ->count();
        if (!empty($_rdata['count'])) {
            $_list = Db::name('integral_activity')
                       ->alias('ia')
                       ->field($_field)
                       ->where($_map)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $_rdata['list'] = $_list;
            }
        }
        /* 我的积分 */
        $_rdata['myintegral'] = 0;
        $_rdata['mymoney'] = 0;
        $_mem_id = $this->mem_id;
        if (!empty($_mem_id)) {
            $_rdata['myintegral'] = $this->mitg_class->get();
            $_mi_class = new \huosdk\player\Meminfo($_mem_id);
            $_rdata['mymoney'] = $_mi_class->getAppmoney();
            $this->getMemactList($_rdata['list']);
        }

        return $_rdata;
    }

    private function getMemactList(&$list = array()) {
        if (empty($list)) {
            return null;
        }
        // TODO: 2017/2/22 代码需要优化 wuyonghong
        foreach ($list as $_k => $_v) {
            $list[$_k]['finishflag'] = 1;
            switch ($_v['actcode']) {
                case ITG_CHARGE: {
                    $_charge_data = $this->getChargeList($_v['actid']);
                    if (!empty($_charge_data)) {
                        unset($list[$_k]);
                        $list = array_merge($list, $_charge_data);
                    }
                    break;
                }
                case ITG_TGUSER: {
                    break;
                }
                case ITG_SIGN: {
                    $_ms_class = new \huosdk\player\Memsign($this->mem_id);
                    $_sign_data = $_ms_class->getMemsignDays();
                    if (1 == $_sign_data['2']) {
                        $list[$_k]['finishflag'] = 2;
                    } else {
                        $_sign_data[0] += 1;
                    }
                    /* 签到奖励  */
                    $_sr_data = $this->mitg_class->getSrinfo($_sign_data[0]);
                    if (false == $_sr_data) {
                        $list[$_k]['integral'] = 0;
                        break;
                    } else {
                        $list[$_k]['integral'] = $_sr_data['give_integral'];
                    }
                    break;
                }
                case ITG_UPPORTRAIT:/* 上传头像 */
                case ITG_STARTAPP: /* 启动APP */
                case ITG_FIRSTCHARGE: /* 首次充值 */
                case ITG_BINDMOBILE:  /* 手机绑定 */
                    $_mi_map['mem_id'] = $this->mem_id;
                    $_mi_map['ia_id'] = $_v['actid'];
                    $_total_cnt = Db::name('mem_itg')->where($_mi_map)->value('totol_cnt');
                    if (!empty($_total_cnt)) {
                        $list[$_k]['finishflag'] = 2;
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }

        return true;
    }

    /**
     * 获取充值列表
     *
     * @param int $ia_id
     *
     * @return array
     */
    public function getChargeList($ia_id = 0) {
        $_list = Db::name('integral_activity_pay')->order('end_money asc')->select();
        $_m_map['mem_id'] = $this->mem_id;
        $_m_map['ia_id'] = $ia_id;
        $_ias_ids = Db::name('mem_integral_log')->where($_m_map)->order('ias_id asc')->column('ias_id');
        $_rs_data = array();
        foreach ($_list as $_kay => $_val) {
            if (0 >= $_val['end_money']) {
                continue;
            }
            $_rdata['actid'] = $ia_id;
//            $_rdata['actname'] = '充值'.$_val['end_money'].'元';
            $_rdata['actname'] = $_val['end_money'];
            $_rdata['actcode'] = ITG_CHARGE;
            $_rdata['actdesc'] = '';
            $_rdata['integral'] = $_val['give_integral'];
            $_rdata['starttime'] = 0;
            $_rdata['enttime'] = 0;
            $_rdata['typeid'] = 3;
            if (in_array($_val['id'], $_ias_ids)) {
                $_rdata['finishflag'] = 2;
                $_rs_data[] = $_rdata;
            } else {
                $_rdata['finishflag'] = 1;
                $_rs_data[] = $_rdata;
                break;
            }
        }

        return $_rs_data;
    }

    /**
     * 充值一定金额后可获得积分
     *
     * @param float $money
     *
     * @return float
     */
    public function getItgbyMoney($money = 0.00) {
        /* 获得玩家现在充值金额 */
        $_mi_class = new \huosdk\player\Meminfo($this->mem_id);
        $_app_money = $_mi_class->getAppmoney();
        $_now_iap = $this->mitg_class->getIapbyMoney($_app_money);
        if (empty($_now_iap)) {
            $_now_iap['end_money'] = 0;
            $_now_iap['give_integral'] = 0;
        }
        /* 充值后金额 */
        $_total_money = $_app_money + $money;
        $_total_iap = $this->mitg_class->getIapbyMoney($_total_money);
        if (empty($_total_iap)
            || empty($_total_iap['end_money'])
            || ($_total_iap['end_money'] == $_now_iap['end_money'])
        ) {
            /* 没有跨级 级别相同 返回0 */
            return 0 + floatval($money * $_total_iap['rebate']);
        }

//        return $this->mitg_class->getTotalIapbyMoney($_app_money, $_total_money) + $money * $_total_iap['rebate'];
        return $money * $_total_iap['rebate'];
    }

    public function getMoneyLevel($money) {
        $_map['is_delete'] = 2;
        $_map['end_money'] = ['lt', $money];
    }
}