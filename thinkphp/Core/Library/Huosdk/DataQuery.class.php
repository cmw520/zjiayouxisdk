<?php
namespace Huosdk;
class DataQuery {
    public function _where_start_time(&$where, $field) {
        $name = "start_time";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $where[$field] = array("gt", strtotime($_GET[$name]));
        }
    }

    public function _where_end_time(&$where, $field) {
        $name = "end_time";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $where[$field] = array("lt", strtotime($_GET[$name]));
        }
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

    public function _where_member_name(&$where, $field) {
        $name = "membername";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $v = $_GET[$name];
            $where[$field] = array("like", "%$v%");
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

    public function query() {
//        $agent_id=  get_current_admin_id();                
//        
//        $where=array("agc.agent_id"=>$agent_id);                
//        $this->_where_start_time($where,"agc.create_time");        
//        $this->_where_end_time($where, "agc.create_time");        
//        $this->_where_order_id($where,"agc.order_id");
//        $this->_where_agent_name($where,"u.user_login");
//        $this->_where_admin_name($where,'agc.admin_id','ua.user_login');
//        
//        
//        $count=M('gm_agentcharge')
//                ->alias('agc')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
//                ->where($where)
//                ->count();      
//        $page = $this->page($count,20);
//        
//        
//        $items=M('gm_agentcharge')
//                ->field("agc.*,u.user_login as agentname,ua.user_login as adminname")
//                ->alias('agc')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
//                ->where($where)
//                ->order("agc.id desc")
//                ->limit($page->firstRow . ',' . $page->listRows)
//                ->select();      
//        
//        foreach($items as $k=>$item){
//            if($item['admin_id']==0){
//                $items[$k]['adminname']="平台官方";
//            }
//        }
//        $this->assign("orders",$items);
//        
//        $sums=M('gm_agentcharge')
//                ->field("sum(agc.money) as total")
//                ->alias('agc')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
//                ->where($where)
//                ->select();
//        $this->assign("sums", $sums[0]['total']);
//        
//        $this->assign("formget", $_GET);
//        $this->assign("Page", $page->show('Admin'));
//        $this->assign("current_page", $page->GetCurrentPage());
//        $this->display();
    }
}

