<?php
namespace Huosdk;
class SubAgent {
    private $roleid;
    private $model;
    private $where;
    public  $subId;
    private $subAgentRoleId;

    public function __construct($subId) {
        $this->model = M('users');
        $this->roleid = 6;
        $this->subAgentRoleId = 7;
        $this->subId = $subId;
        $this->where = array("id" => $subId, "user_type" => $this->subAgentRoleId);
    }

    public function test() {
        echo "hi";
    }

    public function getMyGameList() {
        $game_table = C("DB_PREFIX")."game";
        $items = M('agent_game')
            ->field("ag.app_id,ag.id agid,g.name as gamename,g.icon,g.id,ag.update_time,g.initial,ag.agentgame,agr.*")
            ->alias('ag')
            ->where(array("ag.agent_id" => $this->subId))
            ->join("LEFT JOIN $game_table g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id")
            ->select();
        foreach ($items as $key => $value) {
            $fp = DOWNSITE.$value['initial']."/".$value['agentgame'].".apk";
            $items[$key]['app_fp'] = $fp;
        }
        return $items;
    }

    public function phoneInUseExceptSelf($phone) {
        return $this->model->where("(mobile = $phone) AND (id != $this->subId )")->find();
    }

    public function getMyPhone() {
        return M('users')->where(array("id" => $this->subId))->getField("mobile");
    }

    public function getMyEmail() {
        return M('users')->where(array("id" => $this->subId))->getField("user_email");
    }

    public function setPhone($phone) {
        if ($_SESSION['phoneVerifyCodeMatch'] == false) {
            return "需要先验证您的手机号码";
        }
        $old_phone = $this->getMyPhone();
        if ($old_phone == $phone) {
            return "新手机号不能跟旧手机号一样";
        }
        if ($this->phoneInUseExceptSelf($phone)) {
            return "此手机号已经被使用，请尝试其他手机号";
        }
//        M('users')->where(array("id"=>$this->subId))->save(array("mobile"=>$phone,"user_login"=>$phone));
        M('users')->where(array("id" => $this->subId))->save(array("mobile" => $phone));
        return "1";
    }

    public function setEmail($email) {
        $old_email = $this->getMyEmail();
        if ($old_email == $email) {
            return "新旧邮箱不能一样";
        }
        M('users')->where(array("id" => $this->subId))->setField("user_email", $email);
        return "1";
    }

    public function getMyMembers() {
        $model = M("members");
        $where = "m.agent_id=$this->subId";
//        $count=$model->alias('m')
//                ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id" )
//                ->where($where)->count();
//        
//        $Page= new \Think\Page($count,10);                            
//        $show = $Page->show();// 分页显示输出   
        $members = $model
            ->field("m.*,g.name as gamename,u.user_type,u.user_nicename")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->where($where)
            ->order("m.reg_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach ($members as $key => $member) {
            $type = $members[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $members[$key]['user_type'] = '代理';
            } else if ($type == $this->subagent_roleid) {
                $members[$key]['user_type'] = '下级代理';
            }
        }
        return $members;
    }
}
