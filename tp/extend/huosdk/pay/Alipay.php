<?php
/**
 * Alipay.php UTF-8
 * 支付宝支付函数
 *
 * @date    : 2016年11月17日上午12:40:40
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月17日上午12:40:40
 */
namespace huosdk\pay;

use think\Db;
use think\Loader;
use think\Session;

class Alipay extends Pay {
    private $config;

    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct() {
        $_conf_file = CONF_PATH."extra/pay/alipay/config.php";
        if (file_exists($_conf_file)) {
            $aliconf = include $_conf_file;
        } else {
            $aliconf = array();
        }
        $this->config = array(
            'partner'          => $aliconf['partner'],  // partner 从支付宝商户版个人中心获取
            'seller_email'     => $aliconf['seller_email'],  // email 从支付宝商户版个人中心获取
            'key'              => $aliconf['key'],  // key 从支付宝商户版个人中心获取
            'sign_type'        => strtoupper(trim('RSA')),  // 可选md5 和 RSA
            'input_charset'    => 'utf-8',  // 编码 (固定值不用改)
            'transport'        => 'http',  // 协议 (固定值不用改)
            'cacert'           => CONF_PATH.'extra/pay/alipay/cacert.pem',  // cacert.pem存放的位置 (固定值不用改)
            'notify_url'       => config('domain.SDKSITE').url('Pay/Alipay/notifyurl'),  // 异步接收支付状态通知的链接
            'return_url'       => config('domain.SDKSITE').url('Pay/Alipay/returnurl'),
            // 页面跳转 同步通知 页面路径 支付宝处理完请求后,当前页面自 动跳转到商户网站里指定页面的 http 路径。 (扫码支付专用)
            'show_url'         => config('domain.SDKSITE').url('Pay/Alipay/returnurl'),
            // 商品展示网址,收银台页面上,商品展示的超链接。 (扫码支付专用)
            'private_key_path' => CONF_PATH.'extra/pay/alipay/key/rsa_private_key.pem',
            // 移动端生成的私有key文件存放于服务器的 绝对路径 如果为MD5加密方式；此项可为空 (移动支付专用)
            'public_key_path'  => CONF_PATH.'extra/pay/alipay/key/alipay_public_key.pem'
            /*移动端生成的公共key文件存放于服务器的 绝对路径 如果为MD5加密方式；此项可为空 (移动支付专用)*/
        );
    }

    /**
     * 移动APP支付函数
     */
    public function clientPay() {
        $this->config['sign_type'] = 'RSA';
        $_data = array(
            "service"        => "mobile.securitypay.pay",
            "partner"        => trim($this->config['partner']),
            "_input_charset" => trim(strtolower($this->config['input_charset'])),
            "sign_type"      => strtoupper(trim($this->config['sign_type'])),
            "notify_url"     => $this->config['notify_url'],
            "out_trade_no"   => Session::get('order_id', 'order'),
            "subject"        => Session::get('product_name', 'order'),
            "body"           => Session::get('product_desc', 'order'),
            "payment_type"   => "1",
            "seller_id"      => trim($this->config['seller_email']),
            "total_fee"      => Session::get('real_amount', 'order'),
            "it_b_pay"       => "30m"
        );
        Loader::import('pay.alipay.AlipaySubmit', '', '.class.php');
        // 建立请求，请求成功之后，会通知服务器的alipay_notify方法，客户端会通知$return_url配置的方法
        $_alipay_submit = new \AlipaySubmit($this->config);
        $_token = $_alipay_submit->buildClientRequestParaToString($_data);

        return $this->clientAjax('alipay', $_token);
    }

    /**
     * wap端下单
     */
    public function mobilePay() {
    }

    /**
     * PC端下单
     */
    public function pcPay() {
    }

    /**
     * 钱包充值回调函数
     */
    public function walletNotify() {
    }

    /**
     * 游戏币充值回调
     */
    public function gmNotify() {
    }

