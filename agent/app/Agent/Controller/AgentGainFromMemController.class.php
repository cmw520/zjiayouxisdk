<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class AgentGainFromMemController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->display();
    }
}

