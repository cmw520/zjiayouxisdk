<?php
namespace Huosdk;
class GmBalance {
    public function getMemGmBalance($mem_id, $app_id) {
        $result = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->getField("remain");
        if (!$result) {
            $result = 0;
        }
        $r = (float)$result;
        return $r;
    }

    public function addIncRecord($give_user_id, $mem_id, $app_id, $amount, $remark) {
        $data = array();
        $data['order_id'] = setorderid();
        $data['admin_id'] = $give_user_id;
        $data['flag'] = 5;/* 代理发放  */
        $data['mem_id'] = $mem_id;
        $data['app_id'] = $app_id;
        $data['money'] = $amount;
        $data['gm_cnt'] = $amount;
        $data['ip'] = get_client_ip();
        $data['remark'] = $remark;
        $data['status'] = 2;
        $data['update_time'] = time();
        $data['create_time'] = time();
        M('gm_given')->add($data);
    }

    public function Inc($mem_id, $app_id, $amount) {
        $exist = M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->find();
        if ($exist) {
            $data = array();
            $data['remain'] = $exist['remain'] + $amount;
            $data['total'] = $exist['total'] + $amount;
            $data['update_time'] = time();
            M('gm_mem')->where(array("mem_id" => $mem_id, "app_id" => $app_id))->save($data);
        } else {
            $data = array();
            $data['mem_id'] = $mem_id;
            $data['app_id'] = $app_id;
            $data['remain'] = $amount;
            $data['total'] = $amount;
            $data['update_time'] = time();
            $data['create_time'] = time();
            M('gm_mem')->add($data);
        }
    }

    public function getList($where_extra = array(), $start = 0, $limit = 0) {
        $items = M('gm_mem')
            ->field("m.username as mem_name,gm.remain,g.name as game_name,gm.mem_id,u.user_nicename as agent_name")
            ->alias("gm")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=gm.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gm.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=m.agent_id")
            ->where($where_extra)
            ->limit($start, $limit)
            ->order("gm.id desc")
            ->select();
        $hs_mem_obj = new \Huosdk\Member();
        foreach ($items as $k => $v) {
            if (!$items[$k]['agent_name']) {
                $items[$k]['agent_name'] = "官方渠道";
            }
//           $items[$k]['agent_name']=$hs_mem_obj->get_mem_agent_name($v['mem_id']);
        }
        return $items;
    }
}

