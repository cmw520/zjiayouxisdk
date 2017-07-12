<?php
namespace Pay\Controller;

use Common\Controller\AdminbaseController;

class BalanceController extends AdminbaseController {
    public function give() {
        $ptbname = C('PTBNAME');
        if (empty($ptbname)) {
            $ptbname = '平台币';
        }
        $this->assign("ptbname", $ptbname);
        $this->display();
    }

    public function give_post() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $type = I('type');
        if ($type == 'agent') {
            $user_type = $agent_roleid;
        } else if ($type == 'sub') {
            $user_type = $subagent_roleid;
        }
        $agent_name = I('username');
        $amount = I('amount');
        $remark = I("beizhu");
        $password = I('post.password');
        if (empty($password)) {
            $this->error("请填写二级密码");
            exit;
        }
        //验证密码
        $this->verifyPaypwd($password);
        if (!(is_numeric($amount))) {
            $this->error("充值数量格式不对");
//            $this->ajaxReturn(array("error"=>"1","msg"=>"充值数量格式不对"));
        }
//        if($amount<=0){
//            $this->error("充值金额必须大于0");
//        }
//        
//        if($amount>10000){
//            $this->error("单笔充值金额不能大于1万");
//        }
        $agent_data = M('users')->where(array("user_login" => $agent_name, "user_type" => $user_type))->find();
        if (!$agent_data) {
//            $this->ajaxReturn(array("error"=>"1","msg"=>"代理不存在"));
            $this->error("代理不存在");
        }
        $agent_id = $agent_data['id'];
//        $age_data=M('agent_ext')->where(array("agent_id"=>$agent_id))->find();
//        $new_balance=$age_data['balance']+$amount;
//        M('agent_ext')->where(array("agent_id"=>$agent_data['id']))->setField("balance",$new_balance);
        $admin_id = get_current_admin_id();
        $charge_obj = new \Huosdk\Charge();
        $charge_obj->addAdminChargeForAgentRecord_PTB($admin_id, $agent_id, $amount, $remark);
//        $this->ajaxReturn(array("error"=>"0","msg"=>"发放成功"));
        $this->success("发放成功");
    }

    public function record() {
        $agent_id = get_current_admin_id();
        $where = array();
//        $where["_string"]="agc.admin_id = 1 OR agc.admin_id =0"; 
        $where["_string"] = "agc.admin_id = 1";
//        $where=array("agc.agent_id"=>$agent_id);                
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "agc.create_time");
        $this->_where_order_id($where, "agc.order_id");
        $this->_where_agent_name($where, "u.user_login");
        $this->_where_admin_name($where, 'agc.admin_id', 'ua.user_login');
        $count = M('ptb_agentcharge')
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('ptb_agentcharge')
            ->field("agc.*,u.user_login as agentname,ua.user_login as adminname")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->order("agc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
//        foreach($items as $k=>$item){
//            if($item['admin_id']==0){
//                $items[$k]['adminname']="平台官方";
//            }
//        }
        $this->assign("orders", $items);
        $sums = M('ptb_agentcharge')
            ->field("sum(agc.money) as total")
            ->alias('agc')
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

    public function data() {
        $items = M('ptb_agent')->order("id desc")->select();
    }

    public function data_agent() {
        $obj = new \Huosdk\Account();
        $ids = $obj->allAgentIds();
        $ids_txt = join(",", $ids);
        $where = array();
        $where['_string'] = "ae.agent_id IN ($ids_txt)";
        $dq_obj = new \Huosdk\DataQuery();
        $dq_obj->_where_agent_name($where, "u.user_login");
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "unix_timestamp(u.create_time)");
//        $this->_where_start_time($where, "unix_timestamp(u.create_time)");
//        $this->_where_end_time($where, "unix_timestamp(u.create_time)");
        $count = M('ptb_agent')
            ->field("ae.*,u.user_login")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('ptb_agent')
            ->field("ae.agent_id,ae.remain,u.user_login as agentname,u.create_time as account_create_time")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("ae.agent_id desc")
            ->select();
        foreach ($items as $key => $item) {
            $items[$key]['recent_charge_time'] = $this->getAgentRecentChargeTime($item['agent_id']);
        }
        $sums = M('ptb_agent')
            ->field("sum(ae.remain) as total")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("ae.agent_id desc")
            ->select();
        $this->assign("sums", $sums[0]['total']);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function data_sub() {
        $obj = new \Huosdk\Account();
        $ids = $obj->allSubAgentIds();
        $ids_txt = join(",", $ids);
        $where = array();
        $where['_string'] = "ae.agent_id IN ($ids_txt)";
        $dq_obj = new \Huosdk\DataQuery();
        $dq_obj->_where_agent_name($where, "u.user_login");
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "unix_timestamp(u.create_time)");
//        $this->_where_start_time($where, "unix_timestamp(u.create_time)");
//        $this->_where_end_time($where, "unix_timestamp(u.create_time)");
        $count = M('ptb_agent')
            ->field("ae.*,u.user_login")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('ptb_agent')
            ->field("ae.agent_id,ae.remain,u.user_login as agentname,u.create_time as account_create_time")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("ae.agent_id desc")
            ->select();
        foreach ($items as $key => $item) {
            $items[$key]['recent_charge_time'] = $this->getAgentRecentChargeTime($item['agent_id']);
        }
        $sums = M('ptb_agent')
            ->field("sum(ae.remain) as total")
            ->alias("ae")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ae.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("ae.agent_id desc")
            ->select();
        $this->assign("sums", $sums[0]['total']);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
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

    public function getAgentRecentChargeTime($agid) {
        $data = M('ptb_agentcharge')->where(array("agent_id" => $agid))->order("create_time desc")->getField(
            "create_time"
        );
        if ($data) {
            return date("Y-m-d  H:i:s", $data);
        } else {
            return "--";
        }
    }
}
