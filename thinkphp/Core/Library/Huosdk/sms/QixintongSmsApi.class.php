<?php
class QixintongSmsApi {
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS($mobile, $code, $needstatus = 'false', $extno = '') {
	    //获取创蓝短信配置信息
	    if(file_exists(SITE_PATH."conf/sms/qixintong.php")){
	        $config = include SITE_PATH."conf/sms/qixintong.php";
	    }else{
	        $config = array();
	    }
        if (empty($config)) {
            return false;
        }
        $usr=$config['USR'];  //用户名
        $pw=$config['PW'];  //密码
        $tem=$config['TEM'];  //模板类型
        $mob=$mobile;  //手机号,只发一个号码：13800000001。发多个号码：13800000001,13800000002,...N 。使用半角逗号分隔。

        $mt="验证码".$code."，您正在注册牛刀手游，请妥善保管验证码";  //要发送的短信内容，特别注意：签名必须设置，网页验证码应用需要加添加【图形识别码】。

        $mt = urlencode($mt);//执行URLencode编码  ，$content = urldecode($content);解码

        $sendstring = "usr=".$usr."&pw=".$pw."&mob=".$mob."&mt=".$mt;
        $url = $config['URL'];
        $sendline = $url."?".$sendstring;
        $result = @file_get_contents($sendline);
        if ($result=="00" || $result == "01") {
            return true;
        } else {
            return false;
        }
	}
}
?>