<?php
namespace huosdk;
require_once("function.php");
require_once("md5.function.php");
require_once("rsa.function.php");

class Notify {
    /**
     * HTTPS形式消息验证地址
     */
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式消息验证地址
     */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	var $huosdk_config;

	function __construct($huosdk_config){
		$this->huosdk_config = $huosdk_config;
	}
    function Notify($huosdk_config) {
    	$this->__construct($huosdk_config);
    }
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyNotify(){
		if(empty($_POST)) {//判断POST来的数组是否为空
			return false;
		}
		else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_POST, $_POST["sign"],$_POST['sign_type']);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}


			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}

	function verifyJsonnofity(){
	    $postdata = file_get_contents("php://input");
	    if(empty($postdata)) {//判断POST来的数组是否为空
	        return false;
	    } else {
	        $postdata = json_decode($postdata,true);
	        if (empty($postdata["sign"]) || empty($postdata['sign_type'])){
	            return false;
	        }
	        //生成签名结果
	        $isSign = $this->getJsonSignVeryfy($postdata, $postdata["sign"],$postdata['sign_type']);
	        if ($isSign) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	}

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
	function verifyReturn(){
		if(empty($_GET)) {//判断POST来的数组是否为空
			return false;
		}else {
			//生成签名结果
			$isSign = $this->getSignVeryfy($_GET, $_GET["sign"],$_GET["sign_type"]);
			//获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
			$responseTxt = 'true';
			if (! empty($_GET["notify_id"])) {
				$responseTxt = $this->getResponse($_GET["notify_id"]);
			}

			//写日志记录
			//if ($isSign) {
			//	$isSignStr = 'true';
			//}
			//else {
			//	$isSignStr = 'false';
			//}
			//$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
			//$log_text = $log_text.createLinkString($_GET);
			//logResult($log_text);

			//验证
			//$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
			//isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
			// p(preg_match("/true$/i",$responseTxt));
			// p($isSign);
			// die;
			if (preg_match("/true$/i",$responseTxt) && $isSign) {
				return true;
			} else {
				return false;
			}
		}
	}

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
	function getSignVeryfy($para_temp, $sign,$sign_type) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);
		$isSgin = false;
		switch (strtoupper(trim($sign_type))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->huosdk_config['key']);
				break;
            case "RSA" :
                $isSgin = rsaVerify($prestr, $sign,$this->huosdk_config['public_key_path']);
                break;
			default :
				$isSgin = false;
		}
		return $isSgin;
	}

	function getJsonSignVeryfy($para_temp, $sign,$sign_type) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createnoSignString($para_temp);
// 		$sign = rsaSign($prestr, $this->huosdk_config['private_key_path']);  //test wuyonghong

		$isSgin = false;
		switch (strtoupper(trim($sign_type))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->huosdk_config['key']);
				break;
            case "RSA" :
                $isSgin = rsaVerify($prestr, $sign,$this->huosdk_config['public_key_path']);
                break;
			default :
				$isSgin = false;
		}

		return $isSgin;
	}

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->huosdk_config['transport']));
		$partner = trim($this->huosdk_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponseGET($veryfy_url, $this->huosdk_config['cacert']);

		return $responseTxt;
	}
}
?>