    /*
     * 异步回调函数
     */
    public function notifyUrl($wallet = false) {
        // 引入支付宝
        Loader::import('pay.alipay.AlipayNotify', '', '.class.php');
        $_ali_notify = new \AlipayNotify($this->config);
        // 验证支付数据
        $_verify_result = $_ali_notify->verifyNotify();
        if ($_verify_result) {
            /* 平台订单号 */
            $out_trade_no = $_POST['out_trade_no'];
            /* 支付宝交易号 */
            $trade_no = $_POST['trade_no'];
            /* 交易金额 */
            $amount = $_POST['total_fee'];
            // 交易状态
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_FINISHED') {
            } else if ($trade_status == 'TRADE_SUCCESS') {
                $this->selectNotify($out_trade_no, $amount, $trade_no);
            }
            echo "success";
            // 下面写验证通过的逻辑 比如说更改订单状态等等 $_POST['out_trade_no'] 为订单号；
        } else {
            echo "fail";
        }
    }

    /*
     * 返回接收页面
     */
    public function returnUrl() {
    }

    /*
     * 组建订单数据
     */
    private function buildOrderdata(array $paydata) {
        $_paydata = $paydata;
        if (empty($_paydata['product_price']) || $_paydata['product_price'] < 0) {
            return false;
        }
        $_order_data['mem_id'] = Session::get('id', 'user');
        $_order_data['order_id'] = \huosdk\common\Commonfunc::setOrderid($_order_data['mem_id']);
        $_order_data['agent_id'] = Session::get('agent_id', 'user');
        $_order_data['app_id'] = Session::get('app_id', 'app');
        $_order_data['amount'] = $_paydata['product_price'];
        $_order_data['gm_cnt'] = 0;
        $_order_data['real_amount'] = $_order_data['amount'];
        $_order_data['rebate_cnt'] = 0;
        $_order_data['from'] = Session::get('from', 'device');
        $_order_data['status'] = 1;
        $_order_data['cpstatus'] = 1;
        $_order_data['payway'] = 0;
        $_order_data['create_time'] = time();
        $_order_data['update_time'] = $_order_data['create_time'];
        $_order_data['attach'] = isset($_paydata['ext']) ? $_paydata['ext'] : '';
        $_order_data['remark'] = '';

        return $_order_data;
    }

    /*
     * SDK预下单
     */
    public function sdkPreorder(array $paydata = array()) {
        /* 校验入参合法性 huosdktest */
        // $_paydata = checkParam($paydata);
        // 组建订单数据
        $_order_data = $this->buildOrderdata($paydata);
        Session::set('order_id', $_order_data['order_id'], 'order');
        /* 1 查询余额 */
        $_wallet_remain = \huosdk\wallet\Wallet::getRemain($_order_data['mem_id'], $_order_data['app_id']);
        $_wallet_rate = \huosdk\wallet\Wallet::getRate(); /* 钱包与实际价格比例 */
        $_wallet_real_price = number_format($_wallet_remain / abs($_wallet_rate), 2, '.', ''); /* 钱包实际价值 */
        $_product_price = number_format($_order_data['amount'], 2, '.', '');
        $_no_wallet_amount = $_product_price; /* 非wallet支付金额 */
        if (0 < $_wallet_real_price && $_wallet_real_price <= $_product_price) {
            /* 实际余额少于商品价格 */
            $_order_data['gm_cnt'] = $_wallet_remain;
            $_no_wallet_amount = $_product_price - $_wallet_real_price;
        } else if ($_wallet_real_price > $_product_price) {
            /* 余额大于商品价格 */
            $_order_data['gm_cnt'] = number_format($_product_price * abs($_wallet_rate), 2, '.', '');
            $_no_wallet_amount = 0;
        }
        if ($_no_wallet_amount > 0) {
            // 除去游戏币或平台币支付 后需要支付的金额
            // 去除游戏币计算折扣
            $this->setRate($_order_data, $_no_wallet_amount);
        }
        $_pay_id = $this->insertPay($_order_data);
        if ($_pay_id) {
            return true;
        }

        return false;
    }

    /*
     * 折扣计算金额
     * huosdktest
     */
    private function setRate(&$order_data, $_no_wallet_amount) {
        $order_data['real_amount'] = $_no_wallet_amount;
        $order_data['rebate_cnt'] = 0;

        return;
    }

    /*
     * 请求数据
     */
    private function insertPayext($pay_id) {
        $_payext_data['pay_id'] = $pay_id;
        $_payext_data['product_id'] = Session::get('product_id', 'order');
        $_payext_data['product_name'] = Session::get('product_name', 'order');
        $_payext_data['product_desc'] = Session::get('product_desc', 'order');
        $_payext_data['deviceinfo'] = Session::get('deviceinfo', 'device');
        $_payext_data['userua'] = Session::get('userua', 'device');
        $_payext_data['agentgame'] = Session::get('agentgame', 'user');
        $_payext_data['pay_ip'] = Session::get('ip', 'device');
        $_payext_data['imei'] = Session::get('device_id', 'device');
//         $_payext_data['cityid'] = Session::get('ipaddrid', 'device');
        $_payext_data['cp_order_id'] = Session::get('cp_order_id', 'order');
        $_payext_data['product_count'] = Session::get('product_count', 'order');
        $_payext_data['exchange_rate'] = Session::get('exchange_rate', 'order');
        $_payext_data['currency_name'] = Session::get('currency_name', 'order');
        $_payext_data['server_id'] = Session::get('server_id', 'role');
        $_payext_data['server_name'] = Session::get('server_name', 'role');
        $_payext_data['role_id'] = Session::get('role_id', 'role');
        $_payext_data['role_name'] = Session::get('role_name', 'role');
        $_payext_data['party_name'] = Session::get('party_name', 'role');
        $_payext_data['role_level'] = Session::get('role_level', 'role');
        $_payext_data['role_vip'] = Session::get('role_vip', 'role');
        $_payext_data['role_balence'] = Session::get('role_balence', 'role');
        Db::name('pay_ext')->insert($_payext_data);

        return;
    }

    protected function payAction($pay_id) {
        /* 1 插入充值扩展表 */
        $this->insertPayext($pay_id);
        /* 2 CP 回调组装 */
        $this->setCpparam($pay_id);
        /* 3 角色数据插入 huosdktest */
        $_r_class = new \huosdk\log\Memrolelog('mg_role_log');
        $_data['money'] = 0;
        $_data['type'] = 5;
        $_r_class->insert($_data);

        return;
    }

    protected function insertPay(array $order_data) {
        // 插入充值表
        $_pay_id = Db::name('pay')->insertGetid($order_data);
        if ($_pay_id) {
            // 异步操作其他数据
            $this->payAction($_pay_id);
            Session::set('gm_cnt', $order_data['gm_cnt'], 'order');
            Session::set('real_amount', $order_data['real_amount'], 'order');
            Session::set('rebate_cnt', $order_data['rebate_cnt'], 'order');
        }

        return $_pay_id;
    }

    protected function setCpparam($pay_id) {
        $_param['app_id'] = Session::get('app_id', 'app');
        $_param['cp_order_id'] = Session::get('cp_order_id', 'order');
        $_param['ext'] = Session::get('ext', 'order');
        $_param['mem_id'] = Session::get('id', 'user');
        $_param['order_id'] = Session::get('order_id', 'order');
        $_param['order_status'] = 2;
        $_param['pay_time'] = time();
        $_param['product_id'] = Session::get('product_id', 'order');
        $_param['product_name'] = Session::get('product_name', 'order');
        $_param['product_price'] = Session::get('product_price', 'order');
        $_param = \huosdk\common\Commonfunc::argSort($_param);
        $_signstr = \huosdk\common\Commonfunc::createLinkstring($_param);
        /* 获取游戏信息 */
        $_g_class = new \huosdk\game\Game($_param['app_id']);
        $_g_info = $_g_class->getGameinfo($_param['app_id']);
        if (empty($_g_info['cpurl']) || empty($_g_info['app_key'])) {
            return false;
        }
        $_sign = md5($_signstr."&app_key=".$_g_info['app_key']);
        $_pc_data['pay_id'] = $pay_id;
        $_pc_data['order_id'] = $_param['order_id'];
        $_pc_data['cp_order_id'] = Session::get('cp_order_id', 'order');
        $_pc_data['params'] = $_signstr."&sign=".$_sign;
        $_pc_data['cpurl'] = $_g_info['cpurl'];
        $_pc_data['status'] = 1;
        $_pc_data['cpstatus'] = 1;
        $_pc_data['create_time'] = $_param['pay_time'];
        $_pc_data['update_time'] = 0;
        $_pc_data['cnt'] = 0;
        $_rs = DB::name('pay_cpinfo')->insert($_pc_data);
        if ($_rs) {
            return true;
        }

        return false;
    }

    // 根据支付方式获取支付方式ID
    public function getPaywayid($payway) {
        if (empty($payway)) {
            return 0;
        }
        $map['payname'] = $payway;
        $pw_id = M('payway')->where($map)->getField('id');
        if (empty($pw_id)) {
            return 0;
        } else {
            return $pw_id;
        }
    }
}