<?php
/**
 * PhoneController.class.php UTF-8
 * 短信处理函数
 *
 * @date    : 2016年9月9日下午5:59:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : SMS 2.0
 */
namespace Common\Controller;
class SmsController extends AppframeController {
    protected static $template = "您的验证码是：#code#。请不要把验证码泄露给其他人。";

    /*
     * 发送手机校验码，登陆注册时
     */
    public function send_auth_code($phone, $type, $expaire_time = 0) {
        // 检查手机号码合法性
        $checkExpressions = "/^[1][34578][0-9]{9}$/";
        if (false == preg_match($checkExpressions, $phone)) {
            $result['status'] = -1;
            $result['info'] = "手机号不正确";
            return $result;
        }
        if (empty($expaire_time)) {
            $limit_time = 120;
        } else {
            $limit_time = $expaire_time; // 设定超时时间 2min
        }
        // 先判断是否发送过验证码
        if (isset($_SESSION['mobile']) && $_SESSION['mobile'] == $phone
            && $_SESSION['sms_time'] + $limit_time > time()
        ) {
            $result['status'] = 1;
            $result['info'] = "已发送过验证码";
            return $result;
        }
        $sms_code = rand(1000, 9999); // 获取随机码
        $_SESSION['sms_code'] = $sms_code;
        $rs = $this->send_sms_code($phone, $sms_code, $type);
        if ($rs) {
            $_SESSION['sms_time'] = time();
            $_SESSION['mobile'] = $phone;
            // 发送成功
            $result['status'] = 1;
            $result['info'] = "短信发送成功";
            return $result;
        } else {
            // 短信发送失败
            $result['status'] = -1;
            $result['info'] = "短信发送失败";
            return $result;
        }
    }

    public function sms_verify_code($phone, $code, $expaire_time = 0) {
        if (empty($expaire_time)) {
            $limit_time = 120;
        } else {
            $limit_time = $expaire_time; // 设定超时时间 2min
        }
        if (!$this->checkPhone($phone)) {
            $data = array(
                'status' => '-1',
                'info'   => '手机号错误'
            );
            return $data;
        }
        if (empty($_SESSION['sms_time']) || $_SESSION['sms_time'] + $limit_time < time()) {
            $data = array(
                'status' => '-1',
                'info'   => '验证码已过期,请重新获取'
            );
            session('sms_time', null);
            session('sms_code', null);
            session('mobile', null);
            return $data;
        }
        // 判断手机号码是否有效
        if (empty($_SESSION['mobile']) || $_SESSION['mobile'] != $phone) {
            $data = array(
                'status' => '-1',
                'info'   => '手机号错误或未填验证码'
            );
            return $data;
        }
        // 验证验证码是否正确
        if (empty($_SESSION['sms_code']) || $_SESSION['sms_code'] != $code) {
            $data = array(
                'status' => '-1',
                'info'   => '验证码错误'
            );
            return $data;
        }
        //清空验证码与验证码时间
        session('sms_time', null);
        session('sms_code', null);
        $data = array(
            'status' => '1',
            'info'   => '验证码正确'
        );
        return $data;
    }

    public function setMemphone($phone) {
        $userdata['id'] = sp_get_current_userid();
        $userdata['mobile'] = $phone;
        $rs = M('members')->save($userdata);
        return $rs;
    }

    /*
     * 检查手机号合法性
     * @param string $phone 手机号
     * @return boole 是否发送成功
     */
    public function checkPhone($phone) {
        // 检查手机号码合法性
        $checkExpressions = "/^[1][34578][0-9]{9}$/";
        if (false == preg_match($checkExpressions, $phone)) {
            return false;
        }
        return true;
    }

