<?php
/**
 * SdkpayController.class.php UTF-8
 * SDK支付页面，所有支付在此
 *
 * @date    : 2016年6月27日下午4:20:46
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 */
namespace Pay\Controller;

use Common\Controller\MobilebaseController;

class WalletpayController extends MobilebaseController {
    protected $pc_model;

    function _initialize() {
        parent::_initialize();
        $this->pc_model = M('ptb_charge');
    }

    // 获取支付方式
    function _payway($appid = null) {
        $paywaydata = M('payway')->where('status=2')->getField('payname payway,disc');
        return $paywaydata;
    }

    /**
     * 支付页面，选择支付
     *
     * @date  : 2016年6月27日下午4:21:52
     *
     * @param arg 参数一的说明
     *
     * @return array
     * @since 1.0
     */
    public function index() {
        // 获取玩家钱包信息
        // // $pay_token = I('pay_token');
        // // if (isset($_SESSION['pay_token'])){
        // // //支付点击进入 必须校验pay_token
        // // if(!empty($pay_token)){
        // // //验证是否有效客户
        // // if ($pay_token != $_SESSION['pay_token']){
        // // $this->error('玩家未登陆,或支付失效!');
        // // }
        // // $param_token = md5(md5($pay_token.'float').$_SESSION['clientkey']);
        // // $p_token = I('param_token');
        // // if ($param_token != $p_token){
        // // $this->error('玩家未登陆,或支付失效!');
        // // }
        // // }
        // // }else{
        // // $this->error('玩家未登陆,或支付失效!');
        // // }
        // // if (empty($_SESSION['order_id']) || empty($_SESSION['app_id'])){
        // // $this->error('参数错误');
        // // }
        // // $order_id = $_SESSION['order_id'];
        // // $app_id = $_SESSION['app_id'];
        // // $mem_id = $_SESSION['mem_id'];
        // // $pay_token = $_SESSION['pay_token'];
        // $mem_id = "24557";
        // $order_id = '1465922599287524640';
        // $pay_token = '123123123';
        // $app_id = 1;
        // $_SESSION['order_id'] = $order_id;
        // //获取支付方式
        // $paywaydata = $this->_payway($app_id);
        // //根据保留的订单号，获取订单信息
        // $paydata = $this->p_model->where(array('order_id'=>$order_id))->find();
        // if ($paydata['mem_id'] != $mem_id){
        // $this->error('参数错误');
        // }
        // if ($paydata['app_id'] != $app_id){
        // $this->error('参数错误');
        // }
        // if ($paydata['id']){
        // //根据保留的订单号，获取订单扩展信息
        // $payextdata = M('pay_ext')->where(array('pay_id'=>$paydata['id']))->find();
        // }
        // //钱包余额
        // $ptb_remain = M('ptb_mem')->where(array('mem_id'=>$mem_id))->getField('remain');
        // if (empty($ptb_remain)){
        // $ptb_remain = 0;
        // }
        // $ptb_remain = $ptb_remain/10;
        // $this->assign("paytoken", $pay_token);
        // $this->assign("remain", $ptb_remain);
        // $this->assign("payways", $paywaydata);
        // $this->assign("paydata", $paydata);
        // $this->assign("payextdata", $payextdata);
        $this->display();
    }

    /**
     * 支付post,需校验一些参数
     * 函数的含义说明
     *
     * @date  : 2016年6月27日下午4:32:05
     *
     * @param arg 参数一的说明
     *
     * @return array
     * @since 1.0
     */
    public function preorder() {
        $rand = I('post.randnum/d', 0);
        $pay_token = I('post.paytoken/s', '');
        $payway = I('post.paytype/s', '');
        $amount = I('post.money/d', 0);
        $gameid = I('post.gameid/d', 0);
        if ($amount <= 0) {
            $this->error("充值金额不正确，请输入整数");
        }
        if ($gameid <= 0) {
            $this->error("请选择充值游戏");
        }
        if (empty($payway)) {
            $this->error("请选择充值方式");
        }
        // 通过支付方式获取支付编号
        $pay_controller = new \Common\Controller\PaybaseController();
        $pw_id = $pay_controller->getPaywayid($payway);
        if (empty($pw_id)) {
            $this->error("请选择充值方式");
        }
        if (empty($_SESSION['paytoken']) || strcmp($pay_token, $_SESSION['paytoken']) !== 0) {
            $this->error("非法参数");
        }
        $data = $this->_wallet_pay($amount, $pw_id, $gameid);
        switch ($pw_id) {
            case 1 : {
                break;
            }
            case 2 : {
                break;
            }
            case 3 : {
                $rdata['orderid'] = $data['order_id'];
                $rdata['amount'] = $data['real_amount'];
                //$rdata['amount'] = 0.05;
                $rdata['productname'] = "游戏币充值";
                $rdata['productdesc'] = "游戏币充值";
                $rdata['notify_url'] = SDKSITE.U("Pay/Alipay/wallet_notify");
                return $rdata;
            }
            case 4 : {
                break;
            }
            case 5 : {
                break;
            }
            case 8 : {
                /* 微付通支付 */
                $spay_controller = new SpayController();
                $rdata = $spay_controller->pay($data);
//                     $rdata['orderid'] = $data['money'];
//                     $rdata['amount'] = $data['order_id'];
//                     $rdata['productname'] = C('CURRENCY_NAME') . "充值";
//                     $rdata['productdesc'] = C('CURRENCY_NAME') . "充值";
//                     $rdata['notify_url'] = SDKSITE . U("Pay/Alipay/alipay_walletnotify");
                break;
            }
            default : {
                $this->error("请选择充值方式");
            }
        }
    }

