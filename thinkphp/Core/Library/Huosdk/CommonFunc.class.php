<?php
/**
 * CommonFunc.class.php UTF-8
 * 公共静态类
 *
 * @date    : 2016年10月11日下午11:16:07
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : huosdk 7.0
 */
namespace Huosdk;
class CommonFunc {
    public static function payback($url, $params) {
        $params = json_encode($params);
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);//设置传送的参数
        curl_setopt(
            $curl, CURLOPT_HTTPHEADER, array(
                     'Content-Type: application/json',
                     'Content-Length: '.strlen($params))
        );
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        $rs = curl_exec($curl);//运行curl
        $rs = strtoupper($rs);
        $result = 0;
        if ($rs == 'SUCCESS') {
            $result = 1;
        } else {
            $result = 0;
        }
        curl_close($curl);//关闭curl
        return $result;
    }

    public static function cpPayback($url, $params) {
        $curl = curl_init(); //初始化curl
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, 1); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);// post传输数据
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $responseText = curl_exec($curl);
        curl_close($curl);
        $rs = strtoupper($responseText);
        $result = 0;
        if ('SUCCESS' == $rs) {
            $result = 1;
        } else {
            $result = 0;
        }
// echo $responseText;
        return $result;
    }
}

