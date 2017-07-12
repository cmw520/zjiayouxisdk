<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function getgameptbcheck() {
        $agent_id = $_SESSION['agent_id'];
        $three_month_ago = time() - 90 * 24 * 3600;
        $model = M("gm_pay");
        $where = array();
        $where['gp.agent_id'] = $agent_id;
        $where['gp.create_time'] = array("gt", $three_month_ago);
        if (isset($_GET['memname']) && $_GET['memname'] != '') {
            $mem_id = get_memid_by_name($_GET['memname']);
            $where["gp.mem_id"] = $mem_id;
        }
//        if(isset($_GET['gamename'])&&$_GET['gamename']!=''){
//            $name=$_GET['gamename'];
//            $app_id=M('game')->where(array("name"=>$name))->getField("id");
//            $where["gp.app_id"]=$app_id;    
//        }
        if (isset($_GET['gamename']) && $_GET['gamename'] != '') {
            $gamename = $_GET['gamename'];
            $where["g.name"] = array("like", "%$gamename%");
        }
        $count = $model->alias('gp')->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")->where($where)
                       ->count();
        $page = new \Think\Page($count, 5);
        $records = $model
            ->field("gp.*,g.name as gamename,m.username as memname")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gp.mem_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $model
            ->field("sum(gm_cnt) as sum_cnt")
            ->alias('gp')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=gp.app_id")
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $this->assign("formget", $_GET);
        $this->assign("num_of_records", $count);
        $this->assign("Page", $page->show());
        $this->assign("records", $records);
        $this->display();
    }

    public function getuserptbvalidityduration() {
        $agent_id = $_SESSION['agent_id'];
        $three_month_ago = time() - 3 * 30 * 24 * 3600;
        $model = M('gm_mem');
        $where = array();
        $where['m.agent_id'] = $agent_id;
        $where['gc.create_time'] = array("gt", $three_month_ago);
        if (isset($_GET['memname']) && $_GET['memname'] != '') {
            $mem_id = get_memid_by_name($_GET['memname']);
            $where["gc.mem_id"] = $mem_id;
        }
//        if(isset($_GET['gamename'])&&$_GET['gamename']!=''){
//            $name=$_GET['gamename'];
//            $app_id=M('game')->where(array("name"=>$name))->getField("id");
//            $where["gc.app_id"]=$app_id;    
//        }
        if (isset($_GET['gamename']) && $_GET['gamename'] != '') {
            $gamename = $_GET['gamename'];
            $where["g.name"] = array("like", "%$gamename%");
        }
        $count = $model->alias('gc')
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
                       ->join("INNER JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")->where($where)->count();
        $page = new \Think\Page($count, 5);
        $items = $model
            ->field("gc.*,m.username as memname,g.name as gamename")
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("INNER JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->order("gc.update_time desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumitems = $model
            ->field("sum(remain) as sum_remain")
            ->alias('gc')
            ->join("INNER JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $this->assign("formget", $_GET);
        $this->assign("num_of_records", $count);
        $this->assign("Page", $page->show());
        $this->assign("records", $items);
        $this->display();
    }

    public function getBalanceChargeRecord() {
        $agent_id = $_SESSION['agent_id'];
        $three_month_ago = time() - 3 * 30 * 24 * 3600;
//        $where="(agent_id = $agent_id) AND (create_time > $three_month_ago)";
        $where = array();
        $where['gac.agent_id'] = $agent_id;
        $where['gac.create_time'] = array("gt", $three_month_ago);
        if (isset($_GET['s_time']) && $_GET['s_time'] != '') {
            $where["gac.create_time"] = array('gt', strtotime($_GET['s_time']));
        }
        if (isset($_GET['e_time']) && $_GET['e_time'] != '') {
            $where["gac.create_time"] = array('lt', strtotime($_GET['e_time']));
        }
        $model = M("gm_agentcharge");
        $count = $model->alias('gac')->where($where)->count();
        $page = new \Think\Page($count, 10);
//        $items=M('gm_agentcharge')
//                ->field("ac.*,u.user_nicename,u.mobile,u.user_login,g.name as gamename")
//                ->alias('ac')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=ac.agent_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ac.app_id")
//                ->order("ac.id desc")
//                ->limit($page->firstRow . ',' . $page->listRows)
//                ->select();               
        $records = $model
            ->field("gac.*,u.user_login")
            ->alias('gac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gac.admin_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("gac.id desc")
            ->select();
        foreach ($records as $k => $record) {
            if ($record['admin_id'] == 0) {
                $records[$k]['money'] = "<span style='color:red;'>-".$records[$k]['money']."</span>";
            } else {
                $records[$k]['money'] = "<span style='color:green;'>+".$records[$k]['money']."</span>";
            }
        }
        $sumitems = $model
            ->field("sum(money) as sum_money")
            ->alias('gac')
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show());
        $this->assign("num_of_records", $count);
        $this->assign("records", $records);
        $this->display();
    }

    public function set_user_type(&$items) {
        foreach ($items as $key => $member) {
            $type = $items[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $items[$key]['user_type'] = '渠道';
            } else if ($type == $this->subagent_roleid) {
                $items[$key]['user_type'] = '下级渠道';
            }
        }
    }

    public function getUserPayment() {
        $agent_id = $_SESSION['agent_id'];
        $where = array();
//        $where['gc.admin_id']=$agent_id;
        $subids_txt = $this->get_subids_txt();
        if (!$subids_txt) {
            $where['_string'] = "gc.admin_id=$agent_id";
        } else {
            $where['_string'] = "gc.admin_id=$agent_id OR gc.admin_id IN ($subids_txt)";
        }
        $where['gc.create_time'] = array("gt", $three_month_ago);
        if (isset($_GET['memname']) && $_GET['memname'] != '') {
            $mem_id = get_memid_by_name($_GET['memname']);
            $where["gc.mem_id"] = $mem_id;
        }
        if (isset($_GET['gamename']) && $_GET['gamename'] != '') {
//            $name=$_GET['gamename'];
//            $app_id=M('game')->where(array("name"=>$name))->getField("id");
//            $where["gc.app_id"]=$app_id;    
            $gamename = $_GET['gamename'];
            $where["g.name"] = array("like", "%$gamename%");
        }
        $count = M('gm_charge')
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->count();
        $page = new \Think\Page($count, 5);
        $items = M('gm_charge')
            ->field(
                "gc.*,u.user_nicename,u.mobile,u.user_login,m.username as memname,g.name as gamename,u.user_type,gc.admin_id"
            )
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->where($where)
            ->order("gc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->set_user_type($items);
        $sumitems = M('gm_charge')
            ->field("sum(gm_cnt) as sum_cnt")
            ->alias('gc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->where($where)
            ->select();
        $this->assign("sumitems", $sumitems);
        $this->assign("formget", $_GET);
        $this->assign("num_of_records", $count);
        $this->assign("Page", $page->show());
        $this->assign("records", $items);
        $this->display();
    }

    public function get_subids_txt() {
        $subids = $this->huoshu_agent->getMySubAgentsIds();
        if (!$subids) {
            return false;
        }
        $subids_txt = join(",", $subids);
        return $subids_txt;
    }

    public function member() {
        $model = M("members");
//        $where=array("m.agent_id"=>$_SESSION['agent_id']);
        $subids = $this->huoshu_agent->getMySubAgentsIds();
        array_push($subids, $this->agid);
        $subids_txt = join(",", $subids);
//        $where="m.agent_id=$this->agid OR m.agent_id IN ($subids_txt)";
        $where = "m.agent_id IN ($subids_txt)";
        $count = $model->alias('m')
                       ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
                       ->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出   
        $members = $model
            ->field("m.*,g.name as gamename,u.user_type,u.user_nicename")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->where($where)
            ->order("m.reg_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach ($members as $key => $member) {
            $type = $members[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $members[$key]['user_type'] = '渠道';
            } else if ($type == $this->subagent_roleid) {
                $members[$key]['user_type'] = '下级渠道';
            }
        }
        $n = count($members);
        $this->assign("n", $n);
        $this->assign("page", $show);
        $this->assign("members", $members);
        $this->assign("page_title", "玩家注册明细");
        $this->display();
    }

    public function normalChargeRecord() {
        $ids = $this->huoshu_agent->getMySubAgentsIds();
        $ids[] = $this->agid;
        $ids_txt = join(",", $ids);
        $where = "p.agent_id IN ($ids_txt) AND (p.status=2)";
        $field = "p.order_id, p.amount, m.username,p.agent_id, p.payway, u.user_login agentname, "
                 ."u.user_nicename agentnickname, g.name gamename,p.status,p.cpstatus, p.create_time, p.app_id";
        $count = M('pay')
            ->alias("p")
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->count();
        $page = new \Think\Page($count, 10);
        $items = M('pay')
            ->alias("p")
            ->field($field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->order("p.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sum_field = "sum( p.amount) as sum_amount";
        $sumitems = M('pay')
            ->alias("p")
            ->field($sum_field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON p.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."users u ON p.agent_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON p.mem_id = m.id")
            ->order("p.id DESC")
            ->select();
        $this->assign("formget", $_GET);
        $this->assign("sum_value", $sumitems[0]);
        $this->_payway();
        $this->_cpstatus();
        $this->_paystatus();
        $this->assign("num_of_records", $count);
        $this->assign("Page", $page->show());
        $this->assign("orders", $items);
        $this->display();
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

    public function mgrr3() {
        $hs_data_ptbmem_obj = new \Huosdk\Data\PtbMem();
        $table_header = $hs_data_ptbmem_obj->get_table_header_agent();
        $this->assign("table_header", $table_header);
        $count = $hs_data_ptbmem_obj->get_agent_count($this->agid);
        $page = new \Think\Page($count, 10);
        $table_content = $hs_data_ptbmem_obj->get_agent_table($this->agid, $page->firstRow, $page->listRows);
        $this->assign("table_content", $table_content);
        $this->assign("Page", $page->show());
        $sum = $hs_data_ptbmem_obj->get_sum_agent_txt($this->agid);
        $this->assign("table_sum", $sum);
        $this->display();
    }

    public function memGmRechargeRecord() {
//        $hs_data_ptbmem_obj=new \Huosdk\Data\PtbMem();
//       
//        $table_header=$hs_data_ptbmem_obj->get_table_header();
//        $this->assign("table_header",$table_header);
//       
//        
//        $table_content=$hs_data_ptbmem_obj->get_agent_table($this->agid);
//        $this->assign("table_content",$table_content);
//
//        $this->display();
//        
        $hs_data_ptbmem_obj = new \Huosdk\Data\PtbMem();
        $table_header = $hs_data_ptbmem_obj->get_table_header_agent();
        $this->assign("table_header", $table_header);
        $count = $hs_data_ptbmem_obj->get_agent_count($this->agid);
        $page = new \Think\Page($count, 10);
        $table_content = $hs_data_ptbmem_obj->get_agent_table($this->agid, $page->firstRow, $page->listRows);
        $this->assign("table_content", $table_content);
        $this->assign("Page", $page->show());
        $sum = $hs_data_ptbmem_obj->get_sum_agent_txt($this->agid);
        $this->assign("table_sum", $sum);
        $this->display();
    }

    public function PtbAgentChargeRecord() {
        $hs_data_pac_obj = new \Huosdk\Data\PtbAgentCharge($this->agid);
        $table_header = $hs_data_pac_obj->get_table_header();
        $this->assign("table_header", $table_header);
        $count = $hs_data_pac_obj->get_count($this->agid);
        $page = new \Think\Page($count, 10);
//        echo $count." ".$page->firstRow." ".$page->listRows;
//        exit;
        $table_content = $hs_data_pac_obj->get_table($page->firstRow, $page->listRows);
        $this->assign("table_content", $table_content);
        $this->assign("Page", $page->show());
        $sum = $hs_data_pac_obj->get_sum_txt($this->agid);
        $this->assign("table_sum", $sum);
        $this->display();
    }
}

