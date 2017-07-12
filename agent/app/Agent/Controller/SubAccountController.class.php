<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class SubAccountController extends AgentbaseController {
    public $huoshu_subagent;

    function _initialize() {
        parent::_initialize();
        $this->huoshu_subagent = new \Huosdk\SubAgent($_SESSION['agent_id']);
    }

    public function check_member_account_post() {
        $name = I('name');
        $r = $this->huoshu_account->checkMemberBelongToAgent($name, $this->agid);
        if ($r) {
            $this->ajaxReturn(array("error" => "0", "msg" => "帐号存在"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "您无法为此帐号充值"));
        }
    }

    public function get_agent_game_rate($game_id) {
        $hs_benefit_obj = new \Huosdk\Benefit();
        $rate = $hs_benefit_obj->get_agent_game_agent_rate_V2($_SESSION['agent_id'], $game_id);
        return $rate;
    }

    public function get_agent_balance() {
        $balance = M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->getField("balance");
        $result = (float)$balance;
        return $result;
    }

    public function charge_for_member_post() {
        $game_id = I('gameId');
        $mem_name = I('player_account');
        $amount = I('amount');
        $payPwd = I('payPwd1');
        $payway = I('payway');
//        $this->inCaseBalanceNotEnough($amount);
        //检查后台给渠道设置的折扣是否正确
        $rate = $this->get_agent_game_rate($game_id);
        if ($rate == 0) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣设置有问题"));
            exit;
        }
        //检查游戏用户是否存在
        $mem_id = '';
        $model = M('members');
        $exist = $model->where(array("username" => $mem_name, "agent_id" => $_SESSION['agent_id']))->find();
        if ($exist) {
            $mem_id = get_memid_by_name(I('account'));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "用户不存在".$mem_name));
            exit;
        }
        //检查支付密码是否正确
        $sp_pw = pay_password($payPwd);
        $pay_pwd_result = M('users')->where(array("id" => $_SESSION['agent_id'], "pay_pwd" => $sp_pw))->find();
        if (!$pay_pwd_result) {
            $this->ajaxReturn(array("error" => "1", "msg" => "支付密码错误"));
            exit;
        }
        $real_pay = round($amount * $rate, 2);
        $total_balance = $this->get_agent_balance();
        if (($total_balance < $real_pay)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "您的账户余额不足，请充值"));
            exit;
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "成功"));
    }

    public function order_member_post() {
        $amount = I('os_amount');
//        $payway=I('os_payway');
        $app_id = I('os_gameid');
        $mem_name = I('os_mem_name');
        $mem_id = get_memid_by_name($mem_name);
        $rate = $this->get_agent_game_rate($app_id);
        if (!($rate > 0 && $rate < 1)) {
            echo '内部错误';
            exit;
        }
        $gm_cnt = round($amount / $rate);
        $this->order_member_balance($amount, $gm_cnt, $rate);
    }

    public function order_member_balance($amount, $gm_cnt, $rate) {
        $total_balance = $this->get_agent_balance();
        if ($total_balance < $amount) {
            echo '余额不足，请充值';
            exit;
        } else {
            $amount = I('os_amount');
            $app_id = I('os_gameid');
            $mem_name = I('os_mem_name');
            $mem_id = get_memid_by_name($mem_name);
            //从agent的balance中减去相应金额
            $pre = M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->find();
            if ($pre) {
                $new_balance = $pre['balance'] - $amount;
                M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->setField("balance", $new_balance);
            }
            //玩家账户中的平台币金额要增加，表格是gm_mem
            $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
            //如果账户中已经存在，就更新
            if ($pre_gmm) {
                $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))
                                      ->save(
                                          array(
                                              "sum_money"   => $pre_gmm['sum_money'] + $amount,
                                              "total"       => $pre_gmm['total'] + $gm_cnt,
                                              "remain"      => $pre_gmm['remain'] + $gm_cnt,
                                              "update_time" => time()
                                          )
                                      );
            } else {
                //如果账户中不存在，就创建初始记录
                M('gm_mem')->add(
                    array(
                        "mem_id"      => $mem_id, "app_id" => $app_id,
                        "sum_money"   => $amount, "total" => $gm_cnt, "remain" => $gm_cnt,
                        "create_time" => time(), "update_time" => time()
                    )
                );
            }
            //gm_charge中要加入这次充值记录，这是渠道给玩家充的
            M('gm_charge')->add(
                array(
                    "order_id"    => setorderid(1),
                    "admin_id"    => $_SESSION['agent_id'],
                    "mem_id"      => $mem_id,
                    "app_id"      => $app_id,
                    "money"       => $amount,
                    "gm_cnt"      => $gm_cnt,
                    "discount"    => $rate,
                    "payway"      => "balance",
                    "ip"          => get_client_ip(),
                    "status"      => "2",
                    "create_time" => time(),
                    "update_time" => time(),
                    "remark"      => "from agent ".$_SESSION['agent_id']
                )
            );
            //gm_agent_charge中要加入这个记录，这相当于用余额兑换平台币
            M('gm_agentcharge')->add(
                array(
                    "order_id"    => setorderid(1),
                    "admin_id"    => "0",
                    "agent_id"    => $_SESSION['agent_id'],
                    "app_id"      => $app_id,
                    "money"       => $amount,
                    "gm_cnt"      => $gm_cnt,
                    "discount"    => $rate,
                    "payway"      => "balance",
                    "ip"          => get_client_ip(),
                    "status"      => "2",
                    "create_time" => time(),
                    "update_time" => time()
                )
            );
            $this->display('charge_success');
        }
    }

    public function index() {
        $this->show('hi');
    }

    public function setPassword() {
        $this->display();
    }

    public function setPayPwd() {
        $obj = new \Huosdk\UI\Form();
        $this->display();
    }

    public function setPassword_post() {
        if (!(I('prepass') && I('pass1') && I('pass2'))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请填写密码"));
        }
        $pre = I('prepass');
        $p1 = I('pass1');
        $p2 = I('pass2');
        if ($p1 != $p2) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次输入不一致"));
        }
        $result = $this->huoshu_account->setUserPwd($this->agid, $pre, $p1);
        if ($result != 1) {
            $this->ajaxReturn(array("error" => "1", "msg" => $result));
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    public function setPayPwd_post() {
        if (!(I('prepass') && I('paypwd1') && I('paypwd2'))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请填写密码"));
        }
        $prepass = I('prepass');
        $p1 = I('paypwd1');
        $p2 = I('paypwd2');
        if ($p1 != $p2) {
            $this->ajaxReturn(array("error" => "1", "msg" => "两次输入不一致"));
        }
        $result = $this->huoshu_account->setUserPayPwd($this->agid, $prepass, $p1);
        if ($result != 1) {
            $this->ajaxReturn(array("error" => "1", "msg" => $result));
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功，请牢记"));
    }

    public function bindPhone() {
        $phone_now = $this->huoshu_account->getUserPhone($this->agid);
//        echo $this->subid;
        $this->assign("phone_now", $phone_now);
        $this->display();
    }

    public function VerifyPhoneCode() {
        $PhoneVerifyCode = I('PhoneVerifyCode');
        if ($PhoneVerifyCode != $_SESSION['sms_code']) {
            $_SESSION['phoneVerifyCodeMatch'] = false;
            $this->ajaxReturn(array("error" => "1", "msg" => "验证码不正确"));
            exit;
        }
        $_SESSION['phoneVerifyCodeMatch'] = true;
//        $this->ajaxReturn(array("error"=>"0","msg"=>"验证成功"));
    }

    public function bindPhone_post() {
        $phone = I('phone');
        $phone = trim($phone);
        $PhoneVerifyCode = I('PhoneVerifyCode');
        if (!$phone || !$PhoneVerifyCode) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请填写手机号码和验证码"));
            exit;
        }
        $this->VerifyPhoneCode();
        $result = $this->huoshu_subagent->setPhone($phone);
        if ($result == "1") {
            $_SESSION['user_login'] = $phone;
            $_SESSION['phone'] = $phone;
            $this->ajaxReturn(array("error" => "0", "msg" => "绑定成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => $result));
        }
    }

    public function bindEmail() {
        $r = $this->huoshu_account->getUserEmail($this->agid);
//        echo $this->subid;
        $this->assign("email_now", $r);
        $this->display();
    }

    public function bindEmail_post() {
        $email = I('email');
        $email = trim($email);
        if (!$email) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请填写邮件地址"));
            exit();
        }
        $result = $this->huoshu_subagent->setEmail($email);
        if ($result == "1") {
            $this->ajaxReturn(array("error" => "0", "msg" => "绑定成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => $result));
        }
    }
}

