<?php
namespace huosdk\common;
/**
 * Simplesec..php UTF-8
 * 简单对称加密算法
 *
 * @date    : 2016年11月9日下午11:29:44
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月9日下午11:29:44
 */
class Simplesec {
    /**
     * 简单对称加密算法之加密
     *
     * @param String $string 需要加密的字串
     * @param String $skey   加密EKY
     *
     * @return String
     */
    public static function encode($string = '', $skey = 'huosdk') {
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value) {
            $key < $strCount && $strArr[$key] .= $value;
        }
        return str_replace(
            array('=', '+', '/'
            ), array('O0O0O', 'o000o', 'oo00o'
            ), join('', $strArr)
        );
    }

    /**
     * 简单对称加密算法之解密
     *
     * @param String $string 需要解密的字串
     * @param String $skey   解密KEY
     *
     * @return String
     */
    public static function decode($string = '', $skey = 'cxphp') {
        $strArr = str_split(
            str_replace(
                array('O0O0O', 'o000o', 'oo00o'
                ), array('=', '+', '/'
                ), $string
            ), 2
        );
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value) {
            $key <= $strCount && isset($strArr[$key]) && $strArr[$key][1] === $value
            && $strArr[$key] = $strArr[$key][0];
        }
        return base64_decode(join('', $strArr));
    }
}