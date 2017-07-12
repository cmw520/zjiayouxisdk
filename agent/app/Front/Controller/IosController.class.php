<?php
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class IosController extends AgentPublicController {
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->display();
    }
}

