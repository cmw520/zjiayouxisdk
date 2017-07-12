<?php
namespace Huosdk\Data;
class FormatRecords {
    public static function agent_level(&$items) {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        foreach ($items as $k => $v) {
            $type = $v['user_type'];
            if ($type == $agent_roleid) {
                $items[$k]['agent_level'] = '一级渠道';
            } else if ($type == $subagent_roleid) {
                $items[$k]['agent_level'] = '二级渠道';
            }
        }
    }

    public static function member_status_txt($status) {
        $data = array(
            "1" => "试玩",
            "2" => "正常",
            "3" => "冻结"
        );
        return $data["$status"];
    }

    public static function package_generate_status(&$items) {
        $data = array(
            "1" => "<span class='label label-default'>待出包</label>",
            "2" => "<span class='label label-success'>已出包</label>",
            "3" => "<span class='label label-danger'>出包失败</label>"
        );
        foreach ($items as $key => $value) {
            $items[$key]['package_status_txt'] = $data[$value['status']];
        }
    }

    /**
     * 玩家充值的时候，来源于哪些地方
     * 对应于gm_charge中的flag字段
     *
     * @param string $items
     *
     * @author 严旭
     */
    public static function mem_charge_from(&$items) {
        $data = array(
            "1" => "官网充值",
            "2" => "浮点充值",
            "3" => "sdk充值游戏",
            "4" => "app充值游戏",
            "5" => "代理发放",
            "6" => "7881充值",
            "7" => "SDK充值返利",
            "8" => "官方发放",
        );
        foreach ($items as $key => $value) {
            $items[$key]["from"] = $data[$value['flag']];
        }
    }

    public static function pay_status(&$items) {
        $data = array(
            "1" => "待支付",
            "2" => "支付完成",
            "3" => "支付失败"
        );
        foreach ($items as $key => $value) {
            $items[$key]["status_txt"] = $data[$value['status']];
        }
    }

    public static function payway(&$items) {
        $data = M('payway')->getField("payname,realname", true);
        foreach ($items as $key => $value) {
            $items[$key]["payway_txt"] = $data[$value['payway']];
        }
    }

    public function settle_status(&$items) {
        $data = array(
            "1" => "待运营审核",
            "2" => "待财务审核",
            "3" => "已结算",
            "4" => "审核不通过"
        );
        foreach ($items as $key => $value) {
            $items[$key]['settle_status_txt'] = $data[$value['status']];
        }
    }
}

