<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class SettleController extends AdminbaseController {
    public $hs_settle_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_settle_obj = new \Huosdk\Settle();
    }

    public function operator_check() {
        $model = M('settle');
        $where = array();
        $where["awr.status"] = "1";
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "agent_id", "awr.agent_id");
        $count = $model->alias('awr')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = $model
            ->field('awr.*,u.user_nicename,u.user_login,pa.remain as balance,u.linkman,u.qq,u.mobile')
            ->alias('awr')
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=awr.agent_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."ptb_agent pa ON pa.agent_id=awr.agent_id")
            ->where($where)
            ->order("awr.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->time_txt($items);
        $this->status_txt($items);
        $hs_ff_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ff_obj->agent_select());
        $this->assign("formget", $_GET);
        $this->assign("items", $items);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function getList($status) {
    }

    public function check() {
        $hs_ff_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ff_obj->agent_select());
        $model = M('settle');
        $where = array();
        $where["awr.status"] = "2";
        if (isset($_GET['agent']) && $_GET['agent'] != '') {
            $where = array("awr.agent_id" => $_GET['agent']);
        }
        $count = $model->alias('awr')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = $model
            ->field('awr.*,u.user_nicename,pa.remain as balance')
            ->alias('awr')
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=awr.agent_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."ptb_agent pa ON pa.agent_id=awr.agent_id")
            ->where($where)
            ->order("awr.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->time_txt($items);
        $this->status_txt($items);
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("items", $items);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function time_txt(&$items) {
        foreach ($items as $k => $v) {
            $items[$k]['check_time_txt'] = '';
            $items[$k]['settle_time_txt'] = '';
            $items[$k]['create_time_txt'] = '';
            if ($v['check_time']) {
                $items[$k]['check_time_txt'] = date("Y-m-d H:i:s", $v['check_time']);
            }
            if ($v['settle_time']) {
                $items[$k]['settle_time_txt'] = date("Y-m-d H:i:s", $v['settle_time']);
            }
            if ($v['create_time']) {
                $items[$k]['create_time_txt'] = date("Y-m-d H:i:s", $v['create_time']);
            }
        }
    }

    public function status_txt(&$items) {
        $hs_fr_obj = new \Huosdk\Data\FormatRecords();
        $hs_fr_obj->settle_status($items);
    }

    public function okrecord() {
        $hs_ff_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ff_obj->agent_select());
        $model = M('settle');
        $where = array();
        $where["awr.status"] = "3";
        if (isset($_GET['agent']) && $_GET['agent'] != '') {
            $where["awr.agent_id"] = $_GET['agent'];
        }
        $count = $model->alias('awr')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = $model
            ->field('awr.*,u.user_nicename,ae.share_remain as balance,u.user_login,u.qq,u.mobile')
            ->alias('awr')
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=awr.agent_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_ext ae ON ae.agent_id=awr.agent_id")
            ->where($where)
            ->order("awr.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("items", $items);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function pass() {
        $settle_id = I('id');
        $hs_settle_obj = new \Huosdk\Settle();
        $hs_settle_obj->setApplyFinancePass($settle_id);
        $this->success("审核成功");
    }

    public function notpass() {
        $settle_id = I('id');
        $hs_settle_obj = new \Huosdk\Settle();
        $hs_settle_obj->setApplyFinanceNotPass($settle_id);
        $this->success("审核成功");
    }

    public function operator_pass() {
        $settle_id = I('id');
        M('settle')->where(array("id" => $settle_id))->setField("status", 2);
        M('settle')->where(array("id" => $settle_id))->setField("check_time", time());
        $this->success("设置成功");
    }

    public function operator_notpass() {
        $settle_id = I('id');
        $hs_settle_obj = new \Huosdk\Settle();
        $hs_settle_obj->setApplyOpertorNotPass($settle_id);
        $this->success("设置成功");
    }

    public function giveMoney() {
        $settle_id = I('id');
        $this->hs_settle_obj->markApplyPaid($settle_id);
        $this->success("标记打款成功");
    }
}

