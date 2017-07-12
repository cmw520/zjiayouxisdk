<?php
/**
 * 游戏充值
 *
 * @ou
 * @2016-4-7
 */
namespace Web\Controller;

use Web\Controller\PayController;

class AlipayController extends PayController {
    function _initialize() {
        parent::_initialize();
    }

    // 支付宝支付函数
    function alipay() {
        import("Vendor.lib.alipay_submit");
        $data = $this->_insertpay();
        if (empty($data['order_id'])) {
            $this->error("内部服务器发生错误");
            exit();
        }
        $notifyurl = WEBSITE.U('Web/Alipay/notify_url');
        $return_url = WEBSITE.U('Web/Alipay/return_url');
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"           => 'create_direct_pay_by_user',
            "partner"           => trim(C('alipay_config_partner')),
            "payment_type"      => C('payment_type'),
            "notify_url"        => $notifyurl,
            "return_url"        => $return_url,
            "seller_email"      => C('seller_email'),
            "out_trade_no"      => $data['order_id'],
            "subject"           => C('subject'),
            "total_fee"         => $data['money'],
            "body"              => C('body'),
            "show_url"          => C('show_url'),
            "anti_phishing_key" => C('anti_phishing_key'),
            "exter_invoke_ip"   => C('exter_invoke_ip'),
            "_input_charset"    => trim(strtolower(C('alipay_config_input_charset')))
        );
        //构造要请求的参数$alipay_config
        $alipay_config = $this->get_config_data();
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        echo $html_text;
    }

    //导入文件并构造要请求的参数$alipay_config
    private function get_config_data() {
        //构造要请求的参数$alipay_config
        $alipay_config = array(
            'partner'       => C('alipay_config_partner'),
            'key'           => C('alipay_config_key'),
            'sign_type'     => C('alipay_config_sign_type'),
            'input_charset' => C('alipay_config_input_charset'),
            'cacert'        => C('alipay_config_cacert'),
            'transport'     => C('alipay_config_transport')
        );
        return $alipay_config;
    }

    //回调方法
    public function return_url() {
        import("Vendor.lib.alipay_notify");
        //构造要请求的参数$alipay_config
        $alipay_config = $this->get_config_data();
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        $paysite = WEBSITE.U("Web/Pay/index");
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
        if ($verify_result) {
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];
            //充值金额
            $amount = $_GET['total_fee'];
            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $html .= "<div class='cz_b'>";
                $html .= "<div class='cz_ba'>";
                $html .= "<p style='font-size:16px; font-weight:bold;'>恭喜您，充值成功！</p>";
                $html .= "<p style='border-bottom:1px solid #e0e0e0; padding-bottom:10px; line-height:20px;'>如果查询未到账可能是运营商网络问题而导致暂时充值不成功，请联系客服。</p>";
                $html .= "<p class='mna'>订单号：".$trade_no."</p>";
                $html .= "<p>充值金额：".$amount."</p>";
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
        $html .= "<title>支付宝即时到账交易接口</title>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
    }

    public function notify_url() {
        import("Vendor.lib.alipay_notify");
        //构造要请求的参数$alipay_config
        $alipay_config = $this->get_config_data();
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            //充值金额
            $amount = $_POST['total_fee'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $this->paypost($out_trade_no, $amount);
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";        //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }
}