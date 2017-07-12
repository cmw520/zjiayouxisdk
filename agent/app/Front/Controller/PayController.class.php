<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class PayController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
    }

    public function getwxconfig() {
        $conffile = SITE_PATH."conf/pay/spay/pcconfig.php";
        if (file_exists($conffile)) {
            $spayconf = include $conffile;
        } else {
            $spayconf = array();
        }
//        $this->spayversion = $spayconf["version"]; // 汇付宝商户号
//        $this->spayurl = $spayconf["url"]; // 汇付宝签名
//        $this->spaymchId = $spayconf["mchId"]; // 汇付宝签名
//        $this->spaykey = $spayconf["key"]; // 汇付宝签名
        return $spayconf;
    }

    public function wxnotify() {
//        $hs_debug_obj=new \HuoShu\Debug("/debug/charge_debug");
//        $hs_debug_obj->log("notify_start");
        $config = $this->getwxconfig();
//        $hs_debug_obj->log("get config data");
        import("Vendor/wftpay/Utils");
        import("Vendor/wftpay/class/ClientResponseHandler");
        $resHandler = new \ClientResponseHandler();
        $xml = file_get_contents('php://input');
        $resHandler->setContent($xml);
        $resHandler->setKey($config['key']);
        //判断签名结果
        $signResult = $resHandler->isTenpaySign();
//        $hs_debug_obj->log($signResult.'start db manipulation');
        if ($signResult) {
            if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
                //更改订单状态
                $out_trade_no = $resHandler->getParameter('out_trade_no'); //自己生成的订单状态
                $total_fee = $resHandler->getParameter('total_fee') / 100;
                //将订单状态写入数据表中
//                $hs_debug_obj->log("write order record $out_trade_no $total_fee ");
                $this->charge_balance_after($out_trade_no, $total_fee * 100);
                echo 'success';
                exit;
            } else {
                echo 'failure1';
                exit();
            }
        } else {
            echo 'failure2';
        }
    }

    public function charge_balance_after($orderid, $real_amount) {
        $model = M('ptb_agentcharge');
        $pre_data = $model->where(array("order_id" => $orderid))->find();
        //如果订单的状态已经是成功的，就不要再重复更新了
        if ($pre_data['status'] == '2') {
            return;
        }
        //如果发现实际交易金额跟记录的金额不同，就不进行后续的操作了
        //用分为单位比较两者的金额，所以传递过来的real_amount单位但是分
        if ($real_amount != $pre_data['money'] * 100) {
            return;
        }
        //订单状态标记为成功，更新时间
        $model->where(array("order_id" => $orderid))->setField("status", "2");
        $model->where(array("order_id" => $orderid))->setField("update_time", time());
        $agent_id = $model->where(array("order_id" => $orderid))->getField("agent_id");
        //这个订单成功了，渠道用户的平台币余额就要增加
        $add_value = $real_amount / 100 * C('G_RATE');
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $hs_pb_obj->Inc($agent_id, $add_value);
    }

    public function alipay_notify() {
        vendor("lib.alipay_notify", "", ".class.php");
        $alipay_config = $this->get_alipay_config();
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            //充值金额
            $amount = $_POST['total_fee'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $this->charge_balance_after($out_trade_no, $amount * 100);
            }
            echo "success";        //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    public function get_alipay_config() {
        $alipay_config['partner'] = C("partner");
        //安全检验码，以数字和字母组成的32位字符
        $alipay_config['key'] = C("key");
        $alipay_config['seller_id'] = $alipay_config['partner'];
        $alipay_config['seller_email'] = C("seller_email");
        // 商户的私钥（后缀是.pem）文件相对路径
        $alipay_config['private_key_path'] = SITE_PATH.'conf/pay/alipay/key/rsa_private_key.pem';
        // 支付宝公钥（后缀是.pem）文件相对路径
        $alipay_config['ali_public_key_path'] = SITE_PATH.'conf/pay/alipay/key/alipay_public_key.pem';
        // ↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        // 签名方式 不需修改
        $alipay_config['sign_type'] = strtoupper('MD5');
        // 字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset'] = strtolower('utf-8');
        // ca证书路径地址，用于curl中ssl校验
        // 请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert'] = SITE_PATH.'conf/cacert.pem';
        // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport'] = 'http';
        return $alipay_config;
    }
}

