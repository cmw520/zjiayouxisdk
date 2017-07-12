<?php
/**
 * Heepay.php UTF-8
 * 汇付宝处理函数
 *
 * @date    : 2016.12.13
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播
 * @version : HUOSDK 7.0
 */
namespace huosdk\pay;

use think\Log;
use think\Session;
use think\Db;
use think\Loader;
use think\Config;

class Heepay extends Pay {
    private $bank_return_url;
    private $bank_notify_url;
    private $return_url;
    private $notify_url;
    private $heepayagent_id;
    private $sign_key;
    private $pay_url;

    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct() {
        // 包含配置文件
        $_conf_file = CONF_PATH."extra/pay/heepay/config.php";
        if (file_exists($_conf_file)) {
            $_heepay_conf = include $_conf_file;
        } else {
            $_heepay_conf = array();
        }
        $this->bank_return_url = "";
        $this->bank_notify_url = "";
        $this->return_url = config('domain.SDKSITE').url('Pay/Heepay/notifyurl');
        $this->notify_url = config('domain.SDKSITE').url('Pay/Heepay/notifyurl');
        $this->heepayagent_id = $_heepay_conf["heepayagent_id"];
        $this->sign_key = $_heepay_conf["sign_key"];
        $this->pay_url = $_heepay_conf["pay_url"];
    }

