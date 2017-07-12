<?php
/**
 * Pay.class.php UTF-8
 * 支付类
 *
 * @date    : 2016年10月11日下午11:16:07
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : huosdk 7.0
 */
namespace Huosdk;
class Pay {
    public function __construct() {
    }

    public function preorder($ratedata = array()) {
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

    /*
     * 钱包支付通知函数
     */
    protected function wallet_post($orderid, $amount, $paymark = '') {
        $pay_model = M("ptb_charge");
        // 获取支付表中的支付信息
        $pay_data = $pay_model->where(
            array(
                'order_id' => $orderid
            )
        )->find();
        $myamount = number_format($pay_data['money'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        //判断金额是否正确
        if (($myamount == $amount) && 2 != $pay_data['status']) {
            //         if (2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->save($pay_data);
            if ($rs) {
                $this->updateWallet($pay_data);
            }
        }
    }

    private function updateWallet($paydata) {
        $pm_model = M('ptb_mem');
        $map['mem_id'] = $paydata['mem_id'];
        $pm_data = $pm_model->where($map)->find();
        if (empty($pm_data)) {
            $pm_data['mem_id'] = $paydata['mem_id'];
            $pm_data['sum_money'] = $paydata['money'];
            $pm_data['total'] = $paydata['ptb_cnt'];
            $pm_data['remain'] = $paydata['ptb_cnt'];
            $pm_data['create_time'] = time();
            $pm_data['update_time'] = $pm_data['create_time'];
            $pm_model->add($pm_data);
        } else {
            $pm_data['sum_money'] += $paydata['money'];
            $pm_data['total'] += $paydata['ptb_cnt'];
            $pm_data['remain'] += $paydata['ptb_cnt'];
            $pm_data['update_time'] = time();
            $pm_model->save($pm_data);
        }
    }

    public function getMemPaycnt($mem_id, $app_id) {
        if (empty($mem_id) || empty($app_id)) {
            return 0;
        }
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $app_id;
        $cnt = M('pay')->where($map)->count('id');
        if (empty($cnt)) {
            $cnt = 0;
        }
        return $cnt;
    }

    /**
     * 更新订单支付方式
     *
     * @date  : 2016年10月12日下午3:26:02
     *
     * @param orderid 平台订单号
     * @param payway  支付方式
     *
     * @return false 更新失败  ture 更新成功
     * @since 1.0
     */
    public function updataPayway($orderid, $payway) {
        if (empty($orderid) || empty($payway)) {
            return false;
        }
        //查询订单是否存在
        $map['order_id'] = $orderid;
        $order_info = M('pay')->where($map)->find();
        if (empty($order_info)) {
            return false;
        }
        if ($payway == $order_info['payway']) {
            return true;
        }
        $rs = M('pay')->where($map)->setField('payway', $payway);
        if (false !== $rs) {
            return true;
        }
        return false;
    }

    public function getPayinfo($order_id) {
        if (empty($order_id)) {
            return array();
        }
        $map['order_id'] = $order_id;
        $pay_info = M('pay')->where($map)->find();
        return $pay_info;
    }

    public function getPayextinfo($pay_id) {
        if (empty($pay_id)) {
            return array();
        }
        $map['pay_id'] = $pay_id;
        $payext_info = M('pay_ext')->where($map)->find();
        return $payext_info;
    }

    public function notify($order_id, $amount, $trade_id) {
    }

    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    function createLinkstring($para) {
        $arg = "";
        while (list($key, $val) = each($para)) {
            $arg .= $key."=".urlencode($val)."&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    protected function sdk_post($orderid, $amount, $paymark = '') {
        $pay_model = M("pay");
        // 获取支付表中的支付信息
        $pay_data = $pay_model->where(
            array(
                'order_id' => $orderid
            )
        )->find();
        $myamount = number_format($pay_data['real_amount'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        // 判断充值金额与回调中是否一致，且状态不为2，即待支付状态
        if (($myamount == $amount) && 2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->save($pay_data);
            // 判断订单信息是否修改成功
            if ($rs) {
                $this->updateMeminfo($pay_data);
                $paycp_info = M('pay_cpinfo')->where(array('pay_id' => $pay_data['id']))->find();
                $paycp_info['status'] = 2;
                // 2.2.1 查询CP回调地址与APPKEY
//                 $game_data = $this->getGameinfobyid($pay_data['app_id']);
//                 $cpurl = $game_data['cpurl'];
//                 $app_key = $game_data['app_key'];
//                 $param['order_id'] = (string) $pay_data['order_id'];
//                 $param['mem_id'] = (string) $pay_data['mem_id'];
//                 $param['app_id'] = (string) $pay_data['app_id'];
//                 $param['money'] = (string) $myamount;
//                 $param['order_status'] = '2';
//                 $param['paytime'] = (string) $pay_data['create_time'];
//                 $param['attach'] = (string) $pay_data['attach'];
//                 $signstr = "order_id=" .
//                         $pay_data['order_id'] . "&mem_id=" . $pay_data['mem_id'] . "&app_id=" . $pay_data['app_id'];
//                 $signstr .= "&money=" .
//                         $pay_data . "&order_status=2&paytime=" . $pay_data['create_time'] . "&attach=" .
//                         $pay_data['attach'];
//                 $md5str = $signstr . "&app_key=" . $app_key;
//                 $sign = md5($md5str);
//                 $param['sign'] = (string) $sign;
                $cpurl = $paycp_info['cpurl'];
                $param = $paycp_info['params'];
                // 2.2.3 通知CP
                if ($pay_data['cpstatus'] == 1 || $pay_data['cpstatus'] == 3) {
                    $i = 0;
                    while (1) {
                        $cp_rs = \Huosdk\CommonFunc::payback($cpurl, $param);
                        if ($cp_rs > 0) {
                            $cpstatus = 2;
                            $paycp_info['cpstatus'] = 2;
                            break;
                        } else {
                            $cpstatus = 3;
                            $paycp_info['cpstatus'] = 3;
                            $i++;
                            sleep(2);
                        }
                        if ($i == 3) {
                            break;
                        }
                    }
                }
                // 更新CP状态
                $pay_model->where(array('id' => $pay_data['id']))->setField('cpstatus', $cpstatus);
                //更新cp回调
                M('pay_cpinfo')->save($paycp_info);
            }
        }
    }

    /*
     * 更新用户扩展支付信息
     */
    private function updateMeminfo(array $paydata) {
        $me_model = M('mem_ext');
        $map['mem_id'] = $paydata['mem_id'];
        $ext_data = $me_model->where($map)->find();
        $ext_data['order_cnt'] += 1;
        $ext_data['sum_money'] += $paydata['amount'];
        $ext_data['last_pay_time'] = $paydata['create_time'];
        $ext_data['last_money'] = $paydata['amount'];
        $me_model->save($ext_data);
    }

    /*
     * 获取单个游戏信息
     */
    private function getGameinfobyid($appid) {
        if ($appid <= 0 || empty($appid)) {
            return array();
        }
        $map['id'] = $appid;
        $game_data = M('game')->where($map)->find();
        if (empty($game_data)) {
            return array();
        }
        return $game_data;
    }
}

