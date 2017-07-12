<?php
namespace Data\Controller;

use Common\Controller\AdminbaseController;

class RoleController extends AdminbaseController {
    protected $mr_model;

    function _initialize() {
        parent::_initialize();
        $this->mr_model = M("mg_role");
    }

    function index() {
        $this->_game(false, null, null, null, null, null);
        $this->_getmrdata();
        $this->display();
    }

    /* 获取玩家角色信息 */
    function _getmrdata() {
        $mext_model = M('mg_role');
        $username = I('param.username/s', '');
        if (!empty($username)) {
            $_GET['username'] = $username;
            $id = $this->members_model->where(array('username' => $username))->getField('id');
            if (!empty($id)) {
                $where_ands = array("mr.mem_id".$this->agentwhere);
            } else {
                $this->assign("count", 0);
                $this->assign("members", array());
                $this->assign("formget", $_GET);

                return;
            }
        }
        $fields = array(
            'gid'    => array(
                "field"    => "mr.app_id",
                "operator" => "="
            ),
            'server' => array(
                "field"    => "mr.server",
                "operator" => "="
            ),
            'role'   => array(
                "field"    => "mr.role",
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
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $where = join(" and ", $where_ands);
        $count = $this->mr_model
            ->alias("mr")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "mr.*, m.username, m.reg_time";
        $items = $this->mr_model
            ->alias("mr")
            ->field($field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."members m ON mr.mem_id = m.id")
            ->order("mr.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("count", $count);
        $this->assign("roles", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }
}