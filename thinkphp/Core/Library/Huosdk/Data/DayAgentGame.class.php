<?php
namespace Huosdk\Data;
class DayAgentGame {
    public static function getList($where_extra = array(), $start = 0, $limit = 0) {
        $items = M('day_agentgame')
            ->field(
                "dag.*,g.name as game_name,u.user_nicename as agent_name,"
                ."dag.user_cnt as active_user_cnt,dag.sum_money as charge_amount,"
                ."dag.reg_cnt as new_user_cnt,dag.pay_user_cnt,"
                ."CONCAT(format((dag.pay_user_cnt/dag.user_cnt)*100,2),'%') as pay_rate"
            )
            ->alias("dag")
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=dag.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=dag.agent_id")
            ->limit($start, $limit)
            ->order("dag.id desc")
//                ->group("dag.agent_id")
            ->select();
        foreach ($items as $key => $value) {
            if (!$items[$key]['agent_name']) {
                $items[$key]['agent_name'] = "官方渠道";
            }
        }

        return $items;
    }

    public static function getSumList($where_extra = array()) {
        $items = M('day_agentgame')
            ->field(
                "sum(dag.user_cnt) as sum_active_user_cnt,"
                 ."sum(dag.sum_money) as sum_charge_amount,"
                 ."sum(dag.reg_cnt) as sum_new_user_cnt,"
                 ."sum(dag.pay_user_cnt) as sum_pay_user_cnt"
            )
            ->alias("dag")
            ->where($where_extra)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=dag.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."users u ON u.id=dag.agent_id")
            ->find();
        return $items;
    }

    public static function getTxt($where_extra = array(), $start = 0, $limit = 0) {
        $items = self::getList($where_extra, $start, $limit);
        $fields = array(
            "时间    " => "date            ",
            "渠道名称"   => "agent_name      ",
            //            "游戏    " => "game_name       ",
            "新增用户"   => "new_user_cnt    ",
            "活跃用户"   => "active_user_cnt ",
            "充值金额"   => "charge_amount   ",
            "充值人数"   => "pay_user_cnt    ",
            "付费率  "  => "pay_rate        "
        );
        $table_header = self::get_table_header(array_keys($fields));
        $table_content = self::get_table_total(self::getSumList($where_extra));
        foreach ($items as $k => $item) {
            $table_content .= "<tr>";
            foreach ($fields as $key => $value) {
                $f = trim($value);
                $table_content .= "<td>".$item["$f"]."</td>";
            }
            $table_content .= "</tr>";
        }
//        return array(
//            "head"=>$table_header,
//            "body"=>$table_content
//        );
        return $table_header.$table_content;
    }

    public static function get_table_header($keys) {
        $txt = '<tr>';
        foreach ($keys as $key => $value) {
            $txt .= '<th>'.$value.'</th>';
        }
        $txt .= '</tr>';

        return $txt;
    }

    public static function get_table_total($keys) {
        $txt = '<tr>';
        $txt .= '<th style=\'color:#FF0000\' >--</th>';
        $txt .= '<th style=\'color:#FF0000\' >--</th>';
        $txt .= '<th style=\'color:#FF0000\' >'.$keys['sum_new_user_cnt'].'</th>';
        $txt .= '<th style=\'color:#FF0000\' >'.$keys['sum_active_user_cnt'].'</th>';
        $txt .= '<th style=\'color:#FF0000\' >'.$keys['sum_charge_amount'].'</th>';
        $txt .= '<th style=\'color:#FF0000\' >'.$keys['sum_pay_user_cnt'].'</th>';
        $txt .= '<th style=\'color:#FF0000\' >'.number_format(
                100* $keys['sum_pay_user_cnt'] / $keys['sum_active_user_cnt'], 2
            ).'%</th>';

        return $txt;
    }
}

