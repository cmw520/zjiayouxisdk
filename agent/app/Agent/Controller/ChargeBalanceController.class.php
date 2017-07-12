<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class ChargeBalanceController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function charge() {
        $amount = I('amount/f', 0);
        $payway = I("payway");
        $order_id = $this->setorderid(1);
        $amount = $amount / C('G_RATE');
        $this->charge_balance_pre($amount, $payway, $order_id);
        if ($payway == 'alipay') {
            $this->order_balance_zfb($amount, $order_id);
        } else if ($payway == 'wxpay') {
            //$this->display('Money/charge_ok');
            $this->order_balance_wxpay($amount, $order_id);
        } else if ($payway == 'bank-pay') {
            $this->display('Money/charge_ok');
        } else if ($payway == 'ab') {
            $hs_money_obj = new \Huosdk\Settle();
            $account_balance = $hs_money_obj->getAccountRemain($this->agid);
            if ($amount > $account_balance) {
                redirect(U('Agent/money/charge_fail')."?p=1");
                exit;
            }
            $hs_money_obj->DecAgentAccountBalance($this->agid, $amount);
            $hs_pb_obj = new \Huosdk\PtbBalance();
            $hs_pb_obj->addIncRecord_ab($this->agid, $this->agid, $amount, "账户余额充值平台币");
            $hs_pb_obj->Inc($this->agid, $amount * C('G_RATE'));
            redirect(U('Agent/money/charge_ok'));
            exit;
        }
    }

    public function order_balance_wxpay($amount, $order_id) {
        if ($this->site_type == 'agent') {
            $notify_url = AGENTSITE.U('Front/Pay/wxnotify');
        } else {
            $notify_url = SUBAGENTSITE.U('Front/Pay/wxnotify');
        }
        header("Content-type:text/html;charset=utf-8");
        import("Vendor/wftpay/Utils");
        import("Vendor/wftpay/class/RequestHandler");
        import("Vendor/wftpay/class/ClientResponseHandler");
        import("Vendor/wftpay/class/PayHttpClient");
        $config = $this->getwxconfig();
        $resHandler = new \ClientResponseHandler();
        $reqHandler = new \RequestHandler();
        $pay = new \PayHttpClient();
        //导入回调地址
        $reqHandler->setGateUrl($config['url']);
        $reqHandler->setKey($config['key']);
        $reqHandler->setParameter('out_trade_no', $order_id);
        $reqHandler->setParameter('body', "购买".C('CURRENCY_NAME'));
        $reqHandler->setParameter('attach', C('CURRENCY_NAME')."充值");
        $reqHandler->setParameter('total_fee', $amount * 100);
        $reqHandler->setParameter('mch_create_ip', get_client_ip());
        $reqHandler->setParameter('time_start', date('YmdHis', time()));
        $reqHandler->setParameter('time_expire', date('YmdHis', time() + 7200));
        $reqHandler->setParameter('service', 'pay.weixin.native');//接口类型：pay.weixin.native
        $reqHandler->setParameter('mch_id', $config['mchId']);//必填项，商户号，由威富通分配
        $reqHandler->setParameter('version', $config['version']);
        //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];			//$reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
        $reqHandler->setParameter('notify_url', $notify_url);
        $reqHandler->setParameter('nonce_str', mt_rand(time(), time() + rand()));//随机字符串，必填项，不长于 32 位
        $reqHandler->createSign();//创建签名
        $rdata = \Utils::toXml($reqHandler->getAllParameters());//var_dump($rdata);
//        $hs_debug_obj=new \HuoShu\Debug("/debug/charge_debug");
//        $hs_debug_obj->log($rdata);
        $pay->setReqContent($reqHandler->getGateURL(), $rdata);
        if ($pay->call()) {
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());
            if ($resHandler->isTenpaySign()) {
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if ($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0) {
                    $code_img_url = $resHandler->getParameter('code_img_url');
                    $this->assign('code_img_url', $code_img_url);
                    $this->assign('out_trade_no', $order_id);
                    $this->display('pcnativepay');
                    exit;
                } else {
                    echo json_encode(
                        array('status' => 500,
                              'msg'    => 'Error Code:'.$resHandler->getParameter('err_code').' Error Message:'
                                          .$resHandler->getParameter('err_msg'))
                    );
                    exit();
                }
            }
            echo json_encode(
                array('status' => 500, 'msg' => 'Error Code:'.$resHandler->getParameter('status').' Error Message:'
                                                .$resHandler->getParameter('message'))
            );
        } else {
            echo json_encode(
                array('status' => 500,
                      'msg'    => 'Response Code:'.$pay->getResponseCode().' Error Info:'.$pay->getErrInfo())
            );
        }
    }

    public function getwxconfig() {
        $conffile = SITE_PATH."conf/pay/spay/config.php";
        if (file_exists($conffile)) {
            $spayconf = include $conffile;
        } else {
            $spayconf = array();
        }

        return $spayconf;
    }

    public function order_balance_zfb($amount, $order_id) {
        vendor("lib.alipay_submit", "", ".class.php");
        $alipay_config = $this->get_alipay_config();
        $parameter = array(
            "service"           => "create_direct_pay_by_user",
            "partner"           => trim($alipay_config['partner']),
            "seller_id"         => trim($alipay_config['seller_id']),
            "payment_type"      => "1",
            //这里的异步回调必须是外部能访问的地址
            //2016-12-16 15:53:34  严旭
            "notify_url"        => AGENTSITE.U('Front/Pay/alipay_notify'),
            "return_url"        => AGENTSITE.U('Agent/ChargeBalance/alipay_return'),
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
                //这里不再进行后续操作，只进行支付状态展示，真正的操作都用异步回调实现
                //2016-12-16 15:53:43 严旭
                //$this->charge_balance_after($out_trade_no,$amount*100);
                $this->display('Money/charge_ok');
            }
//            else{
//                $this->display('Money/charge_failed');
//            }
        } else {
            $this->display('Money/charge_fail');
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
        $alipay_config['cacert'] = SITE_PATH.'conf/pay/alipay/cacert.pem';
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
                "ptb_cnt"     => $amount * C('G_RATE'),
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
