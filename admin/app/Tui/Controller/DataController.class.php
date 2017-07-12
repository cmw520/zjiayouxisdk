<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
        $this->row = 20;
    }

    public function order_charge() {
        $this->show("ok");
    }

    public function order_discount() {
        $this->show("ok");
    }

    public function order_rebate() {
        $this->show("ok");
    }

    public function coin_use_record() {
        $this->show("ok");
    }

    public function balance_charge() {
        $this->show("ok");
    }

    public function coin_charge_record() {
        $this->show("ok");
    }
}

