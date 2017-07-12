<?php
/**
 * 后台Controller
 */
namespace Common\Controller;

class AgentbaseController extends AgentPublicController {
    public $huoshu_agent;
    public $agid;
    public $user_type;
    public $user_info;

    function _initialize() {
        parent::_initialize();
        if (isset($_SESSION['agent_id']) && $_SESSION['agent_id']) {
            $this->agid = $_SESSION['agent_id'];
            $user_info = get_user_info_by_id($_SESSION['agent_id']);
            $this->user_info = $user_info;
            $this->assign("user", $user_info);
            $this->huoshu_agent = new \Huosdk\Agent($_SESSION['agent_id']);
        }
        //如果用户没有登录，就跳转到登录界面
        //严旭
        if (!is_logged_in()) {
            redirect($this->login_url);
            exit;
        }
        if ((isset($_SESSION['roleid']) && ($_SESSION['roleid'] == $this->subagent_roleid))) {
            $this->user_type = "subagent";
        }
        if ((isset($_SESSION['roleid']) && ($_SESSION['roleid'] == $this->agent_roleid))) {
            $this->user_type = "agent";
        }
        $this->assign("user_type", $this->user_type);
//         //确保只有agent才能看到这些页面，使用这些功能
//        if($this->is_agent_site()){
//        if(!(isset($_SESSION['roleid'])&&($_SESSION['roleid']==$this->agent_roleid))){
//            exit;
//            } 
//        }
//        //确保只有subagent才能看到这些页面，使用这些功能
//        if($this->is_subagent_site()){
//            if(!(isset($_SESSION['roleid'])&&($_SESSION['roleid']==$this->subagent_roleid))){
//                exit;
//            } 
//        }
    }

    public function is_agent_loggedin() {
        if (isset($_SESSION['roleid']) && ($_SESSION['roleid'] == $this->agent_roleid)) {
            return true;
        }
    }

    public function is_subagent_loggedin() {
        if (isset($_SESSION['roleid']) && ($_SESSION['roleid'] == $this->subagent_roleid)) {
            return true;
        }
    }

    public function is_agent_site_and_loggedin() {
        return $this->is_agent_loggedin() && $this->is_agent_site();
    }

    public function is_subagent_site_and_loggedin() {
        return $this->is_subagent_loggedin() && $this->is_subagent_site();
    }

    public function payway_txt() {
        $data = M('payway')->getField("payname,realname");

        return $data;
    }
}
