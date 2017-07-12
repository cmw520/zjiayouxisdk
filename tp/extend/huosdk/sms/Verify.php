<?php
/**
 * Verify.php UTF-8
 * 短信验证
 *
 * @date    : 2016年11月11日下午10:40:54
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午10:40:54
 */
namespace huosdk\sms;

use think\Log;
use think\Session;

class Verify extends Sms {
    private $mobile;
    private $smstype;
    private $smscode;
    private $expaire_time;

    /**
     * 自定义错误处理
     *
     * @param 输出的文件  $msg
     * @param string $level
     *
     * @internal param 输出的文件 $msg
     */
    private function _error($msg, $level = 'error') {
        $_info = 'sms\Verify Error:'.$msg;
        Log::record($_info, $level);
    }

    /**
     * 构造函数
     *
     * @param int $mobile
     * @param     $smscode
     * @param int $smstype
     * @param int $expaire_time
     *
     * @internal param string $rsa_pri_path rsa私钥地址
     */
    public function __construct($mobile, $smscode, $smstype = 0, $expaire_time = 120) {
        $this->mobile = $mobile;
        $this->smscode = $smscode;
        $this->smstype = $smstype;
        $this->expaire_time = $expaire_time;
    }

    /**
     * @return array
     */
    public function check() {
        $_rdata = array(
            'code' => '413',
            'msg'  => ''
        );
        /* 检查手机号格式 */
        $_check_flag = $this->checkMoblie($this->mobile);
        if (!$_check_flag) {
            $_rdata['msg'] = '手机号格式错误';
            return $_rdata;
        }
        $_sms_info = Session::get('sms');
        /* 查询是否发送过验证码 */
        if (!isset($_sms_info['smscode'])) {
            $_rdata['code'] = '416';
            $_rdata['msg'] = '请发送验证码';
            return $_rdata;
        }
        /* 查询验证码是否过期 */
        if ($_sms_info['expaire_time'] < time()) {
            $_rdata['code'] = '416';
            $_rdata['msg'] = '验证码已过期,请重新发送';
            return $_rdata;
        }
        if ($_sms_info['smscode'] != $this->smscode) {
            $_rdata['code'] = '416';
            $_rdata['msg'] = '验证码错误';
            return $_rdata;
        }
//         if ($_sms_info['smstype'] != $this->smstype) {
//             $_rdata['code'] = '414';
//             $_rdata['msg'] = '验证码类别错误';
//             return $_rdata;
//         }
        Session::set('sms', null);
        $_rdata['code'] = '200';
        $_rdata['msg'] = '验证通过';
        return $_rdata;
    }
}