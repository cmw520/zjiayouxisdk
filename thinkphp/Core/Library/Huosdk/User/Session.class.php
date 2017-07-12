<?php
namespace Huosdk\User;
class Session {
    function mark_user_logged_in($agent_id) {
        $_SESSION['logged_in'] = true;
//        $info=  get_agent_info_by_phone($phone);
        $_SESSION['agent_id'] = $agent_id;
//        $_SESSION['roleid']=$info['user_type'];
    }
}

