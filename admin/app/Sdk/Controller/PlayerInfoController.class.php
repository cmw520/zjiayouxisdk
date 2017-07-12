<?php
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class PlayerInfoController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->display();
    }
}

