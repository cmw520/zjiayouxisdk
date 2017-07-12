<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;
use Sdk\Controller\SubpackageController;

class GameController extends AdminbaseController {
    private $hs_benefit_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_benefit_obj = new \Huosdk\Benefit();
    }

    public function index() {
        $this->show("ok");
    }

    private function get_all_agent_game_items() {
    }

    public function passall_post() {
        M('agent_game')->where(array("agent_id" => I('agent_id')))->setField("check_status", "2");
//         $this->ajaxReturn(array("error"=>"0","msg"=>"ok"));
    }

    public function unpassall_post() {
        M('agent_game')->where(array("agent_id" => I('agent_id')))->setField("check_status", "3");
//         $this->ajaxReturn(array("error"=>"0","msg"=>"ok"));
    }

    public function CheckApply() {
        if (I('agent')) {
            $agent_id = I('agent');
            $where = "agent_id = $agent_id";
            $current_agent = $agent_id;
        } else {
            $where = '';
            $current_agent = '';
        }
        $model = M('agent_game');
        $count = $model->where($where)->count();
        $page = $this->page($count, $this->row);
        $users = $model
            ->field(
                'ag.id,ag.create_time,ag.update_time,ag.check_status,'
                .'g.name as gamename,u.mobile,u.user_nicename as username,'
                .'u.user_login,ag.agentgame,g.initial,'
                .'(gv.ver-ag.ver) as pack_updated'
            )
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ag.agent_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->where($where)
            ->order("id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        foreach ($users as $key => $val) {
            $fp = SITE_PATH.'download/sdkgame/'.$val['initial']."/".$val['agentgame'].".apk";
            $downloadfp = DOWNSITE.$val['initial']."/".$val['agentgame'].".apk";
            if ($this->check_remote_file_exists($downloadfp) && $users["$key"]['check_status'] == '2') {
                $update_pack_api = U('Sdk/Subpackage/updatepackage', '', '')."/id/".$val['id'];
                $users["$key"]['subpackage_status'] = "<a href='$downloadfp' target='_blank'>已生成</a> &nbsp;&nbsp; ";
                if ($users["$key"]['pack_updated']) {
                    $users["$key"]['subpackage_status'] .= "| &nbsp;&nbsp; <a href='$update_pack_api' target='_self'>更新</a>";
                }
            } else {
                $users["$key"]['subpackage_status'] = "<span style='color:gray;'>不存在</span>";
            }
        }
//        print_r($agents);
        $this->assign("current_agent", $current_agent);
        $this->assign("users", $users);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    function check_remote_file_exists($url) {
        $curl = curl_init($url);
        // 不取回数据 
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求 
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败 
        if ($result !== false) {
            // 再检查http响应码是否为200 
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);

        return $found;
    }

    public function get_all_agents() {
        $results = M('agent_game')
            ->field("DISTINCT ag.agent_id,u.user_nicename,u.user_login")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ag.agent_id")
            ->order("u.user_login desc")
            ->select();

        return $results;
    }

    public function checkinfo() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $model = M('users');
        $c_results = $model->where("user_type= $subagent_roleid ")->count();
        $count = count($c_results);
        $row = isset($_POST['row']) ? $_POST['row'] : $this->row;
        $page = $this->page($count, $row);
        $users = $model
            ->field('u.*,am.*')
            ->alias('u')
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_man am ON u.id=am.agent_id")
            ->where("u.user_type= $subagent_roleid ")
            ->order("id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
//        print_r($page);
        $this->assign("users", $users);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function checkwithdraw() {
        $this->show("check");
    }

    public function withdrawRecord() {
        $this->display();
    }

    public function pass() {
//        echo I('id');
        $this->set_check_status_v2(I('id'), 2);
        $this->generateSubPackage();
//        $this->show("ok");
//        echo 'hi';
    }

    public function generateSubPackage() {
        $ag_id = I('id/d', 0);
        if (empty($ag_id)) {
            $this->error('参数错误');
        }
        $subpk_obj = new SubpackageController();
        $subpk_obj->_do_package($ag_id, 2);
    }

    private function set_check_status($id, $status) {
        $model = M('agent_game');
        $model->where("id=$id")->setField("check_status", $status);
        $model->where("id=$id")->setField("update_time", time());
        $this->ajaxReturn(array("error" => "0", "msg" => "审核成功"));//审核成功
//        if($c_results){
//            $this->success("审核成功");
//        }else{
//            $this->error("审核失败，内部错误");
//        }
    }

    private function set_check_status_v2($id, $status) {
        $model = M('agent_game');
        $model->where("id=$id")->setField("check_status", $status);
        $model->where("id=$id")->setField("update_time", time());
    }

    public function notpass() {
        $this->set_check_status(I('id'), 3);
    }

    private function get_all_games() {
        $model = M('game');
        $results = $model->where(array("is_delete" => 2, "is_own" => 2))->order("id desc")->select();

        return $results;
    }

    private function get_tui_games($tag) {
        $model = M('tui_games');
        $records = $model
            ->field("g.name,tg.id,g.icon")
            ->alias('tg')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=tg.game_id")
            ->where(array("tag" => $tag))
            ->order("tg.id desc")
            ->limit(4)
            ->select();

        return $records;
    }

    private function get_tui_games_count($tag) {
        $model = M('tui_games');
        $n = $model->where(array("tag" => $tag))->count();

        return $n;
    }

    public function hot() {
        $this->assign("games", $this->get_all_games());
        $this->assign("items", $this->get_tui_games("hot"));
        $this->display();
    }

    public function about_online() {
        $this->assign("games", $this->get_all_games());
        $this->assign("items", $this->get_tui_games("about_online"));
        $this->display();
    }

    public function hot_post() {
        $gameid = I('gameid');
        if ($this->get_tui_games_count("hot") >= 4) {
            $this->error("最多只能添加4个");
            exit;
        }
        if ($this->tui_game_already_has($gameid, "hot")) {
            $this->error("已经添加过了，不能重复添加");
            exit;
        }
        $model = M('tui_games');
        $data = array(
            "tag"     => "hot",
            "game_id" => $gameid
        );
        $model->add($data);
        $this->success("添加成功", U('tui/game/hot'));
    }

    private function tui_game_already_has($gameid, $tag) {
        $model = M('tui_games');

        return $model->where(array("game_id" => $gameid, "tag" => $tag))->find();
    }

    public function about_online_post() {
        $gameid = I('gameid');
        if ($this->get_tui_games_count("about_online") >= 4) {
            $this->error("最多只能添加4个");
            exit;
        }
        if ($this->tui_game_already_has($gameid, "about_online")) {
            $this->error("已经添加过了，不能重复添加");
            exit;
        }
        $model = M('tui_games');
        $data = array(
            "tag"     => "about_online",
            "game_id" => $gameid
        );
        $model->add($data);
        $this->success("添加成功", U('tui/game/about_online'));
    }

    public function about_online_delete() {
        $id = I('id');
        $model = M('tui_games');
        $model->where(array("id" => $id))->delete();
        $this->success("删除成功", U('tui/game/about_online'));
    }

    public function hot_delete() {
        $id = I('id');
        $model = M('tui_games');
        $model->where(array("id" => $id))->delete();
        $this->success("删除成功", U('tui/game/hot'));
    }

    public function man() {
        $items = $this->hs_benefit_obj->benefitDefinedAppList();
        $this->assign("items", $items);
        $this->display();
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
        $data['benefit_type'] = $_POST['benefit_type'];
        if ($_POST['benefit_type'] == '1') {
            $data['mem_rate'] = $_POST['benefit_refill'];
            $data['first_mem_rate'] = $_POST['benefit_first'];
        } else if ($_POST['benefit_type'] == '2') {
            $data['mem_rebate'] = $_POST['benefit_refill'];
            $data['first_mem_rebate'] = $_POST['benefit_first'];
        }
        $data['agent_rate'] = $_POST['agent_rate'];
        $this->hs_benefit_obj->addAppBenefit($app_id, $data);
        $this->success("添加成功");
    }

    public function edit() {
        $app_id = I('app_id');
        $hs_game_obj = new \Huosdk\Game();
        $app_info = $hs_game_obj->get_app_basic_info($app_id);
        $data = $this->hs_benefit_obj->get_app_benefit_info($app_id);
//        print_r($data);
//        exit();
        $this->assign("app_info", $app_info);
        $this->assign("data", $data);
//        print_r($app_info);
//        print_r($data);
//        exit();
        $this->assign("benefit_type_select_txt", $this->benefit_type_select_txt($data['benefit_type']));
        $this->display();
    }

    public function benefit_type_select_txt($current) {
        $result = '';
        $data = array("1" => "折扣", "2" => "返利");
        foreach ($data as $k => $v) {
            $c = '';
            if ($k == $current) {
                $c = 'checked';
            }
            $result .= "<input type='radio' name='benefit_type' value='$k' $c >$v ";
        }

        return $result;
    }

    public function edit_post() {
        $app_id = I('app_id');
        $check_result = $this->hs_benefit_obj->benefilt_value_filter();
        if (!$check_result['status']) {
            $this->error($check_result['msg']);
            exit;
        }
        $prev_add_data = $_POST;
        $benefit_data['benefit_type'] = $_POST['benefit_type'];
        $benefit_data['agent_rate'] = $_POST['agent_rate'];
        if ($benefit_data['benefit_type'] == '1') {
            $benefit_data['mem_rate'] = $_POST['benefit_refill'];
            $benefit_data['first_mem_rate'] = $_POST['benefit_first'];
        } else if ($benefit_data['benefit_type'] == '2') {
            $benefit_data['mem_rebate'] = $_POST['benefit_refill'];
            $benefit_data['first_mem_rebate'] = $_POST['benefit_first'];
        }
        $this->hs_benefit_obj->addAppBenefit($app_id, $benefit_data);
        $this->hs_benefit_obj->set_agentgame_all_benefit_type($app_id, $prev_add_data['benefit_type']);
        $this->success("修改成功");
    }
}

