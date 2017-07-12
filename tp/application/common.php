<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
// 火树玩家进过加密返回
use think\Response;
use think\Session;
use think\exception\HttpResponseException;

// 火树api返回函数
function hs_api_responce($code = 200, $msg = '', $data = array(), $type = "json") {
    if (empty($data)) {
        $data = null;
    }
    $rdata = array(
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    );
    $response = Response::create($rdata, $type)->code(200);
    if ($code >= 300) {
        throw new HttpResponseException($response);
    } else {
        return $response;
    }
}

function hs_player_responce($code = 200, $msg = '', $data = array(), $key = '', $type = "json") {
    $_key = $key;
    if (empty($data)) {
        $data = null;
    } else {
        if (empty($key)) {
            $_key = Session::get('client_key', 'app');
        }
        $_pri_path = CONF_PATH.'extra/key/rsa_private_key.pem';
        $_rp_class = new \huosdk\response\Rsaauth(false, 0, $_pri_path);
        $data = $_rp_class->getAuthdata($data, $_key);
    }
    $_rdata = array(
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    );
    $_response = Response::create($_rdata, $type)->code(200);
    if ($code >= 300) {
        throw new HttpResponseException($_response);
    } else {
        return $_response;
    }
}

function hs_huosdk_responce($code = 200, $msg = '', $data = array(), $key = '', $type = "json") {
    $_key = $key;
    if (empty($data)) {
        $data = null;
    } else {
        if (empty($key)) {
            $_key = Session::get('client_key', 'app');
        }
        $_rp_class = new \huosdk\response\Rsaauth();
        $data = $_rp_class->getAuthdata($data, $_key);
    }
    $_rdata = array(
        'code' => $code,
        'msg'  => $msg,
        'data' => $data
    );
    $_response = Response::create($_rdata, $type)->code(200);
    if ($code >= 300) {
        throw new HttpResponseException($_response);
    } else {
        return $_response;
    }
}

function hs_pay_responce($status = 0, $msg = '', $payinfo = array(), $key = '', $url = '') {
    $_key = $key;
    if (empty($payinfo)) {
        $_payinfo = null;
    } else {
        if (empty($_key)) {
            $_key = Session::get('web_key');
        }
        $_pri_path = CONF_PATH.'extra/key/rsa_private_key.pem';
        $_rp_class = new \huosdk\response\Rsaauth(false, 0, $_pri_path);
        $_payinfo = $_rp_class->getAuthdata($payinfo, $_key);
    }
    $_rdata = array(
        'status'  => 200,
        'info'    => $msg,
        'payinfo' => $_payinfo
    );
    $_rdata['referer'] = isset($url) ? $url : "";
    $_rdata['state'] = empty($status) ? "fail" : "success";
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($_rdata));
}

// 字母数字随机数
function hs_random($length) {
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

function get_val(array $data, $key, $default = '') {
    if (empty($key) || empty($data) || !isset($data[$key])) {
        return $default;
    }
    return $data[$key];
}

function get_ag_info($downid = 0) {
    if (0 == $downid) {
        return false;
    }
    $_map['id'] = $downid;
    $_ag_info = \think\Db::name('agent_game')->cache('agid_'.$downid)->where($_map)->find();
    if (empty($_ag_info)) {
        return false;
    }
    return $_ag_info;
}

/*
 * 删除空格
 */
function trim_all($str) {
    $qian = array(
        " ",
        "　",
        "\t",
        "\n",
        "\r"
    );
    $hou = array(
        "",
        "",
        "",
        "",
        ""
    );
    return str_replace($qian, $hou, $str);
}

function hs_get_help() {
    $_app_id = \think\Session::get('app_id', 'app');
    if (empty($_app_id)) {
        $map['app_id'] = 0;
    } else {
        $map['app_id'] = array('in', "0,".$_app_id);
    }
    $contact = \think\Db::name('game_contact')->where($map)->order('app_id desc')->limit(1)->find();
    return $contact;
}