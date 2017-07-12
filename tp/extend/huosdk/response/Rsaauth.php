<?php
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
namespace huosdk\response;

use think\Log;
use think\Loader;
use huosdk\common\Rsa;
use huosdk\common\Authcode;

class Rsaauth {
    private $rsa_pri_path    = null;
    private $limit_time_diff = 5;
    private $time_flag       = true;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'Rsaauth Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * Rsaauth constructor.
     *
     * @param bool   $time_flag 时间标记
     * @param int    $limit_time_diff
     * @param string $rsa_pri_path
     */
    public function __construct($time_flag = true, $limit_time_diff = 5, $rsa_pri_path = '') {
        $this->time_flag = $time_flag;
        $this->limit_time_diff = $limit_time_diff;
        $this->rsa_pri_path = $rsa_pri_path;
    }

    public function getAuthkey($key = '', $change_flag = true, $rsa_pri_path = '') {
        $_pri_path = $rsa_pri_path;
        if (empty($_pri_path)) {
            $_pri_path = $this->rsa_pri_path;
        }
        $_rsa_class = new \huosdk\common\Rsa('', $_pri_path);
        $_key = $key;
        if (empty($_key) && !empty($_POST['key'])) {
            $_key = urldecode($_POST['key']);
        }
        $_rsa_key = $_rsa_class->decrypt($_key);
        if (!$_rsa_key) {
            return false;
        }
        $_rsa_key_arr = explode('_', $_rsa_key);
        $_client_id = $_rsa_key_arr[0];
        $_time = $_rsa_key_arr[1];
        $_rand16 = $_rsa_key_arr[2];
        if (empty($_client_id) || empty($_time) || empty($_rand16)) {
            return false;
        }
        if ($change_flag) {
            $_game_class = new \huosdk\game\Game(0, $_client_id);
            $_client_key = $_game_class->getClientkey($_client_id);
            if (empty($_client_key)) {
                return false;
            }
            $_auth_key = $_client_key.$_rand16;
        } else {
            $_auth_key = $_rsa_key;
        }
//         $_time_diff = $this->timeDiff($_time);
//         if ($this->time_flag && $_time > $this->limit_time_diff){
//             return false;
//         }
        return $_auth_key;
    }

    public function timeDiff($time) {
        $_now_time = time();
        $_time_diff = abs($_now_time - $time);
        return $_time_diff;
    }

    /**
     * 获取请求数据
     *
     * @param $path 文件创建路径
     * @param $name 文件创建名称
     *
     * @return bool 成功返回true 失败返回 false
     */
    public function getRqdata($key, $data = '') {
        $_data = $data;
        if (empty($_data) && !empty($_POST['data'])) {
            $_data = urldecode($_POST['data']);
        }
        $_ac_class = new \huosdk\common\Authcode();
        $_rq_data = $_ac_class->discuzAuthcode($_data, 'DECODE', $key);
        if (empty($_rq_data)) {
            return false;
        }
        $_rq_data = json_decode($_rq_data, true);
        return $_rq_data;
    }

    public function getAuthdata(array $responcedata, $key) {
        $_authdata['responcedata'] = json_encode($responcedata);
        $_rsa_class = new \huosdk\common\Rsa('', $this->rsa_pri_path);
        $_authdata['sign'] = $_rsa_class->sign($_authdata['responcedata']);
        //对称加密
        $_auth_class = new \huosdk\common\authCode();
        $_auth_jsondata = json_encode($_authdata);
        return $_auth_class->discuzAuthcode($_auth_jsondata, 'ENCODE', $key, 0);
    }
}