<?php
namespace Huosdk\UI;
class Filter {
    public function member_account_status($current = 0) {
        $txt = "<select name='status'>";
        $data = array(
            "0" => "选择状态",
            "1" => "试玩",
            "2" => "正常",
            "3" => "冻结"
        );
        foreach ($data as $k => $v) {
            $select = '';
            if ($current == $k) {
                $select = ' selected ';
            }
            $txt .= "<option value='$k' $select >$v</option>";
        }
        $txt .= "</select>";

        return $txt;
    }

    public function select_common($data, $name, $current, $select2 = '') {
        $txt = "<select class='select_2' name='$name' select2='$select2' >";
        $txt .= "<option value='0' >请选择</option>";
        foreach ($data as $k => $v) {
            $select = '';
            if ($current == $k) {
                $select = ' selected ';
            }
            $txt .= "<option value='$k' $select >$v</option>";
        }
        $txt .= "</select>";

        return $txt;
    }

    public function app_select() {
        $where = array();
        $where['is_delete'] = 2;
        $apps = M('game')->where($where)->getField("id,name", true);
        $app_select = $this->select_common($apps, "app_id", $_GET['app_id']);

        return $app_select;
    }

    public function memname_input() {
        $v = trim($_GET['mem_name']);
        $txt
            = <<< EOT
       <input type=text name="mem_name" value="$v" placeholder='请输入玩家帐号' />            
EOT;

        return $txt;
    }

    public function app_select2() {
        $where = array();
        $where['is_delete'] = 2;
        $apps = M('game')->where($where)->getField("id,name", true);
        $app_select = $this->select_common($apps, "app_id", $_GET['app_id'], "false");

        return $app_select;
    }

    public function agent_select($incagr = true, $incsagr = true) {
        $agrid = \Huosdk\Data\Values::getAgentRoleId();
        $sagrid = \Huosdk\Data\Values::getSubAgentRoleId();
        $where = array();
        if (true != $incagr) {
            $where['_string'] = "user_type= $sagrid ";
        } else if (true != $incsagr) {
            $where['_string'] = "user_type= $agrid ";
        }  else if (true == $incsagr && true == $incagr) {
            $where['_string'] = "user_type = $agrid or user_type= $sagrid ";
        }

        $data = M('users')->where($where)->getField("id,user_nicename,user_login", ":");
        $select = $this->select_common($data, "agent_id", $_GET['agent_id']);

        return $select;
    }

    public function only_agent_select() {
        $agrid = \Huosdk\Data\Values::getAgentRoleId();
        $sagrid = \Huosdk\Data\Values::getSubAgentRoleId();
        $where = array();
        $where['_string'] = "user_type = $agrid ";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "agent_id", $_GET['agent_id']);