    private function _wallet_pay($amount, $pw_id, $gameid) {
        if (empty($amount)) {
            $this->error("充值金额不正确，请输入整数");
        }
        if ($pw_id <= 0) {
            $this->error("请选择充值方式");
        }
        $payway = M("payway")->where(array("id" => $pw_id))->getField("payname");
        if ($gameid <= 0) {
            $this->error("请选择充值游戏");
        }
        //获取用户agentid
        $userid = $_SESSION['user']['id'];
        $agentid = M("members")->where(array("id" => $userid))->getField("agent_id");
        $ratedata = $this->rate_select($agentid, $gameid);
        if ($ratedata['buy_type'] == 1) {
            $rebate_rate = 0;
            $discount = $ratedata['rate'];
        } elseif ($ratedata['buy_type'] == 2) {
            $rebate_rate = $ratedata['rate'];
            $discount = 1;
        } else {
            $rebate_rate = 0;
            $discount = 1;
        }
        $data['order_id'] = setorderid();
        $data['flag'] = 4;
        $data['admin_id'] = 0;
        $data['app_id'] = $gameid;
        $data['mem_id'] = $_SESSION['user']['id'];
        $data['money'] = $amount;
        $data['gm_cnt'] = $amount;
        $data['rebate_cnt'] = $amount * $rebate_rate;
        $data['real_amount'] = $amount * $discount;
        $real_cnt = $data['gm_cnt'] + $data['rebate_cnt'];
        $data['discount'] = $data['real_amount'] / $real_cnt;
        $data['payway'] = $payway;
        $data['ip'] = get_client_ip();
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['remark'] = "APP充值";
        $rs = M('gm_charge')->add($data);
        if (!$rs) {
            $this->error("内部服务器发生错误");
        }
        return $data;
    }

    //充值还是返利
    function rate_select($agentid, $appid) {
        if (C('G_DISCONT_TYPE') == 0) {
            $data['buy_type'] = 0;
            $data['rate'] = 0;
            return $data;
        }
        $mem_id = $_SESSION['user']['id'];
        if ($appid <= 0) {
            $this->error("请选择充值游戏");
        }
        //判断是否返利，$benefit_type为2为返利
        $benefitdata = M("agent_game_rate")->where(array("agent_id" => $agentid, "app_id" => $appid))->field(
            "benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate"
        )->find();
        if (empty($benefitdata) && C("DEFAULT_SET")) {
            $benefitdata = M("game_rate")->where(array("app_id" => $appid))->field(
                "benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate"
            )->find();
        }
        //首冲跟续冲
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $appid;
        $map['status'] = 2;
        //flag为4代表APP充值
        $map['flag'] = 4;
        $map['payway'] = array("neq", "0");
        $checkfirst = M("gm_charge")->where($map)->getField("id");
        //is_first为1代表首冲
        if ($checkfirst > 0 || C('G_FIRST_EN') == 0) {
            $data['is_first'] = 0;
        } else {
            $data['is_first'] = 1;
        }
        $data['buy_type'] = 0;
        //benefit_type为1折扣，2为返利
        if ($benefitdata['benefit_type'] == 2) {
            $data['rate'] = 0;
            if ($data['is_first'] == 0) {
                $data['rate'] = !empty($benefitdata['mem_rebate']) ? $benefitdata['mem_rebate'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['first_mem_rebate']) ? $benefitdata['first_mem_rebate'] : 0;
            }
            $data['buy_type'] = 2;
        } else if ($benefitdata['benefit_type'] == 1) {
            $data['rate'] = 1;
            if ($data['is_first'] == 0) {
                $data['rate'] = !empty($benefitdata['mem_rate']) ? $benefitdata['mem_rate'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['first_mem_rate']) ? $benefitdata['first_mem_rate'] : 0;
            }
            $data['buy_type'] = 1;
        }
        return $data;
    }
}