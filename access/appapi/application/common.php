<?php
/**
 * common.php UTF-8
 * 公共函数文件
 * @date: 2016年8月17日上午11:38:20
 * 
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author : wuyonghong <wyh@huosdk.com>
 * @version : HUOSHU 2.0
 */
use think\Response;
use think\db;
use think\exception\HttpResponseException;

// 加解密函数
function hs_auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    
    // 密匙
    $key = md5($key ? $key : config('appclientkey'));
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(
            md5(microtime()), 
            -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    // 解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf(
            '%010d', 
            $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    
    $result = '';
    $box = range(0, 255);
    
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(
                md5(substr($result, 26) . $keyb), 
                0, 
                16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

// 判断某个字符串$find是否在$string中存在
function hs_strexists($string, $find) {
    return !(strpos($string, $find) === FALSE);
}

// 日期转时间戳函数
function hs_dmktime($date) {
    if (strpos($date, '-')) {
        $time = explode('-', $date);
        return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
    }
    return 0;
}

// 随机生成多少位数字
function hs_random_num($length = 6) {
    return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// 字母数字随机数
function hs_random($length) {
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

// 生成用户key 64bit
function hs_generate_key() {
    $random = hs_random(32);
    $info = md5(
            $_SERVER['SERVER_SOFTWARE'] .
             $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_PORT'] . $_SERVER['HTTP_USER_AGENT'] .
             time());
    $return = array();
    for ($i = 0; $i < 64; $i++) {
        $p = intval($i / 2);
        $return[$i] = $i % 2 ? $random[$p] : $info[$p];
    }
    return implode('', $return);
}

// 57k手游网api返回函数
function hs_api_responce($code = 200, $msg = '', $data = array(), $type = "json") {
    if (empty($data)) {
        $data = null;
    }
    $rdata = array(
        'code' => $code, 
        'msg' => $msg, 
        'data' => $data 
    );
    $response = Response::create($rdata, $type)->code(200);
    if ($code >= 300) {
        throw new HttpResponseException($response);
    } else {
        return $response;
    }
}

function hs_set_orderid($agent_id = 1) {
    list($usec, $sec) = explode(" ", microtime());

    // 取微秒前3位+再两位随机数+渠道ID后四位
    $orderid = $sec . substr($usec, 2, 3) . rand(10, 99) . sprintf("%04d", $agent_id % 10000);
    return $orderid;
}

// 7881参数返回
function hs_7881_responce($code = 'SUCCESS', $msg = '', $data = array(), $type = "json") {
    if (empty($data)) {
        $data = null;
    }
    $rdata = $data;
    $rdata['code'] =  $code;
    $rdata['message'] =  $msg;
    $rdata['sign_type'] =  "RSA";
    $rdata['version'] =  "v1.0";
    $rdata['partner_id'] =  Config::get('conf_7881.partner_id');
    $rdata['timestamp'] =  hs_get_millisecond();

    Loader::import('huosdk.submit',EXTEND_PATH,'.class.php');
    $sub_class = new \huosdk\Submit(Config::get('conf_7881'));

    $rdata['sign'] = $sub_class->build7881sign($rdata);

    $rdata = urlencode_array($rdata);
    $response = Response::create($rdata, $type)->code(200);
    if (strtoupper($code) != 'SUCCESS') {
        throw new HttpResponseException($response);
    } else {
        return $response;
    }
}


function urldecode_array($para_temp) {
    while (list ($key, $val) = each ($para_temp)) {
        if($key == "sign") continue;
        if (is_array($val)){
            $r_array[$key] = urldecode_array($val);
            continue;
        }
        $r_array[$key] = urldecode($val);
    }
    return $r_array;
}

function urlencode_array($param_array){
    return $param_array;
    foreach ($param_array as $k => $v){
        if ("sign" == $k) {
            $rdata['sign']  = $v;
            continue;
        }

        if (is_array($v)){
            $rdata[$k] =urlencode_array($v);
        }else{
            $rdata[$k] = urlencode($v);
        }
    }
    return $rdata;
}


function hs_get_millisecond()
{
    $time = explode (" ", microtime () );
    $time = $time [1] . ($time [0] * 1000);
    $time2 = explode ( ".", $time );
    $time = $time2 [0];
    return $time;
}

function hs_set_username(){
    $basenum = 10000;

    // 生成用户名
    $min = DB::name('mem_base')->min('base');
    $cnt = DB::name('mem_base')->where(array('base'=>$min))->count('id');

    $limit = rand(0, $cnt);

    $map['base'] = $min;
    $sqllimit = $limit.',1';

    $mem_info = DB::name('mem_base')->where($map)->limit($sqllimit)->select(); 
    $uid = $mem_info[0]['id'];

    $rs = DB::name('mem_base')->where(array('id'=>$uid))->setInc('base',1);

    if(!empty($rs) && 0 < $rs){
        $username =  $basenum * $min + $uid;
    }

    $userinfo = hs_get_meminfo_byname($username);
    $i = 0;
    //存在用户一直向下执行
    while($userinfo && $i < 20){
        $i ++;
        $rs = DB::name('mem_base')->where(array('id'=>$uid))->setInc('base',1);
        if(!empty($rs) && 0 < $rs){
            $username =  $basenum * ($min+$i) + $limit;
        }
        $userinfo = hs_get_meminfo_byname($username);
    }

    return $username ;
}

/*
 * @param mem_id 玩家ID
 * 查询用户是否存在
 */
function hs_get_meminfo_byid($mem_id){
    if (empty($mem_id)){
        return null;
    }
    $map['id'] = $mem_id;
    $mem_info = DB::name('members')->where($map)->find();
    return $mem_info;
}
function hs_get_meminfo_byname($username){
    if (empty($username)){
        return null;
    }
    $map['username'] = $username;
    $mem_info = DB::name('members')->where($map)->find();
    return $mem_info;
}

/* 密码加密函数 */
function hs_pw_encode($pw, $authcode = '') {
    if (empty($authcode)) {
        $authcode = AUTHCODE;
    }

    $result = md5(md5($authcode . $pw) . $pw);
    return $result;
}
/* 比较密码 */
function hs_compare_password($password, $password_in_db) {
    return hs_pw_encode($password) == $password_in_db;
}

/**
 * 转化数据库保存的文件路径，为可以访问的url
 * @param string $file
 * @param boolean $withhost
 * @return string
 */
function hs_get_asset_path($file,$withhost=false){
    if(strpos($file,"http")===0){
        return $file;
    }else if(strpos($file,"/")===0){
        return $file;
    }else{
        $filepath='/'.$file;
        if($withhost){
            if(strpos($filepath,"http")!==0){
                $http = 'http://';
                $http =is_ssl()?'https://':$http;
                $filepath = $http.$_SERVER['HTTP_HOST'].$filepath;
            }
        }
        return $filepath;
    }
}

