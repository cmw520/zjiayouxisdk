<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class BenefitController extends AgentbaseController {
    private $hs_benefit_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_benefit_obj = new \Huosdk\Benefit();
    }

    public function set() {
        $where_extra = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where_extra, "gamename", "g.name");
        $allitems = $this->hs_benefit_obj->AgentGameList_self($_SESSION['agent_id'], $where_extra, 0, 0);
        $totalRows = count($allitems);
        $page = new \Think\Page($totalRows, 15);
        $items = $this->hs_benefit_obj->AgentGameList_self(
            $_SESSION['agent_id'],
            $where_extra,
            $page->firstRow,
            $page->listRows
        );
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->assign("formget", $_GET);
        $this->display();
    }

    public function set_sub() {
        $where_extra = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where_extra, "gamename", "g.name");
        $allitems = $this->hs_benefit_obj->AgentGameList_sub($_SESSION['agent_id'], $where_extra, 0, 0);
        $totalRows = count($allitems);
        $page = new \Think\Page($totalRows, 15);
        $items = $this->hs_benefit_obj->AgentGameList_sub(
            $_SESSION['agent_id'],
            $where_extra,
            $page->firstRow,
            $page->listRows
        );
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->assign("formget", $_GET);
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

    public function set_sub_post() {
        if (C("G_DISCONT_TYPE")) {
            $this->set_sub_post_with_benefit();
        } else {
            $this->set_sub_post_no_benifit();
        }
    }

    public function set_sub_post_no_benifit() {
        $agid = I('agid');
        $data = array();
        $data['agent_rate'] = I('agent_rate');
        $agent_rate = I('agent_rate');
        if (!($agent_rate > 0 && $agent_rate < 1)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "渠道折扣必须介于0和1之间"
                )
            );
            exit();
        }
        $default_agent_rate = $this->get_default_agent_rate($agid);
        if ($default_agent_rate > $agent_rate) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "渠道折扣不能小于".$default_agent_rate
                )
            );
            exit();
        }
        M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->save($data);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "设置成功"
            )
        );
    }

    public function set_sub_post_with_benefit() {
        $agid = I('agid');
        $benefit_type = I('benefit_type');
        $data = array();
        $data['agent_rate'] = I('agent_rate');
        $agent_rate = I('agent_rate');
        if (!($agent_rate > 0 && $agent_rate < 1)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "渠道折扣必须介于0和1之间"
                )
            );
            exit();
        }
        $hs_br_obj = new \Huosdk\Benefit\RulesAgentGame($agid, $agent_rate);
        if (0 == $benefit_type) {
            $data['first_mem_rate'] = 1;
            $data['mem_rate'] = 1;
            $data['first_mem_rebate'] = 0;
            $data['mem_rebate'] = 0;
        } else if ($benefit_type == 1) {
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
        $benefit_first = I('mem_first');
        $benefit_refill = I('mem_refill');
        $default_agent_rate = $this->get_default_agent_rate($agid);
        if ($default_agent_rate > $agent_rate) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "渠道折扣不能小于".$default_agent_rate
                )
            );
            exit();
        }
        if ($benefit_refill < $agent_rate) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "玩家续充不能小于渠道折扣"
                )
            );
            exit();
        }
        if ($benefit_refill < $benefit_first) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "玩家续充不能小于玩家首充"
                )
            );
            exit();
        }
        M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->save($data);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "设置成功"
            )
        );
    }

    public function set_agent_post() {
        $agid = I('agid');
        $benefit_type = I('benefit_type');
        $data = array();
        $default_agent_rate = $this->get_default_agent_rate($agid);
        $hs_br_obj = new \Huosdk\Benefit\RulesAgentGame($agid, $default_agent_rate);
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
        }
        $benefit_first = I('mem_first');
        $benefit_refill = I('mem_refill');
        M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->save($data);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "设置成功"
            )
        );
    }

    public function set_agent_post_no_benefit() {
        $agid = I('agid');
        $data = array();
        M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->save($data);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "设置成功"
            )
        );
    }

    public function set_agent_post_with_benefit() {
        $agid = I('agid');
        $benefit_type = I('benefit_type');
        $data = array();
        $default_agent_rate = $this->get_default_agent_rate($agid);
        $hs_br_obj = new \Huosdk\Benefit\RulesGame($default_agent_rate);
        if ($benefit_type == 1) {
            $data['first_mem_rate'] = I('mem_first');
            $data['mem_rate'] = I('mem_refill');
            // if(!($data['first_mem_rate']>0 && $data['first_mem_rate']<=1)&&($data['mem_rate']>0 && $data['mem_rate']<=1)){
            // $this->ajaxReturn(array("error"=>"1","msg"=>"玩家折扣必须介于0和1之间"));
            // exit;
            // }
            // if(!($data['first_mem_rate'] < $data['mem_rate'])){
            // $this->ajaxReturn(array("error"=>"1","msg"=>"玩家续充折扣必须大于首充折扣"));
            // exit;
            // }
            if (!(($data['first_mem_rate'] > $hs_br_obj->mem_rate_bottom
                   && $data['first_mem_rate'] <= $hs_br_obj->mem_rate_top)
                  && ($data['mem_rate'] > $hs_br_obj->mem_rate_bottom && $data['mem_rate'] <= $hs_br_obj->mem_rate_top))
            ) {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => "玩家折扣必须介于 $hs_br_obj->mem_rate_bottom 和 $hs_br_obj->mem_rate_top 之间"
                    )
                );
                exit();
            }
            if (!($data['first_mem_rate'] < $data['mem_rate'])) {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => "玩家续充折扣必须大于首充折扣"
                    )
                );
                exit();
            }
        } else if ($benefit_type == 2) {
            $data['first_mem_rebate'] = I('mem_first');
            $data['mem_rebate'] = I('mem_refill');
            if (!(($data['first_mem_rebate'] > $hs_br_obj->mem_rebate_bottom
                   && $data['first_mem_rate'] <= $hs_br_obj->mem_rebate_top)
                  && ($data['mem_rebate'] > $hs_br_obj->mem_rebate_bottom
                      && $data['mem_rebate'] <= $hs_br_obj->mem_rebate_top))
            ) {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => "玩家返利必须介于 $hs_br_obj->mem_rebate_bottom 和 $hs_br_obj->mem_rebate_top 之间"
                    )
                );
                exit();
            }
            if (!($data['first_mem_rebate'] > $data['mem_rebate'])) {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => "玩家续充返利必须小于首充返利"
                    )
                );
                exit();
            }
        }
        $benefit_first = I('mem_first');
        $benefit_refill = I('mem_refill');
        if ($default_agent_rate > $benefit_first) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "玩家首充不能小于".$default_agent_rate
                )
            );
            exit();
        }
        if ($benefit_refill < $benefit_first) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "玩家续充不能小于玩家首充"
                )
            );
            exit();
        }
        M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->save($data);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "设置成功"
            )
        );
    }

    public function AgentSetSelf_post() {
        $this->set_agent_post();
    }

    public function AgentSetSub_post() {
        $this->set_sub_post();
    }
}
