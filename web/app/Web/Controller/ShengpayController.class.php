<?php
/**
 * ShengpayController.class.php UTF-8
 * 盛付通支付
 *
 * @date    : 2016年7月20日下午3:39:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 */
namespace Web\Controller;

use Web\Controller\PayController;

class ShengpayController extends PayController {
    private $senderId, $signStr;

    function _initialize() {
        parent::_initialize();
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/shengpay/config.php";
        if (file_exists($conffile)) {
            $shengpayconf = include $conffile;
        } else {
            $shengpayconf = array();
        }
        $this->senderId = $shengpayconf['senderId']; // 商户号
        $this->signStr = $shengpayconf['signStr']; // 密钥
    }

    // 盛付通支付
    function shengpay() {
        header("Content-type:text/html;charset=utf-8");
        if (IS_POST) {
            $data = $this->_insertpay();
            if (empty($data['order_id'])) {
                $this->error("内部服务器发生错误");
                exit();
            }
            $notifyUrl = WEBSITE.U('Web/Shengpay/shengpay_notify');
            $pageUrl = WEBSITE.U('Web/Shengpay/shengpay_return');
            $data_time = date("YmdHis");
            $paydata = array(
                'Name'             => "B2CPayment",
                'Version'          => "V4.1.1.1.1",
                'Charset'          => "UTF-8",
                'MsgSender'        => $this->senderId,
                'TraceNo'          => $data_time,
                'OrderNo'          => $data['order_id'],
                'OrderAmount'      => intval($data['money']),
                'OrderTime'        => $data_time,
                'ExpireTime'       => "",
                'SendTime'         => $data_time,
                'Currency'         => "CNY",
                'PayType'          => "",
                'PayChannel'       => "",
                'InstCode'         => "",
                'CardValue'        => "",
                'Language'         => "zh-CN",
                'PageUrl'          => empty($pageUrl) ? "" : $pageUrl,
                'BackUrl'          => empty($notifyUrl) ? "" : $notifyUrl,
                'NotifyUrl'        => empty($notifyUrl) ? "" : $notifyUrl,
                'ProductId'        => "",
                'ProductName'      => empty($data['productname']) ? "" : $data['productname'],
                'ProductNum'       => "1",
                'UnitPrice'        => "",
                'ProductDesc'      => "",
                'ProductUrl'       => "",
                'SellerId'         => "",
                'PayeeId'          => "",
                'DepositId'        => "",
                'DepositIdType'    => "",
                'BuyerName'        => "",
                'BuyerId'          => "",
                'BuyerContact'     => "",
                'BuyerIp'          => $data['ip'],
                'PayerId'          => "",
                'SharingInfo'      => "",
                'SharingNotifyUrl' => "",
                'Ext1'             => "",
                'Ext2'             => "",
                'SignType'         => "MD5"
            );
            $signStr .= $paydata['Name'];
            $signStr .= $paydata['Version'];
            $signStr .= $paydata['Charset'];
            $signStr .= $paydata['TraceNo'];
            $signStr .= $paydata['MsgSender'];
            $signStr .= $paydata['SendTime'];
            $signStr .= $paydata['OrderNo'];
            $signStr .= $paydata['OrderAmount'];
            $signStr .= $paydata['OrderTime'];
            $signStr .= $paydata['ExpireTime'];
            $signStr .= $paydata['Currency'];
            $signStr .= $paydata['PayType'];
            $signStr .= $paydata['PayChannel'];
            $signStr .= $paydata['InstCode'];
            $signStr .= $paydata['CardValue'];
            $signStr .= $paydata['Language'];
            $signStr .= $paydata['PageUrl'];
            $signStr .= $paydata['BackUrl'];
            $signStr .= $paydata['NotifyUrl'];
            $signStr .= $paydata['SharingInfo'];
            $signStr .= $paydata['SharingNotifyUrl'];
            $signStr .= $paydata['ProductId'];
            $signStr .= $paydata['ProductName'];
            $signStr .= $paydata['ProductNum'];
            $signStr .= $paydata['UnitPrice'];
            $signStr .= $paydata['ProductDesc'];
            $signStr .= $paydata['ProductUrl'];
            $signStr .= $paydata['SellerId'];
            $signStr .= $paydata['BuyerName'];
            $signStr .= $paydata['BuyerId'];
            $signStr .= $paydata['BuyerContact'];
            $signStr .= $paydata['BuyerIp'];
            $signStr .= $paydata['PayeeId'];
            $signStr .= $paydata['DepositId'];
            $signStr .= $paydata['DepositIdType'];
            $signStr .= $paydata['PayerId'];
            $signStr .= $paydata['Ext1'];
            $signStr .= $paydata['Ext2'];
            $signStr .= $paydata['SignType'];
            $signStr .= $this->signStr;
            $paydata['SignMsg'] = strtoupper(md5($signStr));
            $html_text = $this->buildRequestForm($paydata, "post", "确认");
            echo $html_text;
            exit();
        }
        $this->error("请求参数错误");
    }

