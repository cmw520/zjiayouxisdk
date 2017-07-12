<?php
class JuheSmsApi {
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $extno   扩展码，可选
	 */

	public function sendSMS($mobile, $code, $needstatus = 'false', $extno = '') {
	    //短信配置信息
	    if(file_exists(SITE_PATH."conf/sms/juhe.php")){
	        $config = include SITE_PATH."conf/sms/juhe.php";
	    }else{
	        $config = array();
	    }
        if (empty($config)) {
            return false;
        }
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $tplValue = urlencode("#code#=".$code);
        $smsConf = array(
            'key'       => $config['APPKEY'], //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => $config['TEMPLETID'], //您申请的短信模板ID，根据实际情况修改
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
?>