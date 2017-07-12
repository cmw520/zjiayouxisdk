<?php
/**
 * Aliyun.php UTF-8
 * 阿里云短信发送
 *
 * @date    : 2017年04月14日下午1:55:34
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\sms;

use think\Log;
class Aliyun {
    /**
     * 自定义错误处理
     *
     * @param string $msg 输出的文件
     * @param string $level
     *
     * @internal param  $msg
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Aliyun Error:'.$msg;
        Log::record($_info, $level);
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        include EXTEND_PATH."aliyun/aliyun-php-sdk-core/Config.php";
        include EXTEND_PATH."aliyun/aliyun-php-sdk-sms/Sms/Request/V20160927/SingleSendSmsRequest.php";
        // 获取阿里云短信配置信息
        if (file_exists(CONF_PATH."extra/sms/aliyun.php")) {
            $_config = include CONF_PATH."extra/sms/aliyun.php";
        } else {
            $_config = array();
        }
        if (empty($_config)) {
            $this->_error("配置信息错误");

            return false;
        }
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $_config['APPKEY'], $_config['APPSECRET']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new \SingleSendSmsRequest();
        $request->setSignName($_config['SIGNNAME']);/*签名名称*/
        $request->setTemplateCode($_config['SMSTEMPAUTH']);/*模板code*/
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
        }
        catch (\ClientException  $e) {
            $_rdata['code'] = 400;
            $_rdata['msg'] = '短信发送失败';
        }
        catch (\ServerException  $e) {
            $_rdata['code'] = 400;
            $_rdata['msg'] = '短信发送失败';
        }
        return $_rdata;
    }
}