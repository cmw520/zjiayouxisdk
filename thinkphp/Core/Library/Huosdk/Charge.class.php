<?php
namespace Huosdk;
class Charge {
    public function addAdminChargeForAgentRecord($admin_id, $agent_id, $amount, $remark = '') {
        M('gm_agentcharge')->add(
            array(
                "order_id"    => $this->setorderid(1),
                "admin_id"    => $admin_id,
                "agent_id"    => $agent_id,
                "money"       => $amount,
                "gm_cnt"      => $amount,
                "ip"          => get_client_ip(),
                "create_time" => time(),
                "update_time" => time(),
                "status"      => "2",
                "remark"      => "后台发放平台币给一级代理 - ".$remark
            )
        );
        $data = M('agent_ext')->where(array("agent_id" => $agent_id))->find();
        $new_b = $amount + $data['balance'];
        if ($data) {
            M('agent_ext')->where(array("agent_id" => $agent_id))->save(
                array(
                    "balance" => $new_b
                )
            );
        } else {
            M('agent_ext')->add(
                array(
                    "agent_id" => $agent_id,
                    "balance"  => $amount
                )
            );
        }
    }

    public function addAdminChargeForAgentRecord_PTB($admin_id, $agent_id, $amount, $remark = '') {
        M('ptb_agentcharge')->add(
            array(
                "order_id"    => $this->setorderid(1),
                "admin_id"    => $admin_id,
                "agent_id"    => $agent_id,
                "money"       => $amount,
                "ptb_cnt"     => $amount,
                "ip"          => get_client_ip(),
                "create_time" => time(),
                "update_time" => time(),
                "status"      => "2",
                "remark"      => "后台发放平台币给代理 - ".$remark
            )
        );
        $data = M('ptb_agent')->where(array("agent_id" => $agent_id))->find();
        $newdata = array();
        if ($data) {
            $newdata['remain'] = $amount + $data['remain'];
            $newdata['total'] = $amount + $data['total'];
            $newdata['sum_money'] = $amount + $data['sum_money'];
            M('ptb_agent')->where(array("agent_id" => $agent_id))->save($newdata);
        } else {
            $newdata['agent_id'] = $agent_id;
            $newdata['remain'] = $amount;
            $newdata['total'] = $amount;
            $newdata['sum_money'] = $amount;
            M('ptb_agent')->add($newdata);
        }
    }

    //生成订单号
    public function setorderid($mem_id) {
        list($usec, $sec) = explode(" ", microtime());
        // 取微秒前3位+再两位随机数+渠道ID后四位
        $orderid = $sec.substr($usec, 2, 3).rand(10, 99).sprintf("%04d", $mem_id % 10000);
        return $orderid;
    }

    public function adminChargeForMember($admin_id, $mem_id, $app_id, $realpay, $gm_cnt, $remark) {
        //玩家账户中的平台币金额要增加，表格是gm_mem
        $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
        //如果账户中已经存在，就更新
        if ($pre_gmm) {
            $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))
                                  ->save(
                                      array(
                                          "sum_money"   => $pre_gmm['sum_money'] + $realpay,
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
                    "sum_money"   => $realpay, "total" => $gm_cnt, "remain" => $gm_cnt,
                    "create_time" => time(), "update_time" => time()
                )
            );
        }
        $rate = 1;
        //gm_charge中要加入这次充值记录，这是代理给玩家充的
        M('gm_charge')->add(
            array(
                "order_id"    => $this->setorderid(1),
                "admin_id"    => $admin_id,
                "mem_id"      => $mem_id,
                "app_id"      => $app_id,
                "money"       => $realpay,
                "gm_cnt"      => $gm_cnt,
                "discount"    => $rate,
                "payway"      => "balance",
                "ip"          => get_client_ip(),
                "status"      => "2",
                "create_time" => time(),
                "update_time" => time(),
                "remark"      => "from admin ".$admin_id." ".$remark
            )
        );
    }

    public function addAgentForMemberGmChargeRecord($benefit_type, $pay, $get, $rate, $agent_id, $mem_id, $app_id) {
        $data['order_id'] = $this->setorderid(1);
        $data["admin_id"] = $agent_id;
        $data["mem_id"] = $mem_id;
        $data["app_id"] = $app_id;
        $data["payway"] = "ptb";
        $data["ip"] = get_client_ip();
        $data["status"] = "2";
        $data["create_time"] = time();
        $data["update_time"] = time();
        $data["remark"] = "from agent ".$agent_id;
        $data["benefit_type"] = $benefit_type;
        $data["money"] = $pay;
        $data["gm_cnt"] = $get;
        //折扣
        if ($benefit_type == 1) {
            $data["discount"] = $rate;
        } else if ($benefit_type == 2) {
            $data["rebate"] = $rate;
        }
        M('gm_charge')->add($data);
    }

    public function IncMemberAppBalance($mem_id, $app_id, $pay, $get) {
        //玩家账户中的平台币金额要增加，表格是gm_mem
        $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
        //如果账户中已经存在，就更新
        if ($pre_gmm) {
            $pre_gmm = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))
                                  ->save(
                                      array(
                                          "sum_money"   => $pre_gmm['sum_money'] + $pay,
                                          "total"       => $pre_gmm['total'] + $get,
                                          "remain"      => $pre_gmm['remain'] + $get,
                                          "update_time" => time()
                                      )
                                  );
        } else {
            //如果账户中不存在，就创建初始记录
            M('gm_mem')->add(
                array(
                    "mem_id"      => $mem_id, "app_id" => $app_id,
                    "sum_money"   => $pay, "total" => $get, "remain" => $get,
                    "create_time" => time(), "update_time" => time()
                )
            );
        }
    }

    public function DecAgentPtbBalance($agent_id, $amount) {
        $where = array();
        $where['agent_id'] = $agent_id;
        $pre = M('ptb_agent')->where($where)->find();
        $pre_remain = $pre['remain'];
        $new_remain = $pre_remain - $amount;
        M('ptb_agent')->where($where)->setField("remain", $new_remain);
    }

    public function IncAgentPtbBalance($agent_id, $amount) {
        $ae_model = M('ptb_agent');
        $ae_where = array("agent_id" => $agent_id);
        $pre = $ae_model->where($ae_where)->find();
        $new_data = array();
        $new_data['remain'] = $pre['remain'] + $amount;
        $new_data['total'] = $pre['total'] + $amount;
        $new_data['sum_money'] = $pre['sum_money'] + $amount;
        $ae_model->where($ae_where)->save($new_data);
    }
}

