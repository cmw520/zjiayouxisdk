<?php
namespace Huosdk;
class Agent {
    private $roleid;
    private $model;
    private $where;
    private $agentId;
    private $subAgentRoleId;

    public function __construct($agentId) {
        $obj = new \Huosdk\Account();
        $this->model = M('users');
        $this->roleid = $obj->agentRoldId;
        $this->subAgentRoleId = $obj->subAgentRoldId;
        $this->agentId = $agentId;
        $this->where = array("id" => $agentId);
    }

    public function test() {
//        echo sp_password("123456");
    }

    public function setPassword($pass) {
        $newpass = sp_password($pass);
        $this->model->where(array("id" => $this->agentId))->setField("user_pass", $newpass);
    }

    public function setPhone($phone) {
        $this->setField("mobile", $phone);
        $this->setField("user_login", $phone);
    }

    public function setPayPwd($pwd) {
        $sppwd = pay_password($pwd);
        M('users')->where(array("id" => $this->agentId))->setField("pay_pwd", $sppwd);
//        $this->setField("pay_pwd",$sppwd);
    }

    public function getStatus() {
        return $this->getField("user_status");
    }

    public function PayPwdSet() {
        $data = $this->getField("pay_pwd");
        $default = '04aa2bd2d2fce10adc3949ba59abbe56e057f20f883e5cce';
        if ($data == $default) {
            return false;
        } else if ($data == '') {
            return false;
        } else {
            return true;
        }
    }

    public function subAgentPhoneAlreadyRegisterd($phone) {
        $model = M('users');
        $data = $model->where(array("mobile" => "$phone", "user_type" => $this->subAgentRoleId))->count();
        return $data;
    }

    public function userLoginInUse($user_login) {
        return M('users')->where(array("user_login" => $user_login))->count();
    }

    public function createSubAgent($user_login, $phone, $pass, $name, $email) {
        if ($this->userLoginInUse($user_login)) {
            return "此用户名已经使用过";
        }
        //agent不能把自己的手机号注册成subagent
//        $agent_mobile=$this->getField("mobile");
//        if($agent_mobile==$phone){
//            return "不能用自己的手机号注册下级代理";
//        }
//        if($this->subAgentPhoneAlreadyRegisterd($phone)){
//            return "此手机号已经注册过";
//        }
        $sp_pass = sp_password($pass);
        $pay_pwd_en = pay_password($pass);
        $this->model->add(
            array(
//            "mobile"=>$phone,
"user_login"    => $user_login,
"user_nicename" => $name,
"user_pass"     => $sp_pass,
"pay_pwd"       => $pay_pwd_en,
//            "user_email"=>$email,
"user_type"     => $this->subAgentRoleId,
"user_status"   => "2",
"ownerid"       => $this->agentId,
"create_time"   => date("Y-m-d H:i:s", time())
            )
        );
        return "1";
    }

    public function has_user($id) {
        return $this->model->where(array("id" => $id))->count();
    }

    public function phoneInUseExceptSelf($subid, $phone) {
//        $info=$this->getSubAgentInfo($subid);
//        $mobile=$info['mobile'];
//        if($mobile==$phone){
//            return true;
//        }
        return $this->model->where("(mobile = $phone) AND (id!=$subid)")->find();
    }

    public function editSubAgentInfo($subid, $phone, $name, $email) {
        if (!$this->has_user($subid)) {
            return "此下级代理不存在";
        }
        if ($this->phoneInUseExceptSelf($subid, $phone)) {
            return "此手机号已经被其他下级代理使用，请更换号码";
        }
        $this->model->where(array("id" => $subid, "user_type" => $this->subAgentRoleId))->save(
            array(
                "mobile"        => $phone,
                "user_login"    => $phone,
                "user_nicename" => $name,
                "user_email"    => $email
            )
        );
        return "1";
    }

    public function getMySubAgents($where_extra = array(), $start = 0, $limit = 0) {
        return $this->model
            ->where(array("ownerid" => $this->agentId, "user_type" => $this->subAgentRoleId))
            ->where($where_extra)
            ->limit($start, $limit)
            ->select();
    }

    public function getMeAndMySubIdsTxt() {
        $ids = $this->getMySubAgentsIds();
        $ids[] = $this->agentId;
        return join(',', $ids);
    }

    public function getMeAndMySubIds($agent_id) {
        $ids = $this->getMySubAgentsIds();
        $ids[] = $agent_id;
        return $ids;
    }

    public function GetMeAndMySubAgentIDs() {
        $subids = $this->getMySubAgents();
        $result = array();
        $result[] = $this->agentId;
        foreach ($subids as $k => $v) {
            $result[] = $v['id'];
        }
        return $result;
    }

    public function getMySubAgentsIds() {
        $data = $this->getMySubAgents();
        $r = array();
        foreach ($data as $item) {
            $r[] = $item['id'];
        }
        return $r;
    }

    public function SubBelongToMe($subid) {
        return $this->model->where(
            array("ownerid" => $this->agentId, "id" => $subid, "user_type" => $this->subAgentRoleId)
        )->count();
    }

