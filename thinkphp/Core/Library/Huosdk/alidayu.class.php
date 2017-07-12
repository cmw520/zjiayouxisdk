<?php
namespace Huosdk;
class alidayu {
    private $lib_path;
    private $conf_path;

    public function __construct() {
        $this->lib_path = SITE_PATH.'thinkphp/Core/Library/Vendor/taobao/';
        $this->conf_path = SITE_PATH."conf/sms/alidayu.php";
    }

    public function setLibPath($fp) {
        $this->lib_path = $fp;
    }

    public function setConfPath($fp) {
        $this->conf_path = $fp;
    }

    function send($mobile, $smstemp = '', $product = '') {
        include($this->lib_path.'TopSdk.php');
        require_once($this->lib_path.'top/TopClient.php');
        require_once($this->lib_path.'top/request/AlibabaAliqinFcSmsNumSendRequest.php');
        //获取阿里大鱼配置信息
        if (file_exists($this->conf_path)) {
            $dayuconfig = include $this->conf_path;
        } else {
            $dayuconfig = array();
        }
        if (empty($dayuconfig)) {
            return false;
        }
        if (empty($product)) {
            $product = $dayuconfig['PRODUCT'];
        }
        if (empty($smstemp)) {
            $smstemp = 'SMSTEMPAUTH';
        }
        $sms_code = rand(1000, 9999);   //获取随机码
        $_SESSION['sms_code'] = $sms_code;
        $content = array(
            "code"    => "".$sms_code,
            "product" => $product
        );
        $c = new \TopClient;
        $c->appkey = $dayuconfig['APPKEY'];
        $c->secretKey = $dayuconfig['APPSECRET'];
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend($dayuconfig['SETEXTEND']);
        $req->setSmsType($dayuconfig['SMSTYPE']);
        $req->setSmsFreeSignName($dayuconfig['SMSFREESIGNNAME']);
        $req->setSmsParam(json_encode($content));
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($dayuconfig[$smstemp]);
        $resp = $c->execute($req);
        $resp = (array)$resp;
        if (!empty($resp['result'])) {
            $result = (array)$resp['result'];
            $data['code'] = (int)$result['err_code'];
            $data['msg'] = '短信发送成功';
        } else {
            $data['code'] = (int)$resp['code'];
            if (15 == $data['code']) {
                $data['msg'] = "短信发送频繁,请稍后再试";
            } else {
                $data['msg'] = $resp['msg'].$resp['sub_msg'];
            }
        }
        //code为0表示发送成功       
        return $data;
    }
}

