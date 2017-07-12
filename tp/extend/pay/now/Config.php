<?php
    /**
     * 
     * 配置类
     * 接口相关的配置信息，商户需要配置(appId、secure_key)
     */
    class Config{
        static $appId = "#nowpayappid#";//商户的应用ID 
        static $secure_key = "#nowpaysecurekey#";//商户的秘钥
        static $timezone="Asia/Shanghai";//时间时区
        static $trade_time_out="3600";
        static $front_notify_url="";
        static $back_notify_url="";
        
        const TRADE_URL="https://pay.ipaynow.cn";
        const QUERY_URL="https://pay.ipaynow.cn";
        
//        const TRADE_URL="http://api.ipaynow.cn";
//        const QUERY_URL="http://api.ipaynow.cn";
        
        
        const TRADE_FUNCODE="WP001";
        const QUERY_FUNCODE="MQ001";
        const NOTIFY_FUNCODE="N001";
        const FRONT_NOTIFY_FUNCODE="N002";
        const TRADE_TYPE="01";
        const TRADE_CURRENCYTYPE="156";
        const TRADE_CHARSET="UTF-8";
        const TRADE_DEVICE_TYPE="06";
        const TRADE_SIGN_TYPE="MD5";
        const TRADE_QSTRING_EQUAL="=";
        const TRADE_QSTRING_SPLIT="&";
        const TRADE_FUNCODE_KEY="funcode";
        const TRADE_DEVICETYPE_KEY="deviceType";
        const TRADE_SIGNTYPE_KEY="mhtSignType";
        const TRADE_SIGNATURE_KEY="mhtSignature";
        const SIGNATURE_KEY="signature";
        const SIGNTYPE_KEY="signType";
        const VERIFY_HTTPS_CERT=false;
    }
