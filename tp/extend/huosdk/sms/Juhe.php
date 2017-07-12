<?php
/**
 * Juhe.php UTF-8
 * 聚合短信发送
 *
 * @date    : 2017年03月01日
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\sms;

use think\Log;

class Juhe {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\juhe Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function __construct() {
    }

    public function send($mobile, $type, $sms_code) {
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        //短信配置信息
        if (file_exists(CONF_PATH."extra/sms/juhe.php")) {
            $jh_config = include CONF_PATH."extra/sms/juhe.php";
        } else {
            return false;
        }
        if (empty($jh_config)) {
            return false;
        }
        $tplValue = urlencode("#code#=".$sms_code);
        $smsConf = array(
            'key'       => $jh_config['APPKEY'], //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => $jh_config['TEMPLETID'], //您申请的短信模板ID，根据实际情况修改
            'tpl_value' => $tplValue //您设置的模板变量，根据实际情况修改
        );
        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信
        if ($content) {
            $result = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                $_rdata['code'] = '200';
                $_rdata['msg'] = '发送成功';
            } else {
                $_rdata['code'] = '0';
                $_rdata['msg'] = "短信发送失败";
            }
        } else {
            $_rdata['code'] = '0';
            $_rdata['msg'] = "请求发送短信失败";
        }
        return $_rdata;
    }

    /**
     * 请求接口返回内容
     *
     * @param  string $url    [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int    $ipost  [是否采用POST形式]
     *
     * @return  string
     */
    public function juhecurl($url, $params = false, $ispost = 0) {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt(
            $ch, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22'
        );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}