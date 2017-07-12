<?php
namespace Agent\Controller;

class SettleController extends FinanceController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        in_case_notpass();
        $this->assign("page_title", "提现");
        $this->display();
    }

    public function gain() {
        in_case_notpass();
        $this->assign("page_title", "提现");
        $this->display();
    }

    public function zfb() {
        if ($this->huoshu_agent->passedInfoCheck()) {
            redirect(U('Agent/Settle/zfb_ok'));
        } else {
            $this->display();
        }
    }

    public function zfb_ok() {
        $account = get_agent_account_info();
        $this->assign("account", $account);
        $this->display();
    }

    public function bank_ok() {
        $account = get_agent_account_info();
        $this->assign("account", $account);
        $this->display();
    }

    public function bank() {
        if ($this->huoshu_agent->passedInfoCheck()) {
            redirect(U('Agent/Settle/bank_ok'));
        }
        if (js_info_complete() && basic_info_complete()) {
            $check_ok = 'yes';
        } else {
            $check_ok = '';
        }
        if (js_info_complete()) {
            $card_ok = 'yes';
        } else {
            $card_ok = '';
        }
        $this->assign("check_ok", $check_ok);
        $this->assign("card_ok", $card_ok);
        $this->display();
    }

    public function withdraw() {
        $amount = I('amount');
        $payway = I('type');
        $paypwd = I('paypwd');
        $account = I('account');
        $hs_password_obj = new \Huosdk\Password();
        $paypwd_match = $hs_password_obj->CheckAgentPaypwd($this->agid, $paypwd);
        if (!$paypwd_match) {
            $this->ajaxReturn(array("error" => "1", "msg" => "支付密码错误"));
            exit;
        }
        if (!is_numeric($amount)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "金额有误"));
            exit;
        }
        if ($amount < $this->base) {
            $this->ajaxReturn(array("error" => "1", "msg" => "提现金额必须大于".$this->base."元"));
            exit;
        }
        if ($amount > $this->account_remain) {
            $this->ajaxReturn(array("error" => "1", "msg" => "账户余额不足"));
            exit;
        }
        $this->hs_settle_obj->AddRecord($this->agid, $amount, $account);
        $this->hs_settle_obj->DecAgentAccountBalance($this->agid, $amount);
        $this->ajaxReturn(array("error" => "0", "msg" => "提现申请成功，相应金额已转入冻结"));
    }

    public function records() {
        $agent_id = $this->agid;
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "s.create_time");
        $all_items = $this->hs_settle_obj->getList($agent_id);
        $count = count($all_items);
        $page = new \Think\Page($count, 10);
        $items = $this->hs_settle_obj->getList($agent_id, $where, $page->firstRow, $page->listRows);
        $sumitems = $this->hs_settle_obj->getListSum($agent_id, $where);
        $this->assign("sumitems", $sumitems);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show());
        $this->assign("num_of_records", $count);
        $this->assign("records", $items);
        $this->display();
    }
}
