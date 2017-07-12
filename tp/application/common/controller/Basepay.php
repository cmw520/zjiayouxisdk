<?php
namespace app\common\controller;

use huosdk\response\Rsaauth;
use think\Config;
use think\Cookie;
use think\Request;

class Basepay extends Base {
    protected $request; /* 请求实例 */
    protected function _initialize() {
        parent::_initialize();
        // 获取请求参数
        $_key = $this->request->param('key/s', '');
        $_data = $this->request->param('data/s', '');
        if (!empty($_key) && !empty($_data)) {
            // 解析请求数据
            $_pri_path = CONF_PATH.'extra/key/rsa_private_key.pem';
            $_rq_class = new Rsaauth(false, 0, $_pri_path);
            $_auth_key = $_rq_class->getAuthkey($_key);
            $this->auth_key = $_auth_key;
            $this->web_key = $_auth_key;
            if (false == $_auth_key) {
                $this->error("请求key错误");
            }
            $_rq_data = $_rq_class->getRqdata($_auth_key, $_data);
            if (false == $_rq_data) {
                $this->error("请求数据非法");
            }
            $this->rq_data = $_rq_data;
            $_rs = $this->se_class->setSession($this->rq_data);
            if (false === $_rs) {
                $this->resetToken();
            }
            if (!empty($this->web_key)) {
                $this->se_class->set('web_key', $this->web_key);
            }
            $this->setFloatid();
        } else {
            $this->isUserwapLogin();
        }
    }

    private function setFloatid() {
        $_mem_id = $this->se_class->get('id', 'user');
        $_session_id = session_id();
        $_float_id = $_mem_id."_".$_session_id;
        $_ss_class = new \huosdk\common\Authcode();
        $_str = $_ss_class->discuzAuthcode($_float_id, 'ENCODE', Config::get('config.COOKIEKEY'));
        Cookie::set('huosdk_float_id', $_str);
    }
}