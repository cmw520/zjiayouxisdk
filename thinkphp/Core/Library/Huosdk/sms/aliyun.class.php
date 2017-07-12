<?php
namespace Huosdk;
class alidayu {
    private $lib_path;
    private $conf_path;

    public function __construct() {
        $this->lib_path = SITE_PATH.'thinkphp/Core/Library/Vendor/aliyun/';
        $this->conf_path = SITE_PATH."conf/sms/aliyun.php";
    }

    function send($mobile, $smstemp = '', $product = '') {
        include($this->lib_path.'aliyun-php-sdk-core/Config.php');
        include($this->lib_path.'aliyun-php-sdk-sms/Sms/Request/V20160927/SingleSendSmsRequest.php');
        //获取阿里云配置信息
        if (file_exists($this->conf_path)) {
            $_config = include $this->conf_path;
        } else {
            $_config = array();
        }
        if (empty($_config)) {
            return false;
        }
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $_config['APPKEY'], $_config['APPSECRET']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new \SingleSendSmsRequest();
        $request->setSignName($_config['SIGNNAME']);/*签名名称*/
        $request->setTemplateCode($_config['SMSTEMPAUTH']);/*模板code*/
        $sms_code = rand(1000, 9999);   //获取随机码
        $_SESSION['sms_code'] = $sms_code;
        $request->setRecNum($mobile);/*目标手机号*/
        $_content = array(
            "code"    => ''.$sms_code,
            "product" => ''.$_config['PRODUCT']
        );
        $request->setParamString(json_encode($_content));/*模板变量，数字一定要转换为字符串*/
        try {
            $response = $client->getAcsResponse($request);
            $_rdata['code'] = '200';
            $_rdata['msg'] = '发送成功';
        } catch (\ClientException  $e) {
            $_rdata['code'] = 0;
            $_rdata['msg'] = '短信发送失败';
        } catch (\ServerException  $e) {
            $_rdata['code'] = 0;
            $_rdata['msg'] = '短信发送失败';
        }
        return $_rdata;
    }
}

