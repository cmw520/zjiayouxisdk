<?php
namespace Huosdk\Utility;
class RemoteFile {
    function remote_filesize($uri, $user = '', $pw = '') {
        // start output buffering
        ob_start();
        // initialize curl with given uri
        $ch = curl_init($uri);
        // make sure we get the header
        curl_setopt($ch, CURLOPT_HEADER, 1);
        // make it a http HEAD request
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        // if auth is needed, do it here
        if (!emptyempty($user) && !emptyempty($pw)) {
            $headers = array('Authorization: Basic '.base64_encode($user.':'.$pw));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $okay = curl_exec($ch);
        curl_close($ch);
        // get the output buffer
        $head = ob_get_contents();
        // clean the output buffer and return to previous
        // buffer settings
        ob_end_clean();
//    echo '<br>head-->'.$head.'<----end <br>';  
        // gets you the numeric value from the Content-Length
        // field in the http header
        $regex = '/Content-Length:\s([0-9].+?)\s/';
        $count = preg_match($regex, $head, $matches);
        // if there was a Content-Length field, its value
        // will now be in $matches[1]
        if (isset($matches[1])) {
            $size = $matches[1];
        } else {
            $size = 'unknown';
        }
        //$last=round($size/(1024*1024),3);
        //return $last.' MB';
        return $size;
    }

    function getFileSize($url) {
        $url = parse_url($url);
        if ($fp = @fsockopen($url['host'], emptyempty($url['port']) ? 80 : $url['port'], $error)) {
            fputs($fp, "GET ".(emptyempty($url['path']) ? '/' : $url['path'])." HTTP/1.1\r\n");
            fputs($fp, "Host:$url[host]\r\n\r\n");
            while (!feof($fp)) {
                $tmp = fgets($fp);
                if (trim($tmp) == '') {
                    break;
                } elseif (preg_match('/Content-Length:(.*)/si', $tmp, $arr)) {
                    return trim($arr[1]);
                }
            }
            return null;
        } else {
            return null;
        }
    }
}

