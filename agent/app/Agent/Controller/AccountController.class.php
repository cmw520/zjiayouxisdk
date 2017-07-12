<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class AccountController extends AgentbaseController {
    public function logout() {
        $redirect_url = U("front/Index/index").'?agent='.$this->agid;
        end_session();
        redirect($redirect_url);
    }
}