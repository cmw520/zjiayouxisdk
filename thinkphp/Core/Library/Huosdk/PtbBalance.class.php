<?php
namespace Huosdk;
class PtbBalance {
    public function getBalance($agent_id) {
        $where = array();
        $where['agent_id'] = $agent_id;
        $result = M('ptb_agent')->where($where)->getField("remain");
        $r = (float)$result;
        if (!$r) {
            $r = 0;
        }

        return $r;
    }

    public function Dec($agent_id, $amount) {
        $ae_r = M('ptb_agent')->where(array("agent_id" => $agent_id))->find();
        $balance = $ae_r['remain'] - $amount;
        M('ptb_agent')->where(array("agent_id" => $agent_id))->save(array("remain" => $balance));
    }

    public function Inc($agent_id, $amount) {
        $model = M('ptb_agent');
        $ae_where = array("agent_id" => $agent_id);
        $pre = $model->where($ae_where)->find();
        if ($pre) {
            $new_data = array();
            $new_data['remain'] = $pre['remain'] + $amount;
            $new_data['total'] = $pre['total'] + $amount;
            $new_data['sum_money'] = $pre['sum_money'] + $amount / C('G_RATE');
            $new_data['update_time'] = time();
            $rs = $model->where($ae_where)->save($new_data);
        } else {
            $new_data = array();
            $new_data['remain'] = $amount;
            $new_data['total'] = $amount;
            $new_data['sum_money'] = $amount / C('G_RATE');
            $new_data['agent_id'] = $agent_id;
            $new_data['update_time'] = time();
            $new_data['create_time'] = time();
            $rs = $model->add($new_data);
        }

        return $rs;
    }

    public function getAgentList($where_extra = array(), $start = 0, $limit = 0) {
        $model = M('ptb_agent');
        $where = array();
        $items = $model
            ->field("pa.*,u.user_nicename as agent_name,u.user_type")
            ->alias("pa")
            ->where($where)
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pa.agent_id")
            ->limit($start, $limit)
            ->order("pa.agent_id desc")
            ->select();
        \Huosdk\Data\FormatRecords::agent_level($items);

        return $items;
    }

    public function addIncRecord($admin_id, $agent_id, $amount, $remark) {
        $data = array();
        $data['order_id'] = setorderid();
        $data['agent_id'] = $agent_id;
        $data['admin_id'] = $admin_id;
        $data['money'] = $amount;
        $data['ptb_cnt'] = $amount * C('G_RATE');
        $data['discount'] = 1;
        $data['payway'] = "ptb";
        $data['ip'] = get_client_ip();
        $data['status'] = "2";
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['remark'] = $remark;
        M('ptb_given')->add($data);
    }

    public function addIncRecord_ab($admin_id, $agent_id, $amount, $remark) {
        $data = array();
        $data['order_id'] = setorderid();
        $data['agent_id'] = $agent_id;
        $data['admin_id'] = $admin_id;
        $data['money'] = $amount;
        $data['ptb_cnt'] = $amount * C('G_RATE');
        $data['discount'] = 1;
        $data['payway'] = "ab";
        $data['ip'] = get_client_ip();
        $data['status'] = "2";
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['remark'] = $remark;
        M('ptb_agentcharge')->add($data);
    }
}

