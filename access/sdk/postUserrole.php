<?php
/**
 * CP校验用户有效性
 */
define('SYS_ROOT', dirname(__FILE__) . '/');
define('IN_SYS', TRUE);

define('CLASS_PATH', 'include/class/');
require_once SYS_ROOT . 'include/config.inc.php';

require_once (SYS_ROOT . CLASS_PATH . 'Switchlog.class.php');
require_once (SYS_ROOT . CLASS_PATH . 'Db.class.php');
require_once (SYS_ROOT . CLASS_PATH . 'Library.class.php');

$urldata = file_get_contents('php://input');
$rdata = array(
    'status' => 0, 
    'msg' => '请求参数错误' 
);

// 缺少参数
if (empty($urldata)) {
    echo json_encode($rdata);
    exit();
}

$urldata = get_object_vars(json_decode($urldata));

// $mem_id = 10236; // username
// $app_id = 60001; // app_id
// $server = '第二十九服'; // server
// $server = urlencode($server);
// $role = '猎人'; // role
// $role = urlencode($role);
// $money = '100'; // 金币数量
// $money = urlencode($money);
// $level = '200'; // level
// $level = urlencode($level);
// $experience = '100'; // experience
// $experience = urlencode($experience);
// $user_token = "rkmi2huqu9dv6750g5os11ilv2"; //token 登陆时通过SDK客户端传送给游戏服务器
// $sign = 'a317c5251ba3bc8a9106dff033e45a9e'; // token

$mem_id = isset($urldata['mem_id']) ? intval($urldata['mem_id']) : 0; // username
$app_id = isset($urldata['app_id']) ? intval($urldata['app_id']) : 0; // app_id
$server = isset($urldata['server']) ? $urldata['server'] : ''; // server
$role = isset($urldata['role']) ? $urldata['role'] : ''; // role
$money = isset($urldata['money']) ? $urldata['money'] : 0; // 金币数量
$level = isset($urldata['level']) ? $urldata['level'] : ''; // level
$experience = isset($urldata['experience']) ? $urldata['experience'] : ''; // experience
$user_token = isset($urldata['user_token']) ? $urldata['user_token'] : ''; // token 登陆时通过SDK客户端传送给游戏服务器
$sign = isset($urldata['sign']) ? $urldata['sign'] : 0; // token

if ($mem_id <= 0 || $app_id <= 0 || empty($server) || empty($role) || empty($user_token) || empty($sign)) {
    $rdata = array(
        'status' => 0, 
        'msg' => '请求参数错误' 
    );
    
    echo json_encode($rdata);
    exit();
}

$req_ip = Library::get_client_ip();
// 判断IP是否在白名单中

session_id($user_token);
session_start();
$id = session_id();
if (empty($id)) {
    exit(json_encode($rdata));
}
// 13 user_token错误
// if (empty($_SESSION['mem_id'])){
// $rdata = array(
// 'status' => 13,
// 'msg' => 'user_token错误'
// );
// echo json_encode($rdata);
// exit();
// }

// 15 mem_id错误
// if ($mem_id !=$_SESSION['mem_id']){
// $rdata = array(
// 'status' => 15,
// 'msg' => 'mem_id错误'
// );
// echo json_encode($rdata);
// exit();
// }

// if (empty($_SESSION['cp_cnt'])){
// $_SESSION['cp_cnt'] = 0;
// }

// $_SESSION['cp_cnt'] = $_SESSION['cp_cnt']++;

// //16 访问太频繁，超过访问次数
// if ($_SESSION['cp_cnt'] > 5){
// $rdata = array(
// 'status' => 16,
// 'msg' => '访问太频繁，超过访问次数'
// );
// echo json_encode($rdata);
// exit();
// }

// 链接数据库
$db = new DB();
// 10 服务器内部错误
if (empty($db)) {
    $rdata = array(
        'status' => 10, 
        'msg' => '服务器内部错误' 
    );
    echo json_encode($rdata);
    exit();
}

$appkey = $db->getAppkey($app_id);

// 11 app_id错误
if (empty($appkey)) {
    $rdata = array(
        'status' => 11, 
        'msg' => 'app_id错误' 
    );
    echo json_encode($rdata);
    exit();
}

$str = "mem_id=" . $mem_id . "&app_id=" . $app_id . "&server=" . $server . "&role=" . $role . "&money=" . $money;
$str .= "&level=" . $level . "&experience=" . $experience . "&user_token=" . $user_token . "&appkey=" . $appkey;

$verifysign = md5($str);

if ($sign != $verifysign) {
    $rdata = array(
        'status' => 12, 
        'msg' => '签名错误' . $verifysign 
    );
    echo json_encode($rdata);
    exit();
}

$userdata['mem_id'] = $mem_id;
$userdata['app_id'] = $app_id;
$userdata['money'] = 0;
$userdata['server'] = urldecode($server);
$userdata['role'] = urldecode($role);
$userdata['level'] = urldecode($level);
$userdata['experience'] = urldecode($experience);
$userdata['attach'] = urldecode($user_token);
$userdata['create_time'] = time();

$sql = " INSERT INTO  `" . DB_PREFIX . "mg_role_log` ";
$sql .= " (`mem_id`,`app_id`,`server`,`role`,`level`,`money`,`experience`,`attach`,`create_time`) ";
$sql .= " VALUES ";
$sql .= " (:mem_id,:app_id,:server,:role,:level,:money,:experience,:attach,:create_time) ";
$db->bindMore($userdata);
$rs = $db->query($sql);
if (!$rs) {
    $rdata = array(
        'status' => 10, 
        'msg' => '服务器内部错误' 
    );
    echo json_encode($rdata);
    exit();
}

$db->CloseConnection();

$rdata = array(
    'status' => '1', 
    'msg' => '请求成功' 
);
echo json_encode($rdata);
exit();