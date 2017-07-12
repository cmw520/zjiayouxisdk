<?php

/***
 * Class RequestHandler
 * 在请求接口前，对数据进行组装，主要用于数据的签名拼装
 */
class RequestHandler {
	
	/** 密钥 */
	var $key;
	
	/** 请求的参数 */
	var $parameters;

	function __construct() {
		$this->RequestHandler();
	}
	
	function RequestHandler() {
		$this->key = "";
		$this->parameters = array();
	}
	
	/**
	*初始化函数。
	*/
	function init() {
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
	
	/**
	*获取参数值
	*/
	function getParameter($parameter) {
		return isset($this->parameters[$parameter])?$this->parameters[$parameter]:'';
	}
	
	/**
	*设置参数值
	*/
	function setParameter($parameter, $parameterValue) {
		$this->parameters[$parameter] = $parameterValue;
	}

    /**
     * 一次性设置参数
     */
    function setReqParams($post,$filterField=null){
        if($filterField !== null){
            forEach($filterField as $k=>$v){
                unset($post[$v]);
            }
        }
        
        //判断是否存在空值，空值不提交
        forEach($post as $k=>$v){
            if(empty($v)){
                unset($post[$k]);
            }
        }

        $this->parameters = $post;
    }
	
	/**
	*获取所有请求的参数
	*@return array
	*/
	function getAllParameters() {
		return $this->parameters;
	}
	
	/**
	* 签名
	*/
	function createSign() {
		$signPars = "";
		ksort($this->parameters);
		foreach($this->parameters as $k => $v) {
			if("" != $v && "sign" != $k) {
				$signPars .= $k . "=" . $v . "&";
			}
		}
		$signPars .= "key=" . $this->getKey();
		$sign = strtoupper(md5($signPars));
		$this->setParameter("sign", $sign);
	}	


}

?>