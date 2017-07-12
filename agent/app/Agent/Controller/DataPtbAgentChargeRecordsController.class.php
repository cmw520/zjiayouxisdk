<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class DataPtbAgentChargeRecordsController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $agentid = $this->agid;
        $sql
            = "SELECT '代理充值' as `from`,create_time,payway,ptb_cnt FROM c_ptb_agentcharge WHERE agent_id=$agentid AND status=2 "
              ."UNION ALL "
              ."SELECT '官方发放' as `from`, create_time,payway,ptb_cnt FROM c_ptb_given WHERE agent_id=$agentid AND status=2 "
              ."ORDER BY create_time desc";
        $items = M('')->query($sql);
        $payway_data = $this->payway_txt();
        foreach ($items as $key => $value) {
            $items[$key]['payway_txt'] = $payway_data[$value['payway']];
            if ($value['from'] == '代理充值') {
                if (($value['payway'] == 'alipay' || $value['payway'] == 'bankpay' || $value['payway'] == 'wxpay')) {
                    $items[$key]['from'] = "在线充值";
                } else if ($value['payway'] == 'ab') {
                    $items[$key]['from'] = "账户余额充值";
                } else if ($value['payway'] == 'ptb') {
                    $items[$key]['from'] = "代理转账";
                }
            }
        }
        $count = count($items);
        $page = $this->page($count, 20);
        $this->assign("total_rows", $count);
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->display();
    }

    public function index3() {
        $model = M('ptb_agentcharge');
        $where = array();
        $where['pac.status'] = 2;
        $count = $model
            ->field("pac.*")
            ->alias("pac")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.agent_id")
            ->count();
        $page = $this->page($count, 20);
        $items = $model
            ->field("pac.*")
            ->alias("pac")
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=pac.agent_id")
            ->limit($page->firstRow, $page->listRows)
            ->order("pac.id desc")
            ->select();
        $payway_data = $this->payway_txt();
        foreach ($items as $key => $value) {
            $items[$key]['payway_txt'] = $payway_data[$value['payway']];
        }
        $this->assign("total_rows", $count);
        $this->assign("items", $items);
        $this->assign("page", $page->show());
        $this->display();
    }
}

