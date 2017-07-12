<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class DataMemAccountController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $model = M("members");
//        $where=array("m.agent_id"=>$_SESSION['agent_id']);
//        $subids=$this->huoshu_agent->getMySubAgentsIds();
//        array_push($subids,$this->agid);
//        $subids_txt=join(",",$subids);
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
//         $where['_string']="m.agent_id IN ($subids_txt)";
        $count = $model
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->where($where)
            ->count();
        $Page = $this->page($count, 10);
        $show = $Page->show();// 分页显示输出   
        $members = $model
            ->field("m.*,g.name as gamename,u.user_type,u.user_nicename,ll.login_time as last_login_time")
            ->alias('m')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=m.agent_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."login_log ll ON ll.mem_id=m.id")
            ->where($where)
            ->order("m.reg_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach ($members as $key => $member) {
            $type = $members[$key]['user_type'];
            if ($type == $this->agent_roleid) {
                $members[$key]['user_type'] = '代理';
            } else if ($type == $this->subagent_roleid) {
                $members[$key]['user_type'] = '下级代理';
            }
        }
        $n = count($members);
        $this->assign("n", $n);
        $this->assign("page", $show);
        $this->assign("members", $members);
        $this->assign("formget", $_GET);
        $this->display();
    }
}

