<?php
namespace huosdk\common;
/**
 * Authcode.php UTF-8
 * 对称加密函数
 *
 * @date    : 2016年11月9日下午11:29:44
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月9日下午11:29:44
 */
class Authcode {
    public function __construct() {
    }

    function discuzAuthcode($string, $operation = 'DECODE', $key = '', $expiry = 0, $len = 128) {
        $_defuat_key = '';
        $ckey_length = 4;
        $key = md5($key ? $key : $_defuat_key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ?
            substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE'
            ? base64_decode(substr($string, $ckey_length))
            : sprintf(
                  '%010d',
                  $expiry ? $expiry + time() : 0
              ).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, $len - 1);
        $rndkey = array();
        for ($i = 0; $i <= $len - 1; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < $len; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % $len;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % $len;
            $j = ($j + $box[$a]) % $len;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % $len]));
        }
        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
                && substr($result, 10, 16) == substr(
                    md5(substr($result, 26).$keyb),
                    0,
                    16
                )
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    function generate_key() {
        $random = random(32);
        $info = md5(
            $_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_NAME'].$_SERVER['SERVER_ADDR'].$_SERVER['SERVER_PORT']
            .$_SERVER['HTTP_USER_AGENT'].time()
        );
        $return = '';
        for ($i = 0; $i < 64; $i++) {
            $p = intval($i / 2);
            $return[$i] = $i % 2 ? $random[$p] : $info[$p];
        }
        return implode('', $return);
    }
}