<?php
/**
 * SdkpayController.class.php UTF-8
 * 游戏内SDK充值
 *
 * @date    : 2016年7月29日下午8:50:14
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Pay\Controller;

use Common\Controller\MobilebaseController;

class SdkpayController extends MobilebaseController {
    /**
     * SDK下单函数
     * 函数的含义说明
     *
     * @date  : 2016年10月11日下午9:31:52
     *
     * @param arg 参数一的说明
     *
     * @return array
     * @since 7.0
     */
    public function index() {
        $rs = $this->signVeryfy();
        if ($rs != true) {
            $this->error('参数校验不通过1');
        }
        $app_id = I('post.app_id/d', 0);
        $agent_id = $_SESSION['user']['agent_id'];
        $mem_id = $_SESSION['mem_id'];
        $rateclass = new \Huosdk\Rate($app_id);
        $agr_data = $rateclass->getMemrate($agent_id, $app_id, $mem_id, 3);
        $this->assign('ratedata', $agr_data);
        // 插入支付数据
        $payclass = new \Huosdk\Pay();
        $pay_data = $payclass->preorder($agr_data);
        if (false == $pay_data) {
            $this->error("参数错误");
        }
        $this->assign('paydata', $pay_data);
        $this->assign('productname', $_POST['product_name']);
        $_SESSION['paytoken'] = md5(sp_random_string(10));
        $this->assign('paytoken', $_SESSION['paytoken']);
        $this->assign('title', "充值中心");
        $gmmem_class = new \Huosdk\Gmmem($app_id);
        $mem_id = get_current_userid();
        $this->assign('gmremain', $gmmem_class->getRemain($mem_id));
        $this->display();
    }

    private function signVeryfy() {
        $checkdata['agentgame'] = I('post.agentgame/s', '');
        $checkdata['app_id'] = I('post.app_id', 0);
        $checkdata['client_id'] = I('post.client_id', 0);
        $checkdata['cp_order_id'] = I('post.cp_order_id', '');
        $checkdata['from'] = I('post.from', 3);
        $checkdata['imei'] = I('post.imei', '');
        $checkdata['ipaddrid'] = I('post.ipaddrid/d', 0);
        $checkdata['param_token'] = I('post.param_token', '');
        $checkdata['party_name'] = I('post.party_name', '');
        $checkdata['product_count'] = I('post.product_count', 1);
        $checkdata['product_id'] = I('post.product_id', '');
        $checkdata['product_name'] = I('post.product_name', '');
        $checkdata['product_price'] = I('post.product_price', '');
        $checkdata['role_balence'] = I('post.role_balence', '');
        $checkdata['role_id'] = I('post.role_id', '');
        $checkdata['role_level'] = I('post.role_level', '');
        $checkdata['role_name'] = I('post.role_name', '');
        $checkdata['role_vip'] = I('post.role_vip', '');
        $checkdata['server_id'] = I('post.server_id', '');
        $checkdata['server_name'] = I('post.server_name', '');
        $checkdata['session_id'] = I('post.session_id', '');
        $checkdata['user_token'] = I('post.user_token', '');
        $sign = I('post.sign/s', '');
        if (empty($checkdata['agentgame']) || empty($checkdata['app_id']) || empty($checkdata['client_id'])
            || empty($checkdata['cp_order_id'])
            || empty($checkdata['from'])
            || empty($checkdata['imei'])
            || empty($checkdata['param_token'])
            || empty($checkdata['product_count'])
            || empty($checkdata['product_id'])
            || empty($checkdata['product_name'])
            || empty($checkdata['product_price'])
            || empty($checkdata['role_balence'])
            || empty($checkdata['role_id'])
            || empty($checkdata['role_name'])
            || empty($checkdata['server_id'])
            || empty($checkdata['server_name'])
            || empty($checkdata['session_id'])
            || empty($checkdata['user_token'])
        ) {
            $this->error("参数错误");
        }
        $checkdata['product_price'] = number_format($checkdata['product_price'], 2, '.', '');
        $para_sort = $this->argSort($checkdata);
        $params = $this->createLinkstring($para_sort);
        $client_key = M('game_client')->where(
            array(
                'id' => $checkdata['client_id']
            )
        )->getField('client_key');
        return $this->verifySign($params, $sign, $client_key);
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

    function verifySign($prestr, $sign, $key) {
        $param = $prestr.'float'.$key;
        $mysign = md5($param);
        if ($mysign == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * SDK下单支付函数
     *
     * @date  : 2016年10月11日下午9:30:53
     *
     * @param arg 参数一的说明
     *
     * @return array
     * @since 1.0
     */
    public function preorder() {
        $order_id = I('post.orderid/s', '');
        $pay_token = I('post.paytoken/s', '');
        $payway = I('post.paytype/s', '');
        $random = I('post.v/s', '');
        if (empty($random)) {
            $data['info'] = "非法请求,重复请求";
            $this->ajaxReturn($data);
        }
        if ($_SESSION['pay_random'] == $random) {
            $data['info'] = "重复请求";
            $this->ajaxReturn($data);
        }
        $_SESSION['pay_random'] = $random;
        if (empty($order_id) || empty($pay_token) || $pay_token != $_SESSION['paytoken']) {
            $data['info'] = "非法请求,参数错误";
            $this->ajaxReturn($data);
        }
        $payclass = new \Huosdk\Pay();
        $pay_info = $payclass->getPayinfo($order_id);
        if (empty($pay_info)) {
            $data['info'] = "订单参数错误";
            $this->ajaxReturn($data);
        }
        //扣除平台币余额
        if ($pay_info['gm_cnt'] > 0) {
        }
        //实际支付金额少于等于0, 直接回调
        if (0 >= $pay_info['real_amount']) {
            //扣除平台币余额
            $gmmem_class = new \Huosdk\Gmmem($pay_info['app_id']);
            // $gmmem_class = setDec($mem_id, $gm_cnt);
            //更新支付方式
            $payclass->updataPayway($order_id, 'gamepay');
            //回调
            $payclass->notify($order_id, $pay_info['amount'], $order_id);
            $rdata['status'] = 1;
            $gr_data['paytype'] = 'gamepay';
            $gr_data['orderid'] = $order_id;
            $gr_data['status'] = 2;
            $gr_data['info'] = "支付成功";
            $rdata['payinfo'] = json_encode($gr_data);
            $this->ajaxReturn($rdata);
        }
        // 通过支付方式获取支付编号
        $pay_controller = new \Common\Controller\PaybaseController();
        $pw_id = $pay_controller->getPaywayid($payway);
        if (empty($pw_id)) {
            $data['info'] = "请选择充值方式";
            $this->ajaxReturn($data);
        }
        //更新支付方式
        $payclass->updataPayway($order_id, $payway);
        $payext_info = $payclass->getPayextinfo($pay_info['id']);
        // $data = $this->_wallet_pay($amount, $pw_id);
        switch ($pw_id) {
            case 1 : {
                break;
            }
            case 2 : {
                break;
            }
            case 3 : {
                /* 支付宝支付 */
                $orderdata['order_id'] = $pay_info['order_id'];
                $orderdata['productname'] = $payext_info['product_name'];
                $orderdata['productdesc'] = $payext_info['product_desc'];
                $orderdata['real_amount'] = $pay_info['real_amount'];
                $alipay_controller = new AlipayController();
                $data['paytype'] = 'alipay';
                $data['orderid'] = $orderdata['order_id'];
                $data['alipayparam'] = $alipay_controller->clientPay($orderdata);
                $rdata['status'] = 1;
                $rdata['payinfo'] = json_encode($data);
                break;
            }
            case 4 : {
                break;
            }
            case 5 : {
                /* 易联支付 */
                $orderdata['order_id'] = $pay_info['order_id'];
                $orderdata['productname'] = $payext_info['product_name'];
                $orderdata['real_amount'] = $pay_info['real_amount'];
                $payeco_controller = new PayecoController();
                $payeco_data = $payeco_controller->clientPay($orderdata);
                $pr_data['paytype'] = 'payeco';
                if ($payeco_data['token']) {
                    $pr_data['orderid'] = $payeco_data['orderid'];
                    $pr_data['token'] = $payeco_data['token'];
                    $rdata['status'] = 1;
                    $rdata['payinfo'] = json_encode($pr_data);
                } else {
                    $rdata['info'] = $payeco_data['info'];
                }
                break;
            }
            case 8 : {
                /* 微付通支付 */
                $orderdata['order_id'] = $pay_info['order_id'];
                $orderdata['productname'] = $payext_info['product_name'];
                $orderdata['real_amount'] = $pay_info['real_amount'];
                $orderdata['pay_ip'] = $payext_info['pay_ip'];
                $spay_controller = new SpayController();
                $spay_data = $spay_controller->clientPay($orderdata);
                $s_data['paytype'] = 'spay';
                if ($spay_data['token']) {
                    $s_data['orderid'] = $spay_data['orderid'];
                    $s_data['amount'] = $spay_data['amount'];
                    $s_data['token'] = $spay_data['token'];
                    $rdata['status'] = 1;
                    $rdata['payinfo'] = json_encode($s_data);
                } else {
                    $rdata['info'] = $spay_data['info'];
                }
                break;
            }
            default : {
                $rdata['info'] = '参数错误';
                $this->ajaxReturn($rdata);
            }
        }
        $this->ajaxReturn($rdata);
    }
}
