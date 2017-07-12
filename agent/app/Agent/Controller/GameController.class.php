<?php
/**
 * LoginController.class.php UTF-8
 * 登录接口
 *
 * @date    : 2016年7月21日下午8:27:36
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class GameController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    private function get_apply_games_old() {
        $model = M("agent_game");
        $games = $model->order("id desc")->limit(10)->select();

        return $games;
    }

    private function id_in_my_games($id, $my_games) {
        foreach ($my_games as $mygame) {
            if ($mygame['app_id'] == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    private function get_apply_games() {
        $agent_id = $_SESSION['agent_id'];
        $where = "(g.promote_switch = 2) AND (g.is_delete = 2) AND (g.status = 2) "
                 ."AND (g.id NOT IN (select app_id from ".C('DB_PREFIX')."agent_game where agent_id =$agent_id)) "
                 ." ";
        $current_key = '';
        if (I('key') !== '') {
            $key = I('key');
            $where .= "AND ((g.name LIKE '%$key%') OR (g.id LIKE '%$key%')) ";
            $current_key = $key;
        }
        $model = M("game");
        $count = $model->alias('g')
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_rate gr ON gr.app_id=g.id ")
                       ->where($where)
                       ->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出        
        $games = $model
            ->field("g.id,g.name,IFNULL(gi.mobile_icon,g.icon) icon,g.update_time,g.classify,gv.size,gv.version,gr.agent_rate,gr.benefit_type")
            ->alias('g')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_info gi ON gi.app_id=g.game_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_rate gr ON gr.app_id=g.id ")
            ->where($where)
            ->order("g.id desc")
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
        foreach ($games as $k => $v) {
            $games[$k]['size'] = round($v['size'] / (1024 * 1024));
            /* 解析ICON */
            if (strpos($v['icon'], "/") !== 0) {
                $games[$k]['icon'] = '/upload/image/'.$v['icon'];
            }
            if (empty($v['icon'])){
                $games[$k]['icon'] = '/upload/ic_launcher.png';
            }

        }
        $this->assign("current_key", $current_key);
        $this->assign("total_count", $count);
        $this->assign("Page", $show);

        return $games;
    }

    private function get_my_games() {
        $model = M("agent_game");
        $games = $model
            ->field("ag.*,g.name as gamename")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->where(array("agent_id" => $_SESSION['agent_id']))
            ->order("update_time desc")
            ->select();

        return $games;
    }

    public function apply_game() {
        in_case_notpass();
        $games = $this->get_apply_games();
        $choose_agents = $this->huoshu_agent->getMySubAgents();
        $this->assign("choose_agents", $choose_agents);
        $this->assign("games", $games);
        $this->assign("page_title", "申请游戏");
        $this->display();
    }

    public function apply_game_post() {
        $list = I('list');
        if (empty($list)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "参数错误"));
            exit;
        }
        $this->huoshu_agent->addAgentGame($list);
        $this->ajaxReturn(array("error" => "0", "msg" => "申请成功"));
    }

    public function setDownVer() {
        $ag_id = I('ag_id/d');
        $ver = M('agent_game')->where(array("id" => $ag_id))->getField("ver");
        M('agent_game')->where(array("id" => $ag_id))->setField("down_ver", $ver);
    }

    public function mygames() {
        in_case_notpass();
        $agent_id = $_SESSION['agent_id'];
        $where = "(ag.agent_id = $agent_id) AND (g.is_delete=2) AND (g.status=2) AND (g.promote_switch=2) ";
        $query_field = array();
        if (isset($_GET['gamename']) && $_GET['gamename']) {
            $gamename = $_GET['gamename'];
            $query_field['gamename'] = $gamename;
            $where .= "AND ((g.name like '%$gamename%') OR (g.id like '%$gamename%'))";
        }
        $model = M("agent_game");
        $count = $model
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->where($where)
            ->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出  
        $games = $model
            ->field(
                "ag.*,g.name as gamename,g.classify,IFNULL(gi.mobile_icon,g.icon) icon,g.initial,agr.agent_rate,gv.packageurl downurl"
            )
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_info gi ON gi.app_id=g.game_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_game_rate agr ON agr.ag_id=ag.id")
            ->where($where)
            ->order("ag.update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach ($games as $key => $value) {
            if (4 == $value['classify']) {
                $remote_app_fp = MOBILESITE."/mobile.php/Mobile/Game/sub?appid=".$value['app_id']."&agent=".$agent_id;
            } else {
//                if (true === strpos($value['url'],'sdkgame/')){
//                    $value['url'] = str_replace('sdkgame/','',$value['url']);
//                }
                $remote_app_fp = DOWNSITE.$value['url'];
                //               $remote_app_fp = DOWNSITE."?d.php?fn=".$value['url'];
            }
            /* 解析ICON */
            if (strpos($value['icon'], "/") !== 0) {
                $games[$key]['icon'] = '/upload/image/'.$value['icon'];
            }
            $realfp_app = $remote_app_fp;
            $games[$key]['app_fp'] = $realfp_app;
        }
        \Huosdk\Data\FormatRecords::package_generate_status($games);
        $this->assign("query_field", $query_field);
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
        $this->assign("formget", $_GET);
        $this->assign("page_title", "我的游戏");
        $this->display();
    }

    public function _zqdata($games, $key, $value) {
        $savename = M("game")->where(array("id" => $value['app_id']))->getField("initial");
        $savename .= "_info";
        $zqdata_fp = SITE_PATH."access/upload/zqdata/".$savename.".zip";
        $zqdata_fp_rar = SITE_PATH."access/upload/zqdata/".$savename.".rar";
//            $zqdata_fp=iconv("utf-8","gb2312",$zqdata_fp);
        if (file_exists($zqdata_fp)) {
            $realfp = "/access/upload/zqdata/".$savename.".zip";
        } else if (file_exists($zqdata_fp_rar)) {
            $realfp = "/access/upload/zqdata/".$savename.".rar";
        } else {
            $realfp = '#';
        }
        $app_fp = SITE_PATH."download/sdkgame/".$value['initial']."/".$value['agentgame'].".apk";
        $games[$key]['zqdata_fp'] = $realfp;
    }

    private function get_apply_passed_games() {
        $agent_id = $_SESSION['agent_id'];
        $where = array();
        $where["agent_id"] = $agent_id;
        $where["check_status"] = "2";
        $model = M("agent_game");
        $games = $model
            ->field("ag.*,g.name,gv.version,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->where($where)
            ->order("update_time desc")
            ->select();

        return $games;
    }

    public function gamesort() {
        $this->display();
    }

    public function gameinfo() {
        $this->ajaxReturn("hello");
    }

    public function cancelApply() {
        $model = M('agent_game');
        $model->where(array("id" => I('id')))->delete();
        $this->ajaxReturn(array("error" => "0", "msg" => "取消申请成功"));
    }

    public function needInfo() {
        $this->display();
    }

    public function get_rate_base($game_id) {
        $hs_benefit_obj = new \Huosdk\Benefit();
        $base = $hs_benefit_obj->get_app_default_agent_rate($game_id);

//        $base=M('game_rate')->where(array("id"=>$game_id))->getField("agent_rate");
        return $base;
    }

    public function get_appid_by_agent_game_id($agent_game_id) {
        return M('agent_game')->where(array("id" => $agent_game_id))->getField("app_id");
    }

    public function set_mem_rate() {
        $new_rate = I('rate');
        $agent_game_id = I('agent_game_id');
//        $app_id=$this->get_appid_by_agent_game_id($agent_game_id);
        if (!(is_numeric($new_rate) && $new_rate > 0 && $new_rate < 1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣必须是介于0和1之间的数字"));
            exit;
        }
//        $base=$this->get_rate_base($app_id);
        $hs_benefit_obj = new \Huosdk\Benefit();
        $base = $hs_benefit_obj->get_agent_game_agent_rate($agent_game_id);
//        $base=M('agent_game_rate')->where(array("id"=>$agent_game_id))->getField("agent_rate");
        if ($new_rate < $base) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣不能低于基线".$base));
            exit;
        }
        M('agent_game')->where(array("id" => $agent_game_id))->setField("mem_rate", $new_rate);
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    public function get_generateapp_items() {
        $agent_id = $_SESSION['agent_id'];
        $where = array();
        $where["agent_id"] = $agent_id;
        $where["check_status"] = "2";
        $where["in_app"] = "1";
        $current_key = '';
        if (I('key') !== '') {
            $key = I('key');
            $where["_string"] = "((g.name LIKE '%$key%') OR (g.id LIKE '%$key%')) ";
            $current_key = $key;
        }
        $model = M("agent_game");
        $count = $model->alias('ag')
                       ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
                       ->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出   
        $games = $model
            ->field("ag.*,g.name,gv.version,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->where($where)
            ->order("update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        /*
         * 如果图片不存在，用默认图片代替
         * 2016-08-10 11:08:21 严旭
         */
        foreach ($games as $key => $game) {
            $fp = SITE_PATH.'upload/'.$game['icon'];
            if (!file_exists($fp)) {
                $games["$key"]['icon'] = "/upload/default_app_icon.png";
            }
        }
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
    }

    public function get_inapp_items() {
        $agent_id = $_SESSION['agent_id'];
        $where = array();
        $where["agent_id"] = $agent_id;
        $where["check_status"] = "2";
        $where["in_app"] = "2";
        $current_key = '';
        if (I('key') !== '') {
            $key = I('key');
            $where["_string"] = "((g.name LIKE '%$key%') OR (g.id LIKE '%$key%')) ";
            $current_key = $key;
        }
        $model = M("agent_game");
        $count = $model->alias('ag')
                       ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
                       ->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出   
        $games = $model
            ->field("ag.*,g.name,gv.version,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->where($where)
            ->order("update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        /*
         * 如果图片不存在，用默认图片代替
         * 2016-08-10 11:08:21 严旭
         */
        foreach ($games as $key => $game) {
            $fp = SITE_PATH.'upload/'.$game['icon'];
            if (!file_exists($fp)) {
                $games["$key"]['icon'] = "/upload/default_app_icon.png";
            }
        }
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
    }

    public function generateApp() {
        in_case_notpass();
        $this->get_generateapp_items();
        $this->assign("page_title", "生成APP");
        $this->display();
    }

    public function inApp() {
        $this->get_inapp_items();
        $this->display();
    }

    public function app_getcontent() {
        $agent_id = $_GET['agent_id'];
        $where = array();
        $model = M("agent_game");
        $where["agent_id"] = $agent_id;
        $where["check_status"] = "2";
        $where["in_app"] = "1";
        $count = $model->alias('ag')
                       ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
                       ->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $games = $model
            ->field("ag.*,g.name,gv.version,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->where($where)
            ->order("update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        /*
        * 如果图片不存在，用默认图片代替
        * 2016-08-10 11:08:21 严旭
        */
        foreach ($games as $key => $game) {
            $fp = SITE_PATH.'upload/'.$game['icon'];
            if (!file_exists($fp)) {
                $games["$key"]['icon'] = "/upload/default_app_icon.png";
            }
        }
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
    }

//    public function app_getcontent3(){
//            $agent_id=$_GET['agent_id'];
//            
//            $where=array();
//            $model=M("agent_game");
//            
//            $where["agent_id"]=$agent_id;
//            $where["check_status"]="2";
//            $where["in_app"]="2";
//            
//            $count=$model->alias('ag')
//                ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id" )
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
//                ->where($where)->count();
//        
//            $Page= new \Think\Page($count,10);                            
//            $show = $Page->show();// 分页显示输出   
//
//            $games=$model
//                ->field("ag.*,g.name,gv.version,g.icon,gt.name as gametype")
//                ->alias('ag')
//                ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id" )
//                ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id" )
//                    ->join("LEFT JOIN ".C('DB_PREFIX')."game_type gt ON gt.id=g.type" )
//                ->where($where)                
//                ->order("update_time desc")
//                ->limit($Page->firstRow.','.$Page->listRows)
//                ->select();
//            
//            /*
//            * 如果图片不存在，用默认图片代替
//            * 2016-08-10 11:08:21 严旭
//            */
//           foreach($games as $key=>$game){
//               $fp=SITE_PATH.'upload/'.$game['icon'];
//               if(!file_exists($fp)){
//                   $games["$key"]['icon']="default_app_icon.png";
//               }
//           }
//            
//            $this->assign("total_count",$count);
//            $this->assign("Page", $show); 
//            $this->assign("games",$games);
//    }
//    public function app1(){
//        if(isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && ($_GET['agent_id']>=1)){
//            $this->app_getcontent();
//            $this->display("Game/app");
//        }
//        
//    }
//    
//    public function app2(){
//        if(isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && ($_GET['agent_id']>=1)){
//            $this->app_getcontent();
//            $this->display("Game/app_2");
//        }
//        
//    }
//    
//    public function app3(){
//        if(isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && ($_GET['agent_id']>=1)){
//            $this->app_getcontent3();
//            $this->display("Game/app_3");
//        }
//        
//    }
//    
//    public function app(){
//        if(isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && ($_GET['agent_id']>=1)){
//            $this->app_getcontent3();
//            $this->display("Game/app_4");
//        }
//        
//    }
    public function generateApp_post() {
        $list = array();
        $list = I('list');
        $agent_id = $_SESSION['agent_id'];
        $ids = join(",", $list);
        if ($list) {
            M('agent_game')->where("(agent_id=$agent_id) AND (id IN ($ids))")->setField("in_app", "2");
            $this->ajaxReturn(array("error" => "0", "msg" => "成功"));
        }
    }

    public function inApp_cancel_post() {
        $list = array();
        $list = I('list');
        $agent_id = $_SESSION['agent_id'];
        $ids = join(",", $list);
        if ($list) {
            M('agent_game')->where("(agent_id=$agent_id) AND (id IN ($ids))")->setField("in_app", "1");
            $this->ajaxReturn(array("error" => "0", "msg" => "成功"));
        }
    }

    public function toSub() {
        $sub_select = $this->huoshu_agent->mySubSelectList();
//        print_r($this->huoshu_agent->getSubleList(317));
//        exit;
        if (I('subid')) {
            $subid = I('subid');
            if ($subid > 0) {
                $sub_game_list = $this->huoshu_agent->getSubleList($subid);
                $this->assign("sub_game_list", $sub_game_list);
                $sub_select = $this->huoshu_agent->mySubSelectList($subid);
            }
        }
        $this->assign("subtxt", $sub_select);
        $this->display();
    }

    public function sub_mygames() {
        $sub_select = $this->huoshu_agent->mySubSelectList();
        $sub_apply_list = array();
//        print_r($this->huoshu_agent->getSubleList(317));
//        exit;
        if (I('subid')) {
            $subid = I('subid');
            if ($subid > 0) {
                $sub_apply_list = $this->huoshu_agent->getSubApplyList($subid);
                $sub_select = $this->huoshu_agent->mySubSelectList($subid);
            }
        }
        $this->assign("sub_game_list", $sub_apply_list);
        $this->assign("subtxt", $sub_select);
        $this->display();
    }

    public function pack() {
        $agid = I('id');
        $hs_package_obj = new \Huosdk\Package();
        $result = $hs_package_obj->pack($agid);
        if ($result['error'] == 0) {
            M('agent_game')->where(array("id" => $agid))->setField("status", 2);
        }
        $this->ajaxReturn($result);
    }
}