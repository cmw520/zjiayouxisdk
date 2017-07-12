<?php
/**
 * PaybaseController.class.php UTF-8
 * 钱包基类
 *
 * @date    : 2016年9月7日下午6:22:47
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Common\Controller;

class PaybaseController extends AppframeController {
    private $wallet_model;

    public function __construct() {
    }

    function _initialize() {
        parent::_initialize();
    }

    // 获取玩家钱包信息
    public function getUserwallet($mem_id) {
        $this->wallet_model = M('ptb_mem');
        if (empty($mem_id) || $mem_id < 0) {
            // 判断输入参数合法性
            return array();
        }
        $map['mem_id'] = $mem_id;
        $wallet_info = $this->wallet_model->where($map)->find();
        if (empty($wallet_info)) {
            return array();
        }

        return $wallet_info;
    }

    // 获取玩家游戏币信息
    public function getUseryxb($mem_id, $gameid) {
        $this->wallet_model = M('gm_mem');
        if (empty($mem_id) || $mem_id < 0 || empty($gameid) || $gameid < 0) {
            // 判断输入参数合法性
            return array();
        }
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $gameid;
        $yxb_info = $this->wallet_model->where($map)->find();
        if (empty($yxb_info)) {
            return array();
        }

        return $yxb_info;
    }

    // 根据支付方式获取支付方式ID
    public function getPaywayid($payway) {
        if (empty($payway)) {
            return 0;
        }
        $map['payname'] = $payway;
        $pw_id = M('payway')->where($map)->getField('id');
        if (empty($pw_id)) {
            return 0;
        } else {
            return $pw_id;
        }
    }

    /*
     * 钱包支付通知函数
     */
    protected function wallet_post($orderid, $amount, $paymark = '') {
        $pay_model = M("gm_charge");
        // 获取支付表中的支付信息
        $pay_data = $pay_model->where(
            array(
                'order_id' => $orderid
            )
        )->find();
        $myamount = number_format($pay_data['real_amount'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        //判断金额是否正确
        if (($myamount == $amount) && 2 != $pay_data['status']) {
//         if (2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->save($pay_data);
            if ($rs) {
                $this->updateWallet($pay_data);
                //判断是否有渠道id
                $agentid = M('members')->where(array("id" => $pay_data['mem_id']))->getField("agent_id");
                if ($agentid > 0) {
                    $this->insert_statistics($pay_data);
                }
            }
        }
    }

    private function updateWallet($paydata) {
        $pm_model = M('gm_mem');
        $map['app_id'] = $paydata['app_id'];
        $map['mem_id'] = $paydata['mem_id'];
        $pm_data = $pm_model->where($map)->find();
        if (empty($pm_data)) {
            $pm_data['mem_id'] = $paydata['mem_id'];
            $pm_data['app_id'] = $paydata['app_id'];
            $pm_data['sum_money'] = $paydata['money'];
            $pm_data['total'] = $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['remain'] = $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['create_time'] = time();
            $pm_data['update_time'] = $pm_data['create_time'];
            $pm_model->add($pm_data);
        } else {
            $pm_data['sum_money'] += $paydata['money'];
            $pm_data['total'] += $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['remain'] += $paydata['gm_cnt'] + $paydata['rebate_cnt'];
            $pm_data['update_time'] = time();
            $pm_model->save($pm_data);
        }
    }

    protected function sdk_post_old($orderid, $amount, $paymark = '') {
        $pay_model = M("pay");
        // 获取支付表中的支付信息
        $pay_data = $pay_model->where(
            array(
                'order_id' => $orderid
            )
        )->find();
        $myamount = number_format($pay_data['real_amount'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        // 判断充值金额与回调中是否一致，且状态不为2，即待支付状态
        if (($myamount == $amount) && 2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->save($pay_data);
            // 判断订单信息是否修改成功
            if ($rs) {
                $this->updateMeminfo($pay_data);
                // 2.2.1 查询CP回调地址与APPKEY
                $game_data = $this->getGameinfobyid($pay_data['app_id']);
                $cpurl = $game_data['cpurl'];
                $app_key = $game_data['app_key'];
                $param['order_id'] = (string)$pay_data['order_id'];
                $param['mem_id'] = (string)$pay_data['mem_id'];
                $param['app_id'] = (string)$pay_data['app_id'];
                $param['money'] = (string)$myamount;
                $param['order_status'] = '2';
                $param['paytime'] = (string)$pay_data['create_time'];
                $param['attach'] = (string)$pay_data['attach'];
                $signstr = "order_id=".
                           $pay_data['order_id']."&mem_id=".$pay_data['mem_id']."&app_id=".$pay_data['app_id'];
                $signstr .= "&money=".
                            $pay_data."&order_status=2&paytime=".$pay_data['create_time']."&attach=".
                            $pay_data['attach'];
                $md5str = $signstr."&app_key=".$app_key;
                $sign = md5($md5str);
                $param['sign'] = (string)$sign;
                // 2.2.3 通知CP
                if ($pay_data['cpstatus'] == 1 || $pay_data['cpstatus'] == 3) {
                    $i = 0;
                    while (1) {
                        $cp_rs = \Huosdk\CommonFunc::payback($cpurl, $param);
                        if ($cp_rs > 0) {
                            $cpstatus = 2;
                            break;
                        } else {
                            $cpstatus = 3;
                            $i++;
                            sleep(2);
                        }
                        if ($i == 3) {
                            break;
                        }
                    }
                }
                // 更新CP状态
                $pay_model->where(array('id' => $pay_data['id']))->setField('cpstatus', $cpstatus);
            }
        }
    }

    protected function sdk_post($orderid, $amount, $paymark = '') {
        $pay_model = M("pay");
        // 获取支付表中的支付信息
        $pay_data = $pay_model->where(
            array(
                'order_id' => $orderid
            )
        )->find();
        $myamount = number_format($pay_data['real_amount'], 2, '.', '');
        $amount = number_format($amount, 2, '.', '');
        // 判断充值金额与回调中是否一致，且状态不为2，即待支付状态
        if (($myamount == $amount) && 2 != $pay_data['status']) {
            $pay_data['status'] = 2;
            $pay_data['remark'] = $paymark;
            $pay_data['update_time'] = time();
            // 将订单信息写入pay表中，并修改订单状态为2，即支付成功状态
            $rs = $pay_model->save($pay_data);
            // 判断订单信息是否修改成功
            if ($rs) {
                $this->updateMeminfo($pay_data);
                $paycp_info = M('pay_cpinfo')->where(array('pay_id' => $pay_data['id']))->find();
                $paycp_info['status'] = 2;
                // 2.2.1 查询CP回调地址与APPKEY
//                 $game_data = $this->getGameinfobyid($pay_data['app_id']);
//                 $cpurl = $game_data['cpurl'];
//                 $app_key = $game_data['app_key'];
//                 $param['order_id'] = (string) $pay_data['order_id'];
//                 $param['mem_id'] = (string) $pay_data['mem_id'];
//                 $param['app_id'] = (string) $pay_data['app_id'];
//                 $param['money'] = (string) $myamount;
//                 $param['order_status'] = '2';
//                 $param['paytime'] = (string) $pay_data['create_time'];
//                 $param['attach'] = (string) $pay_data['attach'];
//                 $signstr = "order_id=" .
//                         $pay_data['order_id'] . "&mem_id=" . $pay_data['mem_id'] . "&app_id=" . $pay_data['app_id'];
//                 $signstr .= "&money=" .
//                         $pay_data . "&order_status=2&paytime=" . $pay_data['create_time'] . "&attach=" .
//                         $pay_data['attach'];
//                 $md5str = $signstr . "&app_key=" . $app_key;
//                 $sign = md5($md5str);
//                 $param['sign'] = (string) $sign;
                $cpurl = $paycp_info['cpurl'];
                $param = $paycp_info['params'];
                // 2.2.3 通知CP
                if ($pay_data['cpstatus'] == 1 || $pay_data['cpstatus'] == 3) {
                    $i = 0;
                    while (1) {
                        $cp_rs = \Huosdk\CommonFunc::cpPayback($cpurl, $param);
                        if ($cp_rs > 0) {
                            $cpstatus = 2;
                            $paycp_info['cpstatus'] = 2;
                            break;
                        } else {
                            $cpstatus = 3;
                            $paycp_info['cpstatus'] = 3;
                            $i++;
                            sleep(2);
                        }
                        if ($i == 3) {
                            break;
                        }
                    }
                }
                // 更新CP状态
                $pay_model->where(array('id' => $pay_data['id']))->setField('cpstatus', $cpstatus);
                //更新cp回调
                M('pay_cpinfo')->save($paycp_info);
            }
        }
    }

    /*
     * 更新用户扩展支付信息
     */
    private function updateMeminfo(array $paydata) {
        $me_model = M('mem_ext');
        $map['mem_id'] = $paydata['mem_id'];
        $ext_data = $me_model->where($map)->find();
        $ext_data['order_cnt'] += 1;
        $ext_data['sum_money'] += $paydata['amount'];
        $ext_data['last_pay_time'] = $paydata['create_time'];
        $ext_data['last_money'] = $paydata['amount'];
        $me_model->save($ext_data);
    }

    /*
     * 获取单个游戏信息
     */
    private function getGameinfobyid($appid) {
        if ($appid <= 0 || empty($appid)) {
            return array();
        }
        $map['id'] = $appid;
        $game_data = M('game')->where($map)->find();
        if (empty($game_data)) {
            return array();
        }

        return $game_data;
    }

    function _authPaypwd($repwd) {
        if (empty($repwd)) {
            $this->error("请输入二级密码！");
            exit;
        }
        $user_obj = D("Common/Users");
        $uid = get_current_admin_id();
        $admin = $user_obj->where(array("id" => $uid))->find();
        $repwd = sp_password($repwd);
        if ($admin['pay_pwd'] != $repwd) {
            $this->error("二级密码错误,操作失败！");
            exit;
        }
    }

    private function insert_statistics($paydata) {
        //各级渠道比率
        $agentid = M('members')->where(array("id" => $paydata['mem_id']))->getField("agent_id");
        $each_rate_data = $this->lookupeachrate($paydata['app_id'], $agentid);
        //benefit_type为1为折扣
        if ($each_rate_data['benefit_type'] == 1) {
            $secbenefit = 0;
            if ($each_rate_data['sec_agent_rate'] > 0) {
                $secbenefit = $paydata['real_amount'] - $paydata['money'] * $each_rate_data['sec_agent_rate'];
            }
            if ($secbenefit < 0) {
                $secbenefit = 0;
            }
            $this->insert_agent_order($secbenefit, $agentid, $each_rate_data['sec_agent_rate'], $paydata);
            $this->insert_agent_gain($secbenefit, $agentid, $paydata);
            $this->insert_agent_day_gain($secbenefit, $agentid, $paydata);
            $this->insert_agent_ext($secbenefit, $agentid, $paydata);
            //含有一级渠道
            if ($each_rate_data['is_first'] == 1) {
                $firstbenefit = 0;
                if ($each_rate_data['first_agent_rate'] > 0) {
                    $firstbenefit = $paydata['money'] * $each_rate_data['sec_agent_rate'] - $paydata['money']
                                                                                            * $each_rate_data['first_agent_rate'];
                }
                if ($firstbenefit < 0) {
                    $firstbenefit = 0;
                }
                $this->update_agent_order(
                    $firstbenefit, $each_rate_data['first_agent_id'], $each_rate_data['first_agent_rate'], $paydata
                );
                $this->insert_agent_gain($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
                $this->insert_agent_day_gain($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
                $this->insert_agent_ext($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
            }
        } elseif (2 == $each_rate_data['benefit_type']) {
            $realgetcnt = $paydata['money'] + $paydata['rebate_cnt'];
            $berforrate = $paydata['money'] / $realgetcnt;
            $afterrate = $each_rate_data['sec_agent_rate'];
            $secbenefit = $paydata['money'] * ($berforrate - $afterrate);
            if ($secbenefit < 0.01) {
                $secbenefit = 0;
            }
            $this->insert_agent_order($secbenefit, $agentid, $each_rate_data['sec_agent_rate'], $paydata);
            $this->insert_agent_gain($secbenefit, $agentid, $paydata);
            $this->insert_agent_day_gain($secbenefit, $agentid, $paydata);
            $this->insert_agent_ext($secbenefit, $agentid, $paydata);
            //is_first为1则含有一级渠道
            if ($each_rate_data['is_first'] == 1) {
                $rebate_first_benefitrate = $each_rate_data['sec_agent_rate'] - $each_rate_data['first_agent_rate'];
                if ($berforrate - $afterrate < 0.0001) {
                    $rebate_first_benefitrate = $berforrate - $each_rate_data['first_agent_rate'];
                }
                $firstbenefit = $paydata['money'] * $rebate_first_benefitrate;
                if ($rebate_first_benefitrate < 0) {
                    $firstbenefit = 0;
                }
                $this->update_agent_order(
                    $firstbenefit, $each_rate_data['first_agent_id'], $each_rate_data['first_agent_rate'], $paydata
                );
                $this->insert_agent_gain($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
                $this->insert_agent_day_gain($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
                $this->insert_agent_ext($firstbenefit, $each_rate_data['first_agent_id'], $paydata);
            }
        }
    }

    //查出此订单一级渠道，二级渠道分成比率，优惠类型
    public function lookupeachrate($appid, $agentid) {
        //查询游戏返利类型
        $data['benefit_type'] = M('game_rate')->where(array("app_id" => $appid))->getField("benefit_type");
        $data['is_first'] = 0;
        //查询是否有一级代理
        $ownerid = M('users')->where(array("id" => $agentid))->getField("ownerid");
        if ($ownerid > 1) {
            $data['is_first'] = 1;
            $firstrate = $this->getagentrate($appid, $ownerid);
            $data['first_agent_rate'] = $firstrate;
            $data['first_agent_id'] = $ownerid;
        }
        $secrate = $this->getagentrate($appid, $agentid);
        $data['sec_agent_rate'] = $secrate;

        return $data;
    }

    //通过agentid及appid查询优惠类型，比率
    public function getagentrate($appid, $agentid) {
        $agentrate = M('agent_game_rate')->where(array("agent_id" => $agentid, "app_id" => $appid))->getField(
            "agent_rate"
        );

        return $agentrate;
    }

    public function insert_agent_order($leftbenefit, $agent_id, $agentrate, $orderdata) {
        $benefit_data['order_id'] = $orderdata['order_id'];
        $benefit_data['mem_id'] = $orderdata['mem_id'];
        $benefit_data['agent_id'] = $agent_id;
        $benefit_data['app_id'] = $orderdata['app_id'];
        $benefit_data['amount'] = $orderdata['money'];
        $benefit_data['real_amount'] = $orderdata['real_amount'];
        $benefit_data['rebate_cnt'] = $orderdata['rebate_cnt'];
        $benefit_data['agent_rate'] = $agentrate;
        $benefit_data['agent_gain'] = $leftbenefit;
        $benefit_data['from'] = 4;  //4为APP充值
        $benefit_data['status'] = 1;  //1为未结算
        $benefit_data['payway'] = $orderdata['payway'];
        $benefit_data['create_time'] = time();
        $benefit_data['update_time'] = $benefit_data['create_time'];
        $benefit_data['remark'] = $orderdata['remark'];
        $rs = M("agent_order")->add($benefit_data);
    }

    public function update_agent_order($leftbenefit, $agent_id, $agentrate, $orderdata) {
        $benefit_data['parent_id'] = $agent_id;
        $benefit_data['parent_rate'] = $agentrate;
        $benefit_data['parent_gain'] = $leftbenefit;
        $_map['order_id'] = $orderdata['order_id'];
        $rs = M("agent_order")->where($_map)->save($benefit_data);
    }

    public function insert_agent_gain($leftbenefit, $agent_id, $orderdata) {
        //判断agent_game_gain表
        $checkgamegaindata = M("agent_game_gain")->where(
            array("agent_id" => $agent_id, "app_id" => $orderdata['app_id'])
        )->find();
        if (empty($checkgamegaindata)) {
            $ag_id = M('agent_game')->where(array("app_id" => $orderdata['app_id'], "agent_id" => $agent_id))->getField(
                "id"
            );
            if (empty($ag_id)) {
                $agentgame_data['agent_id'] = $agent_id;
                $agentgame_data['app_id'] = $orderdata['app_id'];
                $agentgame_data['create_time'] = time();
                $agentgame_data['update_time'] = time();
                $ag_id = M("agent_game")->add($agentgame_data);
            }
            $agent_game_gain_data['ag_id'] = $ag_id;
            $agent_game_gain_data['agent_id'] = $agent_id;
            $agent_game_gain_data['app_id'] = $orderdata['app_id'];
            $agent_game_gain_data['sum_money'] = $orderdata['money'];
            $agent_game_gain_data['sum_real_money'] = $orderdata['real_amount'];
            $agent_game_gain_data['sum_rebate_cnt'] = $orderdata['rebate_cnt'];
            $agent_game_gain_data['sum_agent_gain'] = $leftbenefit;
            $rrs = M("agent_game_gain")->add($agent_game_gain_data);
        } else {
            $agent_game_gain_data['sum_money'] = $checkgamegaindata['sum_money'] + $orderdata['money'];
            $agent_game_gain_data['sum_real_money'] = $checkgamegaindata['sum_real_money'] + $orderdata['real_amount'];
            $agent_game_gain_data['sum_rebate_cnt'] = $checkgamegaindata['sum_rebate_cnt'] + $orderdata['rebate_cnt'];
            $agent_game_gain_data['sum_agent_gain'] = $checkgamegaindata['sum_agent_gain'] + $leftbenefit;
            $rrs = M("agent_game_gain")->where(array("agent_id" => $agent_id, "app_id" => $checkgamegaindata['app_id']))
                                       ->save($agent_game_gain_data);
        }
    }

    public function insert_agent_day_gain($leftbenefit, $agent_id, $orderdata) {
        $date = date("Y-m-d");
        $adgData = M("agent_day_gain")->where(
            array("date" => $date, "agent_id" => $agent_id, "app_id" => $orderdata['app_id'])
        )->find();
        if (!empty($adgData)) {
            $agent_day_gain_data['sum_money'] = $adgData['sum_money'] + $orderdata['money'];
            $agent_day_gain_data['sum_real_money'] = $adgData['sum_real_money'] + $orderdata['real_amount'];
            $agent_day_gain_data['sum_rebate_cnt'] = $adgData['sum_rebate_cnt'] + $orderdata['rebate_cnt'];
            $agent_day_gain_data['sum_agent_gain'] = $adgData['sum_agent_gain'] + $leftbenefit;
            $rs = M("agent_day_gain")->where(
                array("agent_id" => $agent_id, "app_id" => $adgData["app_id"], "date" => $date)
            )->save($agent_day_gain_data);
        } else {
            $agent_day_gain_data['date'] = $date;
            $agent_day_gain_data['agent_id'] = $agent_id;
            $agent_day_gain_data['app_id'] = $orderdata['app_id'];
            $agent_day_gain_data['sum_money'] = $orderdata['money'];
            $agent_day_gain_data['sum_real_money'] = $orderdata['real_amount'];
            $agent_day_gain_data['sum_rebate_cnt'] = $orderdata['rebate_cnt'];
            $agent_day_gain_data['sum_agent_gain'] = $leftbenefit;
            $rs = M("agent_day_gain")->add($agent_day_gain_data);
        }
    }

    public function insert_agent_ext($leftbenefit, $agent_id, $orderdata) {
        $ageData = M("agent_ext")->where(array("agent_id" => $agent_id))->find();
        if (!empty($ageData)) {
            $cnt = M("pay")->where(array("mem_id" => $orderdata["mem_id"]))->count();
            $addtimes = 1;
            if ($cnt >= 1) {
                $addtimes = 0;
            }
            $agent_ext_data['reg_cnt'] = $ageData['reg_cnt'];
            $agent_ext_data['reg_pay_cnt'] = $ageData['reg_pay_cnt'] + $addtimes;
            $agent_ext_data['sum_money'] = $ageData['sum_money'] + $orderdata['money'];
            $agent_ext_data['sum_real_money'] = $ageData['sum_real_money'] + $orderdata['real_amount'];
            $agent_ext_data['share_total'] = $ageData['share_total'] + $leftbenefit;
            $agent_ext_data['share_remain'] = $ageData['share_remain'] + $leftbenefit;
            $agent_ext_data['order_cnt'] = $ageData['order_cnt'] + 1;
            $rs = M("agent_ext")->where(array("agent_id" => $agent_id))->save($agent_ext_data);
        } else {
            $agent_ext_data['agent_id'] = $agent_id;
            $agent_ext_data['reg_cnt'] = 1;
            $agent_ext_data['reg_pay_cnt'] = 1;
            $agent_ext_data['sum_money'] = $orderdata['money'];
            $agent_ext_data['sum_real_money'] = $orderdata['real_amount'];
            $agent_ext_data['share_total'] = $leftbenefit;
            $agent_ext_data['share_remain'] = $leftbenefit;
            $agent_ext_data['order_cnt'] = 1;
            $rs = M("agent_ext")->add($agent_ext_data);
        }
    }
}