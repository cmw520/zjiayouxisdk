<?php
namespace Pay\Controller;

use Common\Controller\AdminbaseController;

class PtbGiveController extends AdminbaseController {
    public function member() {
        $this->display();
    }

    public function agent() {
        $this->display();
    }

    public function sub() {
        $this->display();
    }

    public function member_post() {
        $mem_id = I('mem_id');
        $amount = I('amount');
        $remark = I('remark');
        $paypwd = I('paypwd');
        $admin_id = get_current_admin_id();
        if (!$mem_id) {
            $this->error("请选择玩家");
            exit;
        }
        if (!$amount || !is_numeric($amount) || ($amount < 0)) {
            $this->error("金额有误");
        }
        $amount = (float)$amount;
        if (!$paypwd) {
            $this->error("请输入二级密码");
            exit;
        }
        $hs_account_obj = new \Huosdk\Account();
        $paypwd_match = $hs_account_obj->check_paypwd($paypwd, $admin_id);
        if (!$paypwd_match) {
            $this->error("二级密码错误");
            exit;
        }
        $hs_charge_obj = new \Huosdk\Charge();
        $hs_charge_obj->adminChargeForMemberPtb($admin_id, $mem_id, $amount, $amount, $remark);
        $this->success("充值成功");
    }
}