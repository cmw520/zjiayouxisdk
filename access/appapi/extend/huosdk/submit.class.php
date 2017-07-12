<?php
namespace huosdk;

require_once("function.php");
require_once("md5.function.php");
require_once("rsa.function.php");

class Submit {

	var $huosdk_config;

	var $huosdk_gateway = 'https://www.huosdk.com?';

	function __construct($huosdk_config){
        if (empty($huosdk_config)){
            $this->Huosdk_config= include SITE_PATH.'conf/store/7881/config.php';
        }else{
            $this->Huosdk_config = $huosdk_config;
        }
	}

    function HuosdkSubmit($huosdk_config) {
    	$this->__construct($huosdk_config);
    }

	/**
	 * 生成签名结果
	 * @param $para_sort 已排序要签名的数组
	 * return 签名结果字符串
	 */
	function buildRequestMysign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);

		$mysign = "";
		switch (strtoupper(trim($this->Huosdk_config['sign_type']))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->Huosdk_config['key']);
				break;
            case "RSA" :
                $mysign = rsaSign($prestr, $this->Huosdk_config['private_key_path']);
                break;
			default :
				$mysign = "";
		}

		return $mysign;
	}

	function build7881sign($para_sort) {
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createnoSignString($para_sort);

		$mysign = "";
		switch (strtoupper(trim($this->Huosdk_config['sign_type']))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->Huosdk_config['key']);
				break;
            case "RSA" :
                $mysign = rsaSign($prestr, $this->Huosdk_config['private_key_path']);
                break;
			default :
				$mysign = "";
		}

		return $mysign;
	}

	/**
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
	function buildRequestPara($para_temp) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);
		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = $this->buildRequestMysign($para_sort);

		//签名结果与签名方式加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->Huosdk_config['sign_type']));
		return $para_sort;
	}

	/**
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
	function buildRequestParaTostringUrlencode($para_temp) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);

		//把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
		$request_data = createLinkstringUrlencode($para);

		return $request_data;
	}

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
	function buildRequestForm($para_temp, $method, $button_name) {
		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);

		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->Huosdk_gateway."_input_charset=".trim(strtolower($this->Huosdk_config['input_charset']))."' method='".$method."'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml=$sHtml.'</form>';
        // echo $sHtml;die;
		$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		return $sHtml;
	}

	/**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取处理结果
     * @param $para_temp 请求参数数组
     * @return 支付宝处理结果
     */
	function buildRequestHttp($para_temp) {
		$sResult = '';
		//待请求参数数组字符串
		$request_data = $this->buildRequestPara($para_temp);
		//远程获取数据
		$sResult = getHttpResponsePOST($this->Huosdk_gateway, $this->Huosdk_config['cacert'],$request_data,trim(strtolower($this->Huosdk_config['input_charset'])));

		return $sResult;
	}

	/**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能
     * @param $para_temp 请求参数数组
     * @param $file_para_name 文件类型的参数名
     * @param $file_name 文件完整绝对路径
     * @return 支付宝返回处理结果
     */
	function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {

		//待请求参数数组
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@".$file_name;

		//远程获取数据
		$sResult = getHttpResponsePOST($this->Huosdk_gateway, $this->Huosdk_config['cacert'],$para,trim(strtolower($this->Huosdk_config['input_charset'])));

		return $sResult;
	}

	function httpJsonPost($url, $para, $input_charset = '') {

	    if (trim($input_charset) != '') {
	        $url = $url."_input_charset=".$input_charset;
	    }
	    $curl = curl_init($url);
	    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
	    curl_setopt($curl,CURLOPT_POST,true); // post传输数据
	    curl_setopt($curl,CURLOPT_HTTPHEADER,array(
	                'Content-Type: application/json; charset=utf-8',
	                'Content-Length: ' . strlen($para)
	            ));
	    curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
	    $responseText = curl_exec($curl);
	    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
	    curl_close($curl);
	    return $responseText;
	}
}
?>