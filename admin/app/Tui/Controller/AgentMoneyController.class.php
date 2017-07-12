<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentMoneyController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function balance() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $model = M('agent_ext');
        $where = array();
        $where['ae.agent_id'] = array("neq", 0);
//        $where[''] = $v;
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "agent_id", "ae.agent_id");
        $hs_where_obj->get_simple($where, "user_type", "u.user_type");
        $count = $model
            ->field("u.user_login as agent_name,ae.*")
            ->alias("ae")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ae.agent_id")
            ->count();
        $page = $this->page($count, 10);
        $items = $model
            ->field("u.user_nicename as agent_name,ae.*,u.user_type")
            ->alias("ae")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ae.agent_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("ae.agent_id desc")
            ->select();
        $this->agent_level($items);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function agent_level(&$items) {
        $hs_account_obj = new \Huosdk\Account();
        foreach ($items as $key => $value) {
            if ($value['user_type'] == $hs_account_obj->agentRoldId) {
                $items[$key]['agent_level_txt'] = "一级代理";
            } else if ($value['user_type'] == $hs_account_obj->subAgentRoldId) {
                $items[$key]['agent_level_txt'] = "二级代理";
            }
        }
    }

    public function income() {
        $agent_id = I('agent_id');
        $model = M('agent_order');
        $where = array();
        $where['aor.agent_id'] = $agent_id;
        $count = $model
            ->alias("aor")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("aor.*,g.name as game_name,m.username as mem_name,u2.user_nicename as parent_agent_name")
            ->alias("aor")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid")
            ->limit($page->firstRow, $page->listRows)
            ->order("aor.id desc")
            ->select();
        foreach ($items as $k => $v) {
            if ($v['rebate_cnt'] == 0) {
                $items[$k]['benefit_type'] = "折扣";
            } else {
                $items[$k]['benefit_type'] = "返利";
            }
        }
        $sumitems = $model
            ->field(
                "sum(aor.agent_gain) as sum_agent_gain, sum(aor.amount) as sum_amount,sum(aor.real_amount) as sum_real_amount"
            )
            ->alias("aor")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=aor.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=aor.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=aor.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid")
            ->order("aor.id desc")
            ->select();
        $hs_ff_obj = new \Huosdk\Data\FormatRecords();
        $hs_ff_obj->payway($items);
        $this->assign("sumitems", $sumitems);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function outcome() {
        $agent_id = I('agent_id');
        $all_items = $hs_oc_obj = \Huosdk\Money\AgentOutcome::getList($agent_id, 0, 0);
        $count = count($all_items);
        $page = $this->page($count, 20);
        $items = $hs_oc_obj = \Huosdk\Money\AgentOutcome::getList($agent_id, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }
}