        return $select;
    }

    public function only_subagent_select() {
        $agrid = \Huosdk\Data\Values::getAgentRoleId();
        $sagrid = \Huosdk\Data\Values::getSubAgentRoleId();
        $where = array();
        $where['_string'] = "user_type= $sagrid ";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "agent_id", $_GET['agent_id']);

        return $select;
    }

    public function parent_agent_select() {
        $agrid = \Huosdk\Data\Values::getAgentRoleId();
        $where = array();
        $where['_string'] = " user_type = $agrid ";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "parent_agent_id", $_GET['parent_agent_id']);

        return $select;
    }

    public function agent_select_Level_one() {
        $where = array();
        $where['_string'] = "user_type =6";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "agent_id", $_GET['agent_id']);

        return $select;
    }

    public function agent_select_Level_two() {
        $where = array();
        $where['_string'] = "user_type= 7";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "agent_id", $_GET['agent_id']);

        return $select;
    }

    public function agent_select2() {
        $hs_account_obj = new \Huosdk\Account();
        $agent_roleid = $hs_account_obj->agentRoldId;
        $subagent_roleid = $hs_account_obj->subAgentRoldId;
        $where = array();
        $where['_string'] = "user_type = $agent_roleid or user_type= $subagent_roleid ";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        $select = $this->select_common($data, "agent_id", "");

        return $select;
    }

    public function member_select() {
        $where = array();
        $data = M('members')->where($where)->getField("id,username", true);
        $select = $this->select_common($data, "mem_id", $_GET['mem_id']);

        return $select;
    }

    public function time_choose($start_time = '', $end_time = '') {
        if (!$start_time) {
            $start_time = $_GET['start_time'];
        }
        if (!$end_time) {
            $end_time = $_GET['end_time'];
        }
        $txt = '<input select2="false" type="text" name="start_time" class="js-date" value="'.$start_time.'"
                    placeholder="开始时间..." style="width: 110px;" autocomplete="off">-
             <input select2="false" type="text" class="js-date" name="end_time" value="'.$end_time.'"
                    placeholder="结束时间..." style="width: 110px;" autocomplete="off">';

        return $txt;
    }

    public function payway_select() {
        $data = array(
            "1" => "自然",
            "2" => "非自然",
            "3" => "支付宝",
            "4" => "微信",
            "5" => "网银",
            "6" => "平台币",
            "7" => "游戏币"
        );
        $select = $this->select_common($data, "payway", $_GET['payway']);

        return $select;
    }

    public function payway_select2() {
        $data = M('payway')->getField("payname,realname", true);
        $select = $this->select_common($data, "payway", $_GET['payway']);

        return $select;
    }

    /**
     * 自然充值，非自然充值
     *
     * @return type
     */
    public function payway_select3() {
        $realdata = array();
        $data = M('payway')->where(
            array(
                "status" => "2"
            )
        )->getField("payname,realname", true);
        $realdata['normal'] = "自然充值";
        $realdata['notnormal'] = "非自然充值";
        $realdata = array_merge($realdata, $data);
        $select = $this->select_common($realdata, "payway", $_GET['payway']);

        return $select;
    }

    public function agent_level_select() {
        $data = array(
            "6" => "一级",
            "7" => "二级"
        );
        $select = $this->select_common($data, "user_type", $_GET['user_type']);

        return $select;
    }

    public function pay_from() {
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
        $select = $this->select_common($data, "pay_from", $_GET['pay_from']);

        return $select;
    }

    public function benefit_type_select() {
        $data = array(
            "1" => "折扣",
            "2" => "返利"
        );
        $select = $this->select_common($data, "benefit_type", $_GET['benefit_type']);

        return $select;
    }

    public function promote_status_select() {
        $data = array(
            "1" => "未上架",
            "2" => "已上架"
        );
        $select = $this->select_common($data, "promote_status", $_GET['promote_status']);

        return $select;
    }

    public function order_id_input() {
        $v = $_GET['order_id'];
        $txt
            = <<< EOT
       <input type=text name="order_id" value="$v" placeholder='请输入订单号' />            
EOT;

        return $txt;
    }

    public function parent_agent_select_with_official() {
        $hs_account_obj = new \Huosdk\Account();
        $agent_roleid = $hs_account_obj->agentRoldId;
        $subagent_roleid = $hs_account_obj->subAgentRoldId;
        $where = array();
        $where['_string'] = "user_type = $agent_roleid or user_type= $subagent_roleid ";
        $real_data = array();
        $real_data["官方渠道"] = "官方渠道";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        // $real_data=array_merge($real_data,$data);
        // $real_data=$real_data;
        // array_push($data,array("官方渠道"=>"官方渠道"));
        // $data["官方渠道"]="官方渠道";
        $select = $this->select_common($data, "parent_agent_id", $_GET['parent_agent_id']);

        return $select;
    }

    public function agent_select_with_official() {
        $hs_account_obj = new \Huosdk\Account();
        $agent_roleid = $hs_account_obj->agentRoldId;
        $subagent_roleid = $hs_account_obj->subAgentRoldId;
        $where = array();
        $where['_string'] = "user_type = $agent_roleid or user_type= $subagent_roleid ";
        $real_data = array();
        $real_data['1'] = "官包";
        $data = M('users')->where($where)->getField("id,user_nicename", true);
        if (!empty($data)) {
            $real_data = $real_data + $data;
        }
        $select = $this->select_common($real_data, "agent_id", $_GET['agent_id']);

        return $select;
    }
}

