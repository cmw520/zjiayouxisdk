<?php
/**
 * Gm.php UTF-8
 * 游戏币处理函数
 *
 * @date    : 2016年11月16日下午5:02:28
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午5:02:28
 */
namespace huosdk\wallet;

use huosdk\common\Commonfunc;
use huosdk\game\Game;
use think\Db;
use think\Log;
use think\Session;

class Gm {
    private $rate;

    /**
     *
     * 自定义错误处理
     *
     * @param        $msg   输出的文件
     * @param string $level 输出级别
     */
    private function _error($msg, $level = 'error') {
        $_info = 'pay\Gm Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构建函数
     *
     * @param float $rate 比例
     *
     */
    public function __construct($rate) {
        $this->rate = $rate;
    }

    /**
     * 游戏消费
     *
     * @access public
     *
     * @param array $paydata 充值数据
     * @param int   $appid   游戏ID
     *
     * @return false|int
     */
    public function pay(array $paydata, $appid = 0) {
        $_gm_cnt = isset($paydata['gm_cnt']) ? $paydata['gm_cnt'] : 0;
        $_status = isset($paydata['status']) ? $paydata['status'] : 0;
        // 扣除游戏币余额
        if ($_gm_cnt <= 0 || 2 != $_status) {
            return false;
        }
        if (empty($appid)) {
            $appid = isset($paydata['app_id']) ? $paydata['app_id'] : '0';
        }
        // 插入消费记录
        $_gmp_data['order_id'] = isset($paydata['order_id']) ? $paydata['order_id'] : '0';
        $_gmp_data['mem_id'] = isset($paydata['mem_id']) ? $paydata['mem_id'] : '0';
        $_gmp_data['agent_id'] = isset($paydata['agent_id']) ? $paydata['agent_id'] : '0';
        $_gmp_data['app_id'] = $appid;
        $_gmp_data['amount'] = isset($paydata['amount']) ? $paydata['amount'] : '0';
        $_gmp_data['gm_cnt'] = isset($paydata['gm_cnt']) ? $paydata['gm_cnt'] : '0';
        $_gmp_data['from'] = isset($paydata['from']) ? $paydata['from'] : '0';
        $_gmp_data['status'] = 2;
        $_gmp_data['create_time'] = isset($paydata['create_time']) ? $paydata['create_time'] : '0';
        $_gmp_data['update_time'] = isset($paydata['update_time']) ? $paydata['update_time'] : '0';
        try {
            $_rs = Db::name('gm_pay')->insert($_gmp_data);
            if ($_rs) {
                $_map['mem_id'] = $_gmp_data['mem_id'];
                $_map['app_id'] = $_gmp_data['app_id'];
                $_gm_info = Db::name('gm_mem')->where($_map)->find();
                if ($_gm_info) {
                    $_gm_info['remain'] -= $_gm_cnt;
                    if ($_gm_info['remain'] < 0) {
                        $this->_error('金额错误:'.json_encode($paydata).'error');
                    } else {
                        Db::name('gm_mem')->update($_gm_info);
                    }
                } else {
                    $this->_error('无游戏币,金额错误:'.json_encode($paydata).'error');
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->_error('游戏币插入失败 错误信息:'.$e->getMessage().'error');

            return false;
        }
    }

    /**
     * SDK充值返利
     *
     * @access public
     *
     * @param array $paydata 充值数据
     * @param int   $appid   游戏ID
     *
     * @return false|int
     */
    public function rebate(array $paydata, $appid = 0) {
        $_rebate_cnt = isset($paydata['rebate_cnt']) ? $paydata['rebate_cnt'] : 0;
        $_status = isset($paydata['status']) ? $paydata['status'] : 0;
        // 扣除游戏币余额
        if ($_rebate_cnt <= 0 || 2 != $_status) {
            return true;
        }
        if (empty($appid)) {
            $appid = isset($paydata['app_id']) ? $paydata['app_id'] : 0;
        }
        // 插入消费记录
        $_gmp_data['order_id'] = isset($paydata['order_id']) ? $paydata['order_id'] : '0';
        $_gmp_data['flag'] = PAYFROM_SDKREBATE;
        $_gmp_data['admin_id'] = 0;
        $_gmp_data['app_id'] = $appid;
        $_gmp_data['mem_id'] = isset($paydata['mem_id']) ? $paydata['mem_id'] : '0';
        $_gmp_data['money'] = isset($paydata['amount']) ? $paydata['amount'] : '0';
        $_gmp_data['gm_cnt'] = $_rebate_cnt;
        $_gmp_data['rebate_cnt'] = $_rebate_cnt;
        $_gmp_data['discount'] = 1;
        $_gmp_data['payway'] = isset($paydata['payway']) ? $paydata['payway'] : '0';
        $_gmp_data['ip'] = '';
        $_gmp_data['status'] = PAYSTATUS_SUCCESS;
        $_gmp_data['create_time'] = isset($paydata['crate_time']) ? $paydata['payway'] : '0';;
        $_gmp_data['real_amount'] = isset($paydata['real_amount']) ? $paydata['real_amount'] : '0';
        $_gmp_data['create_time'] = isset($paydata['create_time']) ? $paydata['create_time'] : '0';
        $_gmp_data['update_time'] = isset($paydata['update_time']) ? $paydata['update_time'] : '0';
        $_gmp_data['remark'] = '充值返利';
        try {
            $_rs = Db::name('gm_charge')->insert($_gmp_data);
            if ($_rs) {
                $_map['mem_id'] = $paydata['mem_id'];
                $_map['app_id'] = $appid;
                $_gm_info = Db::name('gm_mem')->where($_map)->find();
                if ($_gm_info) {
                    $_gm_info['total'] += $_rebate_cnt;
                    $_gm_info['remain'] += $_rebate_cnt;
                    $_gm_info['update_time'] = time();
                    $_rs = Db::name('gm_mem')->where($_map)->update($_gm_info);
                } else {
                    $_gm_info['mem_id'] = $_map['mem_id'];
                    $_gm_info['app_id'] = $_map['app_id'];
                    $_gm_info['total'] = $_rebate_cnt;
                    $_gm_info['remain'] = $_rebate_cnt;
                    $_gm_info['create_time'] = time();
                    $_gm_info['update_time'] = $_gm_info['create_time'];
                    Db::name('gm_mem')->insert($_gm_info);
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->_error('游戏币充值 错误信息:'.$e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * 获取币余额
     *
     * @access public
     *
     * @param INT $mem_id 玩家ID
     * @param INT $appid  游戏ID
     *
     * @return double 余额
     */
    public function getRemain($mem_id, $appid = 0) {
        if (empty($mem_id) || empty($appid)) {
            return 0;
        }
        $_map['mem_id'] = $mem_id;
        $_map['app_id'] = $appid;
        $_remain = Db::name('gm_mem')->where($_map)->value('remain');
        if (empty($_remain)) {
            $_remain = 0;
        }

        return $_remain;
    }

    /**
     * 有游戏币余额的游戏数量
     *
     * @param int $mem_id 玩家ID
     *
     * @return int
     */
    public function getCnt($mem_id) {
        if (empty($mem_id)) {
            return 0;
        }
        $_map['remain'] = ['>', 0];
        $_map['mem_id'] = $mem_id;
        $_count = Db::name('gm_mem')->where($_map)->count();
        if (empty($_count)) {
            return 0;
        }

        return $_count;
    }

    /**
     * @param     $mem_id
     * @param int $app_id
     * @param int $page
     * @param int $offset
     *
     * @return null
     */
    public function getMemlist($mem_id, $app_id = 0, $page = 1, $offset = 10) {
        if (empty($mem_id)) {
            return null;
        }
        if (!empty($app_id)) {
            $_gm_map['app_id'] = $app_id;
            $_map['app_id'] = $app_id;
        }
        $_gm_map['remain'] = ['>', 0];
        $_gm_map['mem_id'] = $mem_id;
        $_list = Db::name('gm_mem')->where($_gm_map)->column('remain gmcnt', 'app_id');
        if (empty($_list)) {
            return null;
        }
        $_map['appstr'] = '';
        foreach ($_list as $_k => $v) {
            $_map['appstr'] .= $_k.',';
        }
        $_map['appstr'] = substr($_map['appstr'], 0, -1);
        $_map['page'] = $page;
        $_map['offset'] = $offset;
        $_game_class = new Game();
        $_game_list = $_game_class->getGameList($_map);
        $_rdata = $_game_list;
        if (empty($_rdata)) {
            return null;
        }
        foreach ($_rdata['list'] as $_k => $_v) {
            $_rdata['list'][$_k]['gmcnt'] = $_list[$_v['gameid']];
        }
        if (empty($_rdata)) {
            return null;
        }

        return $_rdata;
    }

    public function getChargeOrderinfo($pay_id) {
        $_map['id'] = $pay_id;
        $_order_info = Db::name('gm_charge')->where($_map)->find();
        if (empty($_order_info)) {
            return false;
        }

        return $_order_info;
    }

    public function getChargeCnt($pay_id) {
        $_map['id'] = $pay_id;
        $_map['status'] = 2;
        $_mem_id = Db::name('gm_charge')->where($_map)->value('mem_id');
        $_cnt_map['mem_id'] = $_mem_id;
        $_cnt = Db::name('gm_charge')->where($_cnt_map)->count();
        if (empty($_cnt)) {
            return 0;
        }

        return $_cnt;
    }

    public function insertPay(array $order_data) {
        // 插入充值表
        $_pay_id = Db::name('gm_charge')->insertGetid($order_data);

        return $_pay_id;
    }

    /**
     * @param int $from 充值来源
     *
     * @return int
     * 1 官网充值  2 浮点充值 3 sdk充值游戏 4 app充值游戏  5代理发放    6 7881充值 7 充值返利 8 官方发放
     */
    protected function getFlag($from = 0) {
        if (!empty($from)) {
            return $from;
        }
        $_app_id = Session::get('app_id', 'app');
        if (empty($_app_id)) {
            return PAYFROM_WEB; //1 官网充值
        }
        $_in_app = Commonfunc::isApp($_app_id);
        if ($_in_app) {
            return PAYFROM_APP; //4 app充值游戏
        }

        return PAYFROM_FLOAT;
    }

    /**
     * 组建订单数据
     *
     * @param string $payfrom 充值来源
     *
     * @return array
     *
     */
    private function buildOrderdata($payfrom) {
        $_order_data['mem_id'] = Session::get('id', 'user');
        $_order_data['order_id'] = Commonfunc::setOrderid($_order_data['mem_id']);
        $_order_data['flag'] = $this->getFlag($payfrom);
        $_order_data['admin_id'] = 0;
        $_order_data['app_id'] = Session::get('game_id', 'app');
        $_order_data['money'] = Session::get('product_price', 'order');
        $_order_data['gm_cnt'] = Session::get('gm_cnt', 'order');
        $_order_data['real_amount'] = Session::get('real_amount', 'order');
        $_order_data['rebate_cnt'] = 0;
        $_order_data['discount'] = 1;
        $_order_data['payway'] = '0';
        $_order_data['ip'] = Session::get('ip', 'device');
        $_order_data['status'] = 1;
        $_order_data['create_time'] = time();

        return $_order_data;
    }

    /**
     * SDK预下单
     * http://doc.1tsdk.com/43?page_id=689
     *
     * orderinfo.money    是    FLOAT    玩家充值游戏币金额;建议传入整数,可以保留两位小数
     * orderinfo.real_money    是    FLOAT    玩家实际支付金额
     * orderinfo.gamemoney    是    FLOAT    玩家可获得的游戏币
     * orderinfo.integral    是    FLOAT    玩家可获得的积分
     * orderinfo.couponcontent    是    STRING    代金卷闲情 ID:CNT,ID:CNT
     *
     * @param string $payfrom 充值来源
     * @param string $ext     来源扩展
     *
     * @return bool
     *
     */
    public function preorder($payfrom, $ext) {
        // 组建订单数据
        $_order_data = $this->buildOrderdata($payfrom);
        Session::set('order_id', $_order_data['order_id'], 'order');
        $_pay_id = $this->insertPay($_order_data);
        if ($_pay_id) {
            if (PAYFROM_APP == $payfrom) {
                $this->setCouponlog($_order_data, $ext);
            }

            return true;
        }

        return false;
    }

    public function setCouponlog($_order_data, $ext) {
        $_data['order_id'] = $_order_data['order_id'];
        $_data['mem_id'] = $_order_data['mem_id'];
        $_data['couponcontent'] = $ext;
        $_data['status'] = 1;
        $_data['create_time'] = time();
        $_rs = Db::name('coupon_pay_log')->insert($_data);
        if (false == $_rs) {
            return false;
        }

        return true;
    }

    /**
     * 更新支付方式
     *
     * @param string $order_id 订单号
     * @param        $payway   支付代号
     *
     * @return bool
     */
    public function upPayway($order_id, $payway) {
        if (empty($payway) || empty($order_id)) {
            return false;
        }
        $_map['order_id'] = $order_id;
        $_rs = Db::name('gm_charge')->where($_map)->setField('payway', $payway);
        if (false === $_rs) {
            return false;
        } else {
            return true;
        }
    }

    public function walletNotify($order_id, $amount, $paymark = '') {
        $pay_model = Db::name("gm_charge");
        // 获取支付表中的支付信息
        $_map['order_id'] = $order_id;
        $pay_data = $pay_model->where($_map)->find();
        $myamount = number_format($pay_data['real_amount'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        //判断金额是否正确
        if (($myamount == $amount) && 2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->update($pay_data);
            if (false !== $rs) {
                $this->doAfterPay($pay_data);
            } else {
                return false;
            }
        }

        return true;
    }

    private function doAfterPay($pay_data) {
        /* 1更新钱包余额 */
        $this->updateWallet($pay_data);
        /* 更新app充值金额 */
        $_mi_class = new \huosdk\player\Meminfo($pay_data['mem_id']);
        $_mi_class->setAppmoney($pay_data['real_amount']);
        /* 2 充值送积分 */
        //表示第一次上传, 获取积分
        /* BEGIN 获取积分 ITG_UPPORTRAIT */
        $_mitg_class = new \huosdk\integral\Memitg($pay_data['mem_id']);
        $_mitg_class->addbyAction(ITG_CHARGE, $pay_data['id']);
        /* END 获取积分 ITG_UPPORTRAIT */
        /* 如果消耗了代金卷 需对代金卷操作 */
        $this->setCoupon($pay_data);
    }

    private function setCoupon($pay_data) {
        if (PAYFROM_APP != $pay_data['flag']) {
            return true;
        }
        $_map['order_id'] = $pay_data['order_id'];
        $_cpl_data = Db::name('coupon_pay_log')->where($_map)->find();
        if (empty($_cpl_data)) {
            return true;
        }
        if (2 != $_cpl_data['status']) {
            Db::name('coupon_pay_log')->where($_map)->setField('status', 2);
            $_coupon_class = new \huosdk\coupon\Coupon();
            $_c_money = $_coupon_class->getMoneybyString($pay_data['mem_id'], $_cpl_data['couponcontent']);
            if (empty($_c_money)) {
                return true;
            }

            return $_coupon_class->setMoneybyString($pay_data['mem_id'], $_cpl_data['couponcontent']);
        }

        return true;
    }

    /**
     * 更新钱包余额
     *
     * @param $paydata
     */
    private function updateWallet($paydata) {
        $pm_model = Db::name('gm_mem');
        $map['app_id'] = $paydata['app_id'];
        $map['mem_id'] = $paydata['mem_id'];
        $pm_data = $pm_model->where($map)->find();
        if (empty($pm_data)) {
            $pm_data['mem_id'] = $paydata['mem_id'];
            $pm_data['app_id'] = $paydata['app_id'];
            $pm_data['sum_money'] = $paydata['money'];
            $pm_data['total'] = $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['remain'] = $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['create_time'] = time();
            $pm_data['update_time'] = $pm_data['create_time'];
            $pm_model->insert($pm_data);
        } else {
            $pm_data['sum_money'] += $paydata['money'];
            $pm_data['total'] += $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['remain'] += $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['update_time'] = time();
            $pm_model->update($pm_data);
        }
    }
}