<?php
namespace Huosdk;
class Agent2 {
    private $roleid;
    private $model;
    private $subAgentRoleId;

    public function __construct() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->agentRoldId;
        $subagent_roleid = $obj->subAgentRoldId;
        $this->model = M('users');
        $this->roleid = $agent_roleid;
        $this->subAgentRoleId = $subagent_roleid;
    }

    public function getAgentLevelById($agent_id) {
        $ut = $this->model->where(array("id" => $agent_id))->getField("user_type");
        $level = '';
        if ($ut == '6') {
            $level = '一级代理';
        } else if ($ut == '7') {
            $level = '二级代理';
        }
        return $level;
    }

    public function get_info($agent_id) {
        $item = $this->model
            ->field(
                "u.user_nicename as agent_name,u.user_login,u.qq,u.mobile,"
                ."u2.user_login as parent_agent_name,u.create_time,u.user_type,"
                ."u.user_status,u.id,am.link_man,u.ownerid"
            )
            ->alias("u")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid")
            ->join("LEFT JOIN ".C("DB_PREFIX")."agent_man am ON am.agent_id=u.id")
            ->where(array("u.id" => $agent_id))
            ->find();
        if ($item['user_type'] == $this->roleid) {
            $item['user_type_txt'] = '一级代理';
        } else if ($item['user_type'] == $this->subAgentRoleId) {
            $item['user_type_txt'] = '二级代理';
        }
        if ($item['user_status'] == '1') {
            $item['user_status_txt'] = '未验证';
        } else if ($item['user_status'] == '2') {
            $item['user_status_txt'] = '正常';
        } else if ($item['user_status'] == '3') {
            $item['user_status_txt'] = '禁用';
        }
        return $item;
    }

    public function all_agent_list($where = array(), $start = 0, $limit = 0) {
        $users = $this->model
            ->field("u.*,u2.user_nicename as parent_agent_name,u.user_nicename as agent_name")
            ->alias('u')
            ->where("u.user_type= $this->roleid or u.user_type= $this->subAgentRoleId ")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid")
            ->order("u.id desc")
            ->limit($start, $limit)
            ->select();
//        $result = array();
        foreach ($users as $k => $item) {
//            $result[] = $this->get_info($v['id']);
            if ($item['user_type'] == $this->roleid) {
                $users[$k]['user_type_txt'] = '一级代理';
//                $users[$k]['parent_agent_name'] = $item['agent_name'];
            } else if ($item['user_type'] == $this->subAgentRoleId) {
                $users[$k]['user_type_txt'] = '二级代理';
            }
            if ($item['user_status'] == '1') {
                $users[$k]['user_status_txt'] = '未验证';
            } else if ($item['user_status'] == '2') {
                $users[$k]['user_status_txt'] = '正常';
            } else if ($item['user_status'] == '3') {
                $users[$k]['user_status_txt'] = '禁用';
            }
        }
        return $users;
    }
}