    public function mySubSelectList($current = -1) {
        $txt = "<select name='subid'>";
        $txt .= "<option value='-1'>选择下级代理</option>";
        $subs = $this->getMySubAgents();
        foreach ($subs as $sub) {
            $id = $sub['id'];
            $name = $sub['user_nicename'];
            if (($current != -1) && ($current == $id)) {
                $txt .= "<option value='$id' selected='selected'>$name</option>";
            } else {
                $txt .= "<option value='$id'>$name</option>";
            }
        }
        $txt .= "</select>";
        return $txt;
    }

    public function passedInfoCheck() {
        $status = $this->getField("user_status");
        if ($status == 2) {
            return true;
        }
    }

    //获取可以转包给某个下级代理的应用列表，对于自己申请的游戏，审核通过后才能转包给下级代理
    //如果已经申请转包，不能再次申请转包
    public function getSubleList($subid) {
        //不能转包给自己
        if ($subid == $this->agentId) {
            return array();
        }
        $table = C("DB_PREFIX")."agent_game";
        $game_table = C("DB_PREFIX")."game";
        $where = array();
        $where['ag.agent_id'] = $this->agentId;
        $where['g.is_delete'] = 2;
        $where['_string'] = "(ag.app_id NOT IN (SELECT app_id FROM $table WHERE agent_id=$subid))";
        return M('agent_game')
            ->field("g.name,g.icon,g.id,ag.update_time")
            ->alias('ag')
            ->where($where)
            ->join("LEFT JOIN $game_table g ON g.id=ag.app_id")
            ->select();
    }

    public function getSubApplyList($subid) {
        $game_table = C("DB_PREFIX")."game";
        $where = array();
        $where['ag.agent_id'] = $subid;
        return M('agent_game')
            ->field("g.name,g.icon,g.id,ag.update_time")
            ->alias('ag')
            ->where($where)
            ->join("LEFT JOIN $game_table g ON g.id=ag.app_id")
            ->select();
    }

    //传入的参数是app id的列表
    public function addAgentGame($list, $agentid = 0) {
        if ($agentid == 0) {
            $agent_id = $this->agentId;
        } else {
            $agent_id = $agentid;
        }
        $model = M('agent_game');
        foreach ($list as $game) {
            $where = array(
                "agent_id" => $agent_id,
                "app_id"   => $game
            );
            $initial = M('game')->where(array("id" => $game))->getField("initial");
            $hs_benefit_obj = new \Huosdk\Benefit();
            $agentgame = $initial."_".$agent_id;
            $data = array(
                "agent_id"    => $agent_id,
                "app_id"      => $game,
                "agentgame"   => $agentgame,
                "create_time" => time(),
                "update_time" => time(),
                "status"      => 1,
                "flag"        => 8
            );
            if (!$model->where($where)->find()) {
                $ag_id = $model->add($data);
                $hs_benefit_obj->set_agentgame_benefit_by_app_benefit($ag_id, $game, $agent_id);
            }
        }
    }

    public function addSubAgentGame($subid, $list) {
        $this->addAgentGame($list, $subid);
    }

    public function getSubAgentInfo($subid) {
        $data = $this->model->where(array("id" => $subid, "user_type" => $this->subAgentRoleId))->find();
        return $data;
    }

    public function delSubAgent($subid) {
        $exists = $this->getSubAgentInfo($subid);
        if (!$exists) {
            return "下级代理不存在";
        }
        $data = $this->model->where(array("id" => $subid, "user_type" => $this->subAgentRoleId))->delete();
        if ($data) {
            return "1";
        } else {
            return "删除失败";
        }
    }

    public function disableSubAgent($subid) {
        $exists = $this->getSubAgentInfo($subid);
        if (!$exists) {
            return "下级代理不存在";
        }
        $data = $this->model->where(array("id" => $subid, "user_type" => $this->subAgentRoleId))->setField(
            "user_status", "3"
        );
        if ($data) {
            return "1";
        } else {
            return "禁用失败";
        }
    }

    public function enableSubAgent($subid) {
        $exists = $this->getSubAgentInfo($subid);
        if (!$exists) {
            return "下级代理不存在";
        }
        $data = $this->model->where(array("id" => $subid, "user_type" => $this->subAgentRoleId))->setField(
            "user_status", "2"
        );
        if ($data) {
            return "1";
        } else {
            return "启用失败";
        }
    }

    public function getCurrentBalance() {
        return M('agent_ext')->where(array("agent_id" => $this->agentId))->getField("balance");
    }

    private function setField($k, $v) {
        return $this->model->where($this->where)->setField($k, $v);
    }

    private function getField($k) {
        return $this->model->where($this->where)->getField("$k");
    }

    public function getAgentInfo() {
        $result = M('users')->where(array("id" => $this->agentId))->find();
        $ownerid = $result['ownerid'];
        $result['belong_to_agent_id'] = $ownerid;
        $owner_data = M('users')->where(array("id" => $ownerid))->find();
        $result['belong_to_agent_name'] = $owner_data['user_login'];
        return $result;
    }
}

