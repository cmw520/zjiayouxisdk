<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class ApiController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function getAgentPtbRemain() {
//        $user_login=I('user_login');
        $agent_id = I("agent_id");
//        $hs_agent_obj=new \Huosdk\Account();
//        $agent_id=$hs_agent_obj->getAgentIdByUserLogin($user_login);
//        if(!$agent_id){
//            $this->ajaxReturn(array("error"=>"1","msg"=>"渠道帐号不存在"));
//            exit;
//        }
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $result = $hs_pb_obj->getBalance($agent_id);
        $this->ajaxReturn(array("error" => "0", "msg" => $result));
    }

    public function getMemGmBalance() {
        $mem_name = I('mem_name');
        $app_id = I('app_id');
        $hs_agent_obj = new \Huosdk\Account();
        $mem_id = $hs_agent_obj->getMemIdByName($mem_name);
        if (!$mem_id) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家帐号不存在".$mem_id));
            exit;
        }
        $hs_gb_obj = new \Huosdk\GmBalance();
        $remain = $hs_gb_obj->getMemGmBalance($mem_id, $app_id);
        $this->ajaxReturn(array("error" => "0", "msg" => $remain));
    }
}

