<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentDataController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function report() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "game_name", "g.name");
        $hs_where_obj->time($where, "unix_timestamp(dag.date)");
        $hs_where_obj->agent_name_with_official($where, "agent_name", "dag.agent_id", "u.user_nicename");
        $hs_where_obj->get_simple($where, "agent_id", "dag.agent_id");
        $hs_where_obj->get_simple($where, "app_id", "dag.app_id");
        $all_items = \Huosdk\Data\DayAgentGame::getList($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $content = \Huosdk\Data\DayAgentGame::getTxt($where, $page->firstRow, $page->listRows);
        $this->assign("table_content", $content);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "全部渠道统计";
            $expCellName = array(
                array("date", "时间    "),
                array("agent_name", "渠道名称"),
                array("game_name", "游戏    "),
                array("new_user_cnt", "新增用户"),
                array("active_user_cnt", "活跃用户"),
                array("charge_amount", "充值金额"),
                array("pay_user_cnt", "充值人数"),
                array("pay_rate", "付费率  "),
            );
            $expTableData = \Huosdk\Data\DayAgentGame::getList($where);
//            print_r($expTableData);
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget", $_GET);
        $this->display();
    }

    public function report_agent() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select(true, false));
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $hs_account_obj = new \Huosdk\Account();
        $where = array();
        $where['u.user_type'] = $hs_account_obj->agentRoldId;
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "game_name", "g.name");
        $hs_where_obj->time($where, "unix_timestamp(dag.date)");
        $hs_where_obj->agent_name_with_official($where, "agent_name", "dag.agent_id", "u.user_nicename");
        $hs_where_obj->get_simple($where, "agent_id", "dag.agent_id");
        $hs_where_obj->get_simple($where, "app_id", "dag.app_id");
        $all_items = \Huosdk\Data\DayAgentGame::getList($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
        $content = \Huosdk\Data\DayAgentGame::getTxt($where, $page->firstRow, $page->listRows);
        $this->assign("table_content", $content);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget", $_GET);
        $this->display();
    }

    public function member() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "m.reg_time");
        if (isset($_GET['app_id']) && ($_GET['app_id'])) {
            $v = $_GET['app_id'];
            $where['g.id'] = $v;
        }
        if (isset($_GET['mem_id']) && ($_GET['mem_id'])) {
            $v = $_GET['mem_id'];
            $where['m.id'] = $v;
        }
        if (isset($_GET['agent_id']) && ($_GET['agent_id'])) {
            $v = $_GET['agent_id'];
            $where['u.id'] = $v;
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_where_obj->time($where, "m.reg_time");
        $count = $this->getMemCnt($where);
        $Page = $this->page($count, 10);
        $show = $Page->show('Admin');// 分页显示输出   
        $members = $this->getMemList($where, $Page->firstRow, $Page->listRows);
        $this->assign("n", $count);
        $this->assign("page", $show);
        $this->assign("members", $members);
        $this->assign("formget", $_GET);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "用户明细";
            $expCellName = array(
                array("reg_time", "注册时间"),
                array("user_nicename", "注册渠道"),
                array("username", "玩家账号"),
                array("gamename", "注册游戏")
            );
            $expTableData = $this->getMemList($where);
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->display();
    }

    /**
     * 玩家数量
     *
     * @param array $where
     *
     * @return mixed
     * 2017/2/11  wuyonghong
     */
    public function getMemCnt($where = array()) {
        $model = M("members");
        $_count = $model
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->where($where)
            ->count();

        return $_count;
    }

    public function getMemList($where = array(), $start = 0, $limit = 0) {
        $model = M("members");
        $members = $model
            ->field("m.*,g.name as gamename,u.user_type,u.user_nicename")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
//                ->join("LEFT JOIN ".C('DB_PREFIX')."login_log ll ON ll.mem_id=m.id" )
            ->where($where)
            ->order("m.reg_time desc")
//                ->group("m.username")
            ->limit($start, $limit)
            ->select();
        foreach ($members as $key => $member) {
            $type = $members[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $members[$key]['user_type'] = '代理';
            } else if ($type == $this->subagent_roleid) {
                $members[$key]['user_type'] = '下级代理';
            }
            if (!$member['user_nicename']) {
                $members[$key]['user_nicename'] = '官方渠道';
            }
            $members[$key]['reg_time'] = date("Y-m-d H:i:s", $members[$key]['reg_time']);
            $members[$key]['last_login_time'] = $this->getLastLoginTime($member['id']);
        }

        return $members;
    }

    public function getLastLoginTime($mem_id) {
        $llt = M('login_log')
            ->field("login_time")
            ->where(array("mem_id" => $mem_id))
            ->order("id desc")
            ->select();

        return $llt[0]["login_time"];
    }

    public function charge() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("parent_agent_select", $hs_ui_filter_obj->parent_agent_select());
        $this->assign("member_select", $hs_ui_filter_obj->memname_input());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $this->assign("payway_select", $hs_ui_filter_obj->payway_select3());
