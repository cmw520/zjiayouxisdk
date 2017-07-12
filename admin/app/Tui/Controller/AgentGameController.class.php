<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentGameController extends AdminbaseController {
    private $hs_benefit_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_benefit_obj = new \Huosdk\Benefit();
    }

    public function link() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $model = M('agent_game');
        $where = array();
//        $where['_string'] = "gg.url IS NOT NULL";
        $count = $model
            ->field("gg.*,u.user_login as agent_name,g.name as game_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gg.agent_id")
            ->count();
        $page = $this->page($count, 10);
        $items = $model
            ->field("gg.*,u.user_login as agent_name,g.name as game_name")
            ->alias("gg")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gg.agent_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("gg.id desc")
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function man() {
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "appname", "g.name");
        $count = count($this->GameList($where));
        $page = $this->page($count, 10);
        $items = $this->GameList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function GameList($where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $where['g.id'] = array("neq", C("APP_APPID"));
        $where['g.is_delete'] = 2;
        $where['g.status'] = 2;
        $where['gr.agent_rate'] = array("neq", 0);
//        $where['_string']="gr.agent_rate !=0";
        $items = M('game')
            ->field("g.*,gv.size,gv.version,gr.agent_rate")
            ->alias('g')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_rate gr ON gr.app_id=g.id")
            ->where($where)->where($where_extra)
            ->order("g.update_time desc")
            ->limit($start, $limit)
            ->select();
        $promote_data = array("2" => "<span class='label label-success'>已上架</span>",
                              "1" => "<span class='label label-danger'>未上架</span>");
        foreach ($items as $key => $value) {
            $items[$key]['promote_status'] = $promote_data[$value['promote_switch']];
            if (!$value['agent_rate']) {
                $items[$key]['agent_rate'] = "未设置";
            }
        }

        return $items;
    }

    public function add() {
        $app_list = $this->hs_benefit_obj->benefitUndefinedAppList();
        $select_txt = "<option value='0'>选择游戏</option>";
        foreach ($app_list as $k => $v) {
            $app_id = $v['id'];
            $app_name = $v['name'];
            $select_txt .= "<option value='$app_id'>$app_name</option>";
        }
        $this->assign("app_select_list", $select_txt);
        $this->display();
    }

    public function add_post() {
        $app_id = I('app_id');
        if (!$app_id) {
            $this->error("请选择游戏");
            exit;
        }
        if (!$this->hs_benefit_obj->benefilt_value_filter()) {
            $this->error("参数错误");
            exit;
        }
        $this->hs_benefit_obj->addAppBenefit($app_id, $_POST);
        $this->success("添加成功");
    }

    public function edit() {
        $ag_id = I('agent_game_id');
        $data = $this->hs_benefit_obj->get_AgentApp_benefit_info($ag_id);
        $this->assign("data", $data);
        $this->display();
    }

    public function edit_post() {
        if (C("G_DISCONT_TYPE")) {
            $this->edit_post_with_beneift();
        } else {
            $this->edit_post_no_benefit();
        }
    }

    public function edit_post_with_beneift() {
        if (!$this->hs_benefit_obj->benefilt_value_filter()) {
            $this->error("参数错误");
            exit;
        }
        $ag_id = I('agent_game_id');
        $data = $_POST;
        $agent_rate = $data['agent_rate'];
        if (!($agent_rate > 0 && $agent_rate <= 1)) {
            $this->error("渠道折扣必须在(0,1]之间");
        }
        // $hs_br_obj=new \Huosdk\Benefit\RulesGame($data['prev_agent_rate']);
        $hs_br_obj = new \Huosdk\Benefit\RulesAgentGame($ag_id, $agent_rate);
        if ($data['benefit_type'] == 1) {
            $result = $hs_br_obj->check_mem_rate($data['benefit_refill'], $data['benefit_first']);
            if ($result != "ok") {
                $this->error($result);
            }
        } elseif ($data['benefit_type'] == 2) {
            $result = $hs_br_obj->check_mem_rebate($data['benefit_refill'], $data['benefit_first']);
            if ($result != "ok") {
                $this->error($result);
            }
        }
        $ag_id = I('agent_game_id');
        $this->hs_benefit_obj->setAgentGameBenefit($ag_id, $_POST);
        $this->success("修改成功", U('Tui/AgentGameBenefit/man'));
    }

    public function edit_post_no_benefit() {
        $ag_id = I('agent_game_id');
        $add_data['agent_rate'] = $_POST['agent_rate'];
        $add_data['create_time'] = time();
        $add_data['update_time'] = time();
        M('agent_game_rate')->where(array("ag_id" => $ag_id))->save($add_data);
        $this->success("修改成功");
    }

    public function setPormoteStatus() {
        $app_id = I('app_id/d', 0);
        $status = I('status/s');
        if (!($status == "on" || $status == "off") || empty($app_id)) {
            $this->error("参数错误");
//             $this->ajaxReturn(array("error" => "1", "msg" => "参数错误"));
        }
        $_map['app_id'] = $app_id;
        /* 判断是否已经上线 */
        $_game_data = M('game')->field('status, game_id')->where(array('id'=>$app_id))->find();
        if (2 != $_game_data['status']){
            $this->error("请在SDK管理-游戏管理中先上线该游戏",U('Sdk/Game/index',array('app_id'=>$_game_data['game_id'])));
        }
        //判断是否设定渠道折扣
        $_gr_data = M('game_rate')->where($_map)->find();
        if (empty($_gr_data) || empty($_gr_data['agent_rate'])) {
            $this->error("请先设定渠道折扣");
        }
        if ($status == "on") {
            $status_int = 2;
        } else if ($status == "off") {
            $status_int = 1;
        }
        $_data['promote_switch'] = $status_int;
        $_data['update_time'] = time();
        $_data['id'] = $app_id;
        $_rs = M('game')->save($_data);
        if ($_rs) {
            $_gr_data['update_time'] = $_data['update_time'];
            $_gr_data['promote_switch'] = $status_int;
            M('game_rate')->save($_gr_data);
            $this->success("设置成功");
        } else {
            $this->error("设置失败");
        }
//         $_rs = M('game')->where(array("id"=>$app_id))->setField("promote_switch",$status_int);
//         $_rs = M('game')->where(array("id"=>$app_id))->setField("update_time",time());
//         $this->success('设置成功');
    }
}
