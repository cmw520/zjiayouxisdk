<?php
/**
 * 充值统计页面
 *
 * @author
 *
 */
namespace Data\Controller;

use Common\Controller\AdminbaseController;

class PayController extends AdminbaseController {
    protected $daypaymodel, $where, $orderwhere, $roleid, $pay_model;

    function _initialize() {
        parent::_initialize();
        if (2 < $this->role_type) {
            $this->daypaymodel = M('day_agent');
            $this->where = "agent_id".$this->agentwhere;
        } else {
            $this->daypaymodel = M('day_pay');
            $this->where = '1';
        }
        $this->pay_model = M('pay');
    }

    public function index() {
        $this->_game(true, null, null, null, null, null);
        $this->_getpaydata();
        $this->display();
    }

    public function _payindex() {
        $this->display();
    }

    public function orderindex() {
        $this->_game(true, null, null, null, null, null);
//          $this->_agents();
        $this->_payway(null, false);
        $this->_cpstatus();
        $this->_paystatus();
        $this->_orderList();
        $this->display();
    }

    public function gameindex() {
        $this->_game(true, null, null, null, null, null);
        $this->_getgamedata();
        $this->display();
    }

    public function getPayname($payid = 1) {
        $pwmodel = M('payway');
        $where['id'] = $payid;
        $result = $pwmodel->getFieldById($payid, 'payname');

        return $result[0]['payname'];
    }

    /*
     * 充值记录详细
     */
    function _getpaydata() {
        $paymodel = $this->daypaymodel;
        $where_ands = array($this->where);
        $startflag = true;
        $endflag = true;
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
            $startflag = strtotime($start_time) <= $todaytime ? true : false;
        }
        if (isset($end_time) && !empty($end_time)) {
            array_push($where_ands, "`date` <= '".$end_time."'");
            $endflag = strtotime($end_time) >= $todaytime ? true : false;
        }
        $where = join(" AND ", $where_ands);
        $count = $this->daypaymodel
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field
            = "`date`,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum( `order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
        $items = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->group('date')
            ->order("date DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->select();
        $sumwhere = $this->where;
        if (!empty($start_time)) {
            $sumwhere .= " AND date>='".$start_time."'";
        }
        if (!empty($end_time)) {
            $sumwhere .= " AND date<='".$end_time."'";
        }
        $sumitems[0]['user_cnt'] = M('day_user')->where($sumwhere)->count('distinct(mem_id)');
        //今日数据
        if (($startflag && $endflag)) {
            $field
                = "count(distinct(p.mem_id)) pay_user_cnt, sum(p.amount) sum_money, count(p.id) order_cnt,
                      count(distinct (case  when m.reg_time>".$todaytime." then p.`mem_id` end)) reg_pay_cnt,
                      sum(case  when m.reg_time>".$todaytime." then p.amount end) sum_reg_money";
            $todayitem = M('pay')->alias('p')
                                 ->field($field)
                                 ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON p.mem_id=m.id")
                                 ->where("p.create_time>".$todaytime." AND p.status=2 AND m.agent_id".$this->agentwhere)
                                 ->find();
            $todayitem['date'] = date('Y-m-d');
            $todayitem['user_cnt'] = M('login_log')->where("login_time>".$todaytime." AND agent_id".$this->agentwhere)
                                                   ->count('distinct(mem_id)');
            $todayitem['reg_cnt'] = M('members')->where("reg_time>".$todaytime." AND agent_id".$this->agentwhere)
                                                ->count('id');
            $sumitems[0]['sum_money'] += $todayitem['sum_money'];
            $sumitems[0]['pay_user_cnt'] += $todayitem['pay_user_cnt'];
            $sumitems[0]['reg_pay_cnt'] += $todayitem['reg_pay_cnt'];
            $sumitems[0]['sum_reg_money'] += $todayitem['sum_reg_money'];
            $sumitems[0]['reg_cnt'] += $todayitem['reg_cnt'];
            $sumitems[0]['order_cnt'] += $todayitem['order_cnt'];
            $sumitems[0]['user_cnt'] += $todayitem['user_cnt'];
        }
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "每日数据汇总";
            $expCellName = array(
                array("date", "日期"),
                array("reg_cnt", "新增用户数"),
                array("user_cnt", "活跃用户数"),
                array("pay_user_cnt", "付费用户数"),
                array("order_cnt", "订单数量"),
                array("sum_reg_money", "新用户付费金额"),
                array("sum_money", "总付费金额")
            );
            /* BEGIN 2017/2/17 1667-1，每日数据汇总导出是空白 wuyonghong */
            $_exp_field
                = "`date`,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum( `order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
            $expTableData = $this->daypaymodel
                ->field($_exp_field)
                ->where($where)
                ->group('date')
                ->order("date DESC")
                ->select();
            /* END 2017/2/17  */
//            $export_items = $this->daypaymodel
//                ->field($field)
//                ->where($where)
//                ->group('date')
//                ->order("date DESC")
//                ->select();
//            $expTableData = $todayitem;
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
        $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->Current_page);
    }

    public function getAgentIdByName($name) {
        $obj = new \Huosdk\Account();

        return $obj->getAgentIdByName($name);
    }

    public function GetParentAgentName($agentid) {
        $obj = new \Huosdk\Account();
        $agent_role_id = $obj->getAgentRoleId();
        $subagent_role_id = $obj->getSubAgentRoleId();
        $data = M('users')->where(array("id" => $agentid, "user_type" => $agent_role_id))->getField("user_login");
        if ($data) {
            return $data;
        } else {
            $ownerid = M('users')->where(array("id" => $agentid, "user_type" => $subagent_role_id))->getField(
                "ownerid"
            );
            if ($ownerid) {
                return M('users')->where(array("id" => $ownerid, "user_type" => $agent_role_id))->getField(
                    "user_login"
                );
            }
        }
    }

    function _orderList() {
        $hf_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("parent_agent_select_with_official", $hf_filter_obj->parent_agent_select_with_official());
        $this->assign("agent_select_with_official", $hf_filter_obj->agent_select_with_official());
        $where_extra = array();
        $where_extra['_string'] = "p.agent_id".$this->agentwhere;
        $where_extra['_string'] .= " AND p.payway<>'0'";
        if (isset($_GET['payway']) && ($_GET['payway'] == "-1")) {
            $where_extra['_string'] .= " AND (p.payway='alipay' OR p.payway='spay')";
        }
        if (isset($_GET['payway']) && ($_GET['payway'] == "-2")) {
            $where_extra['_string'] .= " AND (p.payway!='alipay' AND p.payway!='spay')";
        }
        if (isset($_GET['payway']) && $_GET['payway'] && ($_GET['payway'] != "-1") && ($_GET['payway'] != "-2")) {
            $where_extra['p.payway'] = $_GET['payway'];
        }
        if (isset($_GET['cpstatus']) && ($_GET['cpstatus'])) {
            $where_extra['p.cpstatus'] = $_GET['cpstatus'];
        }
        if (isset($_GET['paystatus']) && ($_GET['paystatus'])) {
            $where_extra['p.status'] = $_GET['paystatus'];
        }
        if (isset($_GET['orderid']) && ($_GET['orderid'])) {
            $where_extra['p.order_id'] = $_GET['orderid'];
        }
        if (isset($_GET['gid']) && ($_GET['gid'])) {
            $where_extra['p.app_id'] = $_GET['gid'];
        }
        if (isset($_GET['username']) && ($_GET['username'])) {
            $where_extra['m.username'] = $_GET['username'];
        }
        if (isset($_GET['agentname']) && ($_GET['agentname'])) {
            $where_extra['u.user_login'] = $_GET['agentname'];
        }
        if (isset($_GET['agentnickname']) && ($_GET['agentnickname'])) {
            if ($_GET['agentnickname'] == "官包") {
                $where_extra['_string'] .= " AND (p.agent_id = 0 )";
//                $where_extra['p.agent_id'] = array("eq","0");
            } else {
                $where_extra['u.user_nicename'] = $_GET['agentnickname'];
            }
        }
        if (isset($_GET['parent_agentname']) && ($_GET['parent_agentname'])) {
            if ($_GET['parent_agentname'] == "官方渠道") {
                $where_extra['_string'] .= " AND (u2.user_nicename IS NULL )";
            } else {
                $where_extra['u2.user_nicename'] = $_GET['parent_agentname'];
            }
        }
        //父渠道
        if (isset($_GET['parent_agent_id']) && ($_GET['parent_agent_id'])) {
            $where_extra['u.ownerid'] = $_GET['parent_agent_id'];
        }
        //渠道
        if (isset($_GET['agent_id']) && ($_GET['agent_id'])) {
            if (1 == $_GET['agent_id']) {
                $where_extra['m.agent_id'] = 0;
            } else {
                $where_extra['m.agent_id'] = $_GET['agent_id'];
            }
        }
        if ('上周' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 - 7, date("Y")));
            $_GET['end_time'] = date("Y-m-d", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7 - 7, date("Y")));
        } else if ('本月' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
            $_GET['end_time'] = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        } else if ('本周' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
            $_GET['end_time'] = date("Y-m-d", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y")));
        } else if ('上月' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
            $_GET['end_time'] = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), 0, date("Y")));
        } else if ('昨日' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
            $_GET['end_time'] = date("Y-m-d", mktime(23, 59, 59, date("m"), date("d") - 1, date("Y")));
        } else if ('今日' == $_GET['submit']) {
            $_GET['start_time'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
            $_GET['end_time'] = date("Y-m-d", mktime(23, 59, 59, date("m"), date("d"), date("Y")));
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where_extra, "p.create_time");
        $where = $where_extra;
        $count = $this->pay_model
            ->alias("p")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->join("left join ".C('DB_PREFIX')."users u2 ON u2.id = u.ownerid")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "p.order_id, p.amount, m.username,p.agent_id, p.payway, u.user_login agentname,p.real_amount, "
                 ."u.user_nicename agentnickname, g.name gamename,p.status,p.cpstatus, p.create_time, p.app_id,"
                 ."u2.user_nicename as parent_agentname";
        $items = $this->pay_model
            ->alias("p")
            ->field($field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->join("left join ".C('DB_PREFIX')."users u2 ON u2.id = u.ownerid")
            ->order("p.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($items as $k => $v) {
//            $items[$k]['parent_agentname'] = $this->GetParentAgentName($v['agent_id']);
            if (!$items[$k]['parent_agentname']) {
                $items[$k]['parent_agentname'] = "官方渠道";
            }
        }
        $sum_where = array();
        $sum_where = $where;
        if (!isset($sum_where['p.status'])) {
            $sum_where['p.status'] = 2;
        }
        $sums = $this->pay_model
            ->alias("p")
            ->where($sum_where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->join("left join ".C('DB_PREFIX')."users u2 ON u2.id = u.ownerid")
            ->sum('amount');
        $realsum = $this->pay_model
            ->alias("p")
            ->where($sum_where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->join("left join ".C('DB_PREFIX')."users u2 ON u2.id = u.ownerid")
            ->sum('real_amount');
//        $real_sums = $this->pay_model
//                ->alias("p")
//                ->where($sum_where)
//                ->join("left join " . C('DB_PREFIX') . "game g ON p.app_id = g.id")
//                ->join("left join " . C('DB_PREFIX') . "users u ON p.agent_id = u.id")
//                ->join("left join " . C('DB_PREFIX') . "members m ON p.mem_id = m.id")
//                ->sum('real_amount');
        if (!$sums) {
            $sums = '0';
        }
//        if (!$real_sums) {
//            $real_sums = '0';
//        }
        if ("导出数据" == $_GET['submit']) {
            $payways = $this->_payway2();
            $fname = "订单数据";
            $field_excel
                = "p.order_id as 订单号, p.amount as 金额, m.username as 玩家帐号,p.payway as 支付方式,p.agent_id as 父渠道帐号, u.user_login as 渠道帐号, "
                  ." g.name as 游戏名称,p.status as 状态,p.cpstatus as 回调状态, p.create_time as 创建时间";
            $export_items = $this->pay_model
                ->alias("p")
                ->field($field_excel)
                ->where($where)
                ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
                ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
                ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
                ->join("left join ".C('DB_PREFIX')."users u2 ON u2.id = u.ownerid")
                ->order("p.id DESC")
                ->select();
            foreach ($export_items as $k => $item) {
                $export_items[$k]['支付方式'] = $payways[$item['支付方式']];
                $export_items[$k]['创建时间'] = date("Y-m-d H:i:s", $item['创建时间']);
                if ($item['回调状态'] == '2') {
                    $export_items[$k]['回调状态'] = "回调成功";
                } else {
                    $export_items[$k]['回调状态'] = "回调失败或者待支付";
                }
                if ($item['状态'] == '2') {
                    $export_items[$k]['状态'] = "成功";
                } else if ($item['状态'] == '3') {
                    $export_items[$k]['状态'] = "失败";
                } else if ($item['状态'] == '1') {
                    $export_items[$k]['状态'] = "待支付";
                }
                $export_items[$k]['父渠道帐号'] = $this->GetParentAgentName($item['父渠道帐号']);
            }
            $this->export_do($export_items, $fname);
            exit;
        }
        $this->assign("total_rows", $count);
        $this->assign("realsum", $realsum);
        $this->assign("sums", $sums);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    function _getgamedata() {
        $paymodel = M('day_agentgame');
        $where_ands = array();
        array_push($where_ands, $this->where);
        $sumwhere = $this->where;
        $regwehre = $this->where;
        $startflag = true;
        $endflag = true;
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
            $startflag = strtotime($start_time) <= $todaytime ? true : false;
        }
        if (isset($end_time) && !empty($end_time)) {
            array_push($where_ands, "`date` <= '".$end_time."'");
            $endflag = strtotime($end_time) >= $todaytime ? true : false;
        }
        if (isset($_GET['gid']) && !empty($_GET['gid'])) {
            array_push($where_ands, "`appid` = '".$_GET['gid']."'");
            $sumwhere = "`appid` = '".$_GET['gid']."'";
            $regwehre = "`appid` = '".$_GET['gid']."'";
        }
        $where = join(" AND ", $where_ands);
        $count = $paymodel
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        $field
            = "`date`, `app_id`, sum(`user_cnt`) user_cnt, sum(`sum_money`) summoney, sum(`pay_user_cnt`) paycnt,
                sum(`reg_pay_cnt`), sum(`sum_reg_money`) sumregmoney, sum(`reg_cnt`) reg_cnt";
        $sumfield
            = "sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `summoney`,sum(`pay_user_cnt`) `paycnt`,sum(`reg_pay_cnt`) `regpaycnt`,sum(`sum_reg_money`) `sumregmoney`,sum(`reg_cnt`) `reg_cnt`";
        $items = $paymodel
            ->field($field)
            ->where($where)
            ->group('date, app_id')
            ->order("date DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $paymodel
            ->field($sumfield)
            ->where($where)
            ->select();
        if (!empty($start_time)) {
            $sumwhere .= " AND date>='".$start_time."'";
            $regwehre .= " AND login_date>='".$start_time."'";
//             $regwehre .= " AND reg_time>='".strtotime($start_time)."'";
        }
        if (!empty($end_time)) {
            $sumwhere .= " AND date<='".$end_time."'";
            $regwehre .= " AND login_date<='".$end_time."'";
//             $regwehre .= " AND reg_time<'".(strtotime($end_time)+86400)."'";
        }
        $sumitems[0]['user_cnt'] = M('day_pay_user')->where($regwehre)->count('distinct(mem_id)');
        $sumitems[0]['reg_cnt'] = M('day_pay_user')->where($regwehre." AND login_date=reg_date")->count(
            'distinct(mem_id)'
        );
        //今日数据
        if (($startflag && $endflag)) {
//             $field = "count(distinct(p.userid)) paycnt, sum(p.amount) summoney,
//                       count(distinct (case  when m.reg_time>1463328000 then p.`userid` else NULL end)) regpaycnt,
//                       sum(case  when m.reg_time>".$todaytime." then p.amount else 0 end) sumregmoney";
//             $todayitem = M('pay')->alias('p')
//                         ->field($field)
//                         ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON p.userid=m.id")
//                         ->where("p.create_time>".$todaytime." AND p.status=1")
//                         ->find();
//             $todayitem['date'] = date('Y-m-d');
//             $todayitem['user_cnt'] = M('logininfo')->where("login_time>".$todaytime)->count('distinct(userid)');
//             $todayitem['reg_cnt'] = M('members')->where("reg_time>".$todaytime)->count('id');
//             $sumitems[0]['summoney'] += $todayitem['summoney'];
//             $sumitems[0]['paycnt'] += $todayitem['paycnt'];
//             $sumitems[0]['regpaycnt'] += $todayitem['regpaycnt'];
//             $sumitems[0]['sumregmoney'] += $todayitem['sumregmoney'];
//             $sumitems[0]['reg_cnt'] += $todayitem['reg_cnt'];
//             $sumitems[0]['user_cnt'] += $todayitem['user_cnt'];
        }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
//         $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    function _getgameagentdata() {
        $model = M('dayagentgame');
        $start = $_GET['start_time'];
        $end = $_GET['end_time'];
        if ('今日' == $_GET['date_time']) {
            $count = 1;
            $start = date("Y-m-d");
            $end = date("Y-m-d");
        } elseif ('七日' == $_GET['date_time']) {
            $count = 7;
            $start = date("Y-m-d", strtotime("-6 day"));
            $end = date("Y-m-d");
        } elseif ('当月' == $_GET['date_time']) {
            $count = date('d');
            $start = date("Y-m-01");
            $end = date("Y-m-d");
        } elseif ('30天' == $_GET['date_time']) {
            $count = 30;
            $start = date("Y-m-d", strtotime("-29 day"));
            $end = date("Y-m-d");
        }
        $agents = $this->_getOwnerAgents();
        $where = " agentid in ($agents) ";
        if (isset($_GET['gid']) && !empty($_GET['gid'])) {
            $where .= " AND appid=".$_GET['gid'];
        }
        $startflag = true;
        $endflag = true;
        if (isset($start) && !empty($start)) {
            $where .= " AND to_days(date) >= to_days('{$start}')";
            $startflag = strtotime($start) < mktime(0, 0, 0, date("m"), date("d"), date("Y")) ? true : false;
        }
        if (isset($end) && !empty($end)) {
            $where .= " AND to_days(date) <=  to_days('{$end}')";
            $endflag = strtotime($end) >= mktime(0, 0, 0, date("m"), date("d"), date("Y")) ? true : false;
        }
        $count = 1;
        if ($startflag) {
            $count = $model
                ->where($where)->count();
        }
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        if ($startflag) {
            $sumsql
                = "SELECT  SUM(`user_cnt`) `user_cnt`,SUM(`summoney`) `summoney`,SUM(`paycnt`) `paycnt`,SUM(`regpaycnt`) `regpaycnt`,SUM(`sumregmoney`) `sumregmoney`,SUM(`reg_cnt`) `reg_cnt`
					FROM (SELECT appid,agentid,date,user_cnt,summoney,paycnt,regpaycnt,sumregmoney,reg_cnt FROM   ".
                  C('DB_PREFIX')."dayagentgame
					UNION ALL
					SELECT appid,agentid,date,user_cnt,summoney,paycnt,regpaycnt,sumregmoney,reg_cnt  FROM  ".
                  C('DB_PREFIX')."tagentgamepayview) a"
                  ." where ".$where." limit ".$page->firstRow.','.$page->listRows;
            $sumitems = $model->query($sumsql);
            $sql
                = "SELECT  `date`, `appid`, SUM(`user_cnt`) `user_cnt`, SUM(`summoney`) `summoney`, SUM(`paycnt`) `paycnt`, SUM(`regpaycnt`) `regpaycnt`, SUM(`sumregmoney`) `sumregmoney`, SUM(`reg_cnt`) `reg_cnt`
					FROM (SELECT appid,agentid, date,user_cnt,summoney,paycnt,regpaycnt,sumregmoney,reg_cnt FROM   ".
                  C('DB_PREFIX')."dayagentgame
					UNION ALL
					SELECT appid,agentid, date,user_cnt,summoney,paycnt,regpaycnt,sumregmoney,reg_cnt  FROM  ".
                  C('DB_PREFIX')."tagentgamepayview) a"
                  ." where ".$where." GROUP BY appid,date ORDER BY date desc limit ".$page->firstRow.','
                  .$page->listRows;
            $items = $model->query($sql);
        }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
        $this->assign("fromget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    function repairorder() {
        $order_id = I('get.orderid');
        if (empty($order_id)) {
            $this->error("参数错误");
            exit;
        }
        // 1 通过订单号查询订单信息        
        $paydata = M('pay')->where(array('order_id' => $order_id))->find();
        if (empty($paydata)) {
            $this->error("参数错误");
            exit;
        }
        $_pc_map['pay_id'] = $paydata['id'];
        $_paycp_info = M('pay_cpinfo')->where($_pc_map)->find();
        $_cpurl = $_paycp_info['cpurl'];
        $_param = $_paycp_info['params'];
        //2 验证是否已经充值成功并且回调失败
        if (2 != $paydata['cpstatus'] && 2 == $paydata['status']) {
            $cpstatus = 3;
            //2.2.3 通知CP
            $i = 0;
            while (1) {
                $cp_rs = $this->payback($_cpurl, $_param);
                if ($cp_rs > 0) {
                    $cpstatus = 2;
                    break;
                } else {
                    $cpstatus = 3;
                    $i++;
                    sleep(2);
                }
                if ($i == 3) {
                    break;
                }
            }
            if (2 == $cpstatus) {
                $rs = M('pay')->where(array('order_id' => $order_id))->setField('cpstatus', $cpstatus);
                $rrs = M('pay_cpinfo')->where(array('pay_id' => $paydata['id']))->setField('cpstatus', $cpstatus);
                if ($rs && $rrs) {
                    $this->success("补单成功");
                    exit;
                }
            }
        }
        $this->error("补单失败");
    }

    /**
     * 执行一个 HTTP 请求
     *
     * @param $url
     * @param $params
     *
     * @return int
     */
    public static function payback($url, $params) {
        $curl = curl_init(); //初始化curl
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); //post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); //设置传送的参数
        curl_setopt(
            $curl, CURLOPT_HTTPHEADER, array(
                     'Content-Type: application/x-www-form-urlencoded',
                     'Content-Length: '.strlen($params))
        );
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3); //设置等待时间
        $rs = curl_exec($curl); //运行curl
        $rs = strtoupper($rs);
        if ($rs == 'SUCCESS') {
            $result = 1;
        } else {
            $result = 0;
        }
        curl_close($curl); //关闭curl
        return $result;
    }

    function _payway($type = null, $option = true) {
        $cates = array(
            "" => "全部"
        );
        $payways = M('payway')->getField("payname,realname", true);
        if ($option) {
            $payways = $cates + $payways;
        }
        $this->assign("payways", $payways);
    }

    function _payway2($type = null, $option = true) {
        $cates = array(
            "" => "全部"
        );
        $payways = M('payway')->getField("payname,realname", true);
        if ($option) {
            $payways = $cates + $payways;
        }

        return $payways;
    }

    function _paystatus() {
        $cates = array(
            "0" => "全部",
            "1" => "待支付",
            "2" => "支付成功",
            "3" => "支付失败",
        );
        $this->assign("paystatuss", $cates);
    }

    function _cpstatus() {
        $cates = array(
            "0" => "全部",
            "1" => "待支付",
            "2" => "回调成功",
            "3" => "回调失败",
        );
        $this->assign("cpstatuss", $cates);
    }

    public function _where_order_id(&$where, $field) {
        $name = "orderid";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $orderid = $_GET[$name];
            $where[$field] = array("eq", "$orderid");
        }
    }

    public function _where_agent_name(&$where, $field) {
        $name = "agentname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $agentname = $_GET[$name];
            $where[$field] = array("like", "%$agentname%");
        }
    }

    public function _where_admin_name(&$where, $field1, $field2) {
        $name = "adminname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $adminname = trim($_GET[$name]);
            if ($adminname == "平台官方") {
                $where[$field1] = array("eq", "0");
            } else {
                $where[$field2] = array("like", "%$adminname%");
            }
        }
    }

    public function AdminForAgent() {
        $agent_id = get_current_admin_id();
        $where = array("agc.agent_id" => $agent_id);
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "agc.create_time");
        $this->_where_order_id($where, "agc.order_id");
        $this->_where_agent_name($where, "u.user_login");
        $this->_where_admin_name($where, 'agc.admin_id', 'ua.user_login');
        $count = M('gm_agentcharge')
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('gm_agentcharge')
            ->field("agc.*,u.user_login as agentname,ua.user_login as adminname")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->order("agc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach ($items as $k => $item) {
            if ($item['admin_id'] == 0) {
                $items[$k]['adminname'] = "平台官方";
            }
        }
        $this->assign("orders", $items);
        $sums = M('gm_agentcharge')
            ->field("sum(agc.money) as total")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->select();
        $this->assign("sums", $sums[0]['total']);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function AgentForSub() {
        $agent_id = get_current_admin_id();
        $obj = new \Huosdk\Account();
        $info = $obj->get_agent_info_by_id($agent_id);
        $agent_name = $info['user_login'];
        $where = array("agc.admin_id" => $agent_id);
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "agc.create_time");
        $name = "orderid";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $orderid = $_GET[$name];
            $where['agc.order_id'] = array("eq", "$orderid");
        }
//        $name="agentname";
//        if(isset($_GET[$name])&&$_GET[$name]){
//            $agentname=$_GET[$name];
//            $where['u.user_login']=array("like","%$agentname%");
//        }
        $name = "subname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $subname = $_GET[$name];
            $where['u.user_login'] = array("like", "%$subname%");
        }
        $count = M('gm_agentcharge')
            ->field("agc.*,u.user_login as subname,g.name as gamename")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('gm_agentcharge')
            ->field("agc.*,u.user_login as subname,g.name as gamename")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->where($where)
            ->order("agc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sums = M('gm_agentcharge')
            ->field("sum(agc.money) as total")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->where($where)
            ->select();
//        print_r($sums);
        $this->assign("sums", $sums[0]['total']);
        $this->assign("agentname", $agent_name);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function AgentForMmeber() {
        $agent_id = get_current_admin_id();
        $obj = new \Huosdk\Agent($agent_id);
        $subids = $obj->getMySubAgentsIds();
        $subids[] = $agent_id;
        $sub_txt = join(",", $subids);
        $where = array();
        $where['_string'] = "gc.admin_id IN ($sub_txt)";
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gc.create_time");
        $name = "agentname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $agentname = $_GET[$name];
            $where['u.user_login'] = array("like", "%$agentname%");
        }
        $name = "membername";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $membername = $_GET[$name];
            $where['m.username'] = array("like", "%$membername%");
        }
        $name = "orderid";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $orderid = $_GET[$name];
            $where['gc.order_id'] = array("eq", "$orderid");
        }
        $count = M('gm_charge')
            ->field("gc.*,u.user_login as agentname,g.name as gamename,m.username as membername")
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('gm_charge')
            ->field("gc.*,u.user_login as agentname,g.name as gamename,m.username as membername")
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->order("gc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sums = M('gm_charge')
            ->field("sum(gc.money) as total")
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->select();
        $this->assign("sums", $sums[0]['total']);
//        foreach($items as $k => $item){
//            if($item['admin_id']==$agent_id){
//                $items[$k]['agent_type']='代理';
//            }else{
//                $items[$k]['agent_type']='下级代理';
//            }
//        }
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function rebateorder() {
        $this->_game(true, null, null, null, null, null);
//          $this->_agents();
        $this->_payway();
        $this->_orderList(2);
        $this->display();
    }

    public function export() {
        $fname = "订单数据";
        $items = array();
        $field
            = "p.order_id as 订单号, p.amount as 金额, m.username as 帐号 ,p.agent_id, p.payway as 充值方式, u.user_login agentname, "
              ."u.user_nicename agentnickname, g.name gamename,p.status as 支付状态,p.cpstatus, p.create_time as 时间, p.app_id";
        $items = $this->pay_model
            ->alias("p")
            ->field($field)
//        ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->order("p.id DESC")
            ->limit(0, 1000)
            ->select();
        foreach ($items as $k => $v) {
            $items[$k]['时间'] = date("Y-m-d H:i:s", $v['时间']);
            if ($v['支付状态'] == '1') {
                $items[$k]['支付状态'] = '待支付';
            } else if ($v['支付状态'] == '2') {
                $items[$k]['支付状态'] = '成功';
            } else if ($v['支付状态'] == '3') {
                $items[$k]['支付状态'] = '失败';
            }
        }
        $this->export_do($items, $fname);
//        echo 'hi';
    }

    public function export_do($items, $file_name = "后台数据导出") {
        Vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
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
}
