<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DeductController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function deduct_mem_post() {
        if (!I('mem_name')) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请输入玩家帐号"));
            exit;
        }
        $username = I('mem_name');
        $hs_account_obj = new \Huosdk\Account();
        $mem_id = $hs_account_obj->getMemIdByName($username);
        if (!$mem_id) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家帐号不存在"));
            exit;
        }
        $hs_password_obj = new \Huosdk\Password();
        $pass_check = $hs_password_obj->checkAdminPaypwd(get_current_admin_id(), I('paypwd'));
        if (!$pass_check) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级密码错误"));
            exit;
        }
        $amount = I("amount");
        if (!is_numeric($amount) || ($amount <= 0)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "金额有误"));
            exit;
        }
        $app_id = I('app_id');
        $hs_gb_obj = new \Huosdk\GmBalance();
        $remain = $hs_gb_obj->getMemGmBalance($mem_id, $app_id);
        if ($remain < $amount) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家游戏币余额不足 test".$remain));
            exit;
        }
        $hs_deduct_obj = new \Huosdk\Deduct();
        $hs_deduct_obj->DeductMemGm($app_id, $mem_id, $amount, $amount, $remark);
        $this->ajaxReturn(array("error" => "0", "msg" => "扣回成功"));
    }
}

