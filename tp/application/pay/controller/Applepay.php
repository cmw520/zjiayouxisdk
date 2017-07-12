<?php
/**
 * Applepay.php UTF-8
 * 苹果支付下单 验单
 *
 * @date    : 2016年12月20日下午4:20:49
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月20日下午4:20:49
 */
namespace app\pay\controller;

use think\Session;
use app\common\controller\Baseplayer;

class Applepay extends Baseplayer {
    function _initialize() {
        parent::_initialize();
    }

    /*
     * 玩家打开支付页面预下单
     */
    function preorder() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'user_token',
            'timestamp',
            'device_id',
            'userua',
            'cp_order_id',
            'product_price',
            'product_count',
            'product_id',
            'product_name',
            'product_desc',
            'role_type',
            'server_id',
            'server_name',
            'role_id',
            'role_name'
        );
        $_pay_data = $this->getParams($_key_arr);
        Session::set('order', $this->rq_data['orderinfo']);
        Session::set('order_time', time(), 'order');
        Session::set('role', $this->rq_data['roleinfo']);
        $_pay_class = new \huosdk\pay\Pay();
        // sdk预下单
        $_rs = $_pay_class->sdkPreorder($_pay_data);
        if (false == $_rs) {
            return hs_player_responce('1000', '下单失败');
        }
        $_rdata['order_id'] = Session::get('order_id', 'order');
        $_rdata['paytoken'] = md5(uniqid(hs_random(6)));
        Session::set('paytoken', $_rdata['paytoken'], 'order');
        Session::set('order_time', time(), 'order');
        return hs_player_responce('201', '下单成功', $_rdata, $this->auth_key);
    }

    /*
     * 玩家选择支付方式 直接下单
     */
    public function checkorder() {
        $_key_arr = array(
            'app_id',
            'client_id',
            'from',
            'user_token',
            'timestamp',
            'device_id',
            'userua',
            'order_id',
            'trans_id',
            'appverifystr',
            'paytoken',
        );
        $_pay_data = $this->getParams($_key_arr);
        $_order_id = $_pay_data['order_id'];
        $_trans_id = $_pay_data['trans_id'];
        $_is_sandbox = isset($_pay_data['is_sandbox']) ? $_pay_data['is_sandbox'] : 2;
        $_appverifystr = $_pay_data['appverifystr'];
        $_payway = 'applepay';
        /* 验证paytoken */
//         $_s_paytoken = Session::get('paytoken', 'order');
//         if ( $_pay_token != $_s_paytoken) {
//             return hs_pay_responce(0,"非法请求,参数错误");
//         }
        if (1 == $_is_sandbox) {
            $_is_sandbox == 1;
        } else {
            $_is_sandbox == 2;
        }
        /* 1更新支付方式 */
        $_p_class = new  \huosdk\pay\Pay();
        $_p_class->upPayway($_order_id, $_payway);
        /* 2 验证订单是否通过 */
        $_pay_class = new \huosdk\pay\Applepay($_is_sandbox);
        $_check_rs = $_pay_class->clientPay($_appverifystr);
        /* 3通知CP */
        if ($_check_rs) {
            /* 验证订单合法性 是否与产品对应上 */
            $_amount = $_pay_class->getProductprice($_check_rs['product_id'], $_order_id);
            if (false == $_amount || 0.01 > $_amount) {
                return hs_player_responce('434', '验单失败');
            }
            $_p_class->sdkNotify($_order_id, $_amount, $_trans_id);
        }
        /* 4 返回信息给客户端 */
        $_rdata = $_p_class->queryOrder($_order_id);
        if (false == $_rdata) {
            return hs_player_responce('434', '验单失败');
        }
        return hs_player_responce('200', '查询成功', $_rdata, $this->auth_key);
    }
}