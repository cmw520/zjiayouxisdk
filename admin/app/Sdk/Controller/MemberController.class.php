<?php
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;
use Newapp\Controller\DeliveryController;

class MemberController extends AdminbaseController {
    protected $game_model, $members_model, $where;

    function _initialize() {
        parent::_initialize();
        $this->members_model = M("members");
        $this->game_model = D("Common/Game");
    }

    function index() {
        $this->_mem_status();
        $this->_game();
        $this->_agents();
        $this->_mList();
        $admin_id = get_current_admin_id();
        $type = M('users')->where(array("id" => $admin_id))->getField("user_type");
        $this->assign("user_type", $type);
        $this->display();
    }

    public function getMemLoginLog($mem_id) {
        $model = M('login_log');
        $where = array();
        $where['ll.mem_id'] = $mem_id;
        $count = $model
            ->field("")
            ->alias("ll")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ll.app_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("ll.login_time,g.name as game_name")
            ->alias("ll")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ll.app_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("ll.id desc")
            ->select();
        $this->assign("login_records", $items);
        $this->assign("login_page", $page->show("Admin"));
    }

    public function getMemChargeRecords($mem_id) {
        $model = M('pay');
        $where = array();
        $where['p.mem_id'] = $mem_id;
        $where['p.status'] = 2;
        $count = $model
            ->field("")
            ->alias("p")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=p.app_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("p.create_time,p.amount,g.name as game_name")
            ->alias("p")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=p.app_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("p.id desc")
            ->select();
        $this->assign("charge_records", $items);
        $this->assign("charge_page", $page->show("Admin"));
    }

    function edit() {
        $hs_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_filter_obj->agent_select());
        $id = intval(I("get.id"));
        $where['m.id'] = $id;
        $where['p.status'] = 2;
        $member = $this->members_model
            ->field("m.*,g.name as game_name,u.user_login as agent_name,sum(p.amount) total")
            ->alias("m")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=m.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=m.agent_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."pay p ON p.mem_id=m.id")
            ->find();
        $hs_if_obj = new \Huosdk\Data\ItemsFields();
        $member['status_txt'] = $hs_if_obj->member_status_txt($member['status']);
        if (!$member['agent_name']) {
            $member['agent_name'] = "未绑定任何渠道";
        }
        $this->assign("data", $member);
        $mem_id = $id;
        $this->getMemLoginLog($mem_id);
        $this->getMemChargeRecords($mem_id);
        $this->getrole($mem_id);
        $this->display();
    }

    public function getrole($mem_id) {
        $model = M('mg_role_log');
        $where['mrl.mem_id'] = $mem_id;
        $count = $model
            ->alias('mrl')
            ->field("")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=mrl.app_id")
            ->where($where)
            ->group("mrl.app_id,mrl.role_level,mrl.server_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->alias('mrl')
            ->field("mrl.server_name,mrl.role_name,mrl.role_level,g.name")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=mrl.app_id")
            ->where($where)
            ->group("mrl.app_id,mrl.role_level,mrl.server_id")
            ->select();
        $this->assign("role_records", $items);
        $this->assign("role_page", $page->show("Admin"));
    }

    public function respective_edit_post() {
        $name = I("name");
        $value = I("value");
        $mem_id = I("mem_id");
        $hs_validate_obj = new \Huosdk\Validate();
        if ($name == "password") {
            $pass_valide = $hs_validate_obj->password2($value);
            if (!$pass_valide) {
                $this->ajaxReturn(array("error" => "1", "msg" => "密码格式不正确"));
                exit;
            }
            $hs_password_obj = new \Huosdk\Password();
            $en_pass = $hs_password_obj->member_password($value);
            M('members')->where(array("id" => $mem_id))->setField("password", $en_pass);
        } else if ($name == "mobile") {
            $valide = $hs_validate_obj->phone($value);
            if (!$valide) {
                $this->ajaxReturn(array("error" => "1", "msg" => "手机号码格式不正确"));
                exit;
            }
            if (\Huosdk\Member::PhoneInUse($value)) {
                $this->ajaxReturn(array("error" => "1", "msg" => "此手机号已经被使用，请选择其他号码"));
                exit;
            }
            M('members')->where(array("id" => $mem_id))->setField("mobile", $value);
        } else if ($name == "email") {
            M('members')->where(array("id" => $mem_id))->setField("email", $value);
        } else if ($name = "clearsecurity") {
            $_mem_data['mobile'] = '';
            $_mem_data['email'] = '';
            $_mem_data['id'] = $mem_id;
            M('members')->save($_mem_data);
            $this->ajaxReturn(array("error" => "0", "msg" => "密保清空成功"));
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "修改成功"));
    }

    function edit_post() {
        if (IS_POST) {
            $id = I("id");
            if (!empty($id) && $id > 0) {
                $password = I('password');
                if (!empty($password)) {
                    $data['password'] = pw_auth_code($password, C("AUTHCODE"));
                }
                if (trim(I('email'))) {
                    $data['email'] = I('email/s', '');
                }
                if (trim(I('mobile'))) {
                    $data['mobile'] = I('mobile/s', '');
                }
                $rs = $this->members_model->where("id = %d", $id)->save($data);
                if ($rs) {
                    $this->success("修改成功！", U("Member/index"));
                    exit();
                }
            } else {
                $this->error("未找到玩家账号");
            }
        }
    }

    function ban() {
        $id = I('get.id/d');
        if ($id) {
            $rst = $this->members_model->where(array("id" => $id))->setField('status', '3');
            if ($rst) {
                $this->success("账号冻结成功！", U("Member/index", array('id' => $id)));
            } else {
                $this->error('账号冻结失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function cancelban() {
        $id = I('get.id/d');
        if ($id) {
            $rst = $this->members_model->where(array("id" => $id))->setField('status', '2');
            if ($rst) {
                $this->success("账号解封成功！", U("Member/index", array('id' => $id)));
            } else {
                $this->error('账号解封失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /*
     * 玩家列表
     */
    function _mList() {
        $username = I('username');
        $start_time = I('start_time');
        $end_time = I('end_time');
        $mem_id = I('id/d');
        $where = array();
        if (2 < $this->role_type) {
            $where['_string'] = 'agent_id '.$this->agentwhere;
        }
//		$where_arr = array();
        if (!empty($username) && $username != '') {
            $where['username'] = array("like", "%$username%");
            $this->assign('username', $username);
        }
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->time($where, "reg_time");
        if (isset($_GET['status']) && $_GET['status']) {
            $current_status = $_GET['status'];
            $where['status'] = $_GET['status'];
        } else {
            $current_status = 0;
        }
        $hs_where_obj->get_simple($where, "email", "email");
        $hs_where_obj->get_simple($where, "mobile", "mobile");
        $hs_where_obj->get_simple($where, "imei", "imei");
        $hs_where_obj->get_simple($where, "app_id", "app_id");
        $hs_where_obj->get_simple($where, "agent_id", "agent_id");
        if (isset($_GET['username']) && $_GET['username']) {
            $uname = $_GET['username'];
            $where['username'] = array("like", "%$uname%");
        }
        if (!empty($mem_id)) {
            $where['id'] = "$mem_id";
            $count = $this->members_model
                ->where($where)
                ->count();
            $members = $this->members_model
                ->where($where)
                ->select();
            $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
            $page = $this->page(1, $rows);
            $this->assign('username', $members[0]['username']);
        } else {
            $count = $this->members_model->where($where)->count();
            $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
            $page = $this->page($count, $rows);
            $members = $this->members_model
                ->where($where)
                ->order("id DESC")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        }
        $this->assign("total_rows", $count);
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $member_status_select = $hs_ui_filter_obj->member_account_status($current_status);
        $this->assign("member_status_select", $member_status_select);
        $this->assign("app_select", $hs_ui_filter_obj->app_select());
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("members", $members);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("formget", $_GET);
    }

    /**
     * 玩家地址管理
     */
    public function address() {
        $mem_id = I("get.id/d", 0);
        if (empty($mem_id)) {
            $this->error("参数错误");
        }
        $_de_class = new DeliveryController();
        $_addr = $_de_class->getMemAddrFormat($mem_id);
        $this->assign($_addr);
        $this->display();
    }
}