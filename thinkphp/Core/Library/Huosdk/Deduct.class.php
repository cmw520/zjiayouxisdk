<?php
namespace Huosdk;
class Deduct {
    public function DeductAgentPtb($agent_id, $ptb_cnt, $backptb_cnt, $remark) {
//        $hs_charge_obj=new \Huosdk\Charge();
//        $backorder_id=$hs_charge_obj->setorderid($agent_id);
        //在扣回记录表中插入这条记录
        $data = array();
        $data['remark'] = $remark;
        $data['agent_id'] = $agent_id;
        $data['flag'] = 1;/* 扣回代理平台币  */
        $data['backorder_id'] = $this->get_back_order_id($agent_id);
        $data['ptb_cnt'] = $ptb_cnt;
        $data['backptb_cnt'] = $backptb_cnt;
        $data['status'] = 2;
        $data['create_time'] = time();
        M('ptb_back')->add($data);
        //减少代理的平台币余额
        $hs_pb_obj = new \Huosdk\PtbBalance();
        $hs_pb_obj->Dec($agent_id, $backptb_cnt);
    }

    public function agentPtbDeductCnt($where_extra = array()) {
        return M('ptb_back')
            ->field("pb.create_time,u.user_nicename  as agent_name,pb.backptb_cnt,pb.remark")
            ->alias('pb')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pb.agent_id")
            ->where(array("pb.flag" => 1))
            ->where($where_extra)
            ->count();
    }

    public function agentPtbDeductList($where_extra = array(), $start = 0, $limit = 0) {
        return M('ptb_back')
            ->field("pb.create_time,u.user_nicename  as agent_name,pb.backptb_cnt,pb.remark")
            ->alias('pb')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pb.agent_id")
            ->where(array("pb.flag" => 1))
            ->where($where_extra)
            ->limit($start, $limit)
            ->order("pb.id desc")
            ->select();
    }

    public function DeductMemGm($app_id, $mem_id, $gm_cnt, $backgm_cnt, $remark) {
        //插入扣回记录
        $data = array();
        $data['app_id'] = $app_id;
        $data['remark'] = $remark;
        $data['mem_id'] = $mem_id;
        $data['flag'] = 5;/* 代理发放  */
        $data['backorder_id'] = $this->get_back_order_id($mem_id);
        $data['gm_cnt'] = $gm_cnt;
        $data['backgm_cnt'] = $backgm_cnt;
        $data['status'] = 2;
        $data['create_time'] = time();
        M('gm_back')->add($data);
        M('gm_mem')->where(array("app_id" => $app_id, "mem_id" => $mem_id))->setDec("remain", $backgm_cnt);
        M('gm_mem')->where(array("app_id" => $app_id, "mem_id" => $mem_id))->setField("update_time", time());
    }

    public function memGmDeductList($where_extra = array(), $start = 0, $limit = 0) {
        return M('gm_back')
            ->where($where_extra)
            ->limit($start, $limit)
            ->order("id desc")
            ->select();
    }

    public function get_back_order_id($v) {
        $hs_charge_obj = new \Huosdk\Charge();
        $backorder_id = $hs_charge_obj->setorderid($v);
        return $backorder_id;
    }
}

