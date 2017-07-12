<?php
namespace Huosdk;
class Benefit {
    private $game_model, $agent_game_model;

    public function __construct() {
        $this->game_model = M('game');
        $this->agent_game_model = M('agent_game');
    }

    public function get_AgentApp_benefit_info($ag_id) {
        $where = array(
            "ag.id" => $ag_id
        );
        $info = $this->agent_game_model->field("g.name,ag.app_id,ag.id,agr.*")->alias("ag")->join(
            "LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id"
        )->join(
            "LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id"
        )->where($where)->find();
        $this->type_txt_single($info);
        $this->benefit_info_output_item($info);

        return $info;
    }

    public function benefitUndefinedAppList() {
        $where = "(gr.benefit_type IS NULL) AND (g.is_delete = 2)";
        $items = $this->game_model->alias("g")->where($where)->join(
            "LEFT JOIN ".C('DB_PREFIX')."game_rate gr ON gr.app_id = g.id"
        )->select();
        $this->type_txt($items);

        return $items;
    }

    public function benefitDefinedAppList() {
        $where = "gr.benefit_type IS NOT NULL ";
        $items = $this->game_model->field("g.id,g.name,gr.*")->alias("g")->where($where)->join(
            "LEFT JOIN ".C('DB_PREFIX')."game_rate gr ON gr.app_id = g.id"
        )->order("gr.update_time desc")->select();
        $this->type_txt($items);
        foreach ($items as $k => $v) {
            if ($v['benefit_type'] == 1) {
                $items[$k]['benefit_first'] = $v['first_mem_rate'];
                $items[$k]['benefit_refill'] = $v['mem_rate'];
            } else if ($v['benefit_type'] == 2) {
                $items[$k]['benefit_first'] = $v['first_mem_rebate'];
                $items[$k]['benefit_refill'] = $v['mem_rebate'];
            }
        }
        return $items;
    }

    private function type_txt(&$items) {
        foreach ($items as $k => $item) {
            $txt = '';
            if ($item['benefit_type'] == 1) {
                $txt = '折扣';
            } else if ($item['benefit_type'] == 2) {
                $txt = '返利';
            } else {
                $txt = '无优惠';
            }
            $items[$k]['benefit_type_txt'] = $txt;
        }
    }

    private function type_txt_single(&$item) {
        $txt = '';
        if ($item['benefit_type'] == 1) {
            $txt = '折扣';
        } else if ($item['benefit_type'] == 2) {
            $txt = '返利';
        }
        $item['benefit_type_txt'] = $txt;
    }

    public function app_benefit_info_exist($app_id) {
        return M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->find();
    }

    public function addAppBenefit($app_id, $data) {
        if ($this->app_benefit_info_exist($app_id)) {
            $data['create_time'] = time();
            $data['update_time'] = time();
            M('game_rate')->where(
                array(
                    "app_id" => $app_id
                )
            )->save($data);
        } else {
            $data['app_id'] = $app_id;
            $data['create_time'] = time();
            $data['update_time'] = time();
            // $data['mem_rate']=1;
            // $data['first_mem_rate']=1;
            // $data['mem_rebate']=0;
            // $data['first_mem_rebate']=0;
            // $data['benefit_type']=1;
            M('game_rate')->add($data);
        }
    }

    public function setAppBenefit($app_id, $data) {
        $data['create_time'] = time();
        $data['update_time'] = time();
        M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->save($data);
    }

    public function setAgentGameBenefit($ag_id, $data) {
        $add_data = array();
        if ($data['benefit_type'] == "1") {
            $add_data['first_mem_rate'] = $data['benefit_first'];
            $add_data['mem_rate'] = $data['benefit_refill'];
        } else if ($data['benefit_type'] == "2") {
            $add_data['first_mem_rebate'] = $data['benefit_first'];
            $add_data['mem_rebate'] = $data['benefit_refill'];
        } else {
            $add_data['first_mem_rate'] = 1;
            $add_data['mem_rate'] = 1;
            $add_data['first_mem_rebate'] = 0;
            $add_data['mem_rebate'] = 0;
        }
        $add_data['agent_rate'] = $data['agent_rate'];
        $add_data['update_time'] = time();
        M('agent_game_rate')->where(
            array(
                "ag_id" => $ag_id
            )
        )->save($add_data);
    }

