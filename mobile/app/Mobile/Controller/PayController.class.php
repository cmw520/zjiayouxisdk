<?php
namespace Mobile\Controller;

use Common\Controller\PaybaseController;

class PayController extends PaybaseController {
    // 
    protected $resHandler;
    protected $reqHandler;
    protected $pay;

    // 预下单接口
    public function preorder() {
        // 判断提交方式
        if (IS_POST) {
            $this->checkOrderInfo();
        } else {
            $this->response(array('error' => '1', 'msg' => '非法提交'));
        }
    }

    // 添加下单记录
    public function checkOrderInfo() {
        $payway = I('post.type/s', '');
        $amount = I('post.amount/f', 0);
        $pay_ip = get_client_ip();
        // 判断提交请求每隔3秒才允许重新提交
        if (!empty($_SESSION['pay_time']) && ($_SESSION['pay_time'] + 3 > time())) {
            $this->response(array('error' => '1', 'msg' => '重复请求'));
        } else {
            $_SESSION['pay_time'] = time();
        }
        if ($amount <= 0) {
            $this->response(array('error' => '1', 'msg' => '充值金额不正确，请输入大于0的数'));
        }
        if (empty($payway)) {
            $this->response(array('error' => '1', 'msg' => '请选择充值方式'));
        }
        if (empty($_SESSION['user']['id'])) {
            $this->response(array('error' => '11', 'msg' => '请登录'));
        } else {
            $mem_id = $_SESSION['user']['id'];
        }
        $agent_id = M('members')->where("id=$mem_id")->getField('agent_id');
        $gamename = I('post.gamename');
        $app_id = M('game')->where(array('name' => $gamename))->getField('id');
        // 添加充值记录
        $order_data = $this->walletPay($amount, $payway, $agent_id, $mem_id, $app_id);
        switch ($payway) {
            case 2 : {
                $this->alipay($order_data);
                break;
            }
            case 9 : {
                $this->wftpay($order_data);
                break;
            }
            default : {
                $this->response(array('error' => '1', 'msg' => '请选择充值方式'));
            }
        }
    }

    /**
     * 写入充值记录
     *
     * @param float  $amount
     * @param int    $payway
     * @param int    $agent_id
     * @param int    $mem_id
     * @param number $app_id
     */
    private function walletPay($amount, $payway, $agent_id, $mem_id, $app_id = 0) {
        if (empty($amount)) {
            $this->response(array('error' => '1', 'msg' => '充值金额不正确，请输入整数'));
        }
        if (empty($payway)) {
            $this->response(array('error' => '1', 'msg' => '请选择充值方式'));
        }
        $data['order_id'] = setorderid();
        $data['flag'] = 3;
        $data['admin_id'] = $agent_id;
        $data['app_id'] = $app_id;
        $data['money'] = $amount;
        $data['mem_id'] = $mem_id;
        $data['ptb_cnt'] = $amount;
        $data['real_amount'] = $amount;
        $data['rebate_cnt'] = 0;
        $data['discount'] = 1;
        $data['payway'] = $payway;
        $data['ip'] = get_client_ip();
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['remark'] = 'wap端充值';
        $rs = M('ptb_charge')->add($data);
        if (!$rs) {
            $this->response(array('error' => '1', 'msg' => '内部服务器发生错误'));
        }
        return $data;
    }

    /**
     * 支付宝支付接口
     *
     * @param array $data 支付下单参数
     */
    protected function alipay($data = array()) {
        if (empty($data)) {
            $this->response(array('error' => '1', 'msg' => '支付参数错误'));
        }
        $alipay_file = SITE_PATH."conf/pay/alipay/config.php";
        if (file_exists($alipay_file)) {
            $alipay_config = require $alipay_file;
            // 合并支付宝配置参数
            $alipay_config_fix = $this->merge_zfb_config();
            $alipay_config = array_merge($alipay_config, $alipay_config_fix);
            C('private_key_path', $alipay_config['private_key_path']);
        } else {
            $this->response(array('error' => '1', 'msg' => '支付宝配置文件不存在'));
        }
        //选填
        $parameter = array(
            "service"        => "alipay.wap.create.direct.pay.by.user",
            "partner"        => trim($alipay_config['partner']),
            "seller_id"      => trim($alipay_config['partner']),
            "payment_type"   => trim($alipay_config['payment_type']),
            "notify_url"     => trim($alipay_config['notify_url']),
            "return_url"     => trim($alipay_config['return_url']),
            "out_trade_no"   => $data['order_id'],
            "subject"        => trim($alipay_config['subject']),
            "total_fee"      => $data['money'],
            "show_url"       => trim($alipay_config['show_url']),
            "body"           => trim($alipay_config['body']),
            "it_b_pay"       => trim($alipay_config['it_b_pay']),
            "extern_token"   => trim($alipay_config['extern_token']),
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求，请求成功之后，会通知服务器的alipay_notify方法，客户端会通知$return_url配置的方法
        import('Vendor.Alipay.AlipaySubmit');
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "跳转中");
        echo $html_text;
    }

