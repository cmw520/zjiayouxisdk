<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class ManIndexPageController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        redirect(U('Tui/About/ads'));
        $this->display();
    }
}

