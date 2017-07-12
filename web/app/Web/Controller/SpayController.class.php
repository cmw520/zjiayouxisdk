<?php
/**
 * 游戏充值
 *
 * @ou
 * @2016-8-31
 */
namespace Web\Controller;

use Web\Controller\PayController;

class SpayController extends PayController {
    private $spayversion, $spayurl, $spaymchId, $spaykey;

    function _initialize() {
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/spay/pcconfig.php";
        if (file_exists($conffile)) {
            $spayconf = include $conffile;
        } else {
            $spayconf = array();
        }
        $this->spayversion = $spayconf["version"]; // 汇付宝商户号
        $this->spayurl = $spayconf["url"]; // 汇付宝签名
        $this->spaymchId = $spayconf["mchId"]; // 汇付宝签名
        $this->spaykey = $spayconf["key"]; // 汇付宝签名
    }

    //微付通PC扫码支付入口
    function spay() {
        if (IS_POST) {
            header("Content-type:text/html;charset=utf-8");
            $data = $this->_insertpay();
            if (empty($data['order_id'])) {
                $this->error("内部服务器发生错误");
                exit();
            }
            import("Vendor/wftpay/Utils");
            import("Vendor/wftpay/class/RequestHandler");
            import("Vendor/wftpay/class/ClientResponseHandler");
            import("Vendor/wftpay/class/PayHttpClient");
            $this->resHandler = new \ClientResponseHandler();
            $this->reqHandler = new \RequestHandler();
            $this->pay = new \PayHttpClient();
            //导入回调地址
            $this->reqHandler->setGateUrl($this->spayurl);
            $this->reqHandler->setKey($this->spaykey);
            $this->reqHandler->setParameter('out_trade_no', $data['order_id']);
            $this->reqHandler->setParameter('body', "购买".C('CURRENCY_NAME'));
            $this->reqHandler->setParameter('attach', $data['remark']);
            $this->reqHandler->setParameter('total_fee', $data['money'] * 100);
            $this->reqHandler->setParameter('mch_create_ip', $data['ip']);
            $this->reqHandler->setParameter('time_start', date('YmdHis', $data['create_time']));
            $this->reqHandler->setParameter('time_expire', date('YmdHis', $data['create_time'] + 7200));
            $this->reqHandler->setParameter('service', 'pay.weixin.native');//接口类型：pay.weixin.native
            $this->reqHandler->setParameter('mch_id', $this->spaymchId);//必填项，商户号，由威富通分配
            $this->reqHandler->setParameter('version', $this->spayversion);
            //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
            //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];			//$this->reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
            $this->reqHandler->setParameter('notify_url', WEBSITE.'/index.php/Web/Spay/wx_notify');
            $this->reqHandler->setParameter('nonce_str', mt_rand(time(), time() + rand()));//随机字符串，必填项，不长于 32 位
            $this->reqHandler->createSign();//创建签名
            $rdata = \Utils::toXml($this->reqHandler->getAllParameters());//var_dump($rdata);
            $this->pay->setReqContent($this->reqHandler->getGateURL(), $rdata);
            if ($this->pay->call()) {
                $this->resHandler->setContent($this->pay->getResContent());
                $this->resHandler->setKey($this->reqHandler->getKey());
                if ($this->resHandler->isTenpaySign()) {
                    //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                    if ($this->resHandler->getParameter('status') == 0
                        && $this->resHandler->getParameter('result_code') == 0
                    ) {
                        $this->assign('code_img_url', $this->resHandler->getParameter('code_img_url'));
                        $this->assign('out_trade_no', $data['order_id']);
                        $this->display('pcnativepay');
                        exit;
                    } else {
                        echo json_encode(
                            array('status' => 500, 'msg' => 'Error Code:'.$this->resHandler->getParameter('err_code')
                                                            .' Error Message:'.$this->resHandler->getParameter(
                                    'err_msg'
                                ))
                        );
                        exit();
                    }
                }
                echo json_encode(
                    array('status' => 500,
                          'msg'    => 'Error Code:'.$this->resHandler->getParameter('status').' Error Message:'
                                      .$this->resHandler->getParameter('message'))
                );
            } else {
                echo json_encode(
                    array('status' => 500, 'msg' => 'Response Code:'.$this->pay->getResponseCode().' Error Info:'
                                                    .$this->pay->getErrInfo())
                );
            }
        }
    }

    //微付通回调接口
    function wx_notify() {
        import("Vendor/wftpay/Utils");
        import("Vendor/wftpay/class/ClientResponseHandler");
        $this->resHandler = new \ClientResponseHandler();
        $xml = file_get_contents('php://input');
        $this->resHandler->setContent($xml);
        $this->resHandler->setKey($this->spaykey);
        //判断签名结果
        $signResult = $this->resHandler->isTenpaySign();
        if ($signResult) {
            if ($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                //更改订单状态
                $out_trade_no = $this->resHandler->getParameter('out_trade_no'); //自己生成的订单状态
                $total_fee = $this->resHandler->getParameter('total_fee') / 100;
                //将订单状态写入数据表中
                $this->paypost($out_trade_no, $total_fee);
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
