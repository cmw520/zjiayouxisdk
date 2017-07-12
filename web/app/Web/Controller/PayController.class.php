<?php
/**
 * 支付中心控制器
 */
namespace Web\Controller;

use Common\Controller\HomebaseController;

class PayController extends HomebaseController {
    //默认支付宝
    public function index() {
        $url = WEBSITE.U('web/Alipay/alipay');
        $this->assign('url', $url);
        $paytypeid = 3;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display();
    }

    public function payInfo() {
        $pc_model = M('ptbContent');
        $rs = $pc_model->find(1);
        $content = $rs['content'];
        $this->assign('_content', $content);
    }

    public function payBi() {
        $this->display('payBi');
    }

    public function userTtb() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        if ($action == 'name') {
            //检测用户名
            $username = I('username', '');
            if (!empty($username)) {
                $map['username'] = $username;
                $data = M('members')->where($map)->getField('id');
                if ($data) {
                    echo "1";
                } else {
                    echo "2";
                }
            } else {
                echo "2";
            }
        } else if ($action == 'ttb') {
            //通过ajax获取返利比例
            //赠送平台币的数量
            $give = 0;
            $money = I('money', '');
            $return = getTTBtime();
            if ($return == 0) {
                echo $give = 0;
                exit;
            }
            //查询充值金额在活动中设定的金额范围。
            $data1 = M('ptbRate')->field('money,rate,given_money')->where('money<='.$money)->order('money desc')->limit(
                1
            )->find();
            $data2 = M('ptbRate')->field('money,rate,given_money')->where('money>='.$money)->order('money asc')->limit(
                1
            )->find();
            if (empty($data1)) {
                echo $give = 0;
            } else if (empty($data2)) {
                echo $give = 1 * $money;
            } else if ($money == $data1['money']) {
                echo $give = $data1['rate'] * $money + $data1['given_money'];
            } else if ($money == $data2['money']) {
                echo $give = $data2['rate'] * $money + $data2['given_money'];
            } else if ($money > $data1['money'] && $money < $data2['money']) {
                echo $give = $data1['rate'] * $money + $data1['given_money'];
            }
            exit;
        } else if ($action == 'activetime') {
            $return = getTTBtime();
            echo $return;
            exit;
        }
    }

    //查询平台币返利活动是否是在有效时间内
    public function getTTBtime() {
        $time = time();
        $checktime = M('ptbRate')->getfield('start_time,end_time')->where(array('id' => 1))->find();
        $starttime = $checktime['start_time'];
        $endtime = $checktime['end_time'];
        //若不在活动时间之内，则没有返利
        if ($time < $starttime || $time > $endtime) {
            $return = 0;
        } else {
            $return = 1;
        }
        return $return;
    }

    //生成订单号
    function setorderid($mem_id) {
        list($usec, $sec) = explode(" ", microtime());
        // 取微秒前3位+再两位随机数+渠道ID后四位
        $orderid = $sec.substr($usec, 2, 3).rand(10, 99).sprintf("%04d", $mem_id % 10000);
        return $orderid;
    }

    //返回信息
    function returninfo($msg) {
        echo "<script type='text/javascript' >";
        echo "alert('".$msg."');";
        echo "window.close();";
        echo "</script>";
        exit;
    }

    //检查是否已经存在过平台币并更新
    public function checkPtb($mem_id, $ptb_cnt, $amount) {
        //获取玩家平台币余额表中的ID
        $data = M('ptbMem')->where(array('mem_id' => $mem_id))->find();
        $where['remain'] = $data['remain'] + $ptb_cnt;
        $where['update_time'] = time();
        $where['total'] = $data['total'] + $ptb_cnt;
        $where['sum_money'] = $data['sum_money'] + $amount;
        $map['mem_id'] = $mem_id;
        //判断玩家平台币余额表中是否存在数据，没有则添加，有则修改！
        if (!empty($data)) {
            $result = M('ptbMem')->where($map)->save($where);
        } else {
            $where['create_time'] = time();
            $where['mem_id'] = $mem_id;
            $where['total'] = $ptb_cnt;
            $where['remain'] = $ptb_cnt;
            $where['sum_money'] = $amount;
            $result = M('ptbMem')->data($where)->add();
        }
        //判断充值结果
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *支付记录保存
     */
    function _insertpay() {
        $amount = I('amount/d');
        $username = I('username');
        $ptb_cnt = I('ttb/d');
        $paytypeid = I('paytypeid/d');
        //验证参数有效性
        if (empty($amount) || empty($username) || empty($ptb_cnt) || empty($paytypeid)) {
            $str = "缺少参数，请重新提交";
            return $this->returninfo($str);
        }
        //检查用户名是否存在
        $mem_id = M('members')->where(array('username' => $username))->getfield('id');
        if (empty($mem_id)) {
            $str = "用户不存在";
            return $this->returninfo($str);
        }
        if (!empty($_SESSION['paytime']) && $_SESSION['paytime'] + 5 > time()) {
            $str = "订单己存在，请确认是您的付款单号再付款!";
            return $this->returninfo($str);
        }
        //订单流水号
        $order_id = $this->setorderid($mem_id);
        $_SESSION['weborderid'] = $order_id;
        $_SESSION['paytime'] = time();
        //比对比例是否正确
        if ($amount < $ptb_cnt) {
            $str = "参数错误，请重新提交";
            return $this->returninfo($str);
        }
        $model = M('ptbCharge');
        //查询是否为同一订单，插入到平台币充值订单中
        $orderdata = $model->where(array('order_id' => $order_id))->getField('id');
        //判断订单是否存在
        if ($orderdata) {
            $str = "订单己存在，请确认是您的付款单号再付款!";
            echo "<script type='text/javascript' >";
            echo "alert('".$str."');";
            echo "window.close();";
            echo "</script>";
            exit;
        }
        $BuyerIp = get_client_ip();                                        //用户支付时使用的网络终端IP
        $transtime = time();                                                    //交易时间
        $mem_id = M("members")->where(array("username" => $username))->getfield("id");
        $data['order_id'] = $order_id;
        $data['mem_id'] = $mem_id;
        $data['money'] = $amount;
        $data['ptb_cnt'] = $ptb_cnt;
        $data['status'] = 1;
        $data['create_time'] = $transtime;
        $data['payway'] = $paytypeid;
        $data['flag'] = 1; /* 官网充值  */
        $data['remark'] = "官网充值";
        $data['ip'] = $BuyerIp;
        $data["discount"] = "1";
        if ($model->create($data)) {
            $rs = $model->add();
        }
        if (!$rs) {
            $this->error("数据处理出错，请重新提交!");
            exit;
        }
        return $data;
    }

    function paypost($out_trade_no, $total_fee) {
        $time = time();
        $data = M("ptbCharge")->where(array("order_id" => $out_trade_no))->find();
        $myamount = number_format($data['money']);
        $transAmount = number_format($total_fee);
        if ($myamount == $transAmount) {
            if ($data['status'] == 1) {
                $status['status'] = 2;
                $rs = M("ptbCharge")->where(array("order_id" => $out_trade_no))->save($status);
                if ($rs) {
                    $check = $this->checkPtb($data['mem_id'], $data['ptb_cnt'], $myamount);
                    if ($check) {
                        echo "OK";
                        exit;
                    }
                }
            }
        }
    }

    //导入文件并构造要请求的参数$alipay_config
    private function get_config_data() {
        //构造要请求的参数$alipay_config
        $alipay_config = array(
            'partner'       => C('alipay_config_partner'),
            'key'           => C('alipay_config_key'),
            'sign_type'     => C('alipay_config_sign_type'),
            'input_charset' => C('alipay_config_input_charset'),
            'cacert'        => C('alipay_config_cacert'),
            'transport'     => C('alipay_config_transport')
        );
        return $alipay_config;
    }

    //支付公共部分
    public function paycommon() {
        $username = session("user.sdkuser");
        $this->assign('username', $username);
        //订单号
//        $orderid = time().rand(1000,9999);
//        $this->assign('orderid',$orderid);
        $payarr = array(
            '3'  => "支付宝",
            '5'  => "易联",
            '9'  => "汇付宝",
            '7'  => "盛付通",
            '19' => "星启天微信"
        );
        $moneyrate = array(
            //支付宝
            '3'  => array(
                'rate'  => 1,
                'money' => 100,
                'ttb'   => 100 + 100 * $ttbrate
            ),
            //易联
            '5'  => array(
                'rate'  => 1,
                'money' => 100,
                'ttb'   => 100 + 100 * $ttbrate
            ),
            //汇付宝
            '9'  => array(
                'rate'  => 1,
                'money' => 100,
                'ttb'   => 100 + 100 * $ttbrate
            ),
            //盛付通
            '7'  => array(
                'rate'  => 1,
                'money' => 100,
                'ttb'   => 100 + 100 * $ttbrate
            ),
            //星启天微信
            '19' => array(
                'rate'  => 1,
                'money' => 100,
                'ttb'   => 100 + 100 * $ttbrate
            ),
        );
        $this->assign("keywords", "手机游戏,手机游戏推广,手游公会联盟,手机游戏下载,手游礼包");
        $this->assign(
            "description",
            C('BRAND_NAME')."提供最新最好玩的手机游戏下载，首家全民手游充值返利平台及手机游戏公会推广联盟，最新最热的手机游戏下载排行榜评测，为公会提供专属返利APP及手游代理平台服务。".C(
                'BRAND_NAME'
            )."关注游戏玩家利益，助您畅玩手游。"
        );
        $this->assign("title", C('BRAND_NAME')."|最新最好玩的手机游戏下载排行榜_手游公会联盟_手游CPS公会推广渠道及联运发行合作平台");
        // 获取充值页面下面的显示内容
        $this->payInfo();
        // 热门游戏列表清单
        $hotgamelist = hotgamelist();
        $this->assign("footgamelist", $hotgamelist);
        $this->assign('moneyrate', $moneyrate);
        $this->assign('payarr', $payarr);
        $logo = getGuanggao(4);
        $this->assign("WEB_ICP", C('WEB_ICP'));
        $this->assign("logo", $logo);
        $this->assign("website", WEBSITE);
    }

    //汇付宝
    public function heepay() {
        $url = WEBSITE.U('web/Heepay/heepay');
        $this->assign('url', $url);
        $paytypeid = 9;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display("index");
    }

    //盛付通
    public function shengPay() {
        $url = WEBSITE.U('web/Shengpay/shengpay');
        $this->assign('url', $url);
        $paytypeid = 7;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display("index");
    }

    //星启天微信
    public function xqtpay() {
        $url = WEBSITE.U('Web/Xqtpay/xqtpay');
        $this->assign('url', $url);
        $paytypeid = 19;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display("index");
    }

    //易联
    public function payeco() {
        $url = WEBSITE.U('Web/Payeco/payeco');
        $this->assign('url', $url);
        $paytypeid = 5;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display("index");
    }

    //微信
    public function spay() {
        $url = WEBSITE.U('Web/Spay/spay');
        $this->assign('url', $url);
        $paytypeid = 8;
        $this->assign("paytypeid", $paytypeid);
        $this->paycommon();
        $this->display("index");
    }
}