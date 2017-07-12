<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class UcenterController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $status = $this->huoshu_agent->getStatus();
        if ($_SESSION['roleid'] == 7) {
            redirect(U('Agent/game/mygames'));
        } else if ($_SESSION['roleid'] == 6) {
            if ($status == '1') {
                redirect(U('Agent/ucenter/tovip'));
            } else {
                redirect(U('Agent/game/apply_game'));
            }
        }
    }

    public function MyGameCenter() {
        $this->display();
    }

    private function basic_info_complete() {
        $model = M('agent_man');
        $data = $model->where(
            array(
                "agent_id" => $this->agid
            )
        )->find();
        if (!empty($data['banknum']) && !empty($data['bankname']) && !empty($data['branchname'])) {
            return true;
        } else {
            return false;
        }
    }

    private function js_info_complete() {
        $model = M('agent_man');
        $data = $model->where(
            array(
                "agent_id" => $this->agid
            )
        )->find();
        if ($data['alipay'] || $data['banknum']) {
            return true;
        } else {
            return false;
        }
    }

    public function tovip() {
        if ($this->basic_info_complete()) {
            $basic_txt = 'true';
        } else {
            $basic_txt = 'false';
        }
        if ($this->js_info_complete()) {
            $js_txt = 'true';
        } else {
            $js_txt = 'false';
        }
        if ($this->huoshu_agent->PayPwdSet()) {
            $paypwd_txt = 'true';
        } else {
            $paypwd_txt = 'false';
        }
        $model = M('agent_man');
        $agent_man_info = $model->where(
            array(
                "agent_id" => $this->agid
            )
        )->find();
        $all_complete = "";
        if ($this->js_info_complete() && $this->basic_info_complete()) {
            $all_complete = "info";
            $check_status = $this->huoshu_agent->getStatus();
            if ($check_status == '2') {
                $all_complete = "pass";
            } else if ($check_status == '3') {
                $all_complete = "notpass";
            }
        }
        $this->assign("all_complete", $all_complete);
        $this->assign("agent_man_info", $agent_man_info);
        $this->assign("basic_info_complete", $basic_txt);
        $this->assign("js_info_complete", $js_txt);
        $this->assign("paypwd_info_complete", $paypwd_txt);
        $this->display();
    }

    public function toEditPass() {
        $this->display();
    }

    public function toEditPhone() {
        $this->display();
    }

    public function toEditUser() {
        $city_obj = new \Huosdk\City();
        $provinces = $city_obj->get_all_provinces();
        $cities = $city_obj->get_cities_of_province("广东省");
        $model = M('agent_man');
        $agent_man_info = $model->where(
            array(
                "agent_id" => $this->agid
            )
        )->find();
        if ($agent_man_info['branchname']) {
            $info_arr = explode(" ", $agent_man_info['branchname']);
            $agent_man_info['province'] = $info_arr[0];
            $agent_man_info['city'] = $info_arr[1];
        } else {
            $agent_man_info['province'] = "广东省";
            $agent_man_info['city'] = "广州市";
        }
        $this->assign("agent_man_info", $agent_man_info);
        $this->assign("provinces", $provinces);
        $this->assign("cities", $cities);
        $this->display();
    }

    public function get_cities_of_province_post() {
        $p = I('province');
        $city_obj = new \Huosdk\City();
        $cities = $city_obj->get_cities_of_province($p);
        $txt = '';
        foreach ($cities as $city) {
            $txt .= "<li class='li-down'>$city</li>";
        }
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "$txt"
            )
        );
    }

    public function checkvipstate() {
        $this->display();
    }

    public function security() {
        $this->display();
    }

    public function basic_info_post() {
        $name = I('name');
        $qq = I('qq');
        $phone = get_logged_in_user_phone();
        $model = M('users');
        // 用两次setField可能会性能稍低，或许用一次性更新两个字段的比较好
        // 2016-08-08 16:21:48 严旭
        $model->where(
            array(
                "mobile" => "$phone"
            )
        )->setField("user_nicename", $name);
        if (isset($_POST['qq'])) {
            if (!is_numeric($_POST['qq'])) {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => "qq号码格式不正确"
                    )
                );
                exit();
            }
            $model->where(
                array(
                    "mobile" => "$phone"
                )
            )->setField("qq", $qq);
            if (isset($_POST['idcard'])) {
                if (!$this->is_valide_idnum($_POST['idcard'])) {
                    $this->ajaxReturn(
                        array(
                            "error" => "1",
                            "msg"   => "身份证号码格式不正确"
                        )
                    );
                    exit();
                }
                $this->save_IDcard_post(I('idcard'));
            }
            $this->ajaxReturn(
                array(
                    "error" => "0",
                    "msg"   => "保存成功"
                )
            );
        }
    }

    public function is_valide_idnum($idnum) {
        $match = '/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/';
        return preg_match($match, $idnum);
    }

    public function up_info_post() {
        $code = I("code");
        $bankname = I("bank");
        $banknum = I("bankId");
        $cardholder = I("cardholder");
        if (empty($code)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不能为空"
                )
            );
            exit();
        }
        if ($code != $_SESSION['sms_code']) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不正确"
                )
            );
            exit();
        }
        if (empty($cardholder)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "户名不能为空，请填写真实姓名"
                )
            );
            exit();
        }
        if (empty($bankname)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "开户银行不能为空"
                )
            );
            exit();
        }
        if (empty($banknum)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "银行卡号不能为空"
                )
            );
            exit();
        }
        if (!is_valide_card_number($banknum)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "卡号格式不正确"
                )
            );
            exit();
        }
        $agent_id = $this->agid;
        $data = array(
            "agent_id"   => $agent_id,
            "bankname"   => $bankname,
            "banknum"    => $banknum,
            "cardholder" => $cardholder,
            "branchname" => I("province")." ".I("city")
        );
        // $update_data=array(
        // "bankname"=>$bankname,
        // "banknum"=>$banknum,
        // "branchname"=>I("province")." ".I("city"),
        // );
        $model = M('agent_man');
        $exist = $model->where(
            array(
                "agent_id" => $agent_id
            )
        )->find();
        if ($exist) {
            $model->where(
                array(
                    "agent_id" => $agent_id
                )
            )->save($data);
        } else {
            $model->add($data);
        }
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "账户信息保存成功"
            )
        );
    }

    public function zfb_info_post() {
        $zfb = I("zfb");
        $code = I("code");
        if (empty($code)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不能为空"
                )
            );
            exit();
        }
        if ($code != $_SESSION['sms_code']) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不正确"
                )
            );
            exit();
        }
        if (empty($zfb)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "支付宝帐号不能为空"
                )
            );
            exit();
        }
        $agent_id = $this->agid;
        $data = array(
            "agent_id" => $agent_id,
            "zfb"      => $zfb
        );
        $model = M('agent_man');
        $exist = $model->where(
            array(
                "agent_id" => $agent_id
            )
        )->find();
        if ($exist) {
            $model->where(
                array(
                    "agent_id" => $agent_id
                )
            )->save($data);
        } else {
            $model->add($data);
        }
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "保存成功"
            )
        );
    }

    public function IDcard_post() {
        $idcard = I('idCard');
        $agent_id = $this->agid;
        $data = array(
            "agent_id" => $agent_id,
            "idcard"   => $idcard
        );
        $model = M('agent_man');
        $exist = $model->where(
            array(
                "agent_id" => $agent_id
            )
        )->find();
        if ($exist) {
            $model->where(
                array(
                    "agent_id" => $agent_id
                )
            )->setField("idcard", "$idcard");
        } else {
            $model->add($data);
        }
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "身份信息保存成功"
            )
        );
    }

    public function save_IDcard_post($idcard) {
        $agent_id = $this->agid;
        $data = array(
            "agent_id" => $agent_id,
            "idcard"   => $idcard
        );
        $model = M('agent_man');
        $exist = $model->where(
            array(
                "agent_id" => $agent_id
            )
        )->find();
        if ($exist) {
            $model->where(
                array(
                    "agent_id" => $agent_id
                )
            )->setField("idcard", "$idcard");
        } else {
            $model->add($data);
        }
    }

    private function check_old_pass($pass) {
        $sppass = sp_password($pass);
        $model = M('users');
        $exist = $model->where(
            array(
                "id"        => $this->agid,
                "user_pass" => $sppass
            )
        )->find();
        return $exist;
    }

    public function change_password_post() {
        $oldpwd = I('oldpwd');
        $pass = I('pass');
        if (empty($oldpwd) || empty($pass)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "参数不能为空"
                )
            );
            exit();
        }
        if (!$this->check_old_pass($oldpwd)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "原密码不正确"
                )
            );
            exit();
        }
        $this->huoshu_agent->setPassword($pass);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "密码修改成功"
            )
        );
    }

    public function sendPhoneCode() {
        $phone = I('phone');
        $phone_send_result = $this->sendPhoneVerifyCode($phone);
        if ($phone_send_result['status'] == 1) {
            $this->ajaxReturn(
                array(
                    "error" => "0",
                    "msg"   => "验证码发送成功，请尽快输入"
                )
            );
            exit();
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => $phone_send_result['msg']
                )
            );
            exit();
        }
    }

    private function sendPhoneVerifyCode($phone) {
        $_SESSION['phone'] = $phone;
        $result = sendMsg_alidayu($phone);
        return $result;
    }

    public function change_phone_post() {
        // I('newphone');
        if (!I('newphone')) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "手机号码不能为空"
                )
            );
        }
        if (!is_valide_phone_number(I('newphone'))) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "手机号码格式不正确"
                )
            );
        }
        $this->huoshu_agent->setPhone(I('newphone'));
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "修改成功"
            )
        );
    }

    public function change_phone_check_code_post() {
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "验证成功"
            )
        );
    }

    public function toCipherCode() {
        $this->display();
    }

    public function change_pay_pass_post() {
        $code = I('code');
        if (empty($code)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不能为空"
                )
            );
            exit();
        }
        if ($code != $_SESSION['sms_code']) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "短信验证码不正确"
                )
            );
            exit();
        }
        if (!$this->is_valide_pay_pass(I('pwd'))) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "密码格式不符合要求"
                )
            );
            exit();
        }
        $this->huoshu_agent->setPayPwd(I('pwd'));
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "支付密码保存成功"
            )
        );
    }

    private function is_valide_pay_pass($pass) {
        return preg_match("/^[0-9A-Za-z]{6,20}$/", $pass);
    }

    public function apply_game_sub_post() {
        $list = I('list');
        $subid = I('subid');
        if (empty($list) || empty($subid)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "参数错误"
                )
            );
            exit();
        }
        $this->huoshu_agent->addSubAgentGame($subid, $list);
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "申请成功"
            )
        );
    }

    public function check_member_account_post() {
        $model = M('members');
        $exist = $model->where(
            array(
                "username" => I('account'),
                "agent_id" => $this->agid
            )
        )->find();
        if ($exist) {
            $mem_id = get_memid_by_name(I('account'));
            // $model_game=M("mem_game");
            // $result=$model_game
            // ->field("")
            // ->alias("mg")
            // ->join("LEFT JOIN ")
            // ->where(array("mem_id"=>$mem_id))->select();
            $this->ajaxReturn(
                array(
                    "error" => "0",
                    "msg"   => "帐号存在"
                )
            );
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "帐号不存在"
                )
            );
        }
    }

    public function subAgent() {
        $hs_account_obj = new \Huosdk\Account();
        $subrole = $hs_account_obj->subAgentRoldId;
        $where = array();
        $where['ownerid'] = $this->agid;
        $where['user_type'] = $subrole;
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple_like($where, "user_login", "user_login");
        $hs_where_obj->get_simple_like($where, "user_nicename", "user_nicename");
        $count = M('users')->where($where)->count();
        $page = new \Think\Page($count, 20);
        $items = M('users')->where($where)->limit($page->firstRow, $page->listRows)->order("id desc")->select();
        $this->assign("count", $count);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function subAgentInfo() {
        $items = $this->huoshu_agent->getMySubAgents();
        $this->assign("items", $items);
        $this->display();
    }

    public function addSub() {
        // print_r($_SESSION);
        $this->display();
    }

    public function addSub_post() {
        if (I('post.user_login') && I('post.name') && I('post.pass')) {
            $result = $this->huoshu_agent->createSubAgent(I('post.user_login'), '', I('post.pass'), I('post.name'), '');
            if ($result == "1") {
                $this->ajaxReturn(
                    array(
                        "error" => "0",
                        "msg"   => "创建下级渠道成功"
                    )
                );
            } else {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => $result
                    )
                );
            }
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "所有字段都不能为空"
                )
            );
        }
    }

    public function editSub_post() {
        if (I('post.subid') && I('post.name') && I('post.phone') && I('post.email')) {
            $result = $this->huoshu_agent->editSubAgentInfo(
                I('post.subid'),
                I('post.phone'),
                I('post.name'),
                I('post.email')
            );
            if ($result == "1") {
                $this->ajaxReturn(
                    array(
                        "error" => "0",
                        "msg"   => "修改下级渠道信息成功"
                    )
                );
            } else {
                $this->ajaxReturn(
                    array(
                        "error" => "1",
                        "msg"   => $result
                    )
                );
            }
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "所有字段都不能为空"
                )
            );
        }
    }

    public function editSub() {
        $subid = I('path.2');
        $info = $this->huoshu_agent->getSubAgentInfo($subid);
        $this->assign("info", $info);
        $this->display();
    }

    public function delSub() {
        $subid = I('post.subid');
        $data = $this->huoshu_agent->disableSubAgent($subid);
        if ($data == 1) {
            $this->ajaxReturn(
                array(
                    "error" => "0",
                    "msg"   => "禁用成功"
                )
            );
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => $data
                )
            );
        }
    }

    public function enableSub() {
        $subid = I('post.subid');
        $data = $this->huoshu_agent->enableSubAgent($subid);
        if ($data == 1) {
            $this->ajaxReturn(
                array(
                    "error" => "0",
                    "msg"   => "启用成功"
                )
            );
        } else {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => $data
                )
            );
        }
    }
}