//        $this->assign("status_select", $hs_ui_filter_obj->status_select());
        $where = array();
        $where['gp.payway'] = array("neq", "0");
        if (isset($_GET['mem_id'])) {
//            $where['gp.mem_id']=$_GET['mem_id'];
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gp.create_time");
        $hs_where_obj->get_simple($where, "app_id", "gp.app_id");
        $hs_where_obj->get_simple($where, "agent_id", "u.id");
        $hs_where_obj->get_simple($where, "parent_agent_id", "u2.id");
        $hs_where_obj->get_simple_like($where, "mem_name", "m.username");
        $hs_where_obj->get_simple($where, "status", "gp.status");
        if (isset($_GET['game_name']) && ($_GET['game_name'])) {
            $v = $_GET['game_name'];
            $where['g.name'] = array("like", "%$v%");
        }
        if (isset($_GET['agent_name']) && ($_GET['agent_name'])) {
            $v = $_GET['agent_name'];
            $where['u.user_nicename'] = array("like", "%$v%");
        }
        if (isset($_GET['order_id']) && ($_GET['order_id'])) {
            $v = $_GET['order_id'];
            $where['gp.order_id'] = $v;
        }
        if (isset($_GET['payway']) && ($_GET['payway'])) {
            $v = $_GET['payway'];
            if ($v == 'normal') {
                $hs_pw_obj = new \Huosdk\Money\Payway();
                $list = $hs_pw_obj->Normal_payways_txt();
                if ($list) {
                    $where['_string'] = " ( (gp.payway IN ($list)) ) ";
                }
            } else if ($v == 'notnormal') {
                $where['_string'] = " ((gp.payway = 'ptbpay') OR (gp.payway=  'gamepay'))  ";
            } else {
                $where['gp.payway'] = $v;
            }
        }
        $count = $this->getChargeCnt($where);
        $page = $this->page($count, 20);
        $records = $this->getChargeList($where, $page->firstRow, $page->listRows);
        $this->assign("page", $page->show('Admin'));
        $this->assign("items", $records);
        $this->assign("formget", $_GET);
        $this->assign("n", $count);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "充值明细";
            $expCellName = array(
                array("parent_agent_name", "一级渠道归属 "),
                array("agent_name", "渠道名称"),
                array("create_time", "充值时间"),
                array("order_id", "订单号"),
                array("status", "支付状态"),
                array("mem_name", "玩家账号"),
                array("game_name", "充值游戏"),
                array("amount", "充值金额"),
                array("payway_txt", "充值方式 "),
            );
            $expTableData = $this->getChargeList($where);
            foreach ($expTableData as $k => $v) {
                $expTableData[$k]["order_id"] = " ".$v["order_id"];
            }
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->display();
    }

    /**
     * 计算充值数量
     *
     * @param array $where
     *
     * @return mixed
     * 2017/2/11  wuyonghong
     */
    public function getChargeCnt($where = array()) {
        $model = M("pay");
        $_count = $model
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u.ownerid=u2.id")
            ->where($where)
            ->count();

        return $_count;
    }

    public function getChargeList($where = array(), $start = 0, $limit = 0) {
        $model = M("pay");
        $records = $model
            ->field(
                "gp.*,g.name as game_name,m.username as mem_name, "
                ."u.user_nicename as agent_name,u2.user_nicename as parent_agent_name"
            )
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON m.agent_id=u.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u.ownerid=u2.id")
            ->where($where)
            ->order("gp.id desc")
            ->limit($start, $limit)
            ->select();
        $payway_data = $this->payway_txt();
        foreach ($records as $key => $value) {
            if (empty($value['payway'])) {
                $records[$key]['payway_txt'] = "未知";
            } else {
                $records[$key]['payway_txt'] = $payway_data[$value['payway']];
            }
            if (!$records[$key]['agent_name']) {
                $records[$key]['agent_name'] = "官方渠道";
            }
            if (!$records[$key]['parent_agent_name']) {
                $records[$key]['parent_agent_name'] = "官方渠道";
            }
            $records[$key]['create_time'] = date("Y-m-d H:i:s", $records[$key]['create_time']);
            if (1 == $records[$key]['status']) {
                $records[$key]['status'] = "待支付";
            } elseif (2 == $records[$key]['status']) {
                $records[$key]['status'] = "支付成功";
            } else {
                $records[$key]['status'] = "支付失败";
            }
        }

        return $records;
    }

    public function payway_txt() {
        $data = M('payway')->getField("payname,realname", true);

        return $data;
    }
}

