<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataMemAccountController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $model = M("members");
//        $where=array("m.agent_id"=>$_SESSION['agent_id']);
        $subids = $this->huoshu_agent->getMySubAgentsIds();
        array_push($subids, $this->agid);
        $subids_txt = join(",", $subids);
        $where = array();
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "m.reg_time");
        if (isset($_GET['mem_name']) && ($_GET['mem_name'])) {
            $v = $_GET['mem_name'];
            $where['m.username'] = array("like", "%$v%");
        }
        if (isset($_GET['game_name']) && ($_GET['game_name'])) {
            $v = $_GET['game_name'];
            $where['g.name'] = array("like", "%$v%");
        }
        if (isset($_GET['agent_name']) && ($_GET['agent_name'])) {
            $v = $_GET['agent_name'];
            $where['u.user_nicename'] = array("like", "%$v%");
        }
//        $where="m.agent_id=$this->agid OR m.agent_id IN ($subids_txt)";
        $where['_string'] = "m.agent_id IN ($subids_txt)";
        $count = $this->getCnt($where);
        $Page = new \Think\Page($count, 10);
        $items = $this->getList($where, $Page->firstRow, $Page->listRows);
        if ($_GET['submit'] == '导出数据') {
            $hs_ee_obj = new \Huosdk\Data\ExportExcel();
            $expTitle = "玩家账户明细";
            $expCellName = array(
                array("reg_time", "注册时间"),
                array("user_nicename", "注册渠道"),
                array("username", "玩家账号"),
                array("gamename", "注册游戏"),
                array("last_login_time", "最后登录时间")
            );
            $expTableData = $this->getList($where);
            $hs_ee_obj->export($expTitle, $expCellName, $expTableData);
        }
        $this->assign("n", $count);
        $this->assign("page", $Page->show());
        $this->assign("members", $items);
        $this->assign("formget", $_GET);
        $this->assign("page_title", "玩家账户明细");
        $this->display();
    }

    public function getList($where_extra = array(), $start = 0, $limit = 10) {
        $model = M("members");
        $members = $model
            ->field("m.*,g.name as gamename,u.user_type,u.user_nicename")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            //->join("LEFT JOIN ".C('DB_PREFIX')."login_log ll ON ll.mem_id=m.id" )
            ->where($where_extra)
            ->order("m.reg_time desc")
            //->group("m.id")
            ->limit($start, $limit)
            ->select();
        foreach ($members as $key => $member) {
            $type = $members[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $members[$key]['user_type'] = '渠道';
            } else if ($type == $this->subagent_roleid) {
                $members[$key]['user_type'] = '下级渠道';
            }
            $members[$key]['last_login_time'] = $this->get_mem_last_login_time($member['id']);
        }
        $this->formatTime($members, "reg_time");
        $this->formatTime($members, "last_login_time");
        return $members;
    }


    public function getCnt($where_extra = array()) {
        $_cnt = M("members")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->where($where_extra)
            ->count();
        return $_cnt;
    }

    public function get_mem_last_login_time($mem_id) {
//        $data = M('login_log')->where(array("mem_id" => $mem_id))->order("login_time desc")->getField("login_time");
        $data = M('mem_ext')->where(array("mem_id" => $mem_id))->getField("last_login_time");
        return $data;
    }

    public function formatTime(&$items, $field) {
        foreach ($items as $k => $v) {
            $items[$k][$field] = date("Y-m-d H:i:s", $v[$field]);
        }
    }
}

