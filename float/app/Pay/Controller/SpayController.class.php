<?php
/**
 * 游戏充值
 *
 * @ou
 * @2016-8-31
 */
namespace Pay\Controller;

use Common\Controller\PaybaseController;

class SpayController extends PaybaseController {
    private $mchid, $key, $url, $version;

    public function __construct() {
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/spay/config.php";
        if (file_exists($conffile)) {
            $spayconf = include $conffile;
        } else {
            $spayconf = array();
        }
        $this->mchid = $spayconf["mchId"]; // 微付通商户ID
        $this->key = $spayconf["key"]; // 微付通KEY
        $this->url = "https://pay.swiftpass.cn/pay/gateway"; // 微付通URL
        $this->version = $spayconf["version"]; // 微付通版本
    }

    function clientPay($orderdata) {
        vendor('Spay.ClientResponseHandler', '', '.class.php');
        vendor('Spay.RequestHandler', '', '.class.php');
        vendor('Spay.PayHttpClient', '', '.class.php');
        vendor('Spay.Utils', '', '.class.php');
        $resHandler = new \ClientResponseHandler();
        $reqHandler = new \RequestHandler();
        $pay = new \PayHttpClient();
        $reqHandler->setGateUrl($this->url);
        $reqHandler->setKey($this->key);
        $notify_url = SDKSITE.U('Pay/Spay/spay_notify');
        $reqHandler->setReqParams($_POST, array('method'));
        $reqHandler->setParameter('service', 'unified.trade.pay'); // 接口类型：pay.weixin.native
        $reqHandler->setParameter('mch_id', $this->mchid); // 必填项，商户号，由威富通分配
        $reqHandler->setParameter('version', $this->version);
        $reqHandler->setParameter(
            'notify_url', $notify_url
        ); // 接收威富通通知的 URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        $reqHandler->setParameter('nonce_str', mt_rand(time(), time() + rand())); // 随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('out_trade_no', $orderdata['order_id']); // 随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('body', $orderdata['productname']); // 随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('total_fee', (int)($orderdata['real_amount'] * 100)); // 随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('mch_create_ip', $orderdata['pay_ip']); // 订单生成的机器 IP
        $reqHandler->createSign(); // 创建签名
        $data = \Utils::toXml($reqHandler->getAllParameters());
        $pay->setReqContent($reqHandler->getGateURL(), $data);
        if ($pay->call()) {
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());
            if ($resHandler->isTenpaySign()) {
                // 当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
                    $rdata = array(
                        'orderid' => $orderdata['order_id'],
                        'amount'  => $orderdata['real_amount'],
                        'token'   => $resHandler->getParameter('token_id')
                    );
                    return $rdata;
                } else {
                    $rdata = array(
                        'code' => $resHandler->getParameter('err_code'),
                        'info' => $resHandler->getParameter('err_msg')
                    );
                    return $rdata;
                }
            }
            $rdata = array(
                'code' => $resHandler->getParameter('status'),
                'info' => $resHandler->getParameter('message')
            );
            return $rdata;
        } else {
            $rdata = array(
                'code' => $pay->getResponseCode(),
                'info' => $pay->getErrInfo()
            );
            return $rdata;
        }
    }

    public function wallet_notify() {
        $wallet = true;
        $this->spay_notify($wallet);
    }

    //微付通回调接口
    function spay_notify($wallet = false) {
        vendor('Spay.ClientResponseHandler', '', '.class.php');
        vendor('Spay.RequestHandler', '', '.class.php');
        vendor('Spay.PayHttpClient', '', '.class.php');
        vendor('Spay.Utils', '', '.class.php');
        $resHandler = new \ClientResponseHandler();
        $xml = file_get_contents('php://input');
        $resHandler->setContent($xml);
        $resHandler->setKey($this->key);
        //判断签名结果
        $signResult = $resHandler->isTenpaySign();
        if ($signResult) {
            if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
                //更改订单状态
                $out_trade_no = $resHandler->getParameter('out_trade_no'); //自己生成的订单状态
                $amount = $resHandler->getParameter('total_fee') / 100;
                $trade_no = $resHandler->getParameter('transaction_id').'|'.$resHandler->getParameter(
                        'out_transaction_id'
                    );
                //将订单状态写入数据表中
                // 支付成功后，修改支付表中支付状态，并将交易信息写入用户平台充值记录表ptb_charge。
                if ($wallet) {
                    $this->wallet_post($out_trade_no, $amount, $trade_no);
                } else {
                    $this->sdk_post($out_trade_no, $amount, $trade_no);
                }
                echo 'success';
                exit;
            } else {
                echo 'failure1';
                exit();
            }
        } else {
            echo 'failure2';
        }
    }

    public function test() {
        $out_trade_no = '1478076336177960001';
        $trade_no = '7502000053201611021168841434|H16110240571271T';
        $amount = 6;
        $this->sdk_post($out_trade_no, $amount, $trade_no);
    }

    //获取订单支付后通知CP回调的状态
    public function payState() {
        $orderid = I('post.order_id');
        $status = M('ptbCharge')->where(array('order_id' => $orderid))->getField('status');
        //订单状态为2则为支付且回调成功
        if ($status == 2) {
            $res = new \stdClass();
            $res->success = 1;
            echo json_encode($res);
        } else {
            $res = new \stdClass();
            $res->success = 0;
            echo json_encode($res);
        }
    }

    public function return_url() {
        $paysite = WEBSITE.U("Web/Pay/spay");
        $sign = '';
        $sign = strtolower(md5($signStr));
        $html = "<!DOCTYPE HTML>";
        $html .= "<html>";
        $html .= "<head>";
        $html .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        $html .= "<link href='public/pay/css/toper.css' rel='stylesheet' type='text/css'>";
        $html .= "<style type='text/css'>";
        $html .= "	.cz_ba p{ line-height:22px;}";
        $html .= "	.cz_b{width:600px; margin:0 auto; padding-top:50px;}";
        $html .= "	.cz_ba{ background:url(../images/cg.jpg) no-repeat; padding-left:80px;}";
        $html .= "	.mna{padding-top:10px;}";
        $html .= "	.mna a{ color:#006699; padding:0 6px;}";
        $html .= "	.cz_ann{ height:30px; padding:0 10px;}";
        $html .= "</style>";
        $html .= "<div class='cz_b'>";
        $html .= "<div class='cz_ba'>";
        $html .= "<p style='font-size:16px; font-weight:bold;'>恭喜您，充值成功！</p>";
        $html .= "<p style='border-bottom:1px solid #e0e0e0; padding-bottom:10px; line-height:20px;'>如果查询未到账可能是运营商网络问题而导致暂时充值不成功，请联系客服。</p>";
        $html .= "<p style='margin-top:20px;'><a href=".$paysite
                 ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "<title>微信支付</title>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
    }
}
