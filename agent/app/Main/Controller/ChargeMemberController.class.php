<?php
namespace Main\Controller;

use Common\Controller\MainbaseController;

class ChargeMemberController extends MainbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function everCharged($agent_id, $mem_id, $app_id) {
        $where = array();
        $where['admin_id'] = $agent_id;
        $where['mem_id'] = $mem_id;
        $where['app_id'] = $app_id;
        return M('gm_charge')->where($where)->count();
    }

    public function charge() {
        $agent_id = $this->agid;
        $app_id = I('app_id');
        $mem_id = I('mem_id');
        $amount = I('amount');
        $paypwd = I('paypwd');
        $getgold = I('getgold');
        $amount = (float)$amount;
        if (!$amount) {
            $this->ajaxReturn(array("error" => "1", "msg" => "充值金额不能为0"));
            exit;
        }
        //检查支付密码是否正确
        $this->checkPayPwd($agent_id, $paypwd);
        //检查玩家是否存在
        $hs_member_obj = new \Huosdk\Member();
        $mem_exists = $hs_member_obj->mem_id_exists($mem_id);
        if (!$mem_exists) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家不存在"));
            exit;
        }
        $aginfo = $this->getAgInfo($agent_id, $app_id);
        if (!$aginfo) {
            $this->ajaxReturn(array("error" => "1", "msg" => "参数有误"));
            exit;
        }
        $benefit_type = $aginfo['benefit_type'];
        $agid = $aginfo['ag_id'];
        $hs_charge_obj = new \Huosdk\Charge();
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $ptb_balance = $hs_pb_obj->getBalance($agent_id);
        if ($benefit_type == 1) {
            $get = (float)$amount;
            $rate = $aginfo['agent_rate'];
            $rate = (float)$rate;
            $pay = $get * $rate;
            if ($ptb_balance < $pay) {
                $this->ajaxReturn(array("error" => "1", "msg" => "余额不足，请充值"));
                exit;
            }
            $hs_charge_obj->addAgentForMemberGmChargeRecord(
                $benefit_type, $pay, $get, $rate, $agent_id, $mem_id, $app_id
            );
            $hs_charge_obj->IncMemberAppBalance($mem_id, $app_id, $pay, $get);
            $hs_charge_obj->DecAgentPtbBalance($agent_id, $pay);
        } else if ($benefit_type == 2) {
            $pay = (float)$amount;
            if ($ptb_balance < $pay) {
                $this->ajaxReturn(array("error" => "1", "msg" => "余额不足，请充值"));
                exit;
            }
            $rate = $aginfo['agent_rate'];
            $rate = (float)$rate;
            $get = $pay * $rate + $pay;
            $hs_charge_obj->addAgentForMemberGmChargeRecord(
                $benefit_type, $pay, $get, $rate, $agent_id, $mem_id, $app_id
            );
            $hs_charge_obj->IncMemberAppBalance($mem_id, $app_id, $pay, $get);
            $hs_charge_obj->DecAgentPtbBalance($agent_id, $pay);
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "未设定优惠"));
            exit;
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "成功"));
    }

    public function checkPayPwd($agent_id, $paypwd) {
        $sp_pw = pay_password($paypwd);
        $pay_pwd_result = M('users')->where(array("id" => $agent_id, "pay_pwd" => $sp_pw))->find();
        if (!$pay_pwd_result) {
            $this->ajaxReturn(array("error" => "1", "msg" => "支付密码错误"));
            exit;
        }
    }

    public function getAgInfo($agent_id, $app_id) {
        $hs_benefit_obj = new \Huosdk\Benefit();
        $data = $hs_benefit_obj->get_agentgame_agentrate_info($agent_id, $app_id);
        return $data;
    }

    public function add_order_rebate() {
    }

    public function add_order_discount() {
        /**
         * 要更新gm_charge，这是给玩家充值的，要插入一条记录，统计记录这次的优惠类型，应用了什么折扣或者返利
         *
         * 要更新gm_mem，增加玩家在对应游戏的余额
         *
         * 要更新ptb_agent，在渠道自己的余额中减去相应金额
         */
    }
}
