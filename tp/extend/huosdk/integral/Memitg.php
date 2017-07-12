<?php
/**
 * Integral.php UTF-8
 * 积分
 *
 * @date    : 2017/1/20 20:41
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\integral;

use huosdk\log\Memsign;
use huosdk\player\MemAgent;
use think\Config;
use think\Db;
use think\Log;

class Memitg {
    private $mem_id;
    private $integral;

    public function __construct($mem_id = 0) {
        if (!empty($mem_id)) {
            $this->mem_id = $mem_id;
            $this->integral = $this->getMem($mem_id);
        }
    }

    /**
     * 自定义错误处理
     *
     * @param string $msg 输出的信息
     * @param string $level
     *
     */
    private static function _error($msg, $level = 'error') {
        $_info = 'huosdk\integral\Memitg Error:'.$msg;
        Log::record($_info, $level);
    }

    public function get() {
        return $this->integral;
    }

    public function setMem($mem_id) {
        return $this->mem_id = $mem_id;
    }

    /**
     * @param int $mem_id 玩家ID
     *
     * @return int|mixed 返回玩家积分
     */
    public function getMem($mem_id = 0) {
        if (empty($mem_id)) {
            return 0;
        }
        $_map['mem_id'] = $mem_id;
        $_int_remain = Db::name('mem_ext')->where($_map)->value('integral_remain');
        if (empty($_int_remain)) {
            return 0;
        }

        return $_int_remain;
    }

    /**
     *
     * @param int $integral 积分
     *
     * @return bool 增加积分成功或失败
     */
    public function add($integral = 0) {
        if (empty($this->mem_id) || empty($integral)) {
            return false;
        }
        $_map['mem_id'] = $this->mem_id;
        $_ext_model = Db::name('mem_ext');
        $_field = "integral_total, integral_remain";
        $_ext_info = $_ext_model->field($_field)->where($_map)->find();
        if (empty($_ext_info)) {
            return false;
        } else {
            $_ext_info['mem_id'] = $this->mem_id;
            $_ext_info['integral_total'] += $integral;
            $_ext_info['integral_remain'] += $integral;
            $_rs = $_ext_model->update($_ext_info);
            if (false === $_rs) {
                return false;
            }
        }
        $this->integral = $_ext_info['integral_remain'];

        return true;
    }

    /**
     * @param int $mem_id   玩家ID
     * @param int $integral 扣除的积分
     *
     * @return bool 消耗积分成功或失败
     */
    public function decrease($mem_id = 0, $integral = 0) {
        if (empty($mem_id) || empty($integral)) {
            self::_error("decrease() empty(mem_id) || empty(integral)");

            return false;
        }
        $_map['mem_id'] = $mem_id;
        $_ext_model = Db::name('mem_ext');
        $_field = "integral_remain";
        $_ext_info = $_ext_model->field($_field)->where($_map)->find();
        if (empty($_ext_info) || $_ext_info['integral_remain'] < $integral) {
            return false;
        }
        $_ext_info['mem_id'] = $mem_id;
        $_ext_info['integral_remain'] -= $integral;
        $_rs = $_ext_model->update($_ext_info);
        if (false === $_rs) {
            return false;
        }
        $this->integral = $_ext_info['integral_remain'];

        return true;
    }

    /**
     * 获取玩家积分排名
     *
     * @return int 玩家排名 0表示未计入排名
     * @internal param $mem_id
     *
     */
    public function getRank() {
        $_map['integral_remain'] = ['>', $this->get()];
        $_rank = Db::name('mem_ext')->where($_map)->count();
        if (false === $_rank) {
            $_rank = 0;
        } else if (empty($_rank)) {
            $_rank = 1;
        } else {
            $_rank += 1;
        }

        return $_rank;
    }

    /**
     * @param int $page   页码
     * @param int $offset 每页数量
     *
     * @return mixed 返回排名数据
     */
    public function getRanklist($page = 1, $offset = 20) {
        $_order = "me.integral_remain DESC";
        $_join = [
            [
                Config::get('database.prefix').'mem_ext me',
                'me.mem_id=m.id',
                'LEFT'
            ]
        ];
        $_field = [
            'm.id'                                                      => 'mem_id',
            'm.nickname'                                                => 'nicename',
            "CONCAT('".Config::get('domain.STATICSITE')."',m.portrait)" => 'portrait',
            'me.integral_remain'                                        => 'integral',
        ];
        $_map = array();
        $_rdata['count'] = Db::name('members')
                             ->alias('m')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_page = $page." , ".$offset;
            $_list = Db::name('members')
                       ->alias('m')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata['count'] = 0;
            $_rdata['list'] = null;
        }

        return $_rdata;
    }

    public function getItgAction($act_code) {
        if (empty($this->mem_id) || empty($act_code)) {
            return false;
        }
        //通过act_code查找活动
        $_map['act_code'] = $act_code;
        $_map['is_delete'] = 2;
        $_map['start_time'] = array('lt', time());
        $_map['end_time'] = [
            ['gt', time()],
            ['=', 0],
            'or'
        ];
        $_act_data = Db::name('integral_activity')->where($_map)->find();
        if (empty($_act_data)) {
            return false;
        }
        $_ma_class = new MemAgent($this->mem_id);
        // 1 表示限制渠道
        if (1 == $_act_data['limit_agent']) {
            $_is_office_mem = $_ma_class->isOfficeMem();
            if (false == $_is_office_mem) {
                return false;
            }
        }

        return $_act_data;
    }

    /**
     * 获取充值赠送积分信息
     *
     * @param $pay_id
     *
     * @return bool|array
     */
    public function getChargeinfo($pay_id) {
        /* 获取玩家app现在充值金额 */
        $_mi_class = new \huosdk\player\Meminfo($this->mem_id);
        $_app_sum_money = $_mi_class->getAppmoney();
        if (empty($_app_sum_money)) {
            /* 级别不够 */
            return false;
        }
        $_iap_data = $this->getIapbyMoney($_app_sum_money);
        if (false == $_iap_data) {
            /* 级别不够 */
            return false;
        }
        $_money_diff = number_format($_app_sum_money - $_iap_data['end_money'], '2', '.', '');
        /* 查询订单信息 */
        $_wallet_class = \huosdk\wallet\Wallet::init();
        $_ordre_info = $_wallet_class->getChargeOrderinfo($pay_id);
        if (empty($_ordre_info) || empty($_ordre_info['real_amount'])) {
            return false;
        }
        $_amount = number_format($_ordre_info['real_amount'], '2', '.', '');
        if ($_money_diff > $_amount) {
            return false;
        }
        $_rdata['id'] = $_iap_data['id'];
        $_rdata['give_integral'] = $_iap_data['give_integral'] + $_amount * $_iap_data['rebate'];
        $_rdata['end_money'] = $_iap_data['end_money'];
        $_rdata['start_money'] = $_app_sum_money - $_amount;

        return $_rdata;
    }

    /**
     * 通过金额获充值积分奖励
     *
     * @param $money
     *
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    public function getIapbyMoney($money) {
        $_map['end_money'] = array('elt', $money);
        $_map['is_delete'] = 2;
        $_iap_data = Db::name('integral_activity_pay')->where($_map)->order('end_money desc')->find();
        if (false === $_iap_data) {
            return false;
        } elseif (empty($_iap_data)) {
            return null;
        }

        return $_iap_data;
    }

    /**
     * 获取累加总积分
     *
     * @param $start 开始
     * @param $end 结束
     *
     * @return float|int
     */
    public function getTotalIapbyMoney($start,$end) {
        $_map['end_money'] = [
            ['elt',$end],
            ['gt',$start]
        ];

        $_map['is_delete'] = 2;
        $_give_integral = Db::name('integral_activity_pay')->where($_map)->sum('give_integral');
        if (false === $_give_integral) {
            return 0;
        } elseif (empty($_give_integral)) {
            return 0;
        }

        return $_give_integral;
    }

    /**
     * 获取此次签到可获得奖励信息
     *
     * @param int $sign_days 签到的天数
     *
     * @return bool
     */
    public function getSrinfo($sign_days = 0) {
        $_sr_map['date'] = date('Ym', time());
        $_sr_map['sign_days'] = $sign_days;
        $_sr_data = Db::name('sign_reward')->where($_sr_map)->find();
        if (empty($_sr_data)) {
            $_sr_map['date'] = '0000-00';
            $_sr_data = Db::name('sign_reward')->where($_sr_map)->find();
        }
        if (empty($_sr_data['give_integral'])) {
            return false;
        }
        $_rdata['id'] = $_sr_data['id'];
        $_rdata['give_integral'] = $_sr_data['give_integral'];

        return $_rdata;
    }

    public function addbyAction($act_code, $ext = '') {
        /* 校验活动合法性 */
        $_itga_data = $this->getItgAction($act_code);
        if (false === $_itga_data) {
            return false;
        }
        switch ($act_code) {
            case ITG_CHARGE: {
                /* 首充获得积分 $ext 表示pay_id */
                $this->addbyAction(ITG_FIRSTCHARGE, $ext);

                return $this->doChargeAct($_itga_data['id'], $ext);
                break;
            }
            case ITG_SIGN: {
                /* 签到 $ext 表示签到天数 */
                $_sr_data = $this->getSrinfo($ext);
                if (false == $_sr_data) {
                    return false;
                }
                /* 插入签到记录 */
                $_signlog_class = new Memsign();
                $_sign_id = $_signlog_class->sign($this->mem_id, $ext, $_sr_data['give_integral']);

                return $this->doAct($_itga_data['id'], $ext, $_sr_data['give_integral'], $_sign_id);
                break;
            }
            case ITG_TGUSER: {
                /* 推广员任务 */
                /* 插入推广log */
                $_iidl_data['parent_mem_id'] = $this->mem_id;
                $_iidl_data['mem_id'] = $ext;
                $_iidl_data['app_id'] = \huosdk\common\Commonfunc::getAndAppid();
                $_iidl_data['give_integral'] = $_itga_data['give_integral'];
                $_iidl_data['create_time'] = time();
                $_ii_id = Db::name('integral_invitdownlog')->insertGetId($_iidl_data);

                return $this->doAct($_itga_data['id'], 0, $_itga_data['give_integral'], $_ii_id);
                break;
            }
            case ITG_UPPORTRAIT:/* 上传头像 */
            case ITG_STARTAPP: /* 启动APP */
            case ITG_BINDMOBILE:  /* 手机绑定 */ {
                return $this->doAct($_itga_data['id'], 0, $_itga_data['give_integral']);
                break;
            }
            case ITG_FIRSTCHARGE: /* 首次充值 */ {
                /* 判断是否首次充值 */
                $_wallet_class = \huosdk\wallet\Wallet::init();
                $_order_cnt = $_wallet_class->getChargeCnt($ext);
                if ($_order_cnt > 1) {
                    return true;
                }

                return $this->doAct($_itga_data['id'], 0, $_itga_data['give_integral']);
                break;
            }
            default:
                return false;
                break;
        }
    }

    /**
     * 完成充值任务
     *
     * @param $id       integer 充值任务ID
     * @param $order_id string 充值订单号
     *
     * @return bool true 成功  false 失败
     */
    public function doChargeAct($id, $order_id) {
        /* 充值任务 */
        $_sub_data = $this->getChargeinfo($order_id);
        if (false == $_sub_data) {
            return false;
        }
        /* 最大级别充值成功 */
        $rs = $this->doAct($id, $_sub_data['id'], $_sub_data['give_integral'], $order_id);
        if (false == $rs) {
            return false;
        }
        /* 获取除最大级别外 此次充值已完成充值ID */
        $_map['end_money'] = [
            ['lt',$_sub_data['end_money']],
            ['gt',$_sub_data['start_money']]
        ];
        $_map['is_delete'] = 2;
        $_iap_data = Db::name('integral_activity_pay')->where($_map)->select();
        if (false === $_iap_data) {
            return false;
        }
        if (empty($_iap_data)) {
            return true;
        }
        foreach ($_iap_data as $_k => $_v) {
            $rs = $this->doAct($id, $_v['id'], $_v['give_integral'], $order_id);
            if (false === $rs) {
                return false;
            }
        }

        return true;
    }

    public function doAct($ia_id, $sub_ia_id, $give_integral = 0, $ia_link_id = 0) {
        $_rs = $this->add($give_integral);
        if (false == $_rs) {
            return false;
        }
        $_rs = $this->insertLog($ia_id, $sub_ia_id, $give_integral, $ia_link_id);
        if (false == $_rs) {
            return false;
        }

        return true;
    }

    /**
     * 玩家积分活动对应表
     *
     * @param $ia_id
     * @param $give_integral
     *
     * @return bool
     */
    public function setMemItg($ia_id, $give_integral) {
        $_map['mem_id'] = $this->mem_id;
        $_map['ia_id'] = $ia_id;
        $_mi_data = Db::name('mem_itg')->where($_map)->find();
        if (empty($_mi_data)) {
            $_mi_data['mem_id'] = $this->mem_id;
            $_mi_data['ia_id'] = $ia_id;
            $_mi_data['give_integral'] = $give_integral;
            $_mi_data['totol_cnt'] = 1;
            $_mi_data['today_cnt'] = 1;
            $_mi_data['create_time'] = time();
            $_mi_data['update_time'] = $_mi_data['create_time'];
            $_rs = Db::name('mem_itg')->insert($_mi_data);
        } else {
            $_mi_data['give_integral'] += $give_integral;
            $_mi_data['totol_cnt'] += 1;
            $_mi_data['today_cnt'] += 1;
            $_mi_data['update_time'] = time();
            $_rs = Db::name('mem_itg')->update($_mi_data);
        }
        if (false == $_rs) {
            return false;
        }

        return true;
    }

    /**
     * 玩家参与此类活动次数
     *
     * @param int $ia_id      积分活动ID
     * @param int $sub_act_id 积分活动子ID
     * @param int $start_time 查询起始时间
     * @param int $end_time   查询结束时间
     *
     * @return int
     */
    public function getMemactCnt($ia_id, $sub_act_id = 0, $start_time = 0, $end_time = 0) {
        if (empty($this->mem_id)) {
            return 0;
        }
        if (!empty($start_time)) {
            $_map['create_time'] = array('gt', $start_time);
        }
        if (!empty($end_time)) {
            $_map['create_time'] = array('lt', $end_time);
        }
        $_map['ia_id'] = $ia_id;
        $_map['ias_id'] = $sub_act_id;
        $_cnt = Db::name("mem_integral_log")->where($_map)->count();
        if (empty($_cnt)) {
            return 0;
        }

        return $_cnt;
    }

    /**
     * 插入积分记录
     *
     * @param     $ia_id
     * @param     $sub_act_id
     * @param     $give_integral
     * @param int $ia_link_id
     *
     * @return bool
     */
    public function insertLog($ia_id, $sub_act_id = 0, $give_integral = 0, $ia_link_id = 0) {
        $_rs = $this->setMemItg($ia_id, $give_integral);
        if (false == $_rs) {
            self::_error(__LINE__."setMemItg error");

            return false;
        }
        $_mil_data['mem_id'] = $this->mem_id;
        $_mil_data['give_integral'] = $give_integral;
        $_mil_data['ia_id'] = $ia_id;
        $_mil_data['ias_id'] = $sub_act_id;
        $_mil_data['ia_link_id'] = $ia_link_id;
        $_mil_data['create_time'] = time();
        $_rs = Db::name('mem_integral_log')->insert($_mil_data);
        if (false == $_rs) {
            self::_error(__LINE__."mem_integral_log error".json_encode($_mil_data));

            return false;
        }

        return true;
    }

    public function inviteList($page = 1, $offset = 10) {
        $_map['act_code'] = ITG_TGUSER;
        $_ia_id = Db::name('integral_activity')->where($_map)->value('id');
        $_mi_map['mem_id'] = $this->mem_id;
        $_mi_map['ia_id'] = $_ia_id;
        $_rdata['totalintegral'] = Db::name('mem_itg')->where($_mi_map)->value('give_integral');
        $_ii_map['ii.parent_mem_id'] = $this->mem_id;
        $_rdata['count'] = Db::name('integral_invitdownlog')->alias('ii')->where($_ii_map)->count();
        if ($_rdata['count'] > 0) {
            $_field = [
                'ii.create_time'          => 'gettime',
                'm.username'              => 'subusername',
                'floor(ii.give_integral)' => 'integral'
            ];
            $_join = [
                [
                    Config::get('database.prefix').'members m',
                    'm.id=ii.mem_id',
                    'LEFT'
                ]
            ];
            $_order = " ii.create_time DESC ";
            $_page = $page." , ".$offset;
            $_list = Db::name('integral_invitdownlog')
                       ->alias('ii')
                       ->field($_field)
                       ->join($_join)
                       ->where($_ii_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata['list'] = null;
        }

        return $_rdata;
    }

    /**
     * 获取推广员详细信息
     *
     * @return bool|array
     * myintegral 我的积分
     * totalitg 累计积分奖励
     * usercnt 邀请人数
     * disc    邀请说明
     */
    public function getTgAction() {
        $_rdata['myintegral'] = $this->get();
        $_ia_id = $this->getIdbyCode(ITG_TGUSER);
        if (empty($_ia_id)) {
            return false;
        }
        $_mi_map['ia_id'] = $_ia_id;
        $_mi_map['mem_id'] = $this->mem_id;
        $_rdata['totalitg'] = Db::name('mem_itg')->where($_mi_map)->value('give_integral');
        if (empty($_rdata['totalitg'])) {
            $_rdata['totalitg'] = 0;
        }
        $_rdata['usercnt'] = Db::name('mem_itg')->where($_mi_map)->value('totol_cnt');
        if (empty($_rdata['usercnt'])) {
            $_rdata['usercnt'] = 0;
        }
        $_rdata['disc'] = Db::name('integral_activity')->where('id', $_ia_id)->value('act_desc');
        if (empty($_rdata['disc'])) {
            $_rdata['disc'] = '';
        }

        return $_rdata;
    }

    /**
     * 通过act_code 获取act_id
     *
     * @param $act_code
     *
     * @return bool|mixed
     */
    public function getIdbyCode($act_code) {
        $_map['act_code'] = $act_code;
        $_ia_id = Db::name('integral_activity')->where($_map)->value('id');
        if (empty($_ia_id)) {
            return false;
        }

        return $_ia_id;
    }
}