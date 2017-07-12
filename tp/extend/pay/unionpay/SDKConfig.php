<?php
// 包含配置文件
$_conf_file = CONF_PATH."extra/pay/unionpay/config.php";
if (file_exists($_conf_file)) {
    $unionconfig = include $_conf_file;
} else {
    $unionconfig = array();
}
$gconfdir = substr(dirname(__FILE__), 0, -20)."/conf/extra/";
//商户代码
define('SDK_MERID', $unionconfig['SDK_MERID']);
// 签名证书密码
define('SDK_SIGN_CERT_PWD', $unionconfig['SDK_SIGN_CERT_PWD']);
// 签名证书路径
define('SDK_SIGN_CERT_PATH', $gconfdir.'pay/unionpay/certs/'.$unionconfig['PFX_NAME']);
// 密码加密证书（这条一般用不到的请随便配）
define('SDK_ENC_CERT_PATH', $gconfdir.'pay/unionpay/certs/acp_prod_enc.cer');
// 签名证书路径
define('SDK_MIDDLE_CERT_PATH', $gconfdir.'pay/unionpay/certs/acp_prod_middle.cer');
// 密码加密证书（这条一般用不到的请随便配）
define('SDK_ROOT_CERT_PATH', $gconfdir.'pay/unionpay/certs/acp_prod_root.cer');
// 验签证书路径（请配到文件夹，不要配到具体文件）
define('SDK_VERIFY_CERT_DIR', $gconfdir.'pay/unionpay/certs/');
// 前台请求地址
//const SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/gateway/api/frontTransReq.do';
const SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/gateway/api/frontTransReq.do';
// 后台请求地址
const SDK_BACK_TRANS_URL = 'https://gateway.95516.com/gateway/api/backTransReq.do';
// 批量交易
const SDK_BATCH_TRANS_URL = 'https://gateway.95516.com/gateway/api/batchTrans.do';
//单笔查询请求地址
const SDK_SINGLE_QUERY_URL = 'https://gateway.95516.com/gateway/api/queryTrans.do';
//文件传输请求地址
const SDK_FILE_QUERY_URL = 'https://filedownload.95516.com/';
//有卡交易地址
const SDK_Card_Request_Url = 'https://gateway.95516.com/gateway/api/cardTransReq.do';
//App交易地址
const SDK_App_Request_Url = 'https://gateway.95516.com/gateway/api/appTransReq.do';
// 前台通知地址 (商户自行配置通知地址)
define('SDK_FRONT_NOTIFY_URL', SDKSITE.'/sdk/unionpay/notify_font_url.php');
// 后台通知地址 (商户自行配置通知地址，需配置外网能访问的地址)
define('SDK_BACK_NOTIFY_URL', SDKSITE.'/sdk/unionpay/notify_url.php');
//文件下载目录
define('SDK_FILE_DOWN_PATH', $gconfdir.'pay/unionpay/file/');
//日志 目录
// const SDK_LOG_FILE_PATH = 'D:/logs/';
define('SDK_LOG_FILE_PATH', $gconfdir.'pay/unionpay/logs/');
//日志级别，关掉的话改PhpLog::OFF
const SDK_LOG_LEVEL = 'OFF';
//是否验证验签证书的CN，测试环境请设置false，生产环境请设置true。非false的值默认都当true处理。
const IF_VALIDATE_CN_NAME = false;
/** 以下缴费产品使用，其余产品用不到，无视即可 */
// 前台请求地址
const JF_SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/frontTransReq.do';
// 后台请求地址
const JF_SDK_BACK_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/backTransReq.do';
// 单笔查询请求地址
const JF_SDK_SINGLE_QUERY_URL = 'https://gateway.95516.com/jiaofei/api/queryTrans.do';
// 有卡交易地址
const JF_SDK_CARD_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/cardTransReq.do';
// App交易地址
const JF_SDK_APP_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/appTransReq.do';
?>