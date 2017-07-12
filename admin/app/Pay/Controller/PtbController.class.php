<?php
/**
 * 平台充值管理
 *
 * @author
 */
namespace Pay\Controller;

use Common\Controller\AdminbaseController;

class PtbController extends AdminbaseController {
    function _payway($type = null, $option = true) {
        $cates = array(
            "" => "全部"
        );
        $payways = M('payway')->getField("id,realname", true);
        if ($option) {
            $payways = $cates + $payways;
        }
        $this->assign("payways", $payways);
    }

    /* 平台币余额 */
    public function Ptb() {
        $ptbname = C('PTBNAME');
        if (empty($ptbname)) {
            $ptbname = '平台币';
        }
        $this->assign("ptbname", $ptbname);
        $this->_ptbList();
        $this->display();
    }

    public function _ptbList() {
        $where_ands = array("m.agent_id" => get_current_admin_id());
        $fields = array(
            'start_time' => array(
                "field"    => "pm.update_time",
                "operator" => ">"
            ),
            'end_time'   => array(
                "field"    => "pm.update_time",
                "operator" => "<"
            ),
            'username'   => array(
                "field"    => "m.username",
                "operator" => "="
            ),
        );
        if (IS_POST) {
            foreach ($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_POST[$param]);
                    $_GET[$param] = $get;
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach ($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_GET[$param]);
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $where = join(" and ", $where_ands);
        $count = M('ptb_mem')
            ->alias("pm")
            ->join("left join ".C('DB_PREFIX')."members m ON pm.mem_id = m.id")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "pm.*, m.username username, m.reg_time";
        $items = M('ptb_mem')
            ->alias("pm")
            ->field($field)
            ->join("left join ".C('DB_PREFIX')."members m ON pm.mem_id = m.id")
            ->where($where)
            ->order("m.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumlist = M('ptb_mem')
            ->alias("pm")
            ->field("sum(total) total, sum(remain) remain")
            ->join("left join ".C('DB_PREFIX')."members m ON pm.mem_id = m.id")
            ->where($where)
            ->find();
        $this->assign("sumlist", $sumlist);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    /**
     * 平台币手充
     */
    public function setPtb() {
        $ptbname = C('PTBNAME');
        if (empty($ptbname)) {
            $ptbname = '平台币';
        }
        $this->assign("ptbname", $ptbname);
        $this->display();
    }

    public function getAgentBalance() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $agent_name = I('username');
        $agent_data = M('users')->where(array("user_login" => $agent_name, "user_type" => $agent_roleid))->find();
        if (!$agent_data) {
            $this->ajaxReturn(array("error" => "1", "msg" => "代理不存在"));
        }
        $agentId = $agent_data['id'];
        $obj = new \Huosdk\Agent($agentId);
        $balance = $obj->getCurrentBalance();
        $this->ajaxReturn(array("error" => "0", "msg" => $balance));
    }

    /**
     * 平台币手充
     */
    public function setPtb_verify() {
        $data['username'] = I('username');
        $data['ptb'] = I('newptb', 0);
        $data['beizhu'] = I('beizhu');
        $data['amount'] = I('amount');
        $data['create_time'] = time();
        if (empty($data['username']) || $data['ptb'] == 0) {
            $this->error("请填写完整参数.");
            exit();
        }
        //if ($data['amount'] < 0) {
        //    $this->error("充值金额错误");
        //    exit();
        //}
        if ($data['ptb'] > 10000) {
            $this->error("超出单笔最大金额10000");
        }
        $ptbname = C('PTBNAME');
        if (empty($ptbname)) {
            $ptbname = '平台币';
        }
        $this->assign("ptbname", $ptbname);
        $this->assign($_POST);
        $this->display();
    }

    public function setPtb_post() {
        $agent_name = I('username');
        $amount = I('amount');
        $remark = I("beizhu");
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
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $agent_data = M('users')->where(array("user_login" => $agent_name, "user_type" => $agent_roleid))->find();
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
        $charge_obj->addAdminChargeForAgentRecord($admin_id, $agent_id, $amount, $remark);
//        $this->ajaxReturn(array("error"=>"0","msg"=>"发放成功"));
        $this->success("发放成功");
    }

    /**
     * 平台币手充
     */
    public function setPtb_post_old() {
        $action = I('action');
        if (isset($action) && isset($action) == 'add') {
            $username = I('post.username', '');
            $data['ptb_cnt'] = I('post.newptb/d', 0);
            $data['remark'] = I('post.beizhu');
            $data['money'] = I('post.amount/d');
            $password = I('post.password');
            $takeclass = I('post.takeclass/d');
            if (empty($username) || $data['ptb_cnt'] == 0 || empty($takeclass) || empty($password)) {
                $this->error("请填写完整参数.");
                exit();
            }
            //if ($data['money'] < 0) {
            //    $this->error("充值金额错误");
            //   exit();
            //}
            //验证密码
            $this->verifyPaypwd($password);
            //插入记录
            $data['order_id'] = setorderid();
            $data['flag'] = 8;/*官方发放 */
            $data['payway'] = 0;
            $data['ip'] = get_client_ip();
            $data['status'] = 2;
            $data['create_time'] = time();
            $data['discount'] = $data['money'] * 10 / $data['ptb_cnt'];
            $user_model = D("Common/Users");
            if (1 == $takeclass) {
                $agent_id = $user_model->where("user_login='%s'", $username)->getField('id');
                if (empty($agent_id)) {
                    $this->error("用户名不存在!");
                    exit;
                }
                $data['agent_id'] = $agent_id;
                $data['admin_id'] = get_current_admin_id();
                $pc_model = M('ptb_agentcharge');
                $rs = $pc_model->add($data);
                if ($rs) {
                    $data['flag'] = 8;/*官方发放 */
                    M('ptb_given')->add($data);
                    $p_model = M('ptb_agent');
                    $p_data = $p_model->where(array('agent_id' => $agent_id))->find();
                    if ($p_data) {
                        $p_data['sum_money'] = $p_data['sum_money'] + $data['money'];
                        $p_data['remain'] = $p_data['remain'] + $data['ptb_cnt'];
                        $p_data['total'] = $p_data['total'] + $data['ptb_cnt'];
                        $p_data['update_time'] = $data['create_time'];
                        $prs = $p_model->save($p_data);
                        if ($prs) {
                            $this->success("充值成功", U('Ptb/setPtb'));
                            exit;
                        }
                    } else {
                        $p_data['sum_money'] = $data['money'];
                        $p_data['remain'] = $data['ptb_cnt'];
                        $p_data['total'] = $data['ptb_cnt'];
                        $p_data['create_time'] = $data['create_time'];
                        $p_data['update_time'] = $data['create_time'];
                        $p_data['agent_id'] = $agent_id;
                        $prs = $p_model->add($p_data);
                        if ($prs) {
                            $this->success("发放成功", U('Ptb/setPtb'));
                            exit;
                        }
                    }
                }
            } elseif (2 == $takeclass) {
                //充值玩家
                $mem_model = M('members');
                $mem_id = $mem_model->where("username='%s'", $username)->getField('id');
                if (empty($mem_id)) {
                    $this->error("用户名不存在!");
                    exit;
                }
                $data['mem_id'] = $mem_id;
                $data['admin_id'] = get_current_admin_id();
                $pc_model = M('ptb_charge');
                $rs = $pc_model->add($data);
                if ($rs) {
                    $data['flag'] = 8;/*官方发放 */
                    M('ptb_given')->add($data);
                    $p_model = M('ptb_mem');
                    $p_data = $p_model->where(array('mem_id' => $mem_id))->find();
                    if ($p_data) {
                        $p_data['sum_money'] = $p_data['remain'] + $data['money'];
                        $p_data['remain'] = $p_data['remain'] + $data['ptb_cnt'];
                        $p_data['total'] = $p_data['total'] + $data['ptb_cnt'];
                        $p_data['update_time'] = $data['create_time'];
                        $prs = $p_model->save($p_data);
                        if ($prs) {
                            $this->success("充值成功", U('Ptb/setPtb'));
                            exit;
                        }
                    } else {
                        $p_data['sum_money'] = $data['money'];
                        $p_data['remain'] = $p_data['remain'] + $data['ptb_cnt'];
                        $p_data['total'] = $p_data['total'] + $data['ptb_cnt'];
                        $p_data['create_time'] = $data['create_time'];
                        $p_data['update_time'] = $data['create_time'];
                        $p_data['mem_id'] = $mem_id;
                        $prs = $p_model->add($p_data);
                        if ($prs) {
                            $this->success("充值成功", U('Ptb/setPtb'));
                            exit;
                        }
                    }
                }
            }
            $this->error("手充失败！");
        }
        $this->error("参数错误！");
    }

    /**
     * 通过AJAX来获取用户的平台币余额
     */
    public function ajaxGetPtb() {
        $username = I('get.username', '', 'trim');
        $takeclass = I('get.takeclass/d');
        if (2 == $takeclass) {
            //检测该用户是否存在
            $model = M('members');
            $mem_id = $model->where("username='%s'", $username)->getField('id');
            if (empty($mem_id)) {
                echo "noexit";
                exit;
            }
            $model = M('ptb_mem');
            $ptb = $model->where("mem_id='%s'", $mem_id)->getField('remain');
        } elseif (1 == $takeclass) {
            //检测该用户是否存在
            $model = M('users');
            $agent_id = $model->where("user_login='%s'", $username)->getField('id');
            if (empty($agent_id)) {
                echo "noexit";
                exit;
            }
            $model = M('ptb_agent');
            $ptb = $model->where("agent_id='%s'", $agent_id)->getField('remain');
        } else {
            echo "noexit";
            exit;
        }
        if ($ptb) {
            echo $ptb;
            exit;
        } else {
            echo "0";
            exit;
        }
    }

    /**
     * 平台币发放记录列表
     */
    public function ptb_cList() {
        $ptbname = C('PTBNAME');
        if (empty($ptbname)) {
            $ptbname = '平台币';
        }
        $this->_pay_status();
        $this->assign('ptbname', $ptbname);
        $this->_agents();
        $this->_payway();
        $this->_ptb_cList();
        $this->display();
    }

    public function _pay_status() {
        $cates = array(
            1 => "待支付",
            2 => "成功",
            3 => "失败",
        );
        $this->assign('paystatus', $cates);
    }

    /**
     **平台币充值列表
     **/
    public function _ptb_cList() {
        $where_ands = array("m.agent_id" => get_current_admin_id());
        $fields = array(
            'agentname'  => array(
                "field"    => "u.user_login",
                "operator" => "="
            ),
            'start_time' => array(
                "field"    => "pc.create_time",
                "operator" => ">"
            ),
            'end_time'   => array(
                "field"    => "pc.create_time",
                "operator" => "<"
            ),
            'order_id'   => array(
                "field"    => "pc.order_id",
                "operator" => "="
            ),
            'username'   => array(
                "field"    => "m.username",
                "operator" => "="
            ),
        );
        if (IS_POST) {
            foreach ($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_POST[$param]);
                    $_GET[$param] = $get;
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach ($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_GET[$param]);
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $where = join(" and ", $where_ands);
        $count = M('ptb_charge')
            ->alias("pc")
            ->join("left join ".C('DB_PREFIX')."users u ON pc.admin_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON pc.mem_id = m.id")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "pc.*, u.user_login agentname, m.username username";
        $items = M('ptb_charge')
            ->alias("pc")
            ->field($field)
            ->join("left join ".C('DB_PREFIX')."users u ON pc.admin_id = u.id")
            ->join("left join ".C('DB_PREFIX')."members m ON pc.mem_id = m.id")
            ->where($where)
            ->order("pc.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("ptbcharges", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    public function getAgentRecentChargeTime($agid) {
        $data = M('gm_agentcharge')->where(array("agent_id" => $agid))->order("create_time desc")->getField(
            "create_time"
        );
        if ($data) {
            return date("Y-m-d  H:i:s", $data);
        } else {
            return "--";
        }
    }

    public function getAgentForMemberRecentChargeTime($mem_id) {
        $data = M('gm_charge')->where(array("mem_id" => $mem_id))->order("create_time desc")->getField("create_time");
        if ($data) {
            return date("Y-m-d  H:i:s", $data);
        } else {
            return "--";
        }
    }

    public function ptbAgent() {
        $obj = new \Huosdk\Account();
        $ids = $obj->allAgentIds();
        $where = array();
        if ($ids) {
            $ids_txt = join(",", $ids);
            $where['_string'] = "ae.agent_id IN ($ids_txt)";
        }
        $dq_obj = new \Huosdk\DataQuery();
        $dq_obj->_where_agent_name($where, "u.user_login");
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "unix_timestamp(u.create_time)");
//        $this->_where_start_time($where, "unix_timestamp(u.create_time)");
//        $this->_where_end_time($where, "unix_timestamp(u.create_time)");
        $count = M('ptb_agent')
            ->field("pa.*,u.user_login")
            ->alias("pa")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=pa.agent_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('ptb_agent')
            ->field("pa.agent_id,pa.remain,u.user_login as agentname,u.create_time as account_create_time")
            ->alias("pa")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=pa.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("pa.agent_id desc")
            ->select();
        foreach ($items as $key => $item) {
            $items[$key]['recent_charge_time'] = $this->getAgentRecentChargeTime($item['agent_id']);
        }
        $sums = M('ptb_agent')
            ->field("sum(pa.remain) as total")
            ->alias("pa")
            ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=pa.agent_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("pa.agent_id desc")
            ->select();
        $this->assign("sums", $sums[0]['total']);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }

    public function ptbSub() {
        $obj = new \Huosdk\Account();
        $ids = $obj->allSubAgentIds();
        $where = array();
        if ($ids) {
            $ids_txt = join(",", $ids);
            $where['_string'] = "ae.agent_id IN ($ids_txt)";
        }
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

    public function ptbMember() {
        $where = array();
        $dq_obj = new \Huosdk\DataQuery();
        $dq_obj->_where_member_name($where, "m.username");
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "m.reg_time");
        $count = M('ptb_mem')
            ->field("gm.remain,m.username as membername,m.reg_time as account_create_time")
            ->alias("gm")
            ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON m.id=gm.mem_id")
            ->where($where)
            ->order("gm.mem_id desc")
            ->count();
        $page = $this->page($count, 20);
        $items = M('ptb_mem')
            ->field("gm.mem_id,gm.remain,m.username as membername,m.reg_time as account_create_time,gm.id")
            ->alias("gm")
            ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON m.id=gm.mem_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("gm.mem_id desc")
            ->select();
        foreach ($items as $key => $item) {
            $items[$key]['recent_charge_time'] = $this->getAgentForMemberRecentChargeTime($item['mem_id']);
        }
        $sums = M('ptb_mem')
            ->field("sum(gm.remain) as total")
            ->alias("gm")
            ->join("LEFT JOIN ".C('DB_PREFIX')."members m ON m.id=gm.mem_id")
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order("gm.mem_id desc")
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

    public function getBackGameCoin() {
        $gmid = I('gmid');
        if (!($gmid)) {
            //$this->error("参数错误");
            redirect(U('Pay/Ptb/ptbMember'));
            exit;
        }
        $this->display();
    }

    public function getBackGameCoin_post() {
        $gmid = I('gmid');
        $amount = I('amount');
        if (!($amount && ($amount > 0) && (is_numeric($amount)))) {
            $this->ajaxReturn(array("error" => "1", "msg" => "金额有误"));
            exit;
        }
        $amount = (float)$amount;
        $prev = M('gm_mem')->where(array("id" => $gmid))->find();
        if (!$prev) {
            $this->ajaxReturn(array("error" => "1", "msg" => "参数有误"));
            exit;
        }
        //用户的游戏币余额要减少
        $prev_remain = $prev['remain'];
        $new_remain = $prev_remain - $amount;
        if ($new_remain < 0) {
            $this->ajaxReturn(array("error" => "1", "msg" => "扣除金额不能大于用户余额"));
            exit;
        }
        M('gm_mem')->where(array("id" => $gmid))->setField("remain", $new_remain);
        //创建扣除记录
        $mem_id = $prev['mem_id'];
        $app_id = $prev['app_id'];
        $this->addGmMemBackRecord($mem_id, $app_id, $amount, "游戏币扣回");
        $this->ajaxReturn(array("error" => "0", "msg" => "扣回成功，用户现在的游戏币余额为".$new_remain));
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

    public function _where_time_interval(&$where, $field) {
        if (isset($_GET["start_time"]) && $_GET["start_time"] && isset($_GET["end_time"]) && $_GET["end_time"]
        ) {
            $where[$field] = array(
                array("gt", strtotime($_GET["start_time"])),
                array("lt", strtotime($_GET["end_time"]))
            );
        }
    }

    public function AdminForAgent() {
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
//        foreach($items as $k=>$item){
//            if($item['admin_id']==0){
//                $items[$k]['adminname']="平台官方";
//            }
//        }
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
        $where = array();
        $where["_string"] = "agc.admin_id !=0 AND agc.admin_id !=1";
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
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->count();
        $page = $this->page($count, 20);
        $items = M('gm_agentcharge')
            ->field("agc.*,u.user_login as subname,g.name as gamename,ua.user_login as agentname")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
            ->where($where)
            ->order("agc.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sums = M('gm_agentcharge')
            ->field("sum(agc.money) as total")
            ->alias('agc')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=agc.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=agc.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users ua ON ua.id=agc.admin_id")
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
        $where['_string'] = "gc.admin_id !=0 AND gc.admin_id !=1";
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

    public function getback() {
        $this->display();
    }

    public function getMemberRemain($name) {
        $mid = M('members')->where(array("username" => $name))->getField("id");

        return M('gm_mem')->where(array("mem_id" => $mid))->getField("remain");
    }

    public function memberExist($name) {
        return M('members')->where(array("username" => $name))->count();
    }

    public function getMemberId($name) {
        return M('members')->where(array("username" => $name))->getField("id");
    }

    public function getBalanceAllType() {
        $type = I('type');
        $uname = I('username');
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
        $balance = 0;
        if ($type == 'member') {
            if (!$this->memberExist($uname)) {
                $this->ajaxReturn(array("error" => "1", "msg" => "玩家不存在"));
                exit;
            }
            $balance = $this->getMemberRemain($uname);
        } else if ($type == 'agent') {
            $agent_data = M('users')->where(array("user_login" => $uname, "user_type" => $agent_roleid))->find();
            if (!$agent_data) {
                $this->ajaxReturn(array("error" => "1", "msg" => "代理不存在"));
            }
            $agentId = $agent_data['id'];
            $obj = new \Huosdk\Agent($agentId);
            $balance = $obj->getCurrentBalance();
        } else if ($type == 'subagent') {
            $agent_data = M('users')->where(array("user_login" => $uname, "user_type" => $subagent_roleid))->find();
            if (!$agent_data) {
                $this->ajaxReturn(array("error" => "1", "msg" => "下级代理不存在"));
            }
            $agentId = $agent_data['id'];
            $obj = new \Huosdk\Agent($agentId);
            $balance = $obj->getCurrentBalance();
        }
        $this->ajaxReturn(array("error" => "0", "msg" => $balance));
    }

    public function getback_post() {
        $obj = new \Huosdk\Account();
        $agent_roleid = $obj->getAgentRoleId();
        $subagent_roleid = $obj->getSubAgentRoleId();
//        print_r($_POST);
        $from = I('from');
        $uname = I('username');
        $amount = I('amount');
        $remark = I('beizhu');
        if (!$amount || !is_numeric($amount) || ($amount <= 0)) {
            $this->error("数量不正确");
            exit;
        }
        if ($from == 'member') {
            if (!$this->memberExist($uname)) {
                $this->error("玩家不存在");
                exit;
            }
            $mid = $this->getMemberId($uname);
            $this->getMemberCoinBack($mid, $amount, $remark);
            $this->success("扣回成功");
        } else if ($from == 'agent') {
            $agent_data = M('users')->where(array("user_login" => $uname, "user_type" => $agent_roleid))->find();
            if (!$agent_data) {
                $this->error("代理不存在");
                exit;
            }
            $agentId = $agent_data['id'];
            $this->getAgentCoinBack($from, $agentId, $amount, $remark);
            $this->success("扣回成功");
        } else if ($from == 'subagent') {
            $agent_data = M('users')->where(array("user_login" => $uname, "user_type" => $subagent_roleid))->find();
            if (!$agent_data) {
                $this->error("下级代理不存在");
                exit;
            }
            $agentId = $agent_data['id'];
            $this->getAgentCoinBack($from, $agentId, $amount, $remark);
            $this->success("扣回成功");
        }
    }

    public function getMemberCoinBack($mid, $amount, $remark) {
        $prev_data = M('gm_mem')->where(array("mem_id" => $mid))->find();
        $prev_remain = $prev_data['remain'];
        $new_remain = $prev_remain - $amount;
        M('gm_mem')->where(array("mem_id" => $mid))->setField("remain", $new_remain);
        $this->addGmBackRecord("member", $mid, $amount, $remark);
    }

    public function getAgentCoinBack($from, $agent_id, $amount, $remark) {
        $prev_data = M('agent_ext')->where(array("agent_id" => $agent_id))->find();
        $prev_balance = $prev_data['balance'];
        $new_balance = $prev_balance - $amount;
        M('agent_ext')->where(array("agent_id" => $agent_id))->setField("balance", $new_balance);
        $this->addGmBackRecord($from, $agent_id, $amount, $remark);
    }

    public function addGmBackRecord($from, $uid, $amount, $remark) {
        $data = array();
        $data['create_time'] = time();
        $data['from'] = $from;
        $data['uid'] = $uid;
        $data['amount'] = $amount;
        $data['remark'] = $remark;
        M('gm_back')->add($data);
    }

    public function addGmMemBackRecord($uid, $app_id, $amount, $remark) {
        $data = array();
        $data['create_time'] = time();
        $data['from'] = "member";
        $data['uid'] = $uid;
        $data['app_id'] = $app_id;
        $data['amount'] = $amount;
        $data['remark'] = $remark;
        M('gm_back')->add($data);
    }

    public function getBackRecord() {
        $where = array();
//        $where['_string']="(gb.from = 'agent') OR (gb.from = 'subagent')";
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gb.create_time");
        if (isset($_GET['from']) && ($_GET['from'])) {
            $where['gb.from'] = $_GET['from'];
        }
        if (isset($_GET['fromname']) && ($_GET['fromname'])) {
            $uname = $_GET['fromname'];
            if ($this->memberExist($uname)) {
                $where['gb.uid'] = $this->getMemberId($uname);
            } else if ($this->AgentExists($uname)) {
                $where['gb.uid'] = $this->GetAgentId($uname);
            }
        }
        $total_size = M('gm_back')
            ->alias('gb')
            ->where($where)
            ->count();
        $page = $this->page($total_size, 20);
        $items = M('gm_back')
            ->field("gb.*,u.user_login as agentname,m.username as membername,g.name as gamename")
            ->alias('gb')
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gb.uid")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gb.uid")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gb.app_id")
            ->order("gb.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($items as $key => $item) {
            if ($item['from'] == "member") {
                $items[$key]['type'] = "玩家";
                $items[$key]['name'] = $this->getMemberName($item['uid']);
                $items[$key]['coin_type'] = "游戏币";
            } else if ($item['from'] == "agent") {
                $items[$key]['type'] = "代理";
                $items[$key]['name'] = $this->getAgentName($item['uid']);
                $items[$key]['coin_type'] = "平台币";
            } else if ($item['from'] == "subagent") {
                $items[$key]['type'] = "下级代理";
                $items[$key]['name'] = $this->getAgentName($item['uid']);
                $items[$key]['coin_type'] = "平台币";
            }
        }
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function getMemberName($mid) {
        return M('members')->where(array("id" => $mid))->getField("username");
    }

    public function getAgentName($agent_id) {
        return M('users')->where(array("id" => $agent_id))->getField("user_login");
    }

    public function AgentExists($name) {
        return M('users')->where(array("user_login" => $name))->find();
    }

    public function GetAgentId($name) {
        return M('users')->where(array("user_login" => $name))->getField("id");
    }

    public function getMemberSelectList() {
        $ms = M('members')->select();
        $txt = "<option value='0'>选择</option>";
        foreach ($ms as $k => $v) {
            $id = $v['id'];
            $name = $v['username'];
            $txt .= "<option value='$id'>$name</option>";
        }

        return $txt;
    }

    public function getGameSelectList() {
        $ms = M('game')->where(array("is_delete" => "2"))->select();
        $txt = "<option value='0'>选择</option>";
        foreach ($ms as $k => $v) {
            $id = $v['id'];
            $name = $v['name'];
            $txt .= "<option value='$id'>$name</option>";
        }

        return $txt;
    }

    public function chargePtb() {
        $this->assign("member_select_txt", $this->getMemberSelectList());
        $this->assign("game_select_txt", $this->getGameSelectList());
        $this->display();
    }

    public function chargePtb_post() {
        $mem_id = I('mem_id');
        $app_id = I('app_id');
        $amount = I('amount');
        $remark = I('remark');
        if (!$mem_id) {
            $this->error("请选择玩家");
            exit;
        }
        if (!$app_id) {
            $this->error("请选择游戏");
            exit;
        }
        if (!$amount || !is_numeric($amount) || ($amount < 0)) {
            $this->error("金额有误");
        }
        $amount = (float)$amount;
        $admin_id = get_current_admin_id();
        $hs_charge_obj = new \Huosdk\Charge();
        $hs_charge_obj->adminChargeForMember($admin_id, $mem_id, $app_id, $amount, $amount, $remark);
        $this->success("充值成功");
    }

    public function chargePtbRecord() {
        $where = array();
//        $where['_string']="(gb.from = 'agent') OR (gb.from = 'subagent')";
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "gc.create_time");
        $where['gc.admin_id'] = 1;
//        if(isset($_GET['fromname'])&&($_GET['fromname'])){
//            $uname=$_GET['fromname'];
//            if($this->memberExist($uname)){
//                $where['gb.uid']=$this->getMemberId($uname);
//            }else if($this->AgentExists($uname)){
//                $where['gb.uid']=$this->GetAgentId($uname);
//            }
//        }
        if (isset($_GET['order_id']) && $_GET['order_id']) {
            $where['gc.order_id'] = $_GET['order_id'];
        }
        if (isset($_GET['membername']) && $_GET['membername']) {
            $name = $_GET['membername'];
            $where['m.username'] = array("like", "%$name%");
        }
        $total_size = M('gm_charge')
            ->alias('gc')
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->count();
        $page = $this->page($total_size, 20);
        $items = M('gm_charge')
            ->field("gc.*,g.name,m.username,u.user_login")
            ->alias('gc')
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=gc.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gc.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gc.app_id")
            ->order("gc.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }
}