    /**
     * 发送 短信验证码 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     *
     * @return boole 是否发送成功
     */
    public function send_sms_code($phone, $code, $type) {
        $check_phone = $this->checkPhone($phone);
        if (!$check_phone) {
            return false;
        }
        // 获取阿里大鱼配置信息
        if (file_exists(SITE_PATH."conf/sms/setting.php")) {
            $setconfig = include SITE_PATH."conf/sms/setting.php";
            $i = 1;
            foreach ($setconfig as $k => $v) {
                if ($v > 0) {
                    $sendtype = $i;
                    break;
                }
                $i += 1;
            }
        } else {
            $sendtype = 1;
        }
        if (1 == $sendtype) {
            $al_rs = $this->send_alidayu_sms_code($phone, $code, $type);
        } else if (2 == $sendtype) {
            $al_rs = $this->send_ytx_sms_code($phone, $code, $type);
        } else if (3 == $sendtype) {
            $al_rs = $this->send_shangxun_sms_code($phone, $code, $type);
        } else if (4 == $sendtype) {
            $al_rs = $this->send_juhe_sms_code($phone, $code, $type);
        } else if (5 == $sendtype) {
            $al_rs = $this->send_chuanglan_sms_code($phone, $code, $type);
        } else if (6 == $sendtype) {
            $al_rs = $this->send_qixintong_sms_code($phone, $code, $type);
        } else if (7 == $sendtype) {
            $al_rs = $this->send_aliyun_sms_code($phone, $code, $type);
        } else {
            $al_rs = $this->send_alidayu_sms_code($phone, $code, $type);
        }
        return $al_rs;
    }

