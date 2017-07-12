<?php

class Config {
    private $cfg = array(
            'url' => 'https://pay.swiftpass.cn/pay/gateway', 
            'mchId' =>  '', 
            'key' => '', 
            'notify_url' =>  '', 
            'version' =>  '',
            'mch_app_id' =>  '',
            'mch_app_name' =>  ''
    );
    
    //默认构造函数 从配置文件中读取配置
    public function __construct() {
		$gconfdir = SITE_PATH."/conf/";
		if(file_exists($gconfdir."domain.inc.php")){
			include $gconfdir."domain.inc.php";
		}else{
			exit;
		}

		if(file_exists($gconfdir."pay/spay/config.php")){
			$spayconfig = include $gconfdir."pay/spay/config.php";
		}else{
			$spayconfig = array();
		}
		
        $this->cfg['mchId'] = $spayconfig['mchId'];
        $this->cfg['key'] = $spayconfig['key'];
        $this->cfg['notify_url'] = SDKSITE.'/sdk/spay/notify_url.php';
        $this->cfg['version'] = $spayconfig['version'];
        $this->cfg['mch_app_id'] = $spayconfig['mch_app_id'];
        $this->cfg['mch_app_name'] = $spayconfig['mch_app_name'];
    }
	
    public function C($cfgName) {
        return $this->cfg[$cfgName];
    }
}