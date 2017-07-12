<?php
namespace Huosdk\Data;
class Values {
    public static function getAgentRoleId() {
        return M('role')->where(array("name" => "渠道专员"))->getField("id");
    }

    public static function getSubAgentRoleId() {
        return M('role')->where(array("name" => "公会渠道"))->getField("id");
    }
}

