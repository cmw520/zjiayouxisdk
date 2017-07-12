<?php
namespace HuoShu\Pay;
class spay {
    public function submit() {
    }

    public function getConfig() {
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
                        array('status' => 500,
                              'msg'    => 'Error Code:'.$this->resHandler->getParameter('err_code').' Error Message:'
                                          .$this->resHandler->getParameter('err_msg'))
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
                array('status' => 500,
                      'msg'    => 'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo(
                          ))
            );
        }
    }
}

