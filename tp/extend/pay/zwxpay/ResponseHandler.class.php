<?php

/**
 *　对返回的数据进行签名验证
 */

class ResponseHandler  {
	
	/** 密钥 */
	var $key;
	
	/** 应答的参数 */
	var $parameters;
	
	//原始内容
	var $content;
	
	function __construct() {
		$this->ClientResponseHandler();
	}
	
	function ClientResponseHandler() {
		$this->key = "";
		$this->parameters = array();
		$this->content = "";
	}
		
	/**
	*获取密钥
	*/
	function getKey() {
		return $this->key;
	}
	
	/**
	*设置密钥
	*/	
	function setKey($key) {
		$this->key = $key;
	}
	
	//设置原始内容
	function setContent($content) {
		$this->content = $content;

		$xml = simplexml_load_string($this->content);
		$encode = $this->getXmlEncode($this->content);

		if($xml && $xml->children()) {
			foreach ($xml->children() as $node){
				//有子节点
				if($node->children()) {
					$k = $node->getName();
					$nodeXml = $node->asXML();
					$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);

				} else {
					$k = $node->getName();
					$v = (string)$node;
				}

				if($encode!="" && $encode != "UTF-8") {
					$k = iconv("UTF-8", $encode, $k);
					$v = iconv("UTF-8", $encode, $v);
				}

				$this->setParameter($k, $v);
			}
		}
	}
	
	//获取原始内容
	function getContent() {
		return $this->content;
	}
	
	/**
	*获取参数值
	*/	
	function getParameter($parameter) {
		return isset($this->parameters[$parameter])?$this->parameters[$parameter] : '';
	}
	
	/**
	*设置参数值
	*/	
	function setParameter($parameter, $parameterValue) {
		$this->parameters[$parameter] = $parameterValue;
	}
	
	/**
	*获取所有请求的参数
	*@return array
	*/
	function getAllParameters() {
		return $this->parameters;
	}	
	
	/**
	*　接口返回的数据，是否正确的签名
	*/	
	function isRightSign() {
		$signPars = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("sign" != $k && "" != $v) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		
		$sign = strtolower(md5($signPars));
		
		$signOrigin  = strtolower($this->getParameter("sign"));

		return $sign == $signOrigin;
		
	}
	
	//获取xml编码
	function getXmlEncode($xml) {
		$ret = preg_match ("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $xml, $arr);
        return $ret == true ? strtoupper ( $arr[1] ) : "";
	}
	
}


?>