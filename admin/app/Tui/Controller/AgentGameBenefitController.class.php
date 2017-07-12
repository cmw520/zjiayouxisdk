<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentGameBenefitController extends AdminbaseController {
    private $hs_benefit_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_benefit_obj = new \Huosdk\Benefit();
    }

    public function link() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->display();
    }

    public function man() {
        $where = array();
        $hs_Filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_Filter_obj->app_select());
        $this->assign("agent_select", $hs_Filter_obj->agent_select());
        $hs_wh_obj = new \Huosdk\Where();
//         $hs_wh_obj->get_simple_like($where,"appname","g.name");
        $hs_wh_obj->get_simple($where, "app_id", "g.id");
        $hs_wh_obj->get_simple($where, "agent_id", "ag.agent_id");
        $count = $this->hs_benefit_obj->AgentGameListCnt($where);
        $page = $this->page($count, 20);
        $items = $this->hs_benefit_obj->AgentGameList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function add() {
        $app_list = $this->hs_benefit_obj->benefitUndefinedAppList();
        $select_txt = "<option value='0'>选择游戏</option>";
        foreach ($app_list as $k => $v) {
            $app_id = $v['id'];
            $app_name = $v['name'];
            $select_txt .= "<option value='$app_id'>$app_name</option>";
        }
        $this->assign("app_select_list", $select_txt);
        $this->display();
    }

    public function add_post() {
        $app_id = I('app_id');
        if (!$app_id) {
            $this->error("请选择游戏");
            exit;
        }
        if (!$this->hs_benefit_obj->benefilt_value_filter()) {
            $this->error("参数错误");
            exit;
        }
        $this->hs_benefit_obj->addAppBenefit($app_id, $_POST);
        $this->success("添加成功");
    }

    public function edit() {
        $ag_id = I('agent_game_id');
        $data = $this->hs_benefit_obj->get_AgentApp_benefit_info($ag_id);
        $this->assign("data", $data);
        $this->display();
    }

    public function edit_post() {
        if (!$this->hs_benefit_obj->benefilt_value_filter()) {
            $this->error("参数错误");
            exit;
        }
        $ag_id = I('agent_game_id');
        $this->hs_benefit_obj->setAgentGameBenefit($ag_id, $_POST);
        $this->success("修改成功");
    }
}