    /**
     * 移动APP支付函数
     */
    public function clientPay() {
        Loader::import('pay.heepay.Tools');
        $_tools = new \Tools();
        $_transtime = date("YmdHis");    //交易时间
        $_paytype = Session::get('payway', 'order');
        if ('heepaybank' == $_paytype) {
            $_pay_type = 18;  //汇付宝银行快捷支付	信用卡快捷支付和储蓄卡快捷支付
            $_return_url = $this->bank_return_url;
            $_notify_url = $this->bank_notify_url;
        } else if ('heepaycard' == $_paytype) {
            $_pay_type = 12;  //充值卡
            $_return_url = $this->return_url;
            $_notify_url = $this->notify_url;
        } else if ('heepayali' == $_paytype) {
            $_pay_type = 22;  //支付宝
            $_return_url = $this->return_url;
            $_notify_url = $this->notify_url;
        } else {
            $_pay_type = 30;  //微信支付
            $_return_url = $this->return_url;
            $_notify_url = $this->notify_url;
        }
        $_version = 2;
        $_agent_id = $this->heepayagent_id;   //商户编号
        $_sign_key = $this->sign_key;
        $_agent_bill_id = Session::get('order_id', 'order');
        $_pay_amt = sprintf("%01.2f", Session::get('real_amount', 'order'));
        $_user_ip = Session::get('ip', 'device');
        $_agent_bill_time = $_transtime;
        $_goods_name = Session::get('product_name', 'order');
        $_goods_num = 1;
        $_remark = Session::get('order_id', 'order');
        $_goods_note = Session::get('product_desc', 'order');
        $_signstr = "version=".$_version."&agent_id=".$_agent_id."&agent_bill_id=".$_agent_bill_id."&agent_bill_time="
                    .$_agent_bill_time."&pay_type=".$_pay_type."&pay_amt=".$_pay_amt."&notify_url=".$_notify_url
                    ."&user_ip=".$_user_ip;
        $_sign = md5($_signstr."&key=".$_sign_key);
        $_myparams = $_signstr."&return_url=".$_return_url."&goods_name=".$_goods_name."&goods_num=".$_goods_num
                     ."&remark=".$_remark."&goods_note=".$_goods_note;
        if ($_pay_type == 30) {
            $gamewhere['id'] = Session::get('app_id', 'app');
            $field = [
                'name',
                'packagename'
            ];
            $_gamedata = DB::name('game')->field($field)->where($gamewhere)->find();
            if (empty($_gamedata["packagename"])) {
                $_gamedata["packagename"] = "com.example.cysdk_demo";
            }
            $meta_option = '[{"s":"Android","n":"'.$_gamedata["name"].'","id":"'.$_gamedata["packagename"]
                           .'"},{"s":"IOS","n":"","id":""}]';
            $meta_option = urlencode(
                iconv("gb2312//IGNORE", "UTF-8", base64_encode(iconv("UTF-8", "gb2312//IGNORE", $meta_option)))
            );
            $_myparams .= "&meta_option=".$meta_option;
        }
        $_myparams .= "&sign=".$_sign;
        $_myparams = iconv("UTF-8", "GB2312//IGNORE", $_myparams);
        $_Method = "post";
        $_Curl = curl_init();//初始化curl
        if ('get' == $_Method) {//以GET方式发送请求
            curl_setopt($_Curl, CURLOPT_URL, $this->pay_url."?".$_myparams);
        } else {//以POST方式发送请求
            curl_setopt($_Curl, CURLOPT_URL, $this->pay_url);
            curl_setopt($_Curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
            curl_setopt($_Curl, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
            curl_setopt($_Curl, CURLOPT_POST, 1);//post提交方式
            curl_setopt($_Curl, CURLOPT_HEADER, 0);
            curl_setopt($_Curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($_Curl, CURLOPT_POSTFIELDS, $_myparams);//设置传送的参数
        }
        $Res = curl_exec($_Curl);//运行curl
        curl_close($_Curl);//关闭curl
        $_tokenId = $_tools::getXMLValue($Res, "token_id");
        $_cardspw = $_tools::getXMLValue($Res, "error");
        if (!empty($_cardspw)) {
            return false;
        }
        if ($_tokenId) {
            $_return_data = $_tokenId.",".$_agent_id.",".$_agent_bill_id.",".$_pay_type;
            return $this->clientAjax('heepay', $_return_data);
        }
        return false;
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
        $result = $_GET['result'];
        $pay_message = $_GET['pay_message'];
        $agent_id = $_GET['agent_id'];
        $jnet_bill_no = $_GET['jnet_bill_no'];
        $agent_bill_id = $_GET['agent_bill_id'];
        $pay_type = $_GET['pay_type'];
        $pay_amt = $_GET['pay_amt'];
        $remark = $_GET['remark'];
        $return_sign = $_GET['sign'];
        $remark = iconv("GB2312", "UTF-8//IGNORE", urldecode($remark));//签名验证中的中文采用UTF-8编码;
        $signStr = '';
        $signStr = $signStr.'result='.$result;
        $signStr = $signStr.'&agent_id='.$agent_id;
        $signStr = $signStr.'&jnet_bill_no='.$jnet_bill_no;
        $signStr = $signStr.'&agent_bill_id='.$agent_bill_id;
        $signStr = $signStr.'&pay_type='.$pay_type;
        $signStr = $signStr.'&pay_amt='.$pay_amt;
        $signStr = $signStr.'&remark='.$remark;
        $signStr = $signStr.'&key='.$this->sign_key; //商户签名密钥
        $sign = '';
        $sign = strtolower(md5($signStr));
        if ($sign == $return_sign) {   //比较签名密钥结果是否一致，一致则保证了数据的一致性
            if ($wallet) {
                $this->walletNotify($agent_bill_id, $pay_amt, $jnet_bill_no);
            } else {
                $this->sdkNotify($agent_bill_id, $pay_amt, $jnet_bill_no);
            }
            echo 'ok';
            exit();
            //商户自行处理自己的业务逻辑
        } else {
            echo 'fail';
            exit();
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
    private function buildOrderdata(array $_paydata) {
        $_paydata = $_paydata;
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
    public function sdkPreorder(array $_paydata = array()) {
        /* 校验入参合法性 huosdktest */
        // $_paydata = checkParam($_paydata);
        // 组建订单数据
        $_order_data = $this->buildOrderdata($_paydata);
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
    private function insertPayext($_pay_id) {
        $_payext_data['pay_id'] = $_pay_id;
        $_payext_data['product_id'] = Session::get('product_id', 'order');
        $_payext_data['product_name'] = Session::get('product_name', 'order');
        $_payext_data['product_desc'] = Session::get('product_desc', 'order');
        $_payext_data['deviceinfo'] = Session::get('deviceinfo', 'device');
        $_payext_data['userua'] = Session::get('userua', 'device');
        $_payext_data['agentgame'] = Session::get('agentgame', 'user');
        $_payext_data['pay_ip'] = Session::get('ip', 'device');
        $_payext_data['imei'] = Session::get('device_id', 'device');
        // $_payext_data['cityid'] = Session::get('ipaddrid', 'device');
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

    protected function payAction($_pay_id) {
        /* 1 插入充值扩展表 */
        $this->insertPayext($_pay_id);
        /* 2 CP 回调组装 */
        $this->setCpparam($_pay_id);
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

    protected function setCpparam($_pay_id) {
        $_param['app_id'] = Session::get('app_id', 'app');
        $_param['cp_order_id'] = Session::get('cp_order_id', 'order');
        $_param['ext'] = Session::get('ext', 'order');
        $_param['mem_id'] = Session::get('mem_id', 'user');
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
        $_pc_data['pay_id'] = $_pay_id;
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
    public function getPaywayid($_payway) {
        if (empty($_payway)) {
            return 0;
        }
        $map['payname'] = $_payway;
        $pw_id = DB::name('payway')->where($map)->getField('id');
        if (empty($pw_id)) {
            return 0;
        } else {
            return $pw_id;
        }
    }
}