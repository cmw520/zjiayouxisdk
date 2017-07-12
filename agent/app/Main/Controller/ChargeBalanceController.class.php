<?php
namespace Main\Controller;

use Common\Controller\MainbaseController;

class ChargeBalanceController extends MainbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function charge() {
        $amount = I('amount');
        $payway = "alipay";
        $order_id = $this->setorderid(1);
        $this->charge_balance_pre($amount, $payway, $order_id);
        $this->order_balance_zfb($amount, $order_id);
    }

    public function order_balance_zfb($amount, $order_id) {
        vendor("lib.alipay_submit", "", ".class.php");
        $alipay_config = $this->get_alipay_config();
        $parameter = array(
            "service"           => "create_direct_pay_by_user",
            "partner"           => trim($alipay_config['partner']),
            "seller_id"         => trim($alipay_config['seller_id']),
            "payment_type"      => "1",
            "notify_url"        => AGENTSITE.U('Main/ChargeBalance/alipay_notify'),
            "return_url"        => AGENTSITE.U('Main/ChargeBalance/alipay_return'),
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip"   => $alipay_config['exter_invoke_ip'],
            "out_trade_no"      => $order_id,
            "subject"           => "余额充值",
            "total_fee"         => $amount,
            //"show_url"	=> AGENTSITE.U("Game/game",array('appid'=>$data['app_id'])),
            "body"              => "游戏充值",
            "it_b_pay"          => "15d",
            "extern_token"      => "",
            "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "跳转中");
        echo $html_text;
    }

    public function alipay_return() {
        vendor("lib.alipay_notify", "", ".class.php");
        $alipay_config = $this->get_alipay_config();
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
            //交易状态
            $trade_status = $_GET['trade_status'];
            //交易状态
            $amount = $_GET['total_fee'];
            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $this->charge_balance_after($out_trade_no, $amount * 100);
                $this->display('Money/charge_success');
            }
//            else{
//                $this->display('Money/charge_failed');
//            }
        } else {
            $this->display('Money/charge_failed');
        }
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
        $alipay_config['partner'] = C("alipay_config_partner");
        //安全检验码，以数字和字母组成的32位字符
        $alipay_config['key'] = C("alipay_config_key");
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

    public function charge_balance_pre($amount, $payway, $order_id) {
        $model = M('ptb_agentcharge');
        $model->add(
            array(
                "order_id"    => $order_id,
                "agent_id"    => $this->agid,
                "money"       => $amount,
                "discount"    => "1",
                "ptb_cnt"     => $amount,
                "payway"      => $payway,
                "ip"          => get_client_ip(),
                "status"      => "1",
                "create_time" => time(),
                "update_time" => time()
            )
        );
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
        //这个订单成功了，渠道用户的平台币余额就要增加
        $add_value = $real_amount / 100;
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $hs_pb_obj->Inc($this->agid, $add_value);
    }

    //生成订单号
    function setorderid($mem_id) {
        list($usec, $sec) = explode(" ", microtime());
        // 取微秒前3位+再两位随机数+渠道ID后四位
        $orderid = $sec.substr($usec, 2, 3).rand(10, 99).sprintf("%04d", $mem_id % 10000);
        return $orderid;
    }
}