    /**
     * 发送 容联云通讯 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     *
     * @return boole 是否发送成功
     */
    private function send_ytx_sms_code($phone, $code) {
        // 获取容联云配置信息
        if (file_exists(SITE_PATH."conf/sms/yuntongxun.php")) {
            $ytx_config = include SITE_PATH."conf/sms/yuntongxun.php";
        } else {
            return false;
        }
        if (empty($ytx_config)) {
            return false;
        }
        // 请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';
        // 请求端口
        $serverPort = '8883';
        // REST版本号
        $softVersion = '2013-12-26';
        // 主帐号
        $accountSid = $ytx_config['RONGLIAN_ACCOUNT_SID'];
        // 主帐号Token
        $accountToken = $ytx_config['RONGLIAN_ACCOUNT_TOKEN'];
        // 应用Id
        $appId = $ytx_config['RONGLIAN_APPID'];
        $rest = new \Org\Xb\Rest($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $result = $rest->sendTemplateSMS(
            $phone, array(
            $code,
            5
        ), 59939
        );
        if ($result == null) {
            return false;
        }
        if ($result->statusCode != 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 发送 容联云通讯 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_alidayu_sms_code($phone, $code, $type) {
        include VENDOR_PATH."taobao/TopSdk.php";
        include VENDOR_PATH."taobao/top/TopClient.php";
        include VENDOR_PATH."taobao/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
        // 获取阿里大鱼配置信息
        if (file_exists(SITE_PATH."conf/sms/alidayu.php")) {
            $dayuconfig = include SITE_PATH."conf/sms/alidayu.php";
        } else {
            return false;
        }
        if (empty($dayuconfig)) {
            return false;
        }
        $product = $dayuconfig['PRODUCT'];
        $content = array(
            "code"    => "".$code,
            "product" => $product
        );
        $smstemp = 'SMSTEMPAUTH';
        if ($type == 1) {
            $smstemp = 'SMSTEMPREG';
        }
        $c = new \TopClient();
        $c->appkey = $dayuconfig['APPKEY'];
        $c->secretKey = $dayuconfig['APPSECRET'];
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend($dayuconfig['SETEXTEND']);
        $req->setSmsType($dayuconfig['SMSTYPE']);
        $req->setSmsFreeSignName($dayuconfig['SMSFREESIGNNAME']);
        $req->setSmsParam(json_encode($content));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($dayuconfig[$smstemp]);
        $resp = $c->execute($req);
        $resp = (array)$resp;
        if (!empty($resp['result'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送 阿里云短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_aliyun_sms_code($phone, $code, $type) {
        include VENDOR_PATH."aliyun/aliyun-php-sdk-core/Config.php";
        include VENDOR_PATH."aliyun/aliyun-php-sdk-sms/Sms/Request/V20160927/SingleSendSmsRequest.php";
        // 获取阿里云短信配置信息
        // 获取阿里大鱼配置信息
        if (file_exists(SITE_PATH."conf/sms/aliyun.php")) {
            $_config = include SITE_PATH."conf/sms/aliyun.php";
        } else {
            return false;
        }
        if (empty($_config)) {
            return false;
        }
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $_config['APPKEY'], $_config['APPSECRET']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new \SingleSendSmsRequest();
        $request->setSignName($_config['SIGNNAME']);/*签名名称*/
        $request->setTemplateCode($_config['SMSTEMPAUTH']);/*模板code*/
        $request->setRecNum($phone);/*目标手机号*/
        $_content = array(
            "code"    => ''.$code,
            "product" => ''.$_config['PRODUCT']
        );
        $request->setParamString(json_encode($_content));/*模板变量，数字一定要转换为字符串*/
        try {
            $response = $client->getAcsResponse($request);
            return true;
        }
        catch (\ClientException  $e) {
            return false;
        }
        catch (\ServerException  $e) {
            return false;
        }
        return false;
    }

    /**
     * 发送 商讯短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_shangxun_sms_code($phone, $code, $type) {
        // 获取商讯短信配置信息
        if (file_exists(SITE_PATH."conf/sms/shangxun.php")) {
            $sx_config = include SITE_PATH."conf/sms/shangxun.php";
        } else {
            return false;
        }
        if (empty($sx_config)) {
            return false;
        }
        $msg = urlencode(self::content($code));
        $name = $sx_config['SMS_ACC'];
        $pwd = $sx_config['SMS_PWD'];
        $url = $sx_config['SMS_URL'];
        $ret = file_get_contents($url."?name=$name&pwd=$pwd&dst=$phone&msg=$msg");
        $result = explode("&", $ret);
        $num = explode("=", $result[0]);
        if (0 == $num[1]) {
            return true;
        } else {
            return false;
        }
    }

    protected static function content($capcha) {
        return str_replace("#code#", $capcha, self::auto_read(self::$template, "GBK"));
    }

    /**
     * 自动解析编码读入文件
     *
     * @param string $str     字符串
     * @param string $charset 读取编码
     *
     * @return string 返回读取内容
     */
    private static function auto_read($str, $charset = 'UTF-8') {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }

    /**
     * 发送 juhe短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_juhe_sms_code($phone, $code, $type) {
        // 获取商讯短信配置信息
        if (file_exists(SITE_PATH."conf/sms/juhe.php")) {
            $jh_config = include SITE_PATH."conf/sms/juhe.php";
        } else {
            return false;
        }
        if (empty($jh_config)) {
            return false;
        }
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $tplValue = urlencode("#code#=".$code);
        $smsConf = array(
            'key'       => $jh_config['APPKEY'], //您申请的APPKEY
            'mobile'    => $phone, //接受短信的用户手机号码
            'tpl_id'    => $jh_config['TEMPLETID'], //您申请的短信模板ID，根据实际情况修改
            'tpl_value' => $tplValue //您设置的模板变量，根据实际情况修改
        );
        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信
        if ($content) {
            $result = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
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

    /**
     * 发送 创蓝短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_chuanglan_sms_code($phone, $code, $type) {
        include LIB_PATH."Huosdk/sms/ChuanglanSmsApi.class.php";
        $req = new \ChuanglanSmsApi();
        $result = $req->sendSMS($phone, $code, true);
        $result = $req->execResult($result);
        if (0 == $result[1]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送 企信通 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     *
     * @return boole 是否发送成功
     */
    private function send_qixintong_sms_code($phone, $code) {
        // 获取容联云配置信息
        if (file_exists(SITE_PATH."conf/sms/qixintong.php")) {
            $config = include SITE_PATH."conf/sms/qixintong.php";
        } else {
            return false;
        }
        if (empty($config)) {
            return false;
        }
        $usr = $config['USR'];  //用户名
        $pw = $config['PW'];  //密码
        $tem = $config['TEM'];  //模板类型
        $mob = $phone;  //手机号,只发一个号码：13800000001。发多个号码：13800000001,13800000002,...N 。使用半角逗号分隔。
        $mt = "验证码".$code."，您正在注册牛刀手游，请妥善保管验证码";  //要发送的短信内容，特别注意：签名必须设置，网页验证码应用需要加添加【图形识别码】。
        $mt = urlencode($mt);//执行URLencode编码  ，$content = urldecode($content);解码
        $sendstring = "usr=".$usr."&pw=".$pw."&mob=".$mob."&mt=".$mt;
        $url = $config['URL'];
        $sendline = $url."?".$sendstring;
        $result = @file_get_contents($sendline);
        if ($result == "00" || $result == "01") {
            return true;
        } else {
            return false;
        }
    }
}