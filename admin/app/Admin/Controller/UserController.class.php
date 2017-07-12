<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class UserController extends AdminbaseController {
    protected $users_model, $role_model;

    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = D("Common/Role");
        //$this->cadmin_model = D("Common/Clientadmin");
    }

    function index() {
        $_user_type = I('user_type/d', 0);
        $_user_login = I('user_login/d', '');
        $_user_status = I('user_status/d', 0);
        $_user_id= I('user_id/d', 0);
        $_map = array();
        if (!empty($_user_type)) {
            $_map['user_type'] = $_user_type;
            $this->assign('user_type', $_user_type);
        }
        if (!empty($_user_id)) {
            $_map['id'] = $_user_id;
            $this->assign('id', $_user_id);
        }
        if (!empty($_user_login)) {
            $_map['id'] = $_user_login;
            $this->assign('user_login', $_user_login);
        }
        if (!empty($_user_status)) {
            $_map['user_status'] = $_user_status;
            $this->assign('user_status', $_user_status);
        }
        $count = $this->users_model->where($_map)->count();
        $rows = isset($_POST['rows']) ? $_POST['rows'] : $this->row;
        $page = $this->page($count, $rows);
        $_submit = I('submit/s', '');
        //下载
        if ($_submit == '下载') {
            $xlsCell = array(
                array('id', '账号序列'),
                array('user_login', '账号'),
                array('linkman', '联系人'),
                array('last_login_time', '最后登录时间'),
                array('user_email', '邮箱'),
                array('mobile', '电话'),
                array('qq', 'QQ'),
            );
            $users = $this->users_model
                ->order("id DESC")
                ->select();
            $xlsName = "管理员记录表";
            $this->exportExcel($xlsName, $xlsCell, $users);
        }
        $users = $this->users_model
            ->where($_map)
            ->order("create_time DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->_roles();
        $_agents = $this->users_model->getField("id,user_login user_login", true);
        $this->assign('agents', $_agents);
        $this->assign("page", $page->show('Admin'));
        $this->assign("users", $users);
        $this->display();
    }

    function add() {
        $this->_roles('', false);
        $this->display();
    }

    function add_post() {
        if (IS_POST) {
            if (!empty($_POST['role_id'])) {
                $role_id = I('role_id');
                $_POST['user_type'] = $role_id;
                $_POST['pay_pwd'] = $_POST['user_pass'];
                unset($_POST['role_id']);
                if ($this->users_model->create()) {
                    $result = $this->users_model->add();
                    if ($result !== false) {
                        $uid = $result;
                        $role_user_model = M("RoleUser");
                        $role_user_model->where(array("user_id" => $uid))->delete();
                        $role_user_model->add(array("role_id" => $role_id, "user_id" => $uid));
                        $this->success("添加成功！", U('Admin/User/index'));
                    } else {
                        $this->error("添加失败！");
                    }
                } else {
                    $this->error($this->users_model->getError());
                }
            }
        }
    }

    function edit() {
        $id = intval(I("get.id"));
        $user = $this->users_model->where(array("id" => $id))->find();
        $adminid = sp_get_current_admin_id();
        if (2 < $this->role_type) {
            if ($adminid != $user['id'] && $adminid != $user['ownid']) {
                $this->error("无权限");
            }
        }
        $this->_roles(0, false);
        $this->assign($user);
        $this->display();
    }

    function edit_post() {
        if (IS_POST) {
            if (!empty($_POST['role_id'])) {
                $data = $_POST;
                if (empty($data['user_pass'])) {
                    unset($data['user_pass']);
                }else{
                    $data['user_pass'] = sp_password($data['user_pass']);
            }
            if (empty($data['pay_pwd'])) {
                unset($data['pay_pwd']);
            }else{
                $data['pay_pwd'] = pay_password($data['pay_pwd']);
            }
            $role_id = $data['role_id'];
            $data['user_type'] = $role_id;
            unset($data['role_id']);

                if ($this->users_model->create($data)) {
                    $result = $this->users_model->save();
                    if ($result !== false) {
                        $uid = intval($_POST['id']);
                        $role_user_model = M("RoleUser");
                        $role_user_model->where(array("user_id" => $uid))->delete();
                        $role_user_model->add(array("role_id" => $role_id, "user_id" => $uid));
                        $this->success("修改成功！", U('Admin/User/index'));
                    } else {
                        $this->error("修改失败！");
                    }
                } else {
                    $this->error($this->users_model->getError());
                }
            } else {
                $this->error("请为此用户指定角色！");
            }
        }
    }

    /**
     *  删除
     */
    function delete() {
        $id = intval(I("get.id"));
        if ($id == 1) {
            $this->error("最高管理员不能删除！");
        }
        $cuser = $this->users_model->where("id=$id")->find();
        if ($this->users_model->where("id=$id")->delete() !== false) {
            M("RoleUser")->where(array("user_id" => $id))->delete();
// 			$cid = C('CLIENTID');
// 			$this->cadmin_model->where(array("user_login"=>$cuser['user_login'],'cid'=>$cid))->delete();
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    function userinfo() {
        $id = get_current_admin_id();
        $user = $this->users_model->where(array("id" => $id))->find();
        $this->assign($user);
        $this->display();
    }

    function userinfo_post() {
        if (IS_POST) {
            $_POST['id'] = get_current_admin_id();
            $create_result = $this->users_model
                ->field(
                    "user_login,user_email,last_login_ip,last_login_time,create_time,user_activation_key,user_status,role_id,score,user_type",
                    true
                )//排除相关字段
                ->create();
            if ($create_result) {
                if ($this->users_model->save() !== false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->users_model->getError());
            }
        }
    }

    function ban() {
        $id = intval($_GET['id']);
        if ($id) {
            $rst = $this->users_model->where(array("id" => $id))->setField('user_status', '3');
            if ($rst) {
                $this->success("管理员停用成功！", U("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function cancelban() {
        $id = intval($_GET['id']);
        if ($id) {
            $rst = $this->users_model->where(array("id" => $id))->setField('user_status', '2');
            if ($rst) {
                $this->success("管理员启用成功！", U("user/index"));
            } else {
                $this->error('管理员启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
}