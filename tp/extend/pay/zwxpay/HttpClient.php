<?php

/*
 * http 请求调用接口
 */

class HttpClient {
	var $reqContent = array();
	var $resContent;
	var $errInfo;
	var $timeOut;
	var $respCode;
	
	function __construct() {
		$this->HttpClient();
	}
	
	//初始化
	function HttpClient() {
		$this->reqContent = "";
		$this->resContent = "";
		$this->errInfo = "";
		$this->timeOut = 120;
		$this->respCode = 0;
	}

	function setReqContent($url,$data) {
		$this->reqContent['url']=$url;
        $this->reqContent['data']=$data;
	}

	function getResContent() {
		return $this->resContent;
	}

	function getErrInfo() {
		return $this->errInfo;
	}
	
	//设置超时时间,单位秒
	function setTimeOut($timeOut) {
		$this->timeOut = $timeOut;
	}
	
	//执行http调用
	function invoke() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $this->reqContent['url']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->reqContent['data']);
		$res = curl_exec($ch);
		$this->respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($res == NULL) { 
		   $this->errInfo = "call http err :" . curl_errno($ch) . " - " . curl_error($ch) ;
		   curl_close($ch);
		   return false;
		} else if($this->respCode  != "200") {
			$this->errInfo = "call http err httpcode=" . $this->respCode  ;
			curl_close($ch);
			return false;
		}
		curl_close($ch);
		$this->resContent = $res;
		return true;
	}
	
	function getResponseCode() {
		return $this->respCode;
	}
	
}
?>