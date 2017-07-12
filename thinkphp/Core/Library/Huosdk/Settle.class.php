<?php
namespace Huosdk;
class Settle {
    public function DecAgentAccountBalance($agent_id, $amount) {
        $ae_r = M('agent_ext')->where(array("agent_id" => $agent_id))->find();
        $balance = $ae_r['share_remain'] - $amount;
        M('agent_ext')->where(array("agent_id" => $agent_id))->save(array("share_remain" => $balance));
    }

    public function IncAgentAccountBalance($agent_id, $amount) {
        $ae_r = M('agent_ext')->where(array("agent_id" => $agent_id))->find();
        $data = array();
        $data['share_remain'] = $ae_r['share_remain'] + $amount;
        $data['share_total'] = $ae_r['share_total'] + $amount;
        $data['sum_money'] = $ae_r['sum_money'] + $amount;
        $data['sum_real_money'] = $ae_r['sum_real_money'] + $amount;
        M('agent_ext')->where(array("agent_id" => $agent_id))->save($data);
    }

    public function AddRecord($agent_id, $amount, $cardnum) {
        M('settle')->add(
            array(
                "money"       => $amount,
                "agent_id"    => $agent_id,
                "create_time" => time(),
                "status"      => "1",
                "banknum"     => $cardnum
            )
        );
    }

    public function getList($agent_id, $where = array(), $start = 0, $limit = 0) {
        $model = M("settle");
        $where['s.agent_id'] = $agent_id;
        $records = $model
            ->alias('s')
            ->where($where)
            ->limit($start, $limit)
            ->order("s.id desc")
            ->select();
        return $records;
    }

    public function getListSum($agent_id, $where = array()) {
        $model = M("settle");
        $where['s.agent_id'] = $agent_id;
        $sumitems = $model
            ->field("sum(money) as sum_amount")
            ->alias('s')
            ->where($where)
            ->select();
        return $sumitems;
    }

    public function getSettleBase() {
        $result = M('options')->where(array("option_name" => "agent_withdraw_base"))->getField("option_value");
        $r = (float)$result;
        return $r;
    }

    public function getTotalFreeze($agent_id) {
        return M('settle')->where(array("agent_id" => $agent_id, "_string" => "status=1 or status=2"))->sum("money");
    }

    public function getAccountRemain($agent_id) {
        return M('agent_ext')->where(array("agent_id" => $agent_id))->getField("share_remain");
    }

    public function setApplyOpertorNotPass($settle_id) {
        $model = M('settle');
        $where = array("id" => $settle_id);
        $model->where($where)->setField("status", "4");
        $model->where($where)->setField("check_time", time());
        $amount = $model->where($where)->getField("money");
        $agent_id = $model->where($where)->getField("agent_id");
        $ae_model = M('agent_ext');
        $where2 = array("agent_id" => $agent_id);
        $ext = $ae_model->where($where2)->find();
        $new_balance = $ext['share_remain'] + $amount;
        $ae_model->where($where2)->setField("share_remain", $new_balance);
    }

    public function setApplyFinancePass($settle_id) {
        $model = M('settle');
        $model->where(array("id" => $settle_id))->setField("status", "3");
        $model->where(array("id" => $settle_id))->setField("settle_time", time());
    }

    public function setApplyFinanceNotPass($settle_id) {
        $model = M('settle');
        $where = array("id" => $settle_id);
        $model->where($where)->setField("status", "4");
        $amount = $model->where($where)->getField("money");
        $agent_id = $model->where($where)->getField("agent_id");
        $ae_model = M('agent_ext');
        $where2 = array("agent_id" => $agent_id);
        $ext = $ae_model->where($where2)->find();
        $new_balance = $ext['share_remain'] + $amount;
        $ae_model->where($where2)->setField("share_remain", $new_balance);
    }

    public function setApplyPass($settle_id) {
        $model = M('settle');
        $model->where(array("id" => $settle_id))->setField("status", "2");
        $model->where(array("id" => $settle_id))->setField("check_time", time());
    }

    public function markApplyPaid($settle_id) {
        $model = M('settle');
        $model->where(array("id" => $settle_id))->setField("status", "3");
        $model->where(array("id" => $settle_id))->setField("settle_time", time());
    }
}

