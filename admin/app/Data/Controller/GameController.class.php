<?php
/**
 * 游戏数据
 *
 * @author
 *
 */
namespace Data\Controller;

use Common\Controller\AdminbaseController;

class GameController extends AdminbaseController {
    protected $daypaymodel, $where, $orderwhere, $roleid;

    function _initialize() {
        parent::_initialize();
    }

    public function gameindex() {
        if (2 < $this->role_type) {
            $this->daypaymodel = M('day_agentgame');
            $this->where = "agent_id".$this->agentwhere;
            $this->_agdataList();
        } else {
            $this->daypaymodel = M('day_game');
            $this->_gamedata();
        }
        $this->_game(true, null, null, null, null, null);
        $this->display();
    }

    function _agdataList() {
        $where_ands = array($this->where);
        $sumwhere = $this->where;
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
        if (isset($_GET['app_id']) && !empty($_GET['app_id'])) {
            array_push($where_ands, "`app_id` = '".$_GET['app_id']."'");
            $sumwhere = "`app_id` = '".$_GET['app_id']."'";
        }
        $where = join(" AND ", $where_ands);
        $count = $this->daypaymodel
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        $field
            = "`date`,`app_id`,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum( `order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
        $items = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->group('date,app_id')
            ->order("date DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->select();
        if (!empty($start_time)) {
            $sumwhere .= " AND login_date>='".$start_time."'";
        }
        if (!empty($end_time)) {
            $sumwhere .= " AND login_date<='".$end_time."'";
        }
        $sumitems[0]['user_cnt'] = M('day_pay_user')->where($sumwhere)->count('distinct(mem_id)');
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
        $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    //获取总体游戏数据
    function _gamedata() {
        $where_ands = array();
        $sumwhere = "1";
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
        if (isset($_GET['app_id']) && !empty($_GET['app_id'])) {
            array_push($where_ands, "`app_id` = '".$_GET['app_id']."'");
            $sumwhere = "`app_id` = '".$_GET['app_id']."'";
        }
        $where = join(" AND ", $where_ands);
        $count = $this->daypaymodel
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page = $this->page($count, $rows);
        $field
            = "`date`,`app_id`,sum(`user_cnt`) `user_cnt`,sum(`sum_money`) `sum_money`,
                sum(`pay_user_cnt`) `pay_user_cnt`, sum( `order_cnt`) `order_cnt`,
                sum(`reg_pay_cnt`) `reg_pay_cnt`,sum(`sum_reg_money`) `sum_reg_money`,
                sum(`reg_cnt`) `reg_cnt`";
        $items = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->group('date,app_id')
            ->order("date DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $this->daypaymodel
            ->field($field)
            ->where($where)
            ->select();
        if (!empty($start_time)) {
            $sumwhere .= " AND login_date>='".$start_time."'";
        }
        if (!empty($end_time)) {
            $sumwhere .= " AND login_date<='".$end_time."'";
        }
        $sumitems[0]['user_cnt'] = M('day_pay_user')->where($sumwhere)->count('distinct(mem_id)');
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
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            /* BEGIN 2017/2/17 1667-3，游戏数据导出不显示游戏名 wuyonghong */
            $_end_time = $end_time;
            if (empty($end_time)) {
                $_end_time = $todaytime;
            }
            if (empty($start_time)) {
                $_start_time = 0;
            } else {
                $_start_time = strtotime($start_time);
            }
            $expTitle = "游戏数据".date("Ymd", $_start_time)."-".date("Ymd", $_end_time);
            $expCellName = array(
                array("date", "日期"),
                array("gamename", "游戏"),
                array("reg_cnt", "新增用户数"),
                array("user_cnt", "活跃用户数"),
                array("pay_user_cnt", "付费用户数"),
                array("order_cnt", "订单数量"),
                array("sum_reg_money", "新用户付费金额"),
                array("sum_money", "总付费金额"),
                array("pay_user_cnt", "总付费率"),
                array("sum_reg_money", "注册APRU"),
                array("sum_money", "活跃ARPU"),
                array("sum_money", "付费ARPU")
            );
            $_ext_field = $field.", g.name gamename";
            $export_items = $this->daypaymodel
                ->alias('dp')
                ->field($_ext_field)
                ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=dp.app_id")
                ->where($where)
                ->group('date,app_id')
                ->order("date DESC")
                ->select();
            /* END  */
            $expTableData = $export_items;
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("totalpays", $sumitems);
        $this->assign("pays", $items);
        $this->assign("todaypays", $todayitem);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }
}