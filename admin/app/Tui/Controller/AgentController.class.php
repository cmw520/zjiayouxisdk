<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentController extends AdminbaseController {
    private $hs_agent2_obj;

    function _initialize() {
        parent::_initialize();
        $this->hs_agent2_obj = new \Huosdk\Agent2();
    }

    public function man() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "user_login", "u.user_login");
        $hs_where_obj->get_simple($where, "status", "u.user_status");
        $hs_where_obj->get_simple($where, "user_type", "u.user_type");
        $hs_where_obj->get_simple_like($where, "agent_name", "u.user_nicename");
        $hs_where_obj->get_simple_like($where, "parent_agent_name", "u2.user_nicename");
        $all_items = $this->hs_agent2_obj->all_agent_list($where);
        $count = count($all_items);
        $page = $this->page($count, $this->row);
        $users = $this->hs_agent2_obj->all_agent_list($where, $page->firstRow, $page->listRows);
        $this->assign("users", $users);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function detail() {
        $hs_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_filter_obj->agent_select2());
        $agent_id = I('agent_id');
        $data = $this->hs_agent2_obj->get_info($agent_id);
        $this->assign("data", $data);
        $this->get_login_items($agent_id);
        $this->display();
    }

    public function change_parent_agent() {
        $agent_id = I("agent_id");
        $ownerid = I("ownerid");
        M('users')->where(array("id" => $agent_id))->setField("ownerid", $ownerid);
        M('users')->where(array("id" => $agent_id))->setField("update_time", time());
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    public function get_login_items($agent_id) {
        $hs_all_obj = new \Huosdk\AgentLoginLog();
        $count = $hs_all_obj->getListCount($agent_id);
        $page = $this->page($count, 20);
        $items = $hs_all_obj->getListItems($agent_id, array(), $page->firstRow, $page->listRows);
        $this->assign("login_items", $items);
        $this->assign("login_page", $page->show('Admin'));
    }

    public function check() {
        $this->show("check");
    }

    public function checkinfo() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $model = M('users');
        $count = $model->where("user_type= $agent_roleid ")->count();
        $page = $this->page($count, $this->row);
        $users = $model
            ->field('u.*,am.*')
            ->alias('u')
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_man am ON u.id=am.agent_id")
            ->where("u.user_type= $agent_roleid ")
            ->order("id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("users", $users);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    private function get_agent_ext_info() {
        return M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->find();
    }

    public function ban() {
        $agent_id = I('agent_id');
        M('users')->where(array("id" => $agent_id))->setField("user_status", "3");
        $this->success("冻结成功");
    }

    public function unban() {
        $agent_id = I('agent_id');
        M('users')->where(array("id" => $agent_id))->setField("user_status", "2");
        $this->success("解冻成功");
    }

    public function params() {
        $this->assign("base", get_agent_withdraw_base());
        $this->display();
    }

    public function params_post() {
        $base = I('agent_widthdraw_base/d');
//        echo $base;
        if ($base >= 0) {
            M('options')->where(array("option_id" => 1))->setField("option_value", "$base");
//            set_option("agent_widthdraw_base", $base);
            $this->success("提现阈值设置成功");
        } else {
            $this->error("提现阈值必须大于或者等于0");
        }
    }

    public function export() {
        $type = I('path.2');
        $items = array();
        $fname = '数据导出';
        if ($type == "cr") {
            $fname = "充值记录";
            $items = $this->export_cr();
        } else if ($type == "vc") {
            $fname = "代金券消费记录";
            $items = $this->export_vc();
        } else if ($type == "mvc") {
            $fname = "玩家充值代金券记录";
            $items = $this->export_mvc();
        } else if ($type == "ex") {
            $fname = "余额兑换代金券记录";
            $items = $this->export_ex();
        }
        $this->export_do($items, $fname);
    }

    public function export_vc() {
        $items = M('gm_pay')
            ->field(
                "ap.id as ID,ap.amount as 消费金额, u.user_nicename as 代理用户名,u.mobile as 代理手机号,u.user_login as 代理名,m.username as 玩家名,g.name as 游戏名"
            )
            ->alias('ap')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ap.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ap.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ap.mem_id")
            ->order("ap.id desc")
            ->limit(300)
            ->select();

        return $items;
    }

    public function export_mvc() {
        $items = M('gm_charge')
            ->field(
                "ac.id as ID,u.user_nicename as 代理用户名,u.mobile as 代理手机号,u.user_login,m.username as 玩家名,g.name as 游戏名,"
                ."agr.agent_rate as 平台折扣,(ac.discount-agr.agent_rate)*ac.money as 利润"
            )
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ac.mem_id")
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=ac.admin_id) AND (ag.app_id = ac.app_id))"
            )
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")
                ."agent_game_rate agr ON ((agr.agent_id=ac.admin_id) AND (agr.app_id = ac.app_id))"
            )
            ->order("ac.id desc")
            ->limit(300)
            ->select();

        return $items;
    }

    public function export_ex() {
        $items = M('gm_agentcharge')
            ->field("ac.id as ID,u.user_nicename as 用户名,u.mobile as 用户手机号,u.user_login,g.name as 游戏名")
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->order("ac.id desc")
            ->limit(300)
            ->select();

        return $items;
    }

    public function export_cr() {
        $items = M('gm_charge')
            ->field(
                "ac.id as ID,u.user_nicename as 代理用户名,u.mobile as 代理手机号,m.username as 玩家用户名,g.name as 游戏名,g.game_rate as 成本,(ac.discount-g.game_rate)*ac.money as 利润"
            )
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ac.mem_id")
            ->order("ac.id desc")
            ->limit(300)
            ->select();

        return $items;
    }

    public function export_do($items, $file_name = "后台数据导出") {
        Vendor("PHPExcel.PHPExcel");
// Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
// Set document properties
        $objPHPExcel->getProperties()->setCreator("严旭")
                    ->setLastModifiedBy("严旭")
                    ->setTitle("后台数据导出")
                    ->setSubject("后台数据导出")
                    ->setDescription("后台数据导出")
                    ->setKeywords("后台数据导出")
                    ->setCategory("后台数据导出");
        if (count($items) >= 1) {
            $c = $items[0];
            $c_i = 0;
            foreach ($c as $column => $v) {
                $left_txt = chr(65 + $c_i)."1";
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("$left_txt", $column);
                $c_i++;
            }
        }
        $row = 1;
        foreach ($items as $key => $item) {
            $i = 0;
            foreach ($item as $column => $value) {
                $index = $row + 1;
                $left_txt = chr(65 + $i)."$index";
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue("$left_txt", $value);
                $i++;
            }
            $row++;
        }
// Add some data
//$objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A1', 'Hello')
//            ->setCellValue('B2', 'world!')
//            ->setCellValue('C1', 'Hello')
//            ->setCellValue('D2', 'world!');
// Miscellaneous glyphs, UTF-8
//$objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A4', 'Miscellaneous glyphs')
//            ->setCellValue('A5', 'test');
// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Data');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function chargeRecord() {
        $where = array();
        if (isset($_GET['agent_id']) && ($_GET['agent_id'])) {
            $where = array("ac.admin_id" => $_GET['agent_id']);
        }
        if (isset($_GET['mem_id']) && ($_GET['mem_id'])) {
            $where = array("ac.mem_id" => $_GET['mem_id']);
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "ac.create_time");
        $count = M('gm_charge')->alias('ac')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = M('gm_charge')
            ->field(
                "ac.*,u.user_nicename,u.mobile,u.user_login,m.username as memname,g.name as gamename,g.game_rate as cost,(ac.discount-g.game_rate)*ac.money as profit"
            )
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ac.mem_id")
            ->where($where)
            ->order("ac.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $sumitems = M('gm_charge')
            ->field(
                "sum(money) as sum_money,sum(gm_cnt) as sum_cnt,sum((ac.discount-g.game_rate)*ac.money) as sum_profit "
            )
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->where($where)
            ->select();
//        foreach($items as $key=>$value){
//            $items["$key"]['profit']=($value['discount']-$value['cost'])*$value['money'];
//        }
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $hs_filter_obj = new \Huosdk\UI\Filter();
        $mem_select_txt = $hs_filter_obj->member_select();
        $this->assign("mem_select_txt", $mem_select_txt);
        $agent_select_txt = $hs_filter_obj->agent_select2();
        $this->assign("agent_select_txt", $agent_select_txt);
        $this->assign("time_choose_txt", $hs_filter_obj->time_choose());
        $this->assign("sumitems", $sumitems);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function voucherConsumption() {
        $where = array();
        if (isset($_GET['memname']) && $_GET['memname'] != '') {
            $mem_id = get_memid_by_name($_GET['memname']);
            $where["ap.mem_id"] = $mem_id;
        }
        if (isset($_GET['gamename']) && $_GET['gamename'] != '') {
            $name = $_GET['gamename'];
            $app_id = M('game')->where(array("name" => $name))->getField("id");
            $where["ap.app_id"] = $app_id;
        }
        if (isset($_GET['agent']) && $_GET['agent'] != '') {
            $where["ap.agent_id"] = $_GET['agent'];
        }
        $model = M('gm_pay');
        $count = $model->alias('ap')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = $model
            ->field("ap.*,u.user_nicename,u.mobile,u.user_login as agentname,m.username as memname,g.name as gamename")
            ->alias('ap')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ap.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ap.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ap.mem_id")
            ->where($where)
            ->order("ap.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $model
            ->field("sum(amount) as sum_amount,sum(gm_cnt) as sum_cnt")
            ->alias('ap')
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("memname", $memname);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function exchange() {
        $where = array();
        if (isset($_GET['agent']) && ($_GET['agent'] != '')) {
            $where = array("ac.agent_id" => $_GET['agent']);
        }
        $count = M('gm_agentcharge')->alias('ac')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = M('gm_agentcharge')
            ->field("ac.*,u.user_nicename,u.mobile,u.user_login,g.name as gamename")
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->where($where)
            ->order("ac.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = M('gm_agentcharge')
            ->field("sum(money) as sum_money,sum(gm_cnt) as sum_cnt")
            ->alias('ac')
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function memberVoucherCharge() {
        $where = array();
        if (isset($_GET['agent']) && $_GET['agent'] != '') {
            $where["ac.admin_id"] = array('eq', $_GET['agent']);
        }
        if (isset($_GET['memname']) && $_GET['memname'] != '') {
            $mem_id = get_memid_by_name($_GET['memname']);
            $where["ac.mem_id"] = array('eq', $mem_id);
        }
        if (isset($_GET['orderid']) && $_GET['orderid'] != '') {
            $where["ac.order_id"] = array('eq', $_GET['orderid']);
        }
        if (isset($_GET['start_time']) && $_GET['start_time'] != '') {
            $where["ac.create_time"] = array('gt', strtotime($_GET['start_time']));
        }
        if (isset($_GET['end_time']) && $_GET['end_time'] != '') {
            $where["ac.create_time"] = array('lt', strtotime($_GET['end_time']));
        }
        $count = M('gm_charge')->alias('ac')->where($where)->count();
        $page = $this->page($count, $this->row);
        $items = M('gm_charge')
            ->field(
                "ac.*,u.user_nicename,u.mobile,u.user_login,m.username as memname,g.name as gamename,"
                ."agr.agent_rate as platrate,(ac.discount-agr.agent_rate)*ac.money as profit"
            )
            ->alias('ac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=ac.mem_id")
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=ac.admin_id) AND (ag.app_id = ac.app_id))"
            )
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")
                ."agent_game_rate agr ON ((agr.agent_id=ac.admin_id) AND (agr.app_id = ac.app_id))"
            )
            ->where($where)
            ->order("ac.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = M('gm_charge')
            ->field(
                "sum(money) as sum_money,sum(gm_cnt) as sum_cnt,sum((ac.discount-agr.agent_rate)*ac.money) as sum_profit"
            )
            ->alias('ac')
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")."agent_game ag ON ((ag.agent_id=ac.admin_id) AND (ag.app_id = ac.app_id))"
            )
            ->join(
                "LEFT JOIN ".C("DB_PREFIX")
                ."agent_game_rate agr ON ((agr.agent_id=ac.admin_id) AND (agr.app_id = ac.app_id))"
            )
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $agents = get_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function get_all_apply_agents() {
        $items = M('agent_game')
            ->field("DISTINCT agent_id,u.user_login")
            ->alias('ag')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id")
            ->order("u.id desc")
            ->select();

        return $items;
    }

    public function get_all_apply_games() {
        $items = M('agent_game')
            ->field("DISTINCT app_id,g.name as gamename")
            ->alias('ag')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")
            ->order("g.id desc")
            ->select();

        return $items;
    }

    public function setGameAllAgentRate() {
        $where = array();
        if (isset($_GET['gamename']) && ($_GET['gamename'] != '')) {
            $gamename = $_GET['gamename'];
            $where['g.name'] = array("like", "%$gamename%");
        }
        $model = M('game');
//        
        $count = $model->alias('g')->where($where)->count();
        $page = $this->page($count, 20);
        $items = $model
            ->alias('g')
            ->where($where)
            ->order("g.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function setGameAllAgentRate_post() {
        $app_id = I('id');
        $new_rate = I('new_rate');
        if (!(is_numeric($new_rate) && $new_rate > 0 && $new_rate < 1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣必须是介于0和1之间的数字"));
            exit;
        }
//        M('agent_game')->where(array("app_id"=>$app_id))->setField("agent_rate",$new_rate);
        $hs_benefit_obj = new \Huosdk\Benefit();
        $hs_benefit_obj->set_app_agent_rate($app_id, $new_rate);
//        M('game_rate')->where(array("id"=>$app_id))->setField("agent_rate",$new_rate);
        $hs_benefit_obj->set_app_agentgame_all_agentrate($app_id, $new_rate);
        $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
    }

    public function setGameAllAgentRebate_post() {
        $app_id = I('id');
        $new_rate = I('value');
        if (!(is_numeric($new_rate) && $new_rate > 0 && $new_rate < 1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "返点必须是介于0和1之间的数字"));
            exit;
        }
        M('agent_game')->where(array("app_id" => $app_id))->setField("rebate", $new_rate);
        M('game')->where(array("id" => $app_id))->setField("rebate", $new_rate);
        $this->ajaxReturn(array("error" => "0", "msg" => "设置成功"));
    }

    public function Discount() {
        $where = array();
        if (isset($_GET['appid']) && ($_GET['appid'] != -1)) {
            $appid = $_GET['appid'];
            $where['ag.app_id'] = $appid;
        }
        if (isset($_GET['agent']) && ($_GET['agent'] != '')) {
            $agentid = $_GET['agent'];
            $where['ag.agent_id'] = $agentid;
        }
        $count = M('agent_game')->alias('ag')->where($where)->count();
        $page = $this->page($count, 20);
        $items = M('agent_game')
            ->field("ag.*,u.user_nicename,u.mobile,u.user_login,g.name as gamename")
            ->alias('ag')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ag.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ag.app_id")
            ->where($where)
            ->order("ag.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $games = $this->get_all_apply_games();
        $this->assign("games", $games);
//        $agents=$this->get_all_apply_agents();
//        $this->assign("agents",$agents);
//        $this->assign("query_field",$query_field);
        $agents = get_agent_game_all_agents();
        $this->assign("agents", $agents);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function notpass() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        M('users')->where(array("id" => I('id'), "user_type" => $subagent_roleid))->setField("user_status", "3");
        $this->success("修改成功");
    }

    public function pass() {
        M('users')->where(array("id" => I('id'), "user_type" => $subagent_roleid))->setField("user_status", "2");
        Vendor("LanzSMS");
        $obj = new \LanzSMS();
        $userphone = M('users')->where(array("id" => I('id'), "user_type" => $subagent_roleid))->getField("mobile");
        $obj->sendMsg_simple($userphone, "恭喜，您已成功通过资料审核。");
        $this->success("修改成功");
    }

//    public function testSMS(){
//        Vendor("LanzSMS");
//        $obj=new \LanzSMS();
////        $userphone= M('users')->where(array("id"=>I('id'),"user_type"=>"7"))->getField("mobile");
//        $userphone='15914517759';
//        echo $userphone;
//        $obj->sendMsg_simple($userphone, "恭喜，您已成功通过资料审核。【芒果玩游戏】");
//        echo ' hello';
//    }
    public function change_agent_rate() {
        $new_rate = I('new_rate');
        $ag_id = I('id');
        if (!(is_numeric($new_rate) && $new_rate > 0 && $new_rate < 1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣必须是介于0和1之间的数字"));
            exit;
        }
//        $base=$this->get_rate_base($app_id);
//        if($new_rate < $base){
//            $this->ajaxReturn(array("error"=>"1","msg"=>"折扣不能低于基线".$base));
//            exit;
//        }
        $hs_benefit_obj = new \Huosdk\Benefit();
        $hs_benefit_obj->set_app_agentgame_agentrate($ag_id, $new_rate);
//        M('agent_game_rate')->where(array("id"=>$app_id))->setField("agent_rate",$new_rate);
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    public function change_agent_rebate() {
        $new_rate = I('new_rate');
        $app_id = I('id');
        if (!(is_numeric($new_rate) && $new_rate > 0 && $new_rate < 1)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "折扣必须是介于0和1之间的数字"));
            exit;
        }
        M('agent_game')->where(array("id" => $app_id))->setField("rebate", $new_rate);
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    public function resetPayPwd_post() {
        $id = I('id');
        $obj = new \Huosdk\Account();
        $obj->resetUserPayPwd($id);
        $this->ajaxReturn(array("error" => "0", "msg" => "重置支付密码成功"));
    }

    public function resetPayPwd() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $where = array();
        $where['_string'] = "user_type=$agent_roleid OR user_type= $subagent_roleid ";
//        if(isset($_GET['appid'])&&($_GET['appid']!=-1)){
//            $appid=$_GET['appid'];
//            $where['ag.app_id']= $appid;
//        }
//        
        if (isset($_GET['agent']) && ($_GET['agent'] != '')) {
            $agent = $_GET['agent'];
            $where['u.user_login'] = array("like", "%$agent%");
        }
//        
//        
        $count = M('users')->alias('u')->where($where)->count();
        $page = $this->page($count, 20);
        $items = M('users')
            ->alias('u')
            ->where($where)
            ->order("u.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->set_user_type($items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->assign("items", $items);
        $this->display();
    }

    public function set_user_type(&$items) {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        foreach ($items as $key => $member) {
            $type = $items[$key]['user_type'];
            if ($type == $agent_roleid) {
                $items[$key]['user_type'] = '代理';
            } else if ($type == $subagent_roleid) {
                $items[$key]['user_type'] = '下级代理';
            }
        }
    }

    public function memGmRechargeRecord() {
        $hs_data_ptbmem_obj = new \Huosdk\Data\PtbMem();
        $table_header = $hs_data_ptbmem_obj->get_table_header_admin();
        $this->assign("table_header", $table_header);
        $count = $hs_data_ptbmem_obj->get_admin_count();
        $page = $this->page($count, 10);
        $table_content = $hs_data_ptbmem_obj->get_admin_table($page->firstRow, $page->listRows);
        $this->assign("table_content", $table_content);
        $this->assign("Page", $page->show('Admin'));
        $sum_txt = $hs_data_ptbmem_obj->get_sum_admin_txt(array());
        $this->assign("table_sum", $sum_txt);
        $this->display();
    }

    public function addagent_post() {
        if (!$_POST['user_nicename']) {
            $this->ajaxReturn(array("error" => "1", "msg" => "渠道名称不能为空"));
            exit;
        }
        if (!$_POST['user_pass']) {
            $this->ajaxReturn(array("error" => "1", "msg" => "密码不能为空"));
            exit;
        }
        if (!$_POST['user_login']) {
            $this->ajaxReturn(array("error" => "1", "msg" => "帐号不能为空"));
            exit;
        }
        //判断该添加渠道名称是否已经存在
        $checknicename = M("users")->where(array("user_nicename" => $_POST["user_nicename"]))->find();
        if (!empty($checknicename)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "该渠道名称已被使用，请勿重复添加"));
            exit;
        }
        //判断该添加账号是否已经存在
        $checkusername = M("users")->where(array("user_login" => $_POST["user_login"]))->find();
        if (!empty($checkusername)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "该账号已被使用，请勿重复添加"));
            exit;
        }
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $data = array();
        $data['user_pass'] = sp_password($_POST['user_pass']);
        $data['user_login'] = $_POST['user_login'];
        $data['user_nicename'] = $_POST['user_nicename'];
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $data['user_type'] = $agent_roleid;
        $data['user_status'] = 2;
        $data['ownerid'] = 1;
        M('users')->add($data);
        $this->ajaxReturn(array("error" => "0", "msg" => "添加一级代理成功"));
    }

    public function editinfo_post() {
        $name = I('name');
        $value = I('value');
        $agent_id = I('agent_id');
        if ($name == 'qq') {
            $result = M('users')->where(array("id" => $agent_id))->setField("qq", $value);
        } else if ($name == 'mobile') {
            $result = M('users')->where(array("id" => $agent_id))->setField("mobile", $value);
        } else if ($name == 'link_man') {
            $exist = M('agent_man')->where(array("agent_id" => $agent_id))->find();
            if ($exist) {
                $result = M('agent_man')->where(array("agent_id" => $agent_id))->setField("link_man", $value);
            } else {
                $data = array();
                $data['agent_id'] = $agent_id;
                $data['link_man'] = $value;
                $result = M('agent_man')->add($data);
            }
        } else if ($name == 'paypwd') {
            $en_pypwd = pay_password($value);
            $result = M('users')->where(array("id" => $agent_id))->setField("pay_pwd", $en_pypwd);
        } else if ($name == 'loginpwd') {
            $en_loginpwd = sp_password($value);
            $result = M('users')->where(array("id" => $agent_id))->setField("user_pass", $en_loginpwd);
        }
        if ($result) {
            $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "修改失败"));
        }
    }
}

