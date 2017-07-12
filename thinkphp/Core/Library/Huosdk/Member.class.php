<?php
namespace Huosdk;
class Member {
    private $mid;

    public function __construct() {
    }

    public function get_mem_agent_name() {
        $data = M('members')
            ->field("u.user_login as agent_name")
            ->alias("m")
            ->where(array("m.id" => $this->mid))
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=m.agent_id")
            ->find();
        return $data['agent_name'];
    }

    public function setMid($memberid) {
        $this->mid = $memberid;
    }

    public function NamePwdMatch($name, $pwd) {
        $en_pwd = member_password($pwd);
        $data = M('members')->where(array("username" => $name, "password" => $en_pwd))->find();
        return $data;
    }

    public function PwdMatch($pwd) {
        $en_pwd = member_password($pwd);
        $data = M('members')->where(array("id" => $this->mid, "password" => $en_pwd))->find();
        return $data;
    }

    public function setPwd($pwd) {
        $en_pwd = member_password($pwd);
        $data = M('members')->where(array("id" => $this->mid))->setField("password", $en_pwd);
        return $data;
    }

    public function mem_id_exists($mem_id) {
        return M('members')->where(array("id" => $mem_id))->find();
    }

    public static function PhoneInUse($phone) {
        return M('members')->where(array("mobile" => $phone))->find();
    }
}

