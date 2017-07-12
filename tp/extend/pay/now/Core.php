<?php
    
    /**
     * 
     * @author Jupiter
     * 核心工具类
     * 说明:以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写，并非一定要使用该代码。该代码仅供参考
     */
    class Core{
        public static function paraFilter(Array $params) {
            $result=array();
            $flag=$params[Config::TRADE_FUNCODE_KEY];
            foreach($params as $key => $value){
                if (($flag==Config::TRADE_FUNCODE)&&!($key==Config::TRADE_FUNCODE_KEY||$key==Config::TRADE_DEVICETYPE_KEY
                    ||$key==Config::TRADE_SIGNTYPE_KEY||$key==Config::TRADE_SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if(($flag==Config::NOTIFY_FUNCODE||$flag==Config::FRONT_NOTIFY_FUNCODE)&&!($key==Config::SIGNTYPE_KEY||$key==Config::SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if (($flag==Config::QUERY_FUNCODE)&&!($key==Config::TRADE_SIGNTYPE_KEY||$key==Config::TRADE_SIGNATURE_KEY
                    ||$key==Config::SIGNTYPE_KEY||$key==Config::SIGNATURE_KEY)) {
                    $result[$key]=$value;
                    continue;
                }
            }
            return $result;
        }
        
        public static function buildSignature(Array $para){
            $prestr=self::createLinkString($para, true, false);
            $prestr.=Config::TRADE_QSTRING_SPLIT.md5(Config::$secure_key);			
            return md5($prestr);
        }
        public static function createLinkString(Array $para,$sort,$encode) {
			$linkStr = '';
            if ($sort) {
                $para=self::argSort($para);
            }
            foreach ($para as $key => $value){
                if ($encode) {
                    $value = $value;
					//$value=urlencode($value);
                }
                $linkStr.=$key.Config::TRADE_QSTRING_EQUAL.$value.Config::TRADE_QSTRING_SPLIT;
            }

            $linkStr=substr($linkStr, 0,count($linkStr)-2);
            return $linkStr;
        }
        private static function argSort($para) {
            ksort($para);
            reset($para);
            return $para;
        }
    }