    function shengpay_notify() {
        $signMessage = "";
        $signMessage .= empty($_POST["Name"]) ? "" : $_POST["Name"];
        $signMessage .= empty($_POST["Version"]) ? "" : $_POST["Version"];
        $signMessage .= empty($_POST["Charset"]) ? "" : $_POST["Charset"];
        $signMessage .= empty($_POST["TraceNo"]) ? "" : $_POST["TraceNo"];
        $signMessage .= empty($_POST["MsgSender"]) ? "" : $_POST["MsgSender"];
        $signMessage .= empty($_POST["SendTime"]) ? "" : $_POST["SendTime"];
        $signMessage .= empty($_POST["InstCode"]) ? "" : $_POST["InstCode"];
        $signMessage .= empty($_POST["OrderNo"]) ? "" : $_POST["OrderNo"];
        $signMessage .= empty($_POST["OrderAmount"]) ? "" : $_POST["OrderAmount"];
        $signMessage .= empty($_POST["TransNo"]) ? "" : $_POST["TransNo"];
        $signMessage .= empty($_POST["TransAmount"]) ? "" : $_POST["TransAmount"];
        $signMessage .= empty($_POST["TransStatus"]) ? "" : $_POST["TransStatus"];
        $signMessage .= empty($_POST["TransType"]) ? "" : $_POST["TransType"];
        $signMessage .= empty($_POST["TransTime"]) ? "" : $_POST["TransTime"];
        $signMessage .= empty($_POST["MerchantNo"]) ? "" : $_POST["MerchantNo"];
        $signMessage .= empty($_POST["ErrorCode"]) ? "" : $_POST["ErrorCode"];
        $signMessage .= empty($_POST["ErrorMsg"]) ? "" : $_POST["ErrorMsg"];
        $signMessage .= empty($_POST["Ext1"]) ? "" : $_POST["Ext1"];
        $signMessage .= empty($_POST["Ext2"]) ? "" : $_POST["Ext2"];
        $signMessage .= empty($_POST["SignType"]) ? "" : $_POST["SignType"];
        $signMessage .= $this->signStr;
        $signMsg = strtoupper(md5($signMessage));
        $SignMsgMerchant = $_POST["SignMsg"];
        if (isset($SignMsgMerchant) && strcasecmp($signMsg, $SignMsgMerchant) === 0) {
            $TransStatus = $_POST['TransStatus'];
            if ($TransStatus == '01') {
                $orderid = $_POST['OrderNo'];
                $paymark = $_POST["TransNo"];
                $amount = $_POST['TransAmount'];
                $this->paypost($orderid, $paymark, $amount);
                echo "OK"; // 请不要修改或删除
            } else {
                echo "fail";
            }
        } else {
            echo "fail";
        }
    }

