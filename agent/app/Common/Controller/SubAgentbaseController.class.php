<?php
namespace Common\Controller;

class SubAgentbaseController extends AgentPublicController {
    public $subid;
    public $Huosdk_subagent;

    function _initialize() {
        parent::_initialize();
        if (isset($_SESSION['agent_id']) && $_SESSION['agent_id']) {
            $this->subid = $_SESSION['agent_id'];
            $user_info = get_user_info_by_id($_SESSION['agent_id']);
            $this->assign("user", $user_info);
            $this->Huosdk_subagent = new \Huosdk\SubAgent($_SESSION['agent_id']);
        }
        //如果用户没有登录，就跳转到登录界面
        if (!is_logged_in()) {
            redirect($this->login_url);
            exit;
        }
        //确保只有subagent才能看到这些页面，使用这些功能
        if (!(isset($_SESSION['roleid']) && ($_SESSION['roleid'] == $this->subagent_roleid))) {
            exit;
        }
    }
}

