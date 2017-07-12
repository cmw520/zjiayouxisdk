<?php
/**
 * Request.php UTF-8
 * 各个请求函数
 *
 * @date    : 2016年11月16日下午2:45:36
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午2:45:36
 */
/**
 * Rsaauth.class.php UTF-8
 * 对称 非对称加密 请求与返回
 *
 * @date    : 2016年11月9日下午11:46:45
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月9日下午11:46:45
 */
namespace huosdk\request;

use think\Log;

class Request {
    /**
     * 自定义错误处理
     *
     * @param 输出的文件  $msg
     * @param string $level
     *
     * @internal param 输出的文件 $msg
     */
    private function _error($msg, $level = 'error') {
        $_info = 'request\Request Error:'.$msg;
        Log::record($_info, $level);
    }

    /**
     * 异步请求
     *
     * @param $url     string 请求地址与端口
     * @param $post    string post数据
     * @param $cookie  string cookie数据
     * @param $timeout int 请求超时时间
     *
     * @return 请求结果
     */
    public static function asyncRequst($url, $post, $cookie, $timeout) {
        return self::socketRequest($url, 0, $post, $cookie, true, '', $timeout, false);
    }

    /**
     * socket 请求函数
     *
     * @param             $url        string 请求地址与端口
     * @param             $limit      int 读取长度
     * @param             $post       string post数据
     * @param             $cookie     string cookie数据
     * @param             $bysocket   bool 是否使用socket
     * @param bool|string $ip         bool 请求连接的IP 空则取$host
     * @param             $timeout    int 请求超时时间
     * @param             $block      bool 是否阻塞
     * @param             $encodetype string 是否启用urldecode
     *
     * @return 请求结果
     */
    public static function socketRequest(
        $url, $limit = 0, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true,
        $encodetype = 'URLENCODE'
    ) {
        $return = '';
        $matches = parse_url($url);
        $scheme = $matches['scheme'];
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;
        if ($post) {
            $out = "POST $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $boundary = $encodetype == 'URLENCODE' ? '' : ';'.substr($post, 0, trim(strpos($post, "\n")));
            $header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n"
                : "Content-Type: multipart/form-data$boundary\r\n";
            $header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $header .= "Host: $host:$port\r\n";
            $header .= 'Content-Length: '.strlen($post)."\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cache-Control: no-cache\r\n";
            $header .= "Cookie: $cookie\r\n\r\n";
            $out .= $header.$post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $header .= "Host: $host:$port\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cookie: $cookie\r\n\r\n";
            $out .= $header;
        }
        $fpflag = 0;
        if (!$bysocket || !$fp = @fsocketopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout)) {
            $context = array('http' => array('method'  => $post ? 'POST' : 'GET', 'header' => $header,
                                             'content' => $post, 'timeout' => $timeout
            )
            );
            $context = stream_context_create($context);
            $fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
            $fpflag = 1;
        }
        if (!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                while (!feof($fp) && !$fpflag) {
                    if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }
                $stop = false;
                while (!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if ($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);

            return $return;
        }
    }

    /**
     * CP 回调请求函数
     *
     * @param $url    string 请求地址与端口
     * @param $params string post数据
     *
     * @return 请求结果
     */
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        $responseText = curl_exec($curl);
        curl_close($curl);
        $rs = strtoupper($responseText);
        $result = 0;
        if ('SUCCESS' == $rs) {
            $result = 1;
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * CP 回调请求函数
     *
     * @param $url    string 请求地址与端口
     * @param $params string post数据
     *
     * @return 请求结果
     */
    public static function httpJsonpost($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                   'Content-Type: application/json; charset=utf-8',
                   'Content-Length: '.strlen($params))
        );
        //ob_start();
        $return_content = curl_exec($ch);
        //$return_content = ob_get_contents();
        //ob_end_clean();
        return $return_content;
    }
}