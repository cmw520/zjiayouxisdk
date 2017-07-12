<?php
namespace Huosdk\Data;

use Huosdk\Data\Base;

class PtbAgentCharge extends Base {
    private $model;
    private $agent_id;
    private $where;

    public function __construct($agent_id) {
        $this->model = M("ptb_agentcharge");
        $this->agent_id = $agent_id;
        $this->where = array();
//        $this->where['_string']="(pac.agent_id=$agent_id) OR (pac.admin_id=$agent_id)";
        $this->where['_string'] = "(pac.agent_id=$agent_id)";
    }

    public function get_table_header() {
        $data = array("订单编号", "付款渠道", "收款渠道", "充值金额", "充值方式", "充值时间", "状态");
        return $this->generate_table_header($data);
    }

    public function get_count() {
        $data = $this->get_items();
        return count($data);
    }

    public function get_table($start = 0, $limit = 0) {
        $data = $this->get_items(array(), $start, $limit);
        return $this->get_table_content($data);
    }

    public function get_sum_txt($where = array()) {
        $data = $this->get_sum($where);
        $fields = array("汇总", "--", "--", $data['sum_ptb_cnt'], "--", "--", "--");
        return $this->generate_table_sum($fields);
    }

    private function fields() {
        $data = array("order_id", "from_name", "agent_name", "ptb_cnt", "payway_txt", "create_time_txt", "status_txt");
        return $data;
    }

    private function get_table_content($data) {
        $fields = $this->fields();
        $txt = '';
        foreach ($data as $k => $v) {
            $txt .= '<tr>';
            foreach ($fields as $field) {
                $txt .= "<td>".$v[$field]."   </td>";
            }
            $txt .= "</tr>";
        }
        return $txt;
    }

    public function get_items($where_extra = array(), $start = 0, $limit = 0) {
        $where = array();
        $items = $this->model
            ->field(
                "u.user_login as from_name,u2.user_login as agent_name,pac.order_id,pac.ptb_cnt,pac.status,pac.create_time,pac.payway"
            )
            ->alias('pac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=pac.agent_id")
            ->where($this->where)
            ->where($where_extra)
            ->order("pac.id desc")
            ->limit($start, $limit)
            ->select();
        foreach ($items as $k => $v) {
            $items[$k]['create_time_txt'] = date("Y-m-d H:i:s", $v['create_time']);
            if (empty($v['from_name'])) {
                $items[$k]['from_name'] = $v['agent_name'];
            }
            $items[$k]['status_txt'] = $this->status_txt($v['status']);
            $items[$k]['payway_txt'] = $this->payway_txt($v['payway']);
        }
        return $items;
    }

    public function payway_txt($k) {
        $data = array("alipay" => "支付宝", "ptb" => "平台币", "account_balance" => "账户余额");
        if ($k == null) {
            return "官方发放";
        }
        return $data[$k];
    }

    public function status_txt($k) {
        $data = array("1" => "待支付", "2" => "<span style='color:red;'>成功</span>", 3 => "失败");
        return $data[$k];
    }

    public function get_sum($where) {
        $sumitems = $this->model
            ->field("sum(pac.ptb_cnt) as sum_ptb_cnt")
            ->alias('pac')
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.admin_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u2 ON u2.id=pac.agent_id")
            ->where($where)
            ->where($this->where)
            ->order("pac.id desc")
            ->select();
        return $sumitems[0];
    }
}

