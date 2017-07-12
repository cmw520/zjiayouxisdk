<?php
namespace Common\Controller;

class MemberbaseController extends HomebaseController {
    function _initialize() {
        parent::_initialize();
        $this->check_login();
        $this->check_user();
    }
}