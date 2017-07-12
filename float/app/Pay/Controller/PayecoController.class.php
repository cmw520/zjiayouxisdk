<?php
/**
 * PayecoController.class.php UTF-8
 * 易联支付
 *
 * @date    : 2016年7月20日下午4:48:05
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : 7.0
 */
namespace Pay\Controller;

use Common\Controller\PaybaseController;

class PayecoController extends PaybaseController {
    private $merchant_id, $payeco_url, $rsa_private_key, $rsa_public_key;

    public function __construct() {
        vendor('Payeco.HttpClient');
        vendor('Payeco.Log');
        vendor('Payeco.Signatory');
        vendor('Payeco.Tools');
        vendor('Payeco.Xml');
        vendor('Payeco.ConstantsClient');
        vendor('Payeco.TransactionClient');
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/payeco/config.php";
        if (file_exists($conffile)) {
            $payecoconf = include $conffile;
        } else {
            $payecoconf = array();
        }
        $this->merchant_id = $payecoconf['merchant_id']; // 商户号
        $this->payeco_url = $payecoconf['payeco_url']; // 密钥
        $this->rsa_private_key = SITE_PATH.'conf/pay/payeco/key/rsa_private_key.pem'; // 密钥
        $this->rsa_public_key = SITE_PATH.'conf/pay/payeco/key/rsa_public_key.pem'; // 密钥
    }

    // 支付宝客户端加密函数
    function clientPay($orderdata) {
        $merchantId = $this->merchant_id;
        $notifyUrl = SDKSITE.U('Pay/Payeco/payeco_notify'); // 需要做URLEncode
        $tradeTime = \Tools::getSysTime();
        $expTime = ""; // 采用系统默认的订单有效时间
        $notifyFlag = "0";
        // 调用下单接口
        $retXml = new \Xml();
        $retMsgJson = "";
        $bOK = true;
        $transtime = time(); // 交易时间
        $extData = ""; // 商户保留信息,通知结果时，原样返回给商户
        $miscData = ""; // 订单扩展信息
        try {
            \Log::setLogFlag(true);
            \Log::logFile("--------商户下单接口测试---------------");
            $ret = \TransactionClient::MerchantOrder(
                $merchantId,
                $orderdata['order_id'],
                $orderdata['real_amount'],
                $orderdata['productname'],
                $tradeTime,
                $expTime,
                $notifyUrl,
                $extData,
                $miscData,
                $notifyFlag,
                $this->rsa_private_key,
                $this->rsa_public_key,
                $this->payeco_url,
                $retXml
            );
            if (strcmp("0000", $ret)) {
                $bOK = false;
                $rdata = array(
                    'code' => -10,
                    'info' => '订单接口返回错误'
                );
                return $rdata;
            }
        } catch (Exception $e) {
            $bOK = false;
            $errCode = $e->getMessage();
            if (strcmp("E101", $errCode) == 0) {
                $rdata = array(
                    'code' => -11,
                    'info' => '下订单接口无返回数据'
                );
            } else if (strcmp("E102", $errCode) == 0) {
                $rdata = array(
                    'code' => -12,
                    'info' => '验证签名失败'
                );
            } else if (strcmp("E103", $errCode) == 0) {
                $rdata = array(
                    'code' => -13,
                    'info' => '进行订单签名失败'
                );
            } else {
                $rdata = array(
                    'code' => -14,
                    'info' => '下订单通讯失败'
                );
            }
            return $rdata;
        }
        // 设置返回给手机Json数据
        if ($bOK) {
            $retMsgJson = "{\"RetCode\":\"0000\",\"RetMsg\":\"下单成功\",".
                          "\"Version\":\"".$retXml->getVersion()."\",\"MerchOrderId\":\"".$retXml->getMerchOrderId().
                          "\",\"MerchantId\":\"".$retXml->getMerchantId()."\",\"Amount\":\"".$retXml->getAmount().
                          "\",\"TradeTime\":\"".$retXml->getTradeTime()."\",\"OrderId\":\"".$retXml->getOrderId().
                          "\",\"Sign\":\"".$retXml->getSign()."\"}";
            // 输出数据
            \Log::logFile("retMsgJson=".$retMsgJson);
            $rdata = array(
                'orderid' => $orderdata['order_id'],
                'token'   => $retMsgJson
            );
            return $rdata;
        }
        $rdata = array(
            'code' => -1000,
            'info' => '服务器内部错误'
        );
        return $rdata;
    }

    // 易联支付函数
    function pay($orderdata) {
    }

    /*
     * 易联钱包回调
     */
    public function wallet_notify() {
        $wallet = true;
        $this->payeco_notify($wallet);
    }

    /**
     * notify_url接收页面
     */
    public function payeco_notify($wallet = false) { // 结果通知参数，易联异步通知采用GET提交
        $version = $_REQUEST["Version"];
        $merchantId = $_REQUEST["MerchantId"];
        $merchOrderId = $_REQUEST["MerchOrderId"];
        $amount = $_REQUEST["Amount"];
        $extData = $_REQUEST["ExtData"];
        $orderId = $_REQUEST["OrderId"];
        $status = $_REQUEST["Status"];
        $payTime = $_REQUEST["PayTime"];
        $settleDate = $_REQUEST["SettleDate"];
        $sign = $_REQUEST["Sign"];
        // 需要对必要输入的参数进行检查，本处省略...
        if (!get_magic_quotes_gpc()) {
            $version = addslashes($version);
            $merchantId = addslashes($merchantId);
            $merchOrderId = addslashes($merchOrderId);
            $amount = addslashes($amount);
            $extData = addslashes($extData);
            $orderId = addslashes($orderId);
            $status = addslashes($status);
            $payTime = addslashes($payTime);
            $settleDate = addslashes($settleDate);
            $sign = addslashes($sign);
        }
        $trade['merchOrderId'] = $merchOrderId;
        \Log::setLogFlag(true);
        if ($merchantId != $this->merchant_id) {
            \Log::logFile($merchantId."商户号不正确!".$this->merchant_id);
            exit();
        }
        // 订单结果逻辑处理
        $retMsgJson = "";
        try {
            // 验证订单结果通知的签名
            \Log::logFile("------订单结果通知验证-----------------");
            $b = \TransactionClient::bCheckNotifySign(
                $version,
                $merchantId,
                $merchOrderId,
                $amount,
                $extData,
                $orderId,
                $status,
                $payTime,
                $settleDate,
                $sign,
                $this->rsa_public_key
            );
            if (!$b) {
                $retMsgJson = "{\"RetCode\":\"E101\",\"RetMsg\":\"验证签名失败!\"}";
                \Log::logFile("验证签名失败!");
            } else {
                /*
                 * 签名验证成功后，需要对订单进行后续处理
                 * 订单已支付
                 * 1、检查Amount和商户系统的订单金额是否一致
                 * 2、订单支付成功的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理）；
                 * 3、返回响应内容
                 */
                if (strcmp("02", $status) == 0) {
                    $retMsgJson = "{\"RetCode\":\"0000\",\"RetMsg\":\"订单已支付\"}";
                    \Log::logFile("订单已支付!");
                    if ($wallet) {
                        $this->wallet_post($merchOrderId, $amount, $orderId);
                    } else {
                        $this->sdk_post($merchOrderId, $amount, $orderId);
                    }
                } else {
                    // 1、订单支付失败的业务逻辑处理请在本处增加（订单通知可能存在多次通知的情况，需要做多次通知的兼容处理，避免成功后又修改为失败）；
                    // 2、返回响应内容
                    $retMsgJson = "{\"RetCode\":\"E102\",\"RetMsg\":\"订单支付失败".$status."\"}";
                    \Log::logFile("订单支付失败!status=".$status);
                }
            }
        } catch (Exception $e) {
            $retMsgJson = "{\"RetCode\":\"E103\",\"RetMsg\":\"处理通知结果异常\"}";
            \Log::logFile("处理通知结果异常!e=".$e->getMessage());
        }
        \Log::logFile("-----处理完成----");
        // 返回数据
        echo $retMsgJson;
    }
}
