<?php
namespace Huosdk\Data;
class ItemsFields {
    public static function agent_level(&$items) {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        foreach ($items as $k => $v) {
            $type = $v['user_type'];
            if ($type == $agent_roleid) {
                $items[$k]['agent_level'] = '一级代理';
            } else if ($type == $subagent_roleid) {
                $items[$k]['agent_level'] = '二级代理';
            }
        }
    }

    public static function member_status_txt($status) {
        $data = array("1" => "试玩", "2" => "正常", "3" => "冻结");
        return $data["$status"];
    }

    public static function package_generate_status(&$items) {
        $data = array(
            "1" => "<span class='label label-default'>待出包</label>",
            "2" => "<span class='label label-success'>已出包</label>",
            "3" => "<span class='label label-danger'>出包失败</label>");
        foreach ($items as $key => $value) {
            $items[$key]['package_status_txt'] = $data[$value['status']];
        }
    }
}