    function buildRequestForm($para_temp, $method, $button_name) {
        // 待请求参数数组
        $para = $para_temp;
        // $url = "https://cardpay.shengpay.com/mobile-acquire-channel/cashier.htm";
        $url = "https://cardpay.shengpay.com/web-acquire-channel/cashier.htm"; //PC端支付
        $sHtml = "<form id='shengpaysubmit' name='shengpaysubmit' action='{$url}' method='".$method."'>";
        while (list($key, $val) = each($para)) {
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        // submit按钮控件请不要含有name属性
        $sHtml = $sHtml."</form>";
        $sHtml = $sHtml."<script>document.forms['shengpaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 汇付宝支付通知页面
     */
    function shengpay_return() {
        $paysite = WEBSITE.U("Web/Pay/index");
        $signMessage = "";
        $signMessage .= empty($_POST["Name"]) ? "" : $_POST["Name"];
        $signMessage .= empty($_POST["Version"]) ? "" : $_POST["Version"];
        $signMessage .= empty($_POST["Charset"]) ? "" : $_POST["Charset"];
        $signMessage .= empty($_POST["TraceNo"]) ? "" : $_POST["TraceNo"];
        $signMessage .= empty($_POST["MsgSender"]) ? "" : $_POST["MsgSender"];
        $signMessage .= empty($_POST["SendTime"]) ? "" : $_POST["SendTime"];
        $signMessage .= empty($_POST["InstCode"]) ? "" : $_POST["InstCode"];
        $signMessage .= empty($_POST["OrderNo"]) ? "" : $_POST["OrderNo"];
        $signMessage .= empty($_POST["OrderAmount"]) ? "" : $_POST["OrderAmount"];
        $signMessage .= empty($_POST["TransNo"]) ? "" : $_POST["TransNo"];
        $signMessage .= empty($_POST["TransAmount"]) ? "" : $_POST["TransAmount"];
        $signMessage .= empty($_POST["TransStatus"]) ? "" : $_POST["TransStatus"];
        $signMessage .= empty($_POST["TransType"]) ? "" : $_POST["TransType"];
        $signMessage .= empty($_POST["TransTime"]) ? "" : $_POST["TransTime"];
        $signMessage .= empty($_POST["MerchantNo"]) ? "" : $_POST["MerchantNo"];
        $signMessage .= empty($_POST["ErrorCode"]) ? "" : $_POST["ErrorCode"];
        $signMessage .= empty($_POST["ErrorMsg"]) ? "" : $_POST["ErrorMsg"];
        $signMessage .= empty($_POST["Ext1"]) ? "" : $_POST["Ext1"];
        $signMessage .= empty($_POST["Ext2"]) ? "" : $_POST["Ext2"];
        $signMessage .= empty($_POST["SignType"]) ? "" : $_POST["SignType"];
        $signMessage .= $this->signStr;
        $signMsg = strtoupper(md5($signMessage));
        $SignMsgMerchant = $_POST["SignMsg"];
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
        if (isset($SignMsgMerchant) && strcasecmp($signMsg, $SignMsgMerchant) === 0) {
            $TransStatus = $_POST['TransStatus'];
            if ($TransStatus == '01') {
                $html .= "<div class='cz_b'>";
                $html .= "<div class='cz_ba'>";
                $html .= "<p style='font-size:16px; font-weight:bold;'>恭喜您，充值成功！</p>";
                $html .= "<p style='border-bottom:1px solid #e0e0e0; padding-bottom:10px; line-height:20px;'>如果查询未到账可能是运营商网络问题而导致暂时充值不成功，请联系客服。</p>";
                $html .= "<p class='mna'>订单号：".$_POST['OrderNo']."</p>";
                $html .= "<p>充值金额：".$_POST['OrderAmount']."</p>";
                $html .= "<p style='margin-top:20px;'><a href=".$paysite
                         ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
                $html .= "</div>";
                $html .= "</div>";
            } else {
                $html .= "<div class='cz_b'>";
                $html .= "<div class='cz_ba'>";
                $html .= "<p style='font-size:16px; font-weight:bold;'>充值失败，请重试！</p>";
                $html .= "<p style='margin-top:20px;'><a href=".$paysite
                         ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
                $html .= "</div>";
                $html .= "</div>";
            }
        } else {
            $html .= "<div class='cz_b'>";
            $html .= "<div class='cz_ba'>";
            $html .= "<p style='font-size:16px; font-weight:bold;'>充值失败，请重试！</p>";
            $html .= "<p style='margin-top:20px;'><a href=".$paysite
                     ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
            $html .= "</div>";
            $html .= "</div>";
        }
        $html .= "<title>盛付通充值</title>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
    }
}