    /**
     *支付宝回调
     */
    function alipay_notify() {
        import('Vendor.Alipay.AlipayNotify');
        $alipay_file = SITE_PATH."conf/pay/alipay/config.php";
        if (file_exists($alipay_file)) {
            $alipay_config = require $alipay_file;
            // 合并支付宝配置参数
            $alipay_config_fix = $this->merge_zfb_config();
            $alipay_config = array_merge($alipay_config, $alipay_config_fix);
            C('public_key_path', $alipay_config['public_key_path']);
        } else {
            $this->response(array('error' => '1', 'msg' => '支付宝配置文件不存在'));
        }
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {//验证成功
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no']; // 支付宝交易号
            $amount = $_POST['total_fee'];  // 交易金额
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                echo 'success';
                //支付成功后，修改支付表中支付状态，并将交易信息写入用户平台充值记录表ptb_charge。
                $this->payPost($out_trade_no, $trade_no, $amount);
            }
        }
    }

    // 合并支付宝的配置参数
    private function merge_zfb_config() {
        // 支付宝配置参数
        return $alipay_config_fix = array(
            'payment_type'        => "1", // 支付类型
            'notify_url'          => MOBILESITE."/mobile.php/Pay/alipay_notify", // 服务器异步通知页面路径
            'return_url'          => MOBILESITE."/app/newwappc/index.html#/home", //页面跳转同步通知页面路径
            'show_url'            => MOBILESITE."/app/newwappc/index.html#/home", //商品展示地址
            'subject'             => '充值平台币', // 订单名称
            'body'                => "游戏充值", // 订单描述
            'it_b_pay'            => '15d', // 超时时间
            'extern_token'        => '', // 钱包token
            'input_charset'       => 'utf-8', // 字符串类型
            'private_key_path'    => dirname(__FILE__).'/key/rsa_private_key.pem', //商户的私钥（后缀是.pen）文件相对路径
            'ali_public_key_path' => dirname(__FILE__).'/key/alipay_public_key.pem', //支付宝公钥（后缀是.pen）文件相对路径
            'sign_type'           => strtoupper('RSA'),
        );
    }

    // 威富通微信支付
    public function wftpay($data) {
        // 导入配置文件和核心类库
        import("Vendor.spay.Utils");
        import("Vendor.spay.RequestHandler");
        import("Vendor.spay.ClientResponseHandler");
        import("Vendor.spay.PayHttpClient");
        $spay_file = SITE_PATH."conf/pay/spay/config.php";
        if (file_exists($spay_file)) {
            $spay_config = require $spay_file;
        } else {
            $this->response(array('error' => 1, '威富通配置文件不存在'));
        }
        $this->resHandler = new \ClientResponseHandler();
        $this->reqHandler = new \RequestHandler();
        $this->pay = new \PayHttpClient();
        //导入回调地址
        $this->reqHandler->setGateUrl($spay_config['url']);
        $this->reqHandler->setKey($spay_config['key']);
        $this->reqHandler->setParameter('out_trade_no', $data['order_id']);
        $this->reqHandler->setParameter('body', '平台币充值');
        $this->reqHandler->setParameter('attach', $data['remark']);
        $this->reqHandler->setParameter('total_fee', $data['money'] * 100);
        $this->reqHandler->setParameter('mch_create_ip', $data['ip']);
        $this->reqHandler->setParameter('time_start', date('YmdHis', $data['create_time']));
        $this->reqHandler->setParameter('time_expire', date('YmdHis', $data['create_time'] + 7200));
        $this->reqHandler->setParameter('service', 'pay.weixin.wappay');//接口类型：pay.weixin.native
        $this->reqHandler->setParameter('mch_id', $spay_config['mchId']);//必填项，商户号，由威富通分配
        $this->reqHandler->setParameter('version', $spay_config['version']);
        $this->reqHandler->setParameter('device_info', 'AND_SDK');
        $this->reqHandler->setParameter('mch_app_name', $spay_config['mch_app_name']);
        $this->reqHandler->setParameter('mch_app_id', $spay_config['mch_app_id']);
        //通知地址，必填项，接收威富通通知的URL，需给绝对路径，255字符内格式如:http://wap.tenpay.com/tenpay.asp
        //$notify_url = 'http://'.$_SERVER['HTTP_HOST'];			//$this->reqHandler->setParameter('notify_url',$notify_url.'/payInterface/request.php?method=callback');
        $this->reqHandler->setParameter('notify_url', MOBILESITE.U('Pay/spayNotifyUrl'));
        $this->reqHandler->setParameter('callback_url', $spay_config['mch_app_id']);
        $this->reqHandler->setParameter('nonce_str', mt_rand(time(), time() + rand()));//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名
        $rdata = \Utils::toXml($this->reqHandler->getAllParameters());//var_dump($rdata);
        $this->pay->setReqContent($this->reqHandler->getGateURL(), $rdata);
        if ($this->pay->call()) {
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if ($this->resHandler->isTenpaySign()) {
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if ($this->resHandler->getParameter('status') == 0
                    && $this->resHandler->getParameter('result_code') == 0
                ) {
                    echo "<script>window.location.href='".$this->resHandler->getParameter('pay_info')."';</script>";
                    exit;
                } else {
                    echo json_encode(
                        array('status' => 500,
                              'msg'    => 'Error Code:'.$this->resHandler->getParameter('err_code').' Error Message:'
                                          .$this->resHandler->getParameter('err_msg'))
                    );
                    exit();
                }
            }
            echo json_encode(
                array('status' => 500,
                      'msg'    => 'Error Code:'.$this->resHandler->getParameter('status').' Error Message:'
                                  .$this->resHandler->getParameter('message'))
            );
        } else {
            echo json_encode(
                array('status' => 500,
                      'msg'    => 'Response Code:'.$this->pay->getResponseCode().' Error Info:'.$this->pay->getErrInfo(
                          ))
            );
        }
    }

    // 微付通回调
    public function spayNotifyUrl() {
        import("Vendor.spay.Utils");
        import("Vendor.spay.ClientResponseHandler");
        $spay_file = SITE_PATH."conf/pay/spay/config.php";
        if (file_exists($spay_file)) {
            $spay_config = require $spay_file;
        } else {
            $this->response(array('error' => 1, '威富通配置文件不存在'));
        }
        $this->resHandler = new \ClientResponseHandler();
        $xml = file_get_contents('php://input');
        $this->resHandler->setContent($xml);
        $this->resHandler->setKey($spay_config['key']);
        $signResult = $this->resHandler->isTenpaySign();
        if ($signResult) {
            if ($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                //更改订单状态
                $out_trade_no = $this->resHandler->getParameter('out_trade_no');
                $total_fee = $this->resHandler->getParameter('total_fee') / 100;
                $this->payPost($out_trade_no, $this->resHandler->getParameter('transaction_id'), $total_fee);
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

    // 修改订单的状态,并给玩家添加平台币
    protected function payPost($out_trade_no, $trade_no, $amount) {
        // 获取订单信息
        $payInfo = M('ptb_charge')->where(array('order_id' => $out_trade_no))->find();
        if (('2' !== $payInfo['status']) && ($payInfo['real_amount'] == $amount)) {
            $ptb_data['status'] = 2;
            $ptb_data['remark'] = $trade_no;
            $ptb_data['update_time'] = time();
            $ptb_data['id'] = $payInfo['id'];
            $res = M('ptb_charge')->data($ptb_data)->save();
            if ($res) {
                // 给玩家添加平台币
                $this->addPtbForMember($payInfo['mem_id'], $payInfo['ptb_cnt'], $amount);
            }
        }
    }

    /**
     * 给玩家添加平台币
     *
     * @param number $mem_id
     * @param number $ptb_cnt
     * @param number $amount
     */
    protected function addPtbForMember($mem_id = 0, $ptb_cnt = 0, $amount = 0) {
        if (!empty($mem_id) && !empty($ptb_cnt)) {
            $mem_data = M('ptb_mem')->where(array('mem_id' => $mem_id))->find();
        }
        if ($mem_data) {
            $mem_data['sum_money'] += $amount;
            $mem_data['total'] += $ptb_cnt;
            $mem_data['remain'] += $ptb_cnt;
            $mem_data['update_time'] = time();
            $res = M('ptb_mem')->save($mem_data);
        } else {
            $mem_data['mem_id'] = $mem_id;
            $mem_data['sum_money'] = $amount;
            $mem_data['total'] = $ptb_cnt;
            $mem_data['remain'] = $ptb_cnt;
            $mem_data['create_time'] = time();
            $mem_data['update_time'] = time();
            $res = M('ptb_mem')->add($mem_data);
        }
    }

    public function response($data) {
        header("Access-Control-Allow-Origin:*");
        /*星号表示所有的域都可以接受，*/
        header("Access-Control-Allow-Methods:GET,POST");
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}