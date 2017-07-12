<?php
namespace Main\Controller;


class MoneyController extends FinanceController {
    function _initialize() {
        parent::_initialize();
    }

    public function getMyMembers($agent_id) {
        return M('members')->where(array("agent_id" => $agent_id))->select();
    }

    public function _member_select_list() {
        $members = $this->getMyMembers($this->agid);
        $select_txt = "<option value='0'>选择玩家</option>";
        foreach ($members as $k => $v) {
            $mem_id = $v['id'];
            $mem_name = $v['username'];
            $select_txt .= "<option value='$mem_id'>$mem_name</option>";
        }
        $this->assign("member_select_txt", $select_txt);
    }

    public function recharge_member() {
        in_case_notpass();
        $model = M("agent_game");
        $count = $model->where(array("agent_id" => $_SESSION['agent_id']))->count();
        $Page = new \Think\Page($count, 8);
        $show = $Page->show();// 分页显示输出 
        $games = $model
            ->field("ag.id,ag.app_id,agr.benefit_type,agr.agent_rate,g.name as gamename,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."agent_game_rate agr ON agr.ag_id=ag.id")
            ->where(array("ag.agent_id" => $_SESSION['agent_id'], "g.is_delete" => 2))
            ->order("ag.update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $select_txt = "<option value='0'>选择游戏</option>";
        foreach ($games as $k => $v) {
            $id = $v['id'];
            $app_id = $v['app_id'];
            $app_name = $v['gamename'];
            $benefit_type = $v['benefit_type'];
            $agent_rate = $v['agent_rate'];
            $select_txt .= "<option value='$app_id' data-agid='$id' data-agent-rate='$agent_rate'>$app_name</option>";
        }
        $this->assign("game_select_txt", $select_txt);
        $this->_member_select_list();
        if (I('p')) {
            $this->assign("show_game_chooser", 'yes');
        }
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
        $subagents = $this->Huosdk_agent->getMySubAgents();
        $this->assign("subagents", $subagents);
        $this->assign("page_title", "充值");
        $this->display();
    }

    public function recharge_sub() {
        in_case_notpass();
        $model = M("agent_game");
        $count = $model->where(array("agent_id" => $_SESSION['agent_id']))->count();
        $Page = new \Think\Page($count, 8);
        $show = $Page->show();// 分页显示输出 
        $games = $model
            ->field("ag.*,g.name as gamename,g.icon")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->where(array("agent_id" => $_SESSION['agent_id']))
            ->order("update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        if (I('p')) {
            $this->assign("show_game_chooser", 'yes');
        }
        $balance_txt = $this->get_agent_balance();
        if (!$balance_txt) {
            $balance_txt = '0.00';
        }
        $this->assign("balance", $balance_txt);
        $freeze_txt = $this->get_agent_freeze();
        if (!$freeze_txt) {
            $freeze_txt = '0.00';
        }
        $this->assign("freeze", $freeze_txt);
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
        $subagents = $this->Huosdk_agent->getMySubAgents();
        $this->assign("subagents", $subagents);
        $this->assign("page_title", "充值");
        $this->display();
    }

    public function getuserpayment_post() {
        $this->ajaxReturn(array("error" => "0", "msg" => "成功 ".I('gamename')." ".I('memname')));
    }

    private function get_agent_ext_info() {
        return M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->find();
    }

    private function get_agent_freeze() {
        $ext = $this->get_agent_ext_info();
        return $ext['freeze'];
    }

    public function order() {
        $this->display();
    }

    public function order_up() {
        $this->display();
    }

    public function get_alipay_config() {
        $alipay_config['partner'] = C('alipay_config_partner');
        $alipay_config['key'] = C('alipay_config_key');
        $alipay_config['seller_email'] = C('seller_email');
        $alipay_config['seller_id'] = $alipay_config['partner'];
        // 商户的私钥（后缀是.pem）文件相对路径
        $alipay_config['private_key_path'] = SITE_PATH.'conf/pay/alipay/key/rsa_private_key.pem';
        // 支付宝公钥（后缀是.pem）文件相对路径
        $alipay_config['ali_public_key_path'] = SITE_PATH.'conf/pay/alipay/key/alipay_public_key.pem';
        // ↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        // 签名方式 不需修改
        $alipay_config['sign_type'] = strtoupper('RSA');
        // 字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset'] = strtolower('utf-8');
        // ca证书路径地址，用于curl中ssl校验
        // 请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert'] = SITE_PATH.'conf/cacert.pem';
        // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport'] = 'http';
        return $alipay_config;
    }

    //生成订单号
    function setorderid($mem_id) {
        list($usec, $sec) = explode(" ", microtime());
        // 取微秒前3位+再两位随机数+渠道ID后四位
        $orderid = $sec.substr($usec, 2, 3).rand(10, 99).sprintf("%04d", $mem_id % 10000);
        return $orderid;
    }

    function printResult($url, $req, $resp) {
        echo "=============<br>\n";
        echo "地址：".$url."<br>\n";
        echo "请求：".str_replace("\n", "\n<br>", htmlentities(createLinkString($req, false, true)))."<br>\n";
        echo "应答：".str_replace("\n", "\n<br>", htmlentities(createLinkString($resp, false, true)))."<br>\n";
        echo "=============<br>\n";
    }

    public function get_agent_game_rate($game_id) {
        $hs_benefit_obj = new \Huosdk\Benefit();
        $rate = $hs_benefit_obj->get_agent_game_agent_rate_V2($_SESSION['agent_id'], $game_id);
//        $rate=M('agent_game_rate')
//                ->where(array("agent_id"=>$_SESSION['agent_id'],"app_id"=>$game_id))
//                ->getField("agent_rate");
        return $rate;
    }

    public function check_paypwd($payPwd) {
        $sp_pw = pay_password($payPwd);
        $pay_pwd_result = M('users')->where(array("id" => $_SESSION['agent_id'], "pay_pwd" => $sp_pw))->find();
        if (!$pay_pwd_result) {
            return false;
        } else {
            return true;
        }
    }

    public function charge_for_sub_post() {
        $amount = I('post.os_amount');
        $subid = I('post.os_subid');
        $this->inCaseBalanceNotEnough($amount);
        $ptb_agent_model = M("ptb_agent");
        //代理的balance要减少
        $agdata = $ptb_agent_model->where(array("agent_id" => $this->agid))->find();
        $pre_balance_ag = $agdata['remain'];
        $ag_new_balance = $pre_balance_ag - $amount;
        $ptb_agent_model->where(array("agent_id" => $this->agid))->setField("remain", $ag_new_balance);
        //下级代理的balance要增加
        $data = $ptb_agent_model->where(array("agent_id" => $subid))->find();
        if (!$data) {
            $ptb_agent_model->add(
                array(
                    "agent_id"    => $subid,
                    "sum_money"   => $amount,
                    "total"       => $amount,
                    "remain"      => $amount,
                    "create_time" => time(),
                    "update_time" => time()
                )
            );
        } else {
            $new_data = array();
            $new_data['remain'] = $data['remain'] + $amount;
            $new_data['total'] = $data['total'] + $amount;
            $new_data['sum_money'] = $data['sum_money'] + $amount;
            $new_data['update_time'] = time();
            $ptb_agent_model->where(array("agent_id" => $subid))->save($new_data);
        }
        //在充值记录表中加入记录
        M('ptb_agentcharge')->add(
            array(
                "order_id"    => $this->setorderid(1),
                "admin_id"    => $this->agid,
                "agent_id"    => $subid,
                "money"       => $amount,
                "ptb_cnt"     => $amount,
                "discount"    => "1",
                "payway"      => "ptb",
                "ip"          => get_client_ip(),
                "create_time" => time(),
                "update_time" => time(),
                "status"      => "2",
                "remark"      => "agent charge for sub"
            )
        );
        $this->display('Money/order_member_balance');
    }

    public function check_paypwd_post() {
        $payPwd = I('paypwd');
        $sp_pw = pay_password($payPwd);
        $pay_pwd_result = M('users')->where(array("id" => $_SESSION['agent_id'], "pay_pwd" => $sp_pw))->find();
        if (!$pay_pwd_result) {
            $this->ajaxReturn(array("error" => "1", "msg" => "支付密码错误"));
            exit;
        }
        $this->ajaxReturn(array("error" => "0", "msg" => "支付密码正确"));
    }

    public function order_member_post() {
        $amount = I('os_amount');
        $app_id = I('os_gameid');
        $mem_name = I('os_mem_name');
        $mem_id = get_memid_by_name($mem_name);
        $rate = $this->get_agent_game_rate($app_id);
        if (!($rate > 0 && $rate < 1)) {
            echo '内部错误';
            exit;
        }
        $gm_cnt = round($amount / $rate);
        $this->inCaseBalanceNotEnough($amount);
        $this->order_member_balance($amount, $gm_cnt, $rate);
    }

    public function get_agent_balance_old() {
        $balance = M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->getField("balance");
        $result = (float)$balance;
        return $result;
    }

    public function get_agent_balance() {
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $balance = $hs_pb_obj->getBalance($this->agid);
        return (float)$balance;
    }

    public function agent_charge_for_member_db($amount, $app_id, $mem_id, $payway, $order_id) {
        $rate = $this->get_agent_game_rate($app_id);
        if (!($rate > 0 && $rate < 1)) {
            return false;
        }
        $gm_cnt = round($amount / $rate, 2);
        $data1 = array(
            "order_id"    => $order_id,
            "app_id"      => $app_id,
            "mem_id"      => $mem_id,
            "money"       => $amount,
            "gm_cnt"      => $gm_cnt,
            "discount"    => $rate,
            "payway"      => $payway,
            "ip"          => get_client_ip(),
            "status"      => "1",
            "create_time" => time(),
            "update_time" => time(),
            "remark"      => ""
        );
        M('gm_charge')->add($data1);
    }

    public function order_member_do_after_success($order_id, $real_amount) {
        $pre_data = M('gm_charge')->where(array("order_id" => $order_id))->find();
        //如果订单的状态已经是成功的，就不要再重复更新了
        if ($pre_data['status'] == '2') {
//            echo "订单状态已经更新过了<br />";
            return;
        }
        //如果发现实际交易金额跟记录的金额不同，就不进行后续的操作了
        //用分为单位比较两者的金额，所以传递过来的real_amount单位但是分
        if ($real_amount != $pre_data['money'] * 100) {
            $pre_money = $pre_data['money'] * 100;
            echo "订单金额不一致 实际支付金额 $real_amount 提交的金额 $pre_money <br />";
            return;
        }
        //玩家充值记录表里面，此订单的状态要设置为2，update_time要更新
        M('gm_charge')->where(array("order_id" => $order_id))->setField("status", "2");
        M('gm_charge')->where(array("order_id" => $order_id))->setField("update_time", time());
        //gm_agentcharge中要加入这个记录，这相当于用支付宝或者银联购买了平台币
        M('gm_agentcharge')->add(
            array(
                "order_id"    => $order_id,
                "admin_id"    => "0",
                "agent_id"    => $_SESSION['agent_id'],
                "app_id"      => $pre_data['app_id'],
                "money"       => $pre_data['money'],
                "gm_cnt"      => $pre_data['gm_cnt'],
                "discount"    => $pre_data['discount'],
                "payway"      => $pre_data['payway'],
                "ip"          => $pre_data['ip'],
                "status"      => "2",
                "create_time" => time(),
                "update_time" => time()
            )
        );
        //既然给用户充值了平台币，用户的相应余额就要增加
        $this->update_mem_gm($pre_data['mem_id'], $pre_data['app_id'], $pre_data['money'], $pre_data['gm_cnt']);
    }

    public function agent_charge_for_member_db_after($out_trade_no, $trade_no, $amount) {
        $orderid = $out_trade_no;
        M('gm_charge')->where(array("order_id" => $orderid))->setField("status", "2");
        $pay_data = M('gm_charge')->where(array("order_id" => I('orderid')))->find();
        $app_id = $pay_data['app_id'];
        $mem_id = $pay_data['mem_id'];
//        $amount=$pay_data['money'];
        $gm_cnt = $pay_data['gm_cnt'];
        $rate = $pay_data['discount'];
        $payway = $pay_data['payway'];
        $pre = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
        if ($pre) {
            $data = array(
                "sum_money"   => $pre['sum_money'] + $gm_cnt,
                "total"       => $pre['total'] + $gm_cnt,
                "remine"      => $pre['remine'] + $gm_cnt,
                "create_time" => time(),
                "update_time" => time(),
            );
            M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->save($data);
        } else {
            $data = array(
                "mem_id"      => $mem_id,
                "app_id"      => $app_id,
                "sum_money"   => $gm_cnt,
                "total"       => $gm_cnt,
                "remine"      => $gm_cnt,
                "create_time" => time(),
                "update_time" => time(),
            );
            M('gm_mem')->add($data);
        }
        $data3 = array(
            "order_id"    => $orderid,
            "flag"        => "5", /* 代理发放  */
            "agent_id"    => $_SESSION['agent_id'],
            "app_id"      => $app_id,
            "money"       => $amount,
            "gm_cnt"      => $gm_cnt,
            "discount"    => $rate,
            "payway"      => $payway,
            "ip"          => get_client_ip(),
            "status"      => "2",
            "create_time" => time(),
            "update_time" => time(),
            "remark"      => ""
        );
        M('gm_agentcharge')->add($data3);
    }

    public function order_member_balance($amount, $gm_cnt, $rate) {
        $total_balance = $this->get_agent_balance();
        $total_balance = (float)$total_balance;
        if ($total_balance < $amount) {
            echo '余额不足，请充值';
            exit;
        } else {
            $amount = I('os_amount');
            $app_id = I('os_gameid');
            $mem_name = I('os_mem_name');
            $mem_id = get_memid_by_name($mem_name);
            //从agent的balance中减去相应金额
            $pre = M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->find();
            if ($pre) {
                $new_balance = $pre['balance'] - $amount;
                M('agent_ext')->where(array("agent_id" => $_SESSION['agent_id']))->setField("balance", $new_balance);
            }
            //玩家账户中的平台币金额要增加，表格是gm_mem
            $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
            //如果账户中已经存在，就更新
            if ($pre_gmm) {
                $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))
                                      ->save(
                                          array(
                                              "sum_money"   => $pre_gmm['sum_money'] + $amount,
                                              "total"       => $pre_gmm['total'] + $gm_cnt,
                                              "remain"      => $pre_gmm['remain'] + $gm_cnt,
                                              "update_time" => time()
                                          )
                                      );
            } else {
                //如果账户中不存在，就创建初始记录
                M('gm_mem')->add(
                    array(
                        "mem_id"      => $mem_id, "app_id" => $app_id,
                        "sum_money"   => $amount, "total" => $gm_cnt, "remain" => $gm_cnt,
                        "create_time" => time(), "update_time" => time()
                    )
                );
            }
            //gm_charge中要加入这次充值记录，这是代理给玩家充的
            M('gm_charge')->add(
                array(
                    "order_id"    => $this->setorderid(1),
                    "admin_id"    => $_SESSION['agent_id'],
                    "mem_id"      => $mem_id,
                    "app_id"      => $app_id,
                    "money"       => $amount,
                    "gm_cnt"      => $gm_cnt,
                    "discount"    => $rate,
                    "payway"      => "balance",
                    "ip"          => get_client_ip(),
                    "status"      => "2",
                    "create_time" => time(),
                    "update_time" => time(),
                    "remark"      => "from agent ".$_SESSION['agent_id']
                )
            );
            //gm_agent_charge中要加入这个记录，这相当于用余额兑换平台币
            M('gm_agentcharge')->add(
                array(
                    "order_id"    => $this->setorderid(1),
                    "admin_id"    => "0",
                    "agent_id"    => $_SESSION['agent_id'],
                    "app_id"      => $app_id,
                    "money"       => $amount,
                    "gm_cnt"      => $gm_cnt,
                    "discount"    => $rate,
                    "payway"      => "balance",
                    "ip"          => get_client_ip(),
                    "status"      => "2",
                    "create_time" => time(),
                    "update_time" => time()
                )
            );
            $this->display('Money/order_member_balance');
        }
    }

    public function update_mem_gm($mem_id, $app_id, $amount, $gm_cnt) {
        //玩家账户中的平台币金额要增加，表格是gm_mem
        $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
        //如果账户中已经存在，就更新
        if ($pre_gmm) {
            $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))
                                  ->save(
                                      array(
                                          "sum_money"   => $pre_gmm['sum_money'] + $amount,
                                          "total"       => $pre_gmm['total'] + $gm_cnt,
                                          "remain"      => $pre_gmm['remain'] + $gm_cnt,
                                          "update_time" => time()
                                      )
                                  );
        } else {
            //如果账户中不存在，就创建初始记录
            M('gm_mem')->add(
                array(
                    "mem_id"      => $mem_id, "app_id" => $app_id,
                    "sum_money"   => $amount, "total" => $gm_cnt, "remain" => $gm_cnt,
                    "create_time" => time(), "update_time" => time()
                )
            );
        }
    }

    public function recharge_balance() {
        $this->display();
    }
}