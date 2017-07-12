<?php
/**
 * 游戏充值
 *
 * @lei
 * @2016-4-7
 */
namespace Web\Controller;

use Web\Controller\PayController;

class HeepayController extends PayController {
    private $heepayagent_id, $heepaysign_key;

    function _initialize() {
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/heepay/config.php";
        if (file_exists($conffile)) {
            $heepayconf = include $conffile;
        } else {
            $heepayconf = array();
        }
        $this->heepayagent_id = $heepayconf["agent_id"]; // 汇付宝商户号
        $this->heepaysign_key = $heepayconf["sign_key"]; // 汇付宝签名
    }

    // 汇付宝支付函数
    function heepay() {
        header("Content-type:text/html;charset=utf-8");
        $data = $this->_insertpay();
        if (empty($data['order_id'])) {
            $this->error("内部服务器发生错误");
            exit();
        }
        $is_phone = '0';
        $is_frame = '0';
//         if ($this->_fromdevice == 2) {
//             // WAP支付
//             $is_phone = 1;
//             $is_frame = 0;
//         } else if ($this->_fromdevice == 3) {
//             // 公众号支付
//             $is_phone = 1;
//             $is_frame = 1;
//         }
        $pay_type = "0"; // 汇付宝支付
        $agent_id = $this->heepayagent_id;
        $sign_key = $this->heepaysign_key; // 签名密钥，需要商户使用为自己的真实KEY
        $notify_url = WEBSITE.U('Web/Heepay/heepay_notify');
        $return_url = WEBSITE.U('Web/Heepay/heepay_return');
        $user_ip = $data['ip'];
        $version = 1;
        $agent_bill_id = $data['order_id'];
        $agent_bill_time = date('YmdHis', $data['create_time']);
        $pay_amt = number_format($data['money'], 2, '.', '');
        $goods_name = urlencode(C("CURRENCY_NAME"));
        $goods_num = urlencode(1);
        $goods_note = urlencode(C("CURRENCY_NAME"));
        $remark = urlencode($data['remark']);
        /**
         * ***********创建签名**************
         */
        $sign_str = '';
        $sign_str = $sign_str.'version='.$version;
        $sign_str = $sign_str.'&agent_id='.$agent_id;
        $sign_str = $sign_str.'&agent_bill_id='.$agent_bill_id;
        $sign_str = $sign_str.'&agent_bill_time='.$agent_bill_time;
        $sign_str = $sign_str.'&pay_type='.$pay_type;
        $sign_str = $sign_str.'&pay_amt='.$pay_amt;
        $sign_str = $sign_str.'&notify_url='.$notify_url;
        $sign_str = $sign_str.'&return_url='.$return_url;
        $sign_str = $sign_str.'&user_ip='.$user_ip;
        $sign_str = $sign_str.'&key='.$sign_key;
        $sign = md5($sign_str); // 签名值
        $url = "https://pay.heepay.com/Payment/Index.aspx";
        $sHtml = "<form id='frmSubmit' method='post' name='frmSubmit' action='{$url}'>";
        $sHtml = $sHtml."<input type='hidden' name='version' value='{$version}' />";
        $sHtml = $sHtml."<input type='hidden' name='agent_id' value='{$agent_id}' />";
        $sHtml = $sHtml."<input type='hidden' name='agent_bill_id' value='{$agent_bill_id}' />";
        $sHtml = $sHtml."<input type='hidden' name='agent_bill_time' value='{$agent_bill_time}' />";
        $sHtml = $sHtml."<input type='hidden' name='pay_type' value='{$pay_type}' />";
        $sHtml = $sHtml."<input type='hidden' name='pay_amt' value='{$pay_amt}' />";
        $sHtml = $sHtml."<input type='hidden' name='notify_url' value='{$notify_url}' />";
        $sHtml = $sHtml."<input type='hidden' name='return_url' value='{$return_url}' />";
        $sHtml = $sHtml."<input type='hidden' name='user_ip' value='{$user_ip}' />";
        $sHtml = $sHtml."<input type='hidden' name='goods_name' value='{$goods_name}' />";
        $sHtml = $sHtml."<input type='hidden' name='goods_num' value='{$goods_num}' />";
        $sHtml = $sHtml."<input type='hidden' name='goods_note' value='{$goods_note}' />";
        $sHtml = $sHtml."<input type='hidden' name='remark' value='{$remark}' />";
        $sHtml = $sHtml."<input type='hidden' name='is_phone' value='{$is_phone}' />";
        $sHtml = $sHtml."<input type='hidden' name='is_frame' value='{$is_frame}' />";
        $sHtml = $sHtml."<input type='hidden' name='sign' value='{$sign}' />";
        $sHtml = $sHtml."</form>";
        $sHtml = $sHtml."<script>document.frmSubmit.submit();</script>";
        echo $sHtml;
    }

