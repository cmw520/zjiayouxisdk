<?php
/**
 * Driver.php UTF-8
 * 钱包 或 游戏币 处理
 *
 * @date    : 2016年11月16日下午3:13:29
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午3:13:29
 */
namespace huosdk\wallet;

use think\Log;
use think\Db;

class Ptbgm {
    protected static $instance = [];

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'pay\Pay Error:'.$msg;
        Log::record($_info, 'error');
    }

    public static function init(array $config = []) {
        $_config = $config;
        if (empty($_config)) {
            $_config = Config::get('wallet');
        }
    }

    /**
     * 移动APP支付函数
     *
     */
    public function clientPay() {
    }

    /**
     * wap端下单
     *
     */
    public function mobilePay() {
    }

    /**
     * PC端下单
     *
     */
    public function pcPay() {
    }

    /**
     * 钱包充值回调函数
     *
     */
    public function walletNotify() {
    }

    /**
     * 游戏币充值回调
     *
     */
    public function gmNotify() {
    }

    /*
     * 异步回调函数
     */
    public function notifyUrl() {
    }

    /*
     *  返回接收页面
     */
    public function returnUrl() {
    }

    /*
     * SDK预下单
     */
    public function sdkPreorder(array $paydata = array()) {
        $pay_data['app_id'] = I('post.app_id/d', 0);
        $pay_data['agent_id'] = $_SESSION['user']['agent_id'];
        $pay_data['mem_id'] = $_SESSION['user']['id'];
        $pay_data['gm_cnt'] = 0;
        $pay_data['amount'] = I('post.product_price/f', 0);
        $gmmemclass = new \Huosdk\Gmmem($pay_data['app_id']);
        $gm_remain = $gmmemclass->getRemain($pay_data['mem_id']);
        $nogm_cnt = $pay_data['amount']; //去除平台币剩余值
        //游戏币余额与此次充值比较
        if (0 < $gm_remain && $gm_remain <= $pay_data['amount']) {
            $pay_data['gm_cnt'] = $gm_remain;
        } else if ($gm_remain > $pay_data['amount']) {
            $pay_data['gm_cnt'] = $nogm_cnt;
        }
        $nogm_cnt = $pay_data['amount'] - $pay_data['gm_cnt'];
        $pay_data['real_amount'] = 0;
        $pay_data['rebate_cnt'] = 0;
        if ($nogm_cnt > 0) {
            //去除游戏币计算折扣
            if (empty($ratedata)) {
                $rateclass = new \Huosdk\Rate($pay_data['app_id']);
                $ratedata = $rateclass->getMemrate($pay_data['agent_id'], $pay_data['app_id'], $pay_data['mem_id']);
            }
            //续充
            $pay_data['real_amount'] = $nogm_cnt * $ratedata['mem_rate'];
            $pay_data['rebate_cnt'] = $nogm_cnt * $ratedata['mem_rebate'];
        }
        //         $pay_data['real_amount'] = 2;
        $pay_data['real_amount'] = number_format($pay_data['real_amount'], 2, '.', '');
        $pay_data['rebate_cnt'] = number_format($pay_data['rebate_cnt'], 2, '.', '');
        $pay_data['order_id'] = setorderid();
        $pay_data['from'] = I('post.from/d', 0);
        $pay_data['status'] = 1;
        $pay_data['cpstatus'] = 1;
        $pay_data['payway'] = '0';
        $pay_data['create_time'] = time();
        $pay_data['attach'] = I('post.ext/s', '');
        //插入充值表
        $pay_id = M('pay')->add($pay_data);
        if ($pay_id) {
            $payext_data['pay_id'] = $pay_id;
            $payext_data['product_id'] = I('post.product_id/s', '');
            $payext_data['product_name'] = I('post.product_name/s', '');
            $payext_data['product_desc'] = I('post.product_desc/s', '');
            $payext_data['deviceinfo'] = I('post.deviceinfo/s', '');
            $payext_data['userua'] = $_SERVER['HTTP_USER_AGENT'];
            $payext_data['agentgame'] = I('post.agentgame/s', '');
            $payext_data['pay_ip'] = get_client_ip();
            $payext_data['imei'] = I('post.imei/s', '');
            $payext_data['cityid'] = I('post.ipaddrid/d', 0);
            $payext_data['cp_order_id'] = I('post.cp_order_id/s', '');
            $payext_data['product_count'] = I('post.product_count/d', 1);
            $payext_data['exchange_rate'] = I('post.exchange_rate/d', 1);
            $payext_data['currency_name'] = I('post.currency_name/s', '');
            $payext_data['server_id'] = I('post.server_id/s', '');
            $payext_data['server_name'] = I('post.server_name/s', '');
            $payext_data['role_id'] = I('post.role_id/s', '');
            $payext_data['role_name'] = I('post.role_name/s', '');
            $payext_data['party_name'] = I('post.party_name/s', '');
            $payext_data['role_level'] = I('post.role_level/s', '');
            $payext_data['role_vip'] = I('post.role_vip/s', '');
            $payext_data['role_balence'] = I('post.role_balence/s', '');
            M('pay_ext')->add($payext_data);
            $this->setCpparam($pay_data, $payext_data);
            return $pay_data;
        }
        return false;
    }

    protected function setCpparam($pay_data, $payext_data) {
        $param['app_id'] = $pay_data['app_id'];
        $param['cp_order_id'] = $payext_data['cp_order_id'];
        $param['ext'] = $pay_data['attach'];
        $param['mem_id'] = $pay_data['mem_id'];
        $param['order_id'] = $pay_data['order_id'];
        $param['order_status'] = 2;
        $param['pay_time'] = $pay_data['create_time'];
        $param['product_id'] = $payext_data['product_id'];
        $param['product_name'] = $payext_data['product_name'];
        $param['product_price'] = $pay_data['amount'];
        $sortparam = $this->argSort($param);
        $signstr = $this->createLinkstring($sortparam);
        $g_info = M('game')->where(array('id' => $param['app_id']))->find();
        if (empty($g_info['cpurl']) || empty($g_info['app_key'])) {
            return false;
        }
        $sign = md5($signstr."&app_key=".$g_info['app_key']);
        $pc_data['pay_id'] = $payext_data['pay_id'];
        $pc_data['params'] = $signstr."&sign=".$sign;
        $pc_data['cpurl'] = $g_info['cpurl'];
        $pc_data['cp_order_id'] = $param['cp_order_id'];
        $pc_data['status'] = $pay_data['status'];
        $pc_data['cpstatus'] = $pay_data['cpstatus'];
        $pc_data['create_time'] = $pay_data['create_time'];
        $pc_data['update_time'] = 0;
        $pc_data['cnt'] = 0;
        $rs = M('pay_cpinfo')->add($pc_data);
        if ($rs) {
            return true;
        }
        return false;
    }

    // 根据支付方式获取支付方式ID
    public function getPaywayid($payway) {
        if (empty($payway)) {
            return 0;
        }
        $map['payname'] = $payway;
        $pw_id = M('payway')->where($map)->getField('id');
        if (empty($pw_id)) {
            return 0;
        } else {
            return $pw_id;
        }
    }
}