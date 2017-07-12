<?php
namespace Main\Controller;

use Common\Controller\MainbaseController;

class BenefitController extends MainbaseController {
    private $hs_benefit_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_benefit_obj = new \Huosdk\Benefit();
    }

    public function set() {
        $allitems = $this->hs_benefit_obj->AgentGameList_single($_SESSION['agent_id'], 0, 0);
        $totalRows = count($allitems);
        $page = new \Think\Page($totalRows, 15);
        $items = $this->hs_benefit_obj->AgentGameList_single($_SESSION['agent_id'], $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function get_default_agent_rate($agid) {
        $hs_benefit_obj = new \Huosdk\Benefit();
        return $hs_benefit_obj->get_app_default_agentrate_by_agid($agid);
    }

    public function discount_filter($agent_rate, $mem_first, $mem_refill) {
    }

    public function rebate_filter($agent_rate, $mem_first, $mem_refill) {
    }

    public function set_sub() {
        $agid = I('agid');
        $benefit_type = I('benefit_type');
        $data = array();
        if ($benefit_type == 1) {
            $data['first_mem_rate'] = I('mem_first');
            $data['mem_rate'] = I('mem_refill');
        } else if ($benefit_type == 2) {
            $data['first_mem_rebate'] = I('mem_first');
            $data['mem_rebate'] = I('mem_refill');
        }
        $data['agent_rate'] = I('agent_rate');
        $benefit_first = I('mem_first');
        $benefit_refill = I('mem_refill');
        $agent_rate = I('agent_rate');
        $hs_br_obj = new \Huosdk\Benefit\RulesAgentGame($agid, $agent_rate);
        if ($benefit_type == 1) {
            $data['first_mem_rate'] = I('mem_first');
            $data['mem_rate'] = I('mem_refill');
            $result = $hs_br_obj->check_mem_rate($data['mem_rate'], $data['first_mem_rate']);
            if ($result != "ok") {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => $result
                    )
                );
            }
        } else if ($benefit_type == 2) {
            $data['first_mem_rebate'] = I('mem_first');
            $data['mem_rebate'] = I('mem_refill');
            $result = $hs_br_obj->check_mem_rebate($data['mem_rebate'], $data['first_mem_rebate']);
            if ($result != "ok") {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => $result
                    )
                );
            }
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "玩家优惠类型有误"
                )
            );
            exit();
        }
        M('agent_game_rate')->where(array("ag_id" => $agid))->save($data);
        $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
    }

    public function set_agent() {
        $agid = I('agid');
        $benefit_type = I('benefit_type');
        $data = array();
        if ($benefit_type == 1) {
            $data['first_mem_rate'] = I('mem_first');
            $data['mem_rate'] = I('mem_refill');
        } else if ($benefit_type == 2) {
            $data['first_mem_rebate'] = I('mem_first');
            $data['mem_rebate'] = I('mem_refill');
        }
        $benefit_first = I('mem_first');
        $benefit_refill = I('mem_refill');
        $default_agent_rate = $this->get_default_agent_rate($agid);
        if ($default_agent_rate > $benefit_first) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家首充不能小于".$default_agent_rate));
            exit;
        }
        if ($benefit_refill < $benefit_first) {
            $this->ajaxReturn(array("error" => "1", "msg" => "玩家续充不能小于玩家首充"));
            exit;
        }
        M('agent_game_rate')->where(array("ag_id" => $agid))->save($data);
        $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
    }
}