    /**
     * 汇付宝服务器异步回调函数
     */
    function heepay_notify() {
        $result = $_GET['result'];
        $pay_message = $_GET['pay_message'];
        $agent_id = $_GET['agent_id'];
        $jnet_bill_no = $_GET['jnet_bill_no'];
        $agent_bill_id = $_GET['agent_bill_id'];
        $pay_type = $_GET['pay_type'];
        $pay_amt = $_GET['pay_amt'];
        $remark = $_GET['remark'];
        $return_sign = $_GET['sign'];
        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark)); // 签名验证中的中文采用UTF-8编码;
        $signStr = '';
        $signStr = $signStr.'result='.$result;
        $signStr = $signStr.'&agent_id='.$agent_id;
        $signStr = $signStr.'&jnet_bill_no='.$jnet_bill_no;
        $signStr = $signStr.'&agent_bill_id='.$agent_bill_id;
        $signStr = $signStr.'&pay_type='.$pay_type;
        $signStr = $signStr.'&pay_amt='.$pay_amt;
        $signStr = $signStr.'&remark='.$remark;
        $signStr = $signStr.'&key='.$this->heepaysign_key; // 商户签名密钥
        $sign = '';
        $sign = strtolower(md5($signStr));
        if ($sign == $return_sign) { // 验证成功
            $this->paypost($agent_bill_id, $jnet_bill_no, $pay_amt);
            echo 'ok';
        } else {
            echo 'error';
        }
    }

    /**
     * 汇付宝支付通知页面
     */
    function heepay_return() {
        $result = $_GET['result'];
        $pay_message = $_GET['pay_message'];
        $agent_id = $_GET['agent_id'];
        $jnet_bill_no = $_GET['jnet_bill_no'];
        $agent_bill_id = $_GET['agent_bill_id'];
        $pay_type = $_GET['pay_type'];
        $pay_amt = $_GET['pay_amt'];
        $remark = $_GET['remark'];
        $return_sign = $_GET['sign'];
        $paysite = WEBSITE.U("Web/Pay/index");
        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark)); // 签名验证中的中文采用UTF-8编码;
        $signStr = '';
        $signStr = $signStr.'result='.$result;
        $signStr = $signStr.'&agent_id='.$agent_id;
        $signStr = $signStr.'&jnet_bill_no='.$jnet_bill_no;
        $signStr = $signStr.'&agent_bill_id='.$agent_bill_id;
        $signStr = $signStr.'&pay_type='.$pay_type;
        $signStr = $signStr.'&pay_amt='.$pay_amt;
        $signStr = $signStr.'&remark='.$remark;
        $signStr = $signStr.'&key='.$this->heepaysign_key; // 商户签名密钥
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
        //请确保 notify.php 和 return.php 判断代码一致
        if ($sign == $return_sign) {   //比较MD5签名结果 是否相等 确定交易是否成功  成功显示给客户信息
            $html .= "<div class='cz_b'>";
            $html .= "<div class='cz_ba'>";
            $html .= "<p style='font-size:16px; font-weight:bold;'>恭喜您，充值成功！</p>";
            $html .= "<p style='border-bottom:1px solid #e0e0e0; padding-bottom:10px; line-height:20px;'>如果查询未到账可能是运营商网络问题而导致暂时充值不成功，请联系客服。</p>";
            $html .= "<p class='mna'>订单号：".$jnet_bill_no."</p>";
            $html .= "<p>充值金额：".$pay_amt."</p>";
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
        $html .= "<title>汇付宝充值</title>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
    }
}
