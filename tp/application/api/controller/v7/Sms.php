<?php
/**
 * Sms.php UTF-8
 * 短信接口
 *
 * @date    : 2016年8月18日下午9:47:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\api\controller\v7;

use app\common\controller\Basehuo;

class Sms extends Basehuo {
    function _initialize() {
        parent::_initialize();
    }

    /*
     * 短信发送
     */
    function send() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'device_id',
            'userua',
            'mobile',
            'smstype'
        );
        $_param_data = $this->getParams($_key_arr);
        $_mobile = $_param_data['mobile'];
        $_smstype = $_param_data['smstype'];
        $_sms_class = new \huosdk\sms\Sms();
        $_data = $_sms_class->send($_mobile, $_smstype);
        $_rdata['agentgame'] = $this->getVal($_param_data, 'agentgame', '');
        $_rdata['agentgame'] = $this->getAgentgame($_rdata['agentgame']);

        return hs_player_responce($_data['code'], $_data['msg'], $_rdata, $this->auth_key);
    }
}