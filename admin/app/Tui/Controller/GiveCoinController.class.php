<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class GiveCoinController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function agent() {
        $agent_id = I('agent_id');
        $hs_ac_obj = new \Huosdk\Account();
        $exist = $hs_ac_obj->get_user_info_by_id($agent_id);
        if (!$exist) {
            $this->ajaxReturn(array("error" => "1", "msg" => "一级代理帐号不存在"));
            exit;
        }
        $this->give_new(I('agent_id'), I('paypwd'), I('remark'), I('amount'));
    }

    public function sub() {
//        $user_login=I('user_login');
//        $hs_ex_obj=new \Huosdk\Exist();
//        $exist=$hs_ex_obj->SubAgentUserLogin($user_login);
//        if(!$exist){
//            $this->ajaxReturn(array("error"=>"1","msg"=>"二级代理帐号不存在"));
//            exit;
//        }
        $agent_id = I('agent_id');
        $hs_ac_obj = new \Huosdk\Account();
        $exist = $hs_ac_obj->get_user_info_by_id($agent_id);
        if (!$exist) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级代理帐号不存在"));
            exit;
        }
        $this->give_new(I('agent_id'), I('paypwd'), I('remark'), I('amount'));
    }

    public function give($user_login, $paypwd, $remark, $amount) {
        $hs_a_obj = new \Huosdk\Account();
        $agent_id = $hs_a_obj->getAgentIdByUserLogin($user_login);
        $admin_id = get_current_admin_id();
        $hs_password_obj = new \Huosdk\Password();
        $pass_match = $hs_password_obj->checkAdminPaypwd($admin_id, $paypwd);
        if (!$pass_match) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级密码错误"));
            exit;
        }
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $hs_pb_obj->Inc($agent_id, $amount);
        $hs_pb_obj->addIncRecord($admin_id, $agent_id, $amount, $remark);
        $this->ajaxReturn(array("error" => "0", "msg" => "发放成功"));
    }

    /**
     * @param INT $agent_id 渠道ID
     * @param STRING $paypwd 支付密码
     * @param STRING $remark 备注
     * @param FLOAT $amount 游戏币(平台币)充值金额
     */
    public function give_new($agent_id, $paypwd, $remark, $amount) {
        $admin_id = get_current_admin_id();
        $hs_password_obj = new \Huosdk\Password();
        $pass_match = $hs_password_obj->checkAdminPaypwd($admin_id, $paypwd);
        if (!$pass_match) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级密码错误"));
            exit;
        }
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $hs_pb_obj->Inc($agent_id, $amount);
        $hs_pb_obj->addIncRecord($admin_id, $agent_id, $amount / C('G_RATE'), $remark);
        $this->ajaxReturn(array("error" => "0", "msg" => "发放成功"));
    }

    public function member() {
        $member_name = I('member_name');
//        $game_name=I('game_name');
        $amount = I('amount');
        $paypwd = I('paypwd');
        $remark = I("remark");
        $admin_id = get_current_admin_id();
        $hs_password_obj = new \Huosdk\Password();
        $pass_match = $hs_password_obj->checkAdminPaypwd($admin_id, $paypwd);
        if (!$pass_match) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级密码错误"));
            exit;
        }
        $hs_account_obj = new \Huosdk\Account();
        $mem_id = $hs_account_obj->getMemIdByName($member_name);
        if (!$mem_id) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家不存在"));
            exit;
        }
        $hs_game_obj = new \Huosdk\Game();
//        $app_id=$hs_game_obj->getAppIdByAppName($game_name);
        $app_id = I('app_id');
        $app_id_exist = $hs_game_obj->app_id_exists($app_id);
        if (!$app_id_exist) {
            $this->ajaxReturn(array("error" => "1", "msg" => "游戏不存在"));
            exit;
        }
        if (!(is_numeric($amount) && $amount > 0 && $amount < 100000)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "金额有误"));
            exit;
        }
        $hs_gmbalance_obj = new \Huosdk\GmBalance();
        $hs_gmbalance_obj->Inc($mem_id, $app_id, $amount);
        $hs_gmbalance_obj->addIncRecord($admin_id, $mem_id, $app_id, $amount, $remark);
        $this->ajaxReturn(array("error" => "0", "msg" => "发放成功"));
    }
}

