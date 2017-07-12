<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentPtbController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function official_deduct() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $where = array();
        $hs_w_obj = new \Huosdk\Where();
        $hs_w_obj->get_simple($where, "agent_id", "pb.agent_id");
        $hs_deduct_obj = new \Huosdk\Deduct();
        $count = $hs_deduct_obj->agentPtbDeductCnt($where);
        $page = $this->page($count, 10);
        $items = $hs_deduct_obj->agentPtbDeductList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show("Admin"));
        $this->display();
    }

    public function official_deduct_member() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("app_select2", $hs_ui_filter_obj->app_select2());
        $where = array();
        $hs_w_obj = new \Huosdk\Where();
        $hs_w_obj->get_simple($where, "app_id", "gb.app_id");
        $hs_w_obj->get_simple_like($where, "mem_name", "m.username");
        $total_rows = $this->official_deduct_member_cnt($where);
        $page = $this->page($total_rows, 10);
        $items = $this->official_deduct_member_list($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show("Admin"));
        $this->display();
    }

    /**
     * 扣回数量
     *
     * @param array $where_extra
     *
     * @return mixed
     * wuyonghong
     */
    public function official_deduct_member_cnt($where_extra = array()) {
        $count = M('gm_back')
            ->alias("gb")
            ->field("gb.*,g.name,m.username")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gb.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gb.mem_id")
            ->where($where_extra)
            ->count();

        return $count;
    }

    public function official_deduct_member_list($where_extra = array(), $start = 0, $limit = 0) {
        $items = M('gm_back')
            ->alias("gb")
            ->field("gb.*,g.name,m.username")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gb.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gb.mem_id")
            ->where($where_extra)
            ->limit($start, $limit)
            ->order("gb.id desc")
            ->select();

        return $items;
    }

    public function official_give() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("agent_select_Level_one", $hs_ui_filter_obj->agent_select_Level_one());
        $this->assign("agent_select_Level_two", $hs_ui_filter_obj->agent_select_Level_two());
        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "pg.create_time");
        if (isset($_GET['agent_id']) && $_GET['agent_id']) {
            $where['pg.agent_id'] = $_GET['agent_id'];
        }
        $hs_ptb_obj = new \Huosdk\Ptb();
        $all_items = $hs_ptb_obj->giveList($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $items = $hs_ptb_obj->giveList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show("Admin"));
        // 显示设置的充值时间段
        $paydatatime = M('options')->where('option_name="self_help_pay"')->getField('option_value');
        $data = json_decode($paydatatime);
        $this->assign('pay_start_time', $data->start_time);
        $this->assign('pay_end_time', $data->end_time);
        $this->display();
    }

    public function official_give_member() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $model = M('gm_given');
        $where = array();
//        $where[''] = $v;
        $hs_w_obj = new \Huosdk\Where();
        $hs_w_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_w_obj->get_simple($where, "app_id", "gg.app_id");
        $hs_w_obj->time($where, "gg.create_time");
        $count = $model
            ->field("gg.*,g.name as game_name,m.username as member_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gg.mem_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("gg.*,g.name as game_name,m.username as member_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gg.mem_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("gg.id desc")
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show("Admin"));
        $this->display();
    }

    public function member_balance() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $where = array();
        /* if(isset($_GET['mem_id'])&&$_GET['mem_id']){
            $where['gm.mem_id']=$_GET['mem_id'];
        } */
        $hs_w_obj = new \Huosdk\Where();
        $hs_w_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_gb_obj = new \Huosdk\GmBalance();
        $all_items = $hs_gb_obj->getList($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $items = $hs_gb_obj->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show("Admin"));
        $this->display();
    }

    public function agent_balance() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $where = array();
        $hs_w_obj = new \Huosdk\Where();
        $hs_w_obj->get_simple($where, "agent_id", "pa.agent_id");
        $hs_w_obj->get_simple($where, "user_type", "u.user_type");
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $all_items = $hs_pb_obj->getAgentList($where, 0, 0);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $items = $hs_pb_obj->getAgentList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function deduct_agent_post() {
        if (!I('agent_id')) {
            $this->ajaxReturn(array("error" => "1", "msg" => "请选择渠道"));
            exit;
        }
        $agent_id = I('agent_id');
//        $hs_agent_obj=new \Huosdk\Account();
//        $agent_id=$hs_agent_obj->getAgentIdByUserLogin($user_login);
//        if(!$agent_id){
//            $this->ajaxReturn(array("error"=>"1","msg"=>"渠道帐号不存在"));
//            exit;
//        }
        $hs_password_obj = new \Huosdk\Password();
        $pass_check = $hs_password_obj->checkAdminPaypwd(get_current_admin_id(), I('paypwd'));
        if (!$pass_check) {
            $this->ajaxReturn(array("error" => "1", "msg" => "二级密码错误"));
            exit;
        }
        $amount = I("amount");
        $amount = (float)$amount;
        if (!(is_numeric($amount) && $amount > 0 && $amount < 100000)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "金额有误"));
            exit;
        }
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $agent_ptb_remain = $hs_pb_obj->getBalance($agent_id);
        if ($agent_ptb_remain < $amount) {
            $this->ajaxReturn(array("error" => "1", "msg" => "渠道平台币余额不足"));
            exit;
        }
        $hs_deduct_obj = new \Huosdk\Deduct();
        $hs_deduct_obj->DeductAgentPtb($agent_id, $amount, $amount, I("remark"));
        $this->ajaxReturn(array("error" => "0", "msg" => "扣回成功"));
    }

    public function deduct_member_post() {
    }

    // 设置自定义时间段
    public function setPayTime() {
        $data['start_time'] = I('post.start_time');
        $data['end_time'] = I('post.end_time');
        $pay_time = json_encode($data);
        $res = M('options')->where("option_name='self_help_pay'")->setField('option_value', $pay_time);
        if ($res) {
            $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "设置失败"));
        }
    }
}

