<?php

/**
 * Created by IntelliJ IDEA.
 * User: wjr
 * Date: 16-6-14
 * Time: 下午4:51
 */
/**
 * 记录调试信息
 */
define("DEBUG_MODE",false);
function sysdebug($msg) {
    if (defined("DEBUG_MODE")) {
        //TODO 检测调试开关，发布时不打印
        $params = func_get_args();
        $traces = debug_backtrace();
        $trace = array_pop($traces);
        sysrecord($params, $trace, 'debug');
    }
}

/**
 * 记录错误信息
 */
function syserror($msg) {
    $params = func_get_args();
    $traces = debug_backtrace();
    $trace = array_pop($traces);
    sysrecord($params, $trace, 'error');
}

/**
 * 写文件
 * @ignore
 */
function sysfile($filename, $msg, $mode = null) {
    $path = dirname($filename);
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    $flag = LOCK_EX;
    if ($mode) {
        switch ($mode) {
            case "add":
                $flag = FILE_APPEND | LOCK_EX;
                break;
            case "a":
                $flag = FILE_APPEND | LOCK_EX;
                break;
            default:
                break;
        }
    }
    file_put_contents($filename, $msg, $flag);
}

/**
 * 记录信息
 * @ignore
 */
function sysrecord($params, $trace, $level) {
    //$path = dirname(__FILE__) . "/logs/";
    $path = '/tmp/phplogs/php/';
    //TODO 日志保存目录最好修改一下
    $file = $trace['file'];
    $func = $trace['function'];
    if ($func == "sys$level") {
        $func = '';
    }
    $filename = $path . "$level/" . date("Y-m-d") . '.log';
    $msg = "[" . date("m-d H:i:s") . "] File:\"" . basename($file) . "\" Func:\"" . $func . "\" Msg:" . json_encode($params) . "\r\n";
    sysfile($filename, $msg, 'add');
}