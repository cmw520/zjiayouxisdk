<?php
namespace HuoShu\Sms;
class Alidayu {
    public static function send($mobile, $smstemp, $product = '') {
        include(SITE_PATH.'thinkphp/Core/Library/Vendor/taobao/TopSdk.php');
        require_once(SITE_PATH.'thinkphp/Core/Library/Vendor/taobao/top/TopClient.php');
        require_once(SITE_PATH.'thinkphp/Core/Library/Vendor/taobao/top/request/AlibabaAliqinFcSmsNumSendRequest.php');
        //获取阿里大鱼配置信息
        if (file_exists(SITE_PATH."conf/alidayu.php")) {
            $dayuconfig = include SITE_PATH."conf/sms/alidayu.php";
        } else {
            $dayuconfig = array();
        }
        if (empty($dayuconfig)) {
            return false;
        }
        if (empty($product)) {
            $product = $dayuconfig['PRODUCT'];
        }
        $sms_code = self::getSmsCode(4);   //获取随机码
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
            $data['msg'] = '发送成功';
        } else {
            $data['code'] = (int)$resp['code'];
            $data['msg'] = $resp['msg'].$resp['sub_msg'];
        }
        return $data;
    }

    public static function getSmsCode($length = 6) {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}

