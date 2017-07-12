<?php
/**
 * 消费充值相关数据
 *
 * @author
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class FloatpayController extends MobilebaseController {
    protected $member_model;

    function _initialize() {
        parent::_initialize();
//         $_SESSION['mem_id'] = "100024";
//         $_SESSION['username'] = "13539750158";
//         $_SESSION['app']['app_id'] = "1";
        $contact = sp_get_help();
        $this->assign('contact', $contact);
        $this->member_model = M('members');
    }

    //充值首页
    public function index() {
        $mem_id = sp_get_current_userid();
        $userdata = $this->member_model->where(array('id' => $mem_id))->find();
        $appid = $_SESSION['app']['app_id'];
        $ptb_sum = M("gm_mem")->where(array('mem_id' => $mem_id, 'app_id' => $appid))->getField("remain");
        $ratedata = $this->rate_select();
        $this->assign("ratedata", $ratedata);
        $this->assign("ptb_sum", $ptb_sum);
        $this->assign("userdata", $userdata);
        $this->display();
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
    public function checkYxb($mem_id, $appid, $gm_cnt, $rebate_gold, $amount) {
        //获取玩家平台币余额表中的ID
        $where['mem_id'] = $mem_id;
        $where['app_id'] = $appid;
        $data = M('gmMem')->where($where)->find();
        $editwhere['remain'] = $data['remain'] + $gm_cnt + $rebate_gold;
        $editwhere['update_time'] = time();
        $editwhere['total'] = $data['total'] + $gm_cnt + $rebate_gold;
        $editwhere['sum_money'] = $data['sum_money'] + $amount;
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $appid;
        //判断玩家平台币余额表中是否存在数据，没有则添加，有则修改！
        if (!empty($data)) {
            $result = M('gmMem')->where($map)->save($editwhere);
        } else {
            $addwhere['create_time'] = time();
            $addwhere['mem_id'] = $mem_id;
            $addwhere['app_id'] = $appid;
            $addwhere['total'] = $gm_cnt + $rebate_gold;
            $addwhere['remain'] = $gm_cnt + $rebate_gold;
            $addwhere['sum_money'] = $amount;
            $result = M('gmMem')->data($addwhere)->add();
        }
        //判断充值结果
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //充值还是返利
    function rate_select() {
        $username = $_SESSION['username'];
        $agentid = M("members")->where(array("username" => $username))->getField("agent_id");
        $appid = $_SESSION['app']['app_id'];
        //判断是否返利，$benefit_type为2为返利
        $benefitdata = M("agent_game")->where(array("agent_id" => $agentid, "app_id" => $appid))->field(
            "benefit_type,mem_first,mem_refill"
        )->find();
        if (empty($benefitdata) && C("DEFAULT_SET")) {
            $benefitdata = M("game")->where(array("id" => $appid))->field("benefit_type,mem_first,mem_refill")->find();
        }
        //首冲跟续冲
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $appid;
        $map['status'] = 2;
        //flag为3代表浮点充值
        $map['flag'] = 2; /* 浮点充值  */
        $map['payway'] = array("neq", "0");
        $checkfirst = M("gm_charge")->where($map)->getField("id");
        //is_first为1代表首冲
        if ($checkfirst > 0) {
            $data['is_first'] = 0;
        } else {
            $data['is_first'] = 1;
        }
        $data['buy_type'] = 0;
        //benefit_type为1折扣，2为返利
        if ($benefitdata['benefit_type'] == 2) {
            $data['rate'] = 0;
            if ($checkfirst > 0) {
                $data['rate'] = !empty($benefitdata['mem_refill']) ? $benefitdata['mem_refill'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['mem_first']) ? $benefitdata['mem_first'] : 0;
            }
            $data['buy_type'] = 2;
        } else if ($benefitdata['benefit_type'] == 1) {
            $data['rate'] = 1;
            if ($checkfirst > 0) {
                $data['rate'] = !empty($benefitdata['mem_refill']) ? $benefitdata['mem_refill'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['mem_first']) ? $benefitdata['mem_first'] : 0;
            }
            $data['buy_type'] = 1;
        }
        return $data;
    }

    /**
     *支付记录保存
     */
    function _insertpay() {
        $amount = I('money/d');
        $username = $_SESSION['username'];
        $gm_cnt = $amount;
        $appid = $_SESSION['app']['app_id'];
        $paytype = I('paytype');
        $agentid = M("members")->where(array("username" => $username))->getField("agent_id");
        if ($gm_cnt == 0) {
            $rdata = array(
                'status' => 0,
                'info'   => "购买平台币数量需大于1",
            );
            $this->ajaxReturn($rdata);
        }
        //验证参数有效性
        if (empty($amount) || empty($username) || empty($gm_cnt) || empty($paytype)) {
            $rdata = array(
                'status' => 0,
                'info'   => "缺少参数，请重新提交",
            );
            $this->ajaxReturn($rdata);
        }
        //检查用户名是否存在
        $mem_id = M('members')->where(array('username' => $username))->getfield('id');
        if (empty($mem_id)) {
            $rdata = array(
                'status' => 0,
                'info'   => "用户不存在",
            );
            $this->ajaxReturn($rdata);
        }
        if (!empty($_SESSION['paytime']) && $_SESSION['paytime'] + 5 > time()) {
            $rdata = array(
                'status' => 0,
                'info'   => "订单己存在，请确认是您的付款单号再付款!",
            );
            $this->ajaxReturn($rdata);
        }
        //订单流水号
        $order_id = $this->setorderid($mem_id);
        $_SESSION['weborderid'] = $order_id;
        $_SESSION['paytime'] = time();
        $model = M('gmCharge');
        //查询是否为同一订单，插入到平台币充值订单中
        $orderdata = $model->where(array('order_id' => $order_id))->getField('id');
        //判断订单是否存在
        if ($orderdata) {
            $rdata = array(
                'status' => 0,
                'info'   => "订单己存在，请确认是您的付款单号再付款!",
            );
            $this->ajaxReturn($rdata);
        }
        $BuyerIp = get_client_ip();                                        //用户支付时使用的网络终端IP
        $transtime = time();                                                    //交易时间
        $realmoney = $amount;
        $rebate_gold = 0;
        $rebate_rate = 0;
        $discount = 1;
        $data['buy_type'] = 0;
        //判断是否为充值返利还是折扣1为折扣，2为返利
        $ratedata = $this->rate_select();
        if ($ratedata['buy_type'] == 1) {
            $data['buy_type'] = 1;
            $discount = $ratedata['rate'];
            $realmoney = $amount * $discount;
        } else if ($ratedata['buy_type'] == 2) {
            $data['buy_type'] = 2;
            $rebate_rate = $ratedata['rate'];
            $rebate_gold = $gm_cnt * $rebate_rate;
        }
        $data['order_id'] = $order_id;
        $data['mem_id'] = $mem_id;
        $data['app_id'] = $appid;
        $data['money'] = $amount;
        $data['realmoney'] = $realmoney;
        $data['gm_cnt'] = $gm_cnt;
        $data['rebate_gold'] = $rebate_gold;
        $data['status'] = 1;
        $data['create_time'] = $transtime;
        $data['payway'] = $paytype;
        $data['flag'] = 2; /* 浮点充值  */
        $data['remark'] = "浮点充值";
        $data['ip'] = $BuyerIp;
        $data["discount"] = $discount;
        $data["rebate_rate"] = $rebate_rate;
        if ($model->create($data)) {
            $rs = $model->add();
        }
        if (!$rs) {
            $rdata = array(
                'status' => 0,
                'info'   => "数据处理出错，请重新提交!",
            );
            $this->ajaxReturn($rdata);
        }
        return $data;
    }

    function paypost($out_trade_no, $total_fee) {
        $time = time();
        $data = M("gmCharge")->where(array("order_id" => $out_trade_no))->find();
        $myamount = number_format($data['realmoney'], 2, '.', '');
        $transAmount = number_format($total_fee, 2, '.', '');
        if ($myamount == $transAmount) {
            if ($data['status'] == 1) {
                $status['status'] = 2;
                $rs = M("gmCharge")->where(array("order_id" => $out_trade_no))->save($status);
                if ($rs) {
                    $check = $this->checkYxb(
                        $data['mem_id'], $data['app_id'], $data['gm_cnt'], $data['rebate_gold'], $myamount
                    );
                    if ($check) {
                        echo "OK";
                        exit;
                    }
                }
            }
        }
    }
}