<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class FinanceController extends AgentbaseController {
    public $account_remain;
    public $account_freeze;
    public $base;
    public $ptb_remain;
    public $hs_settle_obj;

    function _initialize() {
        parent::_initialize();
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $this->ptb_remain = $hs_pb_obj->getBalance($this->agid);
        $this->hs_settle_obj = new \Huosdk\Settle();
        $this->base = $this->hs_settle_obj->getSettleBase();
        $this->account_freeze = $this->hs_settle_obj->getTotalFreeze($this->agid);
        $this->account_remain = $this->hs_settle_obj->getAccountRemain($this->agid);
        $this->assign_value();
    }

    public function assign_value() {
        $this->assign("agent_withdraw_base", $this->base);
        $balance_txt = $this->account_remain;
        if (!$balance_txt) {
            $balance_txt = '0.00';
        }
        $this->assign("account_remain", $balance_txt);
        $freeze_txt = $this->account_freeze;
        if (!$freeze_txt) {
            $freeze_txt = '0.00';
        }
        $this->assign("account_freeze", $freeze_txt);
        $ptb_remain_txt = $this->ptb_remain;
        if (!$ptb_remain_txt) {
            $ptb_remain_txt = '0.00';
        }
        $this->assign("ptb_remain", $ptb_remain_txt);
    }
}