    public function rate_filter($name) {
        if (isset($_POST[$name]) && is_numeric($_POST[$name])) {
            return true;
        }
        return false;
    }

    public function benefilt_value_filter() {
        if ($this->rate_filter("agent_rate")) {
            return true;
        } else {
            return false;
        }
    }

    public function getAgentInfo($agentId) {
        $result = M('users')->where(
            array(
                "id" => $agentId
            )
        )->find();
        $ownerid = $result['ownerid'];
        $result['belong_to_agent_id'] = $ownerid;
        $owner_data = M('users')->where(
            array(
                "id" => $ownerid
            )
        )->find();
        $result['belong_to_agent_name'] = $owner_data['user_nicename'];
        return $result;
    }

    public function AgentGameListCnt($where_extra = array()) {
        $cnt = $items = M('agent_game')->alias('ag')
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id")
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid")
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id")
                                       ->where("(agr.benefit_type IS NOT NULL)")
                                       ->where($where_extra)->count();
        return $cnt;
    }
    public function AgentGameList($where_extra = array(), $start = 0, $limit = 0) {
        $items = M('agent_game')->field(
            "g.name as game_name,ag.*,u.user_nicename as agent_name,agr.*,u2.user_nicename as parent_agent_name"
        )->alias(
            'ag'
        )->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")->join(
            "LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id"
        )->join(
            "LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=u.ownerid"
        )->join(
            "LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id"
        )->where(
            "(agr.benefit_type IS NOT NULL)"
        )->where($where_extra)->order("ag.id desc")->limit($start, $limit)->select();
        $this->type_txt($items);
        foreach ($items as $k => $v) {
            $data = $this->getAgentInfo($v['agent_id']);
            $items[$k]['belong_to_agent_id'] = $data['belong_to_agent_id'];
            // $items[$k]['belong_to_agent_name']=$data['belong_to_agent_name'];
        }
        $this->benefit_info_output($items);
        return $items;
    }

    public function AgentGameList_single($agent_id, $start = 0, $limit = 0) {
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->getMySubAgentsIds();
        array_push($ids, $agent_id);
        $ids_txt = join(",", $ids);
        $where = array();
        $where['_string'] = "ag.agent_id IN ($ids_txt)";
        $where['g.is_delete'] = 2;
        $items = M('agent_game')->field("g.name as game_name,ag.id,u.user_login as agent_name,u.user_type,agr.*")
                                ->alias(
                                    'ag'
                                )->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")->join(
                "LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id"
            )->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id"
            )->where($where)->order(
                "ag.id desc"
            )->limit($start, $limit)->select();
        $this->type_txt($items);
        foreach ($items as $k => $v) {
            $data = $this->getAgentInfo($v['agent_id']);
            $items[$k]['belong_to_agent_id'] = $data['belong_to_agent_id'];
            $items[$k]['belong_to_agent_name'] = $data['belong_to_agent_name'];
        }
        $this->benefit_info_output($items);
        return $items;
    }

    public function AgentGameList_sub($agent_id, $where_extra = array(), $start = 0, $limit = 0) {
        $hs_agent_obj = new \Huosdk\Agent($agent_id);
        $ids = $hs_agent_obj->getMySubAgentsIds();
        $ids_txt = join(",", $ids);
        $where = array();
        if (!$ids_txt) {
            return array();
        }
        $where['_string'] = "ag.agent_id IN ($ids_txt)";
        $where['g.is_delete'] = 2;
        $where['g.promote_switch'] = 2;
        $items = M('agent_game')->field(
            "g.name as game_name,ag.id,u.user_nicename as agent_name,u.user_type,agr.*,"
            ."g.agent_rate as platform_rate,ag.agent_id as sub_agent_id"
        )
                                ->alias('ag')
                                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")
                                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id")
                                ->join("LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id")
                                ->where($where)
                                ->where($where_extra)
                                ->order("ag.id desc")
                                ->limit($start, $limit)
                                ->select();
        $this->type_txt($items);
        foreach ($items as $k => $v) {
            $data = $this->getAgentInfo($v['agent_id']);
            $items[$k]['belong_to_agent_id'] = $data['belong_to_agent_id'];
            $items[$k]['belong_to_agent_name'] = $data['belong_to_agent_name'];
            $items[$k]['parent_agent_rate'] = $this->getParentAgentGameRate($v['sub_agent_id'], $v['app_id']);
            if (empty($items[$k]['parent_agent_rate'])) {
                unset($items[$k]);
            }
        }
        $this->benefit_info_output($items);
        return $items;
    }

    public function getParentAgentGameRate($sub_agent_id, $app_id) {
        $parent_agentid = M('users')->where(
            array(
                "id" => $sub_agent_id
            )
        )->getField("ownerid");
        $parent_agent_rate = M('agent_game_rate')->where(
            array(
                "agent_id" => $parent_agentid,
                "app_id"   => $app_id
            )
        )->getField("agent_rate");
        return $parent_agent_rate;
    }

    public function AgentGameList_self($agent_id, $where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $where['_string'] = "ag.agent_id = $agent_id";
        $where['g.is_delete'] = 2;
        $where['g.promote_switch'] = 2;
        $items = M('agent_game')->field(
            "g.name as game_name,ag.id,u.user_nicename as agent_name,u.user_type,agr.*,g.agent_rate as platform_rate"
        )->alias(
            'ag'
        )->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")->join(
            "LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id"
        )->join(
            "LEFT JOIN ".C("DB_PREFIX")."agent_game_rate agr ON agr.ag_id=ag.id"
        )->where($where)->where(
            $where_extra
        )->order("ag.id desc")->limit($start, $limit)->select();
        $this->type_txt($items);
        foreach ($items as $k => $v) {
            $data = $this->getAgentInfo($v['agent_id']);
            $items[$k]['belong_to_agent_id'] = $data['belong_to_agent_id'];
            $items[$k]['belong_to_agent_name'] = $data['belong_to_agent_name'];
        }
        $this->benefit_info_output($items);
        return $items;
    }

    public function get_app_default_agent_rate($app_id) {
        $default_agent_rate = M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->getField("agent_rate");
        return $default_agent_rate;
    }

    public function get_app_default_agentrate_by_agid($ag_id) {
        $data = M('agent_game')->where(
            array(
                "id" => $ag_id
            )
        )->find();
        $app_id = $data['app_id'];
        return M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->getField("agent_rate");
    }

    public function set_agent_game_agent_rate($agid, $value) {
        return M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->setField("agent_rate", $value);
    }

    public function get_agent_game_agent_rate($agid) {
        return M('agent_game_rate')->where(
            array(
                "ag_id" => $agid
            )
        )->getField("agent_rate");
    }

    public function get_agent_game_agent_rate_V2($agent_id, $app_id) {
        return M('agent_game_rate')->where(
            array(
                "agent_id" => $agent_id,
                "app_id"   => $app_id
            )
        )->getField("agent_rate");
    }

    public function set_app_agent_rate($app_id, $value) {
        return M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->setField("agent_rate", $value);
    }

    public function set_app_agentgame_all_agentrate($app_id, $value) {
        return M('agent_game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->setField("agent_rate", $value);
    }

    public function set_app_agentgame_agentrate($ag_id, $value) {
        return M('agent_game_rate')->where(
            array(
                "id" => $ag_id
            )
        )->setField("agent_rate", $value);
    }

    public function get_agentgame_agentrate_info($agent_id, $app_id) {
        $data = M('agent_game_rate')->where(
            array(
                "agent_id" => $agent_id,
                "app_id"   => $app_id
            )
        )->find();
        return $data;
    }

    public function get_app_benefit_info($app_id) {
        $result = M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->find();
        if (empty($result)) {
            $result['agent_rate'] = 1;
            $result['benefit_type'] = 0;
            $result['mem_rate'] = 1;
            $result['first_mem_rate'] = 1;
            $result['mem_rebate'] = 0;
            $result['first_mem_rebate'] = 0;
        }
        $this->benefit_info_output_item($result);
        return $result;
    }

    public function benefit_info_output(&$data) {
        foreach ($data as $key => $value) {
            $data[$key]['agent_rate_txt'] = floatval($value['agent_rate']).'  ('.floatval($value['agent_rate'] * 10)
                                            .'折)';
            $data[$key]['agent_rate'] = floatval($value['agent_rate']);
            if ($value['benefit_type'] == 1) {
                $data[$key]['benefit_first_txt'] = floatval($value['first_mem_rate']).'  ('.floatval(
                        $value['first_mem_rate'] * 10
                    ).'折)';
                $data[$key]['benefit_refill_txt'] = floatval($value['mem_rate']).'  ('.floatval($value['mem_rate'] * 10)
                                                    .'折)';
                $data[$key]['benefit_first'] = floatval($value['first_mem_rate']);
                $data[$key]['benefit_first'] = floatval($value['first_mem_rate']);
                $data[$key]['benefit_refill'] = floatval($value['mem_rate']);
            } else if ($value['benefit_type'] == 2) {
                $data[$key]['benefit_first_txt'] = 100 * $value['first_mem_rebate'].'%';
                $data[$key]['benefit_refill_txt'] = 100 * $value['mem_rebate'].'%';
                $data[$key]['benefit_first'] = 100 * $value['first_mem_rebate'];
                $data[$key]['benefit_refill'] = 100 * $value['mem_rebate'];
            }
        }
    }

    public function benefit_info_output_item(&$data) {
        if ($data['benefit_type'] == "1") {
            $data['benefit_first'] = $data['first_mem_rate'];
            $data['benefit_refill'] = $data['mem_rate'];
        } else if ($data['benefit_type'] == "2") {
            $data['benefit_first'] = $data['first_mem_rebate'];
            $data['benefit_refill'] = $data['mem_rebate'];
        }
    }

    public function set_agentgame_benefit_by_app_benefit($ag_id, $app_id, $agent_id) {
        $game_benefit_data = $this->get_app_benefit_info($app_id);
        $data['benefit_type'] = $game_benefit_data['benefit_type'];
        if ($data['benefit_type'] == "1") {
            $data['first_mem_rate'] = $game_benefit_data['first_mem_rate'];
            $data['mem_rate'] = $game_benefit_data['mem_rate'];
        } else if ($data['benefit_type'] == "2") {
            $data['first_mem_rebate'] = $game_benefit_data['first_mem_rebate'];
            $data['mem_rebate'] = $game_benefit_data['mem_rebate'];
        }
        $data['agent_rate'] = $game_benefit_data['agent_rate'];
        $data['create_time'] = time();
        $data['update_time'] = time();
        $_ag_map['ag_id'] = $ag_id;
        $exist = M('agent_game_rate')->where($_ag_map)->find();
        if (!$exist) {
            $data['ag_id'] = $ag_id;
            $data['app_id'] = $app_id;
            $data['agent_id'] = $agent_id;
            M('agent_game_rate')->add($data);
        } else {
            M('agent_game_rate')->where($_ag_map)->save($data);
        }
    }

    public function set_app_benefit_info($app_id, $data) {
        M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->save($data);
    }

    public function set_agentgame_all_benefit_type($app_id, $value) {
        M('agent_game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->setField("benefit_type", $value);
    }
}

