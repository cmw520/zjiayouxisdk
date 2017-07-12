<?php
class ShangxunSmsApi {
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $extno   扩展码，可选
	 */
    protected static $template = "您的验证码是：【#code#】。请不要把验证码泄露给其他人。";

	public function sendSMS($mobile, $code, $needstatus = 'false', $extno = '') {
	    //获取创蓝短信配置信息
	    if(file_exists(SITE_PATH."conf/sms/shangxun.php")){
	        $config = include SITE_PATH."conf/sms/shangxun.php";
	    }else{
	        $config = array();
	    }
        if (empty($config)) {
            return false;
        }
        $msg  = urlencode(self::content($code));
        $name = $config['SMS_ACC'];
        $pwd  = $config['SMS_PWD'];
        $url = $config['SMS_URL'];
        $ret = file_get_contents($url."?name=$name&pwd=$pwd&dst=$mobile&msg=$msg");
        $result = explode("&",$ret);
        $num = explode("=",$result[0]);
		return $num;
	}

    protected static function content($capcha){
        return str_replace("#code#", $capcha, self::auto_read(self::$template,"GBK"));
    }

    /**
     * 自动解析编码读入文件
     * @param string $str 字符串
     * @param string $charset 读取编码
     * @return string 返回读取内容
     */
    private static function auto_read($str, $charset='UTF-8') {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }
}
?>