<?php
/**
 * Unionpay.php UTF-8
 * 银联
 *
 * @date    : 2017年03月31日下午4:26:40
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : ou <ozf@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\pay;

use think\Loader;
use think\Session;

class Unionpay extends Pay {
    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct() {
        header('Content-type:text/html;charset=utf-8');
    }

    /**
     * 移动APP支付函数
     */
    public function clientPay() {
        Loader::import('pay.unionpay.acp_service');
        $params = array(
            //以下信息非特殊情况不需要改动
            'version'      => '5.1.0',                 //版本号
            'encoding'     => 'utf-8',                  //编码方式
            'txnType'      => '01',                      //交易类型
            'txnSubType'   => '01',                  //交易子类
            'bizType'      => '000201',                  //业务类型
            'frontUrl'     => config('domain.SDKSITE').'/api/unionpay/return',  //前台通知地址
            'backUrl'      => config('domain.SDKSITE').'/api/unionpay/notify',      //后台通知地址
            'signMethod'   => "01",                  //签名方法
            'channelType'  => '08',                  //渠道类型，07-PC，08-手机
            'accessType'   => '0',                  //接入类型
            'currencyCode' => '156',              //交易币种，境内商户固定156
            //TODO 以下信息需要填写
            'merId'        => SDK_MERID,        //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId'      => Session::get('order_id', 'order'),
            'txnTime'      => date('YmdHis'),    //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt'       => Session::get('real_amount', 'order') * 100,    //交易金额，单位分，此处默认取demo演示页面传递的参数
        );
        \AcpService::sign($params); // 签名
        $url = SDK_App_Request_Url;
        $result_arr = \AcpService::post($params, $url);
        if (count($result_arr) <= 0) { //没收到200应答的情况
            return false;
        }
        if (!\AcpService::validate($result_arr)) {
            return false;
        }
        if ($result_arr["respCode"] == "00") {
            $req_str['tn'] = $result_arr["tn"];
            $req_str['mode'] = "00";
            return $this->clientAjax('unionpay', json_encode($req_str));
        } else {
            return false;
        }
    }

    /*
     * 异步回调函数
     */
    public function notifyUrl($wallet = false) {
        Loader::import('pay.unionpay.acp_service');
        if (isset($_POST ['signature']) && \AcpService::validate($_POST)) {
            $orderid = $_POST ['orderId'];  //商户订单号
            $paymark = $_POST['queryId'];   //交易查询流水号 交易查询流水号
            $trade_status = isset($_POST['respCode']) ? $_POST['respCode'] : "";
            $amount = $_POST['txnAmt'] / 100;
            if ('00' == $trade_status || 'A6' == $trade_status) {
                $this->selectNotify($orderid, $amount, $paymark);
                echo '验签成功';
                exit();
            } else {
                echo '验签失败';
                exit();
            }
        } else {
            echo '签名为空';
            exit();
        }
    }

    /*
     * 返回接收页面
     */
    public function returnUrl() {
    }
}