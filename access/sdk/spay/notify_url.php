<?php
/*
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 * ************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
include ('../include/common.inc.php');
require ('Utils.class.php');
require ('config/config.php');
require ('class/RequestHandler.class.php');
require ('class/ClientResponseHandler.class.php');
require ('class/PayHttpClient.class.php');

$cfg = new Config();
$resHandler = new ClientResponseHandler();
$xml = file_get_contents('php://input');
$resHandler->setContent($xml);
$resHandler->setKey($cfg->C('key'));

if ($resHandler->isTenpaySign()) {
    if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
        
        // 第三方支付交易号
        $out_trade_no = $resHandler->getParameter('transaction_id');
        
        // 交易号
        $trade_no = $resHandler->getParameter('out_trade_no');
        
        // 交易金额
        $amount = $resHandler->getParameter('total_fee') / 100;
        
        // 交易状态
        $trade_status = $resHandler->getParameter('pay_result');
        
        // 支付成功
        if ($trade_status == 0) {
            $db->doPaynotify($trade_no, $amount, $out_trade_no);
        }
        Utils::dataRecodes('接口回调收到通知参数', $resHandler->getAllParameters());
        echo 'success';
    } else {
        echo 'failure1';
    }
} else {
    echo 'failure2';
}
$db->CloseConnection();
