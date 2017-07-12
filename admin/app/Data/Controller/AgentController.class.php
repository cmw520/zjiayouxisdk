<?php
/**
 * 渠道数据统计
 *
 * @author
 *
 */
namespace Data\Controller;

use Common\Controller\AdminbaseController;

class AgentController extends AdminbaseController {
    protected $game_model, $where, $daymodel;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->daymodel = M('day_agent');
        if (2 < $this->role_type) {
            $this->where = "agent_id".$this->agentwhere;
        } else {
            $this->where = "1 ";
        }
    }

    /*
     * 渠道数据
    */
    public function dataindex() {
        $this->_agents();
        $this->_getAgentdata();
        $this->display();
    }

    /*
     * 渠道专员数据
     */
    public function marketindex() {
        $this->_marketList();
        $this->display();
    }

    //渠道数据详细
    function _getAgentdata() {
        $where_ands = array();
        $where_ends = array_push($where_ands, $this->where);
        $bflagstart = true;
        $bflagend = true;
        if ('今日' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d");
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('七日' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d", strtotime("-6 day"));
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('当月' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-01");
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('30天' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d", strtotime("-29 day"));
            $_GET['end_time'] = date("Y-m-d");
        }
        $todaytime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $start_time = trim(I('get.start_time'));
        $end_time = trim(I('get.end_time'));
        if (isset($start_time) && !empty($start_time)) {
            array_push($where_ands, "`date` >= '".$start_time."'");
            $bflagstart = strtotime($start_time) <= $todaytime ? true : false;
        }
        if (isset($end_time) && !empty($end_time)) {
            array_push($where_ands, "`date` <= '".$end_time."'");
            $bflagend = strtotime($end_time) >= $todaytime ? true : false;
        }
        $agent_id = I('get.agent_id/d', 0);
        if (isset($agent_id) && !empty($agent_id)) {
            $role_type = $this->_get_role_type($agent_id);
            if (4 == $role_type) {
                $wherestr = " agent_id=".$agent_id;
            } else if ($role_type == 3) {
                $userids = $this->_getOwnerAgents($agent_id);
                $wherestr = " agent_id in (".$userids.") ";
            } else {
                $wherestr = " agent_id < 0 ";
            }
            $_GET['agent_id'] = $agent_id;
            array_push($where_ands, $wherestr);
        }
        $where = join(" AND ", $where_ands);
        $count = $this->daymodel
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        $field
            = "`date`,`agent_id`,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum(`order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
        $items = $this->daymodel
            ->alias('d')
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=agent_id")
            ->field($field)
            ->where($where)
            ->group('date, agent_id')
            ->order("date DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $this->daymodel
            ->field($field)
            ->where($where)
            ->select();
//         if(!empty($start_time)){
//             $sumwhere .= " AND date>='".$start_time."'";
//             $regwehre .= " AND login_date>='".$start_time."'";
// //             $regwehre .= " AND reg_time>='".strtotime($start_time)."'";
//         }
//         if(!empty($end_time)){
//             $sumwhere .= " AND date<='".$end_time   ."'";
//             $regwehre .= " AND login_date<='".$end_time   ."'";
// //             $regwehre .= " AND reg_time<'".(strtotime($end_time)+86400)."'";
//         }
//         $sumitems[0]['user_cnt'] = M('daypayuser')->where($regwehre)->count('distinct(mem_id)');
// //         $sumitems[0]['reg_cnt'] = M('daypayuser')->where($regwehre." AND login_date=reg_date")->count('distinct(userid)');
// //         $sumitems[0]['reg_cnt'] = M('dayagentgame')->where($regwehre." AND login_date=reg_date")->count('distinct(userid)');
//         //今日数据
//         if (($bflagstart && $bflagend) ){
// //             $field = "count(distinct(p.userid)) paycnt, sum(p.amount) summoney,
// //                       count(distinct (case  when m.reg_time>1463328000 then p.`userid` else NULL end)) regpaycnt,
// //                       sum(case  when m.reg_time>".$todaytime." then p.amount else 0 end) sumregmoney";
// //             $todayitem = M('pay')->alias('p')
// //                         ->field($field)
// //                         ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON p.userid=m.id")
// //                         ->where("p.create_time>".$todaytime." AND p.status=1")
// //                         ->find();
// //             $todayitem['date'] = date('Y-m-d');
// //             $todayitem['user_cnt'] = M('login_log')->where("login_time>".$todaytime)->count('distinct(userid)');
// //             $todayitem['reg_cnt'] = M('members')->where("reg_time>".$todaytime)->count('id');
// //             $sumitems[0]['summoney'] += $todayitem['summoney'];
// //             $sumitems[0]['paycnt'] += $todayitem['paycnt'];
// //             $sumitems[0]['regpaycnt'] += $todayitem['regpaycnt'];
// //             $sumitems[0]['sumregmoney'] += $todayitem['sumregmoney'];
// //             $sumitems[0]['reg_cnt'] += $todayitem['reg_cnt'];
// //             $sumitems[0]['user_cnt'] += $todayitem['user_cnt'];
//         }
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            /* BEGIN 2017/2/17 1667-2，渠道数据导出渠道账户显示的是id wuyonghong */
            $_end_time = $end_time;
            if (empty($end_time)) {
                $_end_time = $todaytime;
            }
            if (empty($start_time)) {
                $_start_time = 0;
            } else {
                $_start_time = strtotime($start_time);
            }
            $expTitle = "渠道数据 ".date("Ymd", $_start_time)."-".date("Ymd", $_end_time);
            $expCellName = array(
                array("date", "日期"),
                array("agent_name", "渠道账号"),
                array("agent_nicename", "渠道昵称"),
                array("reg_cnt", "新增用户数"),
                array("user_cnt", "活跃用户数"),
                array("pay_user_cnt", "付费用户数"),
                array("order_cnt", "订单数"),
                array("sum_reg_money", "新用户付费金额"),
                array("sum_money", "总付费金额"),
                array("pay_user_cnt", "总付费率"),
                array("sum_reg_money", "注册APRU"),
                array("sum_money", "活跃ARPU"),
                array("sum_money", "付费ARPU")
            );
            $_export_field
                = "`date`,`agent_id`,u.user_login agent_name,u.user_nicename agent_nicename,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum(`order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
            $export_items = $this->daymodel
                ->alias('d')
                ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=agent_id")
                ->field($_export_field)
                ->where($where)
                ->group('date, agent_id')
                ->order("date DESC")
                ->select();
            /* END  */
            $expTableData = $export_items;
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
//         $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    function _marketList() {
        $cates = array(
            "0" => "全部渠道"
        );
        $agents = array();
        $aidarr = M('role')->where(array('role_type' => 3, 'status' => 1))->getField('id', true);
        $aidstr = implode(',', $aidarr);
        if ($aidstr) {
            $where['user_type'] = array('in', $aidstr);
            $agents = M('users')->where($where)->getField("id,user_login agentname", true);
        }
        if ($agents) {
            $agents = $cates + $agents;
        } else {
            return null;
        }
        $this->assign("agents", $agents);
        $agentarr = M('users')->where($where)->getField("id", true);
        $agentstr = implode(',', $agentarr);
        if (empty($agentstr)) {
            $where_ands = array("u.ownerid = ''");
            $where_self = array("u.id = ''");
        } else {
            $where_ands = array("u.ownerid in ($agentstr)");
            $where_self = array("u.id in ($agentstr)");
        }
//         $where_ands = array_push($where_ands, $this->where);
//         $where_self = array_push($where_self, $this->where);
        $bflagstart = true;
        $bflagend = true;
        if ('今日' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d");
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('七日' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d", strtotime("-6 day"));
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('当月' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-01");
            $_GET['end_time'] = date("Y-m-d");
        } elseif ('30天' == $_GET['date_time']) {
            $_GET['start_time'] = date("Y-m-d", strtotime("-29 day"));
            $_GET['end_time'] = date("Y-m-d");
        }
        $todaytime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $start_time = trim(I('get.start_time'));
        $end_time = trim(I('get.end_time'));
        if (isset($start_time) && !empty($start_time)) {
            array_push($where_ands, "`date` >= '".$start_time."'");
            array_push($where_self, "`date` >= '".$start_time."'");
            $bflagstart = strtotime($start_time) <= $todaytime ? true : false;
        }
        if (isset($end_time) && !empty($end_time)) {
            array_push($where_ands, "`date` <= '".$end_time."'");
            array_push($where_self, "`date` <= '".$end_time."'");
            $bflagend = strtotime($end_time) >= $todaytime ? true : false;
        }
        $agent_id = I('get.agent_id/d', 0);
        if (isset($agent_id) && !empty($agent_id)) {
            $_GET['agent_id'] = $agent_id;
            $wherestr = " u.ownerid  = ".$agent_id;
            array_push($where_ands, $wherestr);
            $wherestrself = " u.id  = ".$agent_id;
            array_push($where_self, $wherestrself);
        }
        $where = join(" AND ", $where_ands);
        $whereself = join(" AND ", $where_self);
//         $count = $this->daymodel
// 		->alias('d')
//         ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id")
//         ->where($where)
//         ->count();
        $countsql
            = "select `date`,agent_id,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,sum(`pay_user_cnt`) `pay_user_cnt`,sum(`order_cnt`)
                `order_cnt`,sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,sum(`reg_cnt`) `reg_cnt` from
                (SELECT `date`,u.id agent_id,`user_cnt`,`sum_money`,`pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt`
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$whereself."
                UNION ALL
                SELECT `date`,u.ownerid agent_id,`user_cnt`,`sum_money`, `pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt`
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$where." ) aa
                group by date,agent_id order by date desc";
        $list = $this->daymodel->query($countsql);
        $count = count($list);
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        $field
            = "`date`,u.ownerid agent_id,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum(`order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
//         $items = $this->daymodel
//                 ->alias('d')
//                 ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id")
//                 ->field($field)
//                 ->where($where)
//                 ->group('date, u.ownerid')
//                 ->order("date DESC")
//                 ->limit($page->firstRow . ',' . $page->listRows)
//                 ->select();
        $sql
            = "select `date`,agent_id,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,sum(`pay_user_cnt`) `pay_user_cnt`,sum(`order_cnt`)
                 `order_cnt`,sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,sum(`reg_cnt`) `reg_cnt` from
                (SELECT `date`,u.id agent_id,`user_cnt`,`sum_money`,`pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt` 
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$whereself."
                UNION ALL
                SELECT `date`,u.ownerid agent_id,`user_cnt`,`sum_money`, `pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt` 
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$where." ) aa
                group by date,agent_id order by date desc
                LIMIT ".$page->firstRow.','.$page->listRows;
        $items = $this->daymodel->query($sql);
//         $sumitems = $this->daymodel
// 					->alias('d')
// 					->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id")
//                     ->field($field)
//                     ->where($where)
//                     ->select();
        $sumsql
            = "select `date`,agent_id,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,sum(`pay_user_cnt`) `pay_user_cnt`,sum(`order_cnt`)
                 `order_cnt`,sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,sum(`reg_cnt`) `reg_cnt` from
                (SELECT `date`,u.id agent_id,`user_cnt`,`sum_money`,`pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt`
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$whereself."
                UNION ALL
                SELECT `date`,u.ownerid agent_id,`user_cnt`,`sum_money`, `pay_user_cnt`,
                `order_cnt`,`reg_pay_cnt`,`sum_reg_money`,`reg_cnt`
                FROM ".C('DB_PREFIX')."day_agent d LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=d.agent_id  WHERE "
              .$where." ) aa";
        $sumitems = $this->daymodel->query($sumsql);
//         if(!empty($start_time)){
//             $sumwhere .= " AND date>='".$start_time."'";
//             $regwehre .= " AND login_date>='".$start_time."'";
// //             $regwehre .= " AND reg_time>='".strtotime($start_time)."'";
//         }
//         if(!empty($end_time)){
//             $sumwhere .= " AND date<='".$end_time   ."'";
//             $regwehre .= " AND login_date<='".$end_time   ."'";
// //             $regwehre .= " AND reg_time<'".(strtotime($end_time)+86400)."'";
//         }
//         $sumitems[0]['user_cnt'] = M('daypayuser')->where($regwehre)->count('distinct(mem_id)');
// //         $sumitems[0]['reg_cnt'] = M('daypayuser')->where($regwehre." AND login_date=reg_date")->count('distinct(userid)');
// //         $sumitems[0]['reg_cnt'] = M('dayagentgame')->where($regwehre." AND login_date=reg_date")->count('distinct(userid)');
//         //今日数据
//         if (($bflagstart && $bflagend) ){
// //             $field = "count(distinct(p.userid)) paycnt, sum(p.amount) summoney,
// //                       count(distinct (case  when m.reg_time>1463328000 then p.`userid` else NULL end)) regpaycnt,
// //                       sum(case  when m.reg_time>".$todaytime." then p.amount else 0 end) sumregmoney";
// //             $todayitem = M('pay')->alias('p')
// //                         ->field($field)
// //                         ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON p.userid=m.id")
// //                         ->where("p.create_time>".$todaytime." AND p.status=1")
// //                         ->find();
// //             $todayitem['date'] = date('Y-m-d');
// //             $todayitem['user_cnt'] = M('login_log')->where("login_time>".$todaytime)->count('distinct(userid)');
// //             $todayitem['reg_cnt'] = M('members')->where("reg_time>".$todaytime)->count('id');
// //             $sumitems[0]['summoney'] += $todayitem['summoney'];
// //             $sumitems[0]['paycnt'] += $todayitem['paycnt'];
// //             $sumitems[0]['regpaycnt'] += $todayitem['regpaycnt'];
// //             $sumitems[0]['sumregmoney'] += $todayitem['sumregmoney'];
// //             $sumitems[0]['reg_cnt'] += $todayitem['reg_cnt'];
// //             $sumitems[0]['user_cnt'] += $todayitem['user_cnt'];
//         }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
//         $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    public function agentgrant() {
        $this->_agents();
        $this->_game(true, 2, 2);
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $count = 1;
        $where_ands = array("gc.payway = 'ptb' and gc.status = 2 ");
        $start_time = trim(I('get.start_time'));
        $end_time = trim(I('get.end_time'));
        if (isset($start_time) && !empty($start_time)) {
            $start_time = strtotime($start_time." 00:00:00");
            array_push($where_ands, "gc.create_time >= '".$start_time."'");
        }
        if (isset($end_time) && !empty($end_time)) {
            $end_time = strtotime($end_time." 23:59:59");
            array_push($where_ands, "gc.create_time <= '".$end_time."'");
        }
        $agent_id = I("get.agent_id", 0);
        $app_id = I("get.app_id", 0);
        $username = I("get.username", "");
        if (isset($agent_id) && !empty($agent_id)) {
            array_push($where_ands, "gc.admin_id = '".$agent_id."'");
        }
        if (isset($app_id) && !empty($app_id)) {
            array_push($where_ands, "gc.app_id = '".$app_id."'");
        }
        if (isset($username) && !empty($username)) {
            array_push($where_ands, "m.username = '".$username."'");
        }
        $where = join(" AND ", $where_ands);
        $count = M("gm_charge")
            ->alias("gc")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON gc.app_id=g.id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON gc.admin_id=u.id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON m.id=gc.mem_id")
            ->where($where)
            ->count();
        $page = $this->page($count, $rows);
        $filed
            = "date_format(from_unixtime(gc.create_time),'%Y-%m-%d %H:%i:%S') as create_time,
        g.name as gamename,u.user_login as agentname,m.username,gc.gm_cnt,gc.money,gc.discount";
        $items = M("gm_charge")
            ->alias("gc")
            ->field($filed)
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON gc.app_id=g.id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON gc.admin_id=u.id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON m.id=gc.mem_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("items", $items);
        $this->assign("Page", $page->show("Admin"));
        $this->assign("formget", $_GET);
        $this->display();
    }
}
