<?php
/***
*修改日志：(int) ($payinfo['amount'] * 100)改为(int) ($payinfo['real_amount'] * 100)，这是打折系统，下单的时候需要的
*修改者：lingguihua
*modify time: 2016/09/05 15:11
*/
include ('../include/common.inc.php');
require ('Utils.class.php');
require ('config/config.php');
require ('class/RequestHandler.class.php');
require ('class/ClientResponseHandler.class.php');
require ('class/PayHttpClient.class.php');

$urldata = Response::verify('spay',$db);
$rdata = array();
if(!empty($urldata['code']) && $urldata['code']<0){
    $db->CloseConnection();
    if (empty($urldata['w'])){
        $urldata['w'] = 0;
    }
    
    return Response::show($urldata['code'], $rdata, $urldata['msg'], $urldata['w']);
}
$resHandler = new ClientResponseHandler();
$reqHandler = new RequestHandler();
$pay = new PayHttpClient();
$cfg = new Config();

$reqHandler->setGateUrl($cfg->C('url'));
$reqHandler->setKey($cfg->C('key'));
$payinfo = $db->getPayinfo($_SESSION['order_id']);
$payextinfo = $db->getPayextinfo($payinfo['id']);
$reqHandler->setReqParams($_POST, array('method'));
$reqHandler->setParameter('service', 'unified.trade.pay'); // 接口类型：pay.weixin.native
$reqHandler->setParameter('mch_id', $cfg->C('mchId')); // 必填项，商户号，由威富通分配
$reqHandler->setParameter('version', $cfg->C('version'));
$reqHandler->setParameter('notify_url', $cfg->C('notify_url')); // 接收威富通通知的 URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
$reqHandler->setParameter('nonce_str', mt_rand(time(), time() + rand())); // 随机字符串，必填项，不长于 32 位
$reqHandler->setParameter('out_trade_no', $_SESSION['order_id']); // 随机字符串，必填项，不长于 32 位
$reqHandler->setParameter('body', $payextinfo['productname']); // 随机字符串，必填项，不长于 32 位
$reqHandler->setParameter('total_fee', ($payinfo['real_amount'] * 100)); // 随机字符串，必填项，不长于 32 位
$reqHandler->setParameter('mch_create_ip', $payextinfo['pay_ip']); // 订单生成的机器 IP

$reqHandler->setParameter('device_info', 'AND_WAP'); /* 应用类型  iOS_SDK,AND_SDK,iOS_WAP,AND_WAP */
$reqHandler->setParameter('mch_app_id', $cfg->C('mch_app_id')); /* WAP 首页 URL 地址,必须保证公网能正常访问 */
$reqHandler->setParameter('mch_app_name',$cfg->C('mch_app_name')); /* WAP 网站名(如：京东官网) */

$reqHandler->createSign(); // 创建签名
$data = Utils::toXml($reqHandler->getAllParameters());
$pay->setReqContent($reqHandler->getGateURL(), $data);
if ($pay->call()) {
    $resHandler->setContent($pay->getResContent());
    $resHandler->setKey($reqHandler->getKey());
    if ($resHandler->isTenpaySign()) {
        // 当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
        if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
                $pw = $db->updatePayway($_SESSION['order_id'], 'spay');
                if ($pw) {
                    $rdata = array(
                            'a' => $_SESSION['order_id'],
                            'b' => $resHandler->getParameter('token_id')
                    );
                    $db->CloseConnection();
                    return Response::show('1', $rdata, '下单成功', $urldata['w']);
                } else {
                    $db->CloseConnection();
                    return Response::show('-1000', $rdata, '下单失败', $urldata['w']);
                }
        } else {
            $db->CloseConnection();
            return Response::show($resHandler->getParameter('err_code'), $rdata, $resHandler->getParameter('err_msg'), $urldata['w']);
        }
    }
    $db->CloseConnection();
    return Response::show($resHandler->getParameter('status'), $rdata, $resHandler->getParameter('message'), $urldata['w']);
} else {
    $db->CloseConnection();
    return Response::show($pay->getResponseCode(), $rdata,  $pay->getErrInfo(), $urldata['w']);
}
