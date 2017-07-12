<?php
/**
 * Agentincome.php UTF-8
 * 渠道收益
 *
 * @date    : 2016年11月29日下午2:11:36
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月29日下午2:11:36
 */
namespace huosdk\finance;

use think\Config;
use think\Db;

class Agentincome {
    /**
     * 对明文信息进行加密
     *
     * @param $order_id 订单号
     * @param $payfrom  订单来源
     */
    public static function income($order_id, $payfrom) {
        if (empty($order_id)) {
            return false;
        }
        $_map['order_id'] = $order_id;
        /* 查询分成订单中此订单是否存在 存在不再处理 */
        $_ao_info = Db::name('agent_order')->where($_map)->find();
        if (!empty($_ao_info)) {
            return false;
        }
        if (PAYFROM_SDK == $payfrom) {
            $_tablename = 'pay';
            $_agentalias = 'agent_id';
        } else {
            $_tablename = 'gm_charge';
            $_agentalias = 'admin_id';
        }
        $_pay_data = Db::name($_tablename)->where($_map)->find();
        /* 订单不存在 或订单不成功 不处理 */
        if (empty($_pay_data) || PAYSTATUS_SUCCESS != $_pay_data['status']) {
            return false;
        }
        $_ag_map['agent_id'] = $_pay_data[$_agentalias];
        $_ag_map['app_id'] = $_pay_data['app_id'];
        $_ao_data['order_id'] = $_pay_data['order_id'];
        $_ao_data['mem_id'] = $_pay_data['mem_id'];
        $_ao_data['agent_id'] = $_pay_data['agent_id'];
        $_ao_data['app_id'] = $_pay_data['app_id'];
        $_ao_data['amount'] = $_pay_data['amount'];
        $_ao_data['real_amount'] = $_pay_data['real_amount'];
        $_ao_data['rebate_cnt'] = $_pay_data['rebate_cnt'];
        $_ao_data['from'] = $_pay_data['from'];
        $_ao_data['status'] = PAYSTATUS_SUCCESS;
        $_ao_data['payway'] = $_pay_data['payway'];
        $_ao_data['create_time'] = time();
        $_ao_data['update_time'] = 0;
        $_ao_data['remark'] = $_pay_data['remark'];
        $_real_amount = $_pay_data['real_amount'];
        $_agent_gain = 0;
        $_agr_info = Db::name('agent_game_rate')->where($_ag_map)->find();
        //未分包情况下处理
        if (empty($_agr_info) && Config::get('config.G_DEFAULT_EN')) {
            $_agr_info = self::getEmptyrate($_pay_data[$_agentalias], $_pay_data['app_id']);
        }
        if (!empty($_agr_info)) {
            $_wallet_rate = \huosdk\wallet\Wallet::getRate(); /* 钱包与实际价格比例 */
            if (PAYFROM_SDK == $payfrom) {
                $_cp_no_gm_real = $_pay_data['amount'] + number_format(
                        ($_pay_data['rebate_cnt'] - $_pay_data['gm_cnt'])
                        / abs($_wallet_rate), 2, '.', ''
                    );
            } else {
                $_cp_no_gm_real = $_pay_data['amount'] +
                                  number_format(
                                      ($_pay_data['rebate_cnt']) / abs($_wallet_rate), 2, '.', ''
                                  );
            }
            $_agent_gain = $_real_amount - $_agr_info['agent_rate'] * $_cp_no_gm_real;
            if ($_agent_gain < 0.01) {
                $_agent_gain = 0;
            }
            // if (1 == $_agr_info['benefit_type']){
            // $_agent_gain = $_pay_data['real_amount'] - $_agr_info['agent_rate'] * $_pay_data['amount'];
            // }else if (2 == $_agr_info['benefit_type']){
            // $_sdk_gain = $_pay_data['real_amount'] -
            // $_agent_gain = $_pay_data['real_amount'] - $_agr_info['rebate_cnt'] * $_pay_data['amount'];
            // }
            if ($_agent_gain >= 0.01) {
                $_ao_data['agent_rate'] = $_agr_info['agent_rate'];
                $_ao_data['agent_gain'] = $_agent_gain;
                Db::name('agent_order')->insert($_ao_data);
                // 插入渠道每日收益与渠道收益
                self::agentGain($_ao_data);
            } else {
                $_agent_gain = 0;
            }
        }
        /* 查询渠道父渠道 */
        $_u_map['id'] = $_pay_data[$_agentalias];
        $_parent_id = Db::name('users')->where($_u_map)->value('ownerid');
        if ($_parent_id == $_u_map['id'] || empty($_parent_id)) {
            return true;
        }
        $_apg_map['agent_id'] = $_parent_id;
        $_apg_map['app_id'] = $_pay_data['app_id'];
        $_parentr_info = Db::name('agent_game_rate')->where($_apg_map)->find();
        //父渠道未设定折扣处理
        if (empty($_parentr_info) && Config::get('config.G_DEFAULT_EN')) {
            $_parentr_info = self::getEmptyrate($_parent_id, $_pay_data['app_id']);
        }
        if (!empty($_parentr_info)) {
            $_parent_gain = 0;
            /* 二级渠道收益为0时,实际折扣价格与一级渠道差异得到一级收益 */
            if ($_agent_gain <= 0.00001) {
                $_parent_gain = $_real_amount - $_cp_no_gm_real * $_parentr_info['agent_rate'];
            } else {
                $_diff_rate = $_agr_info['agent_rate'] - $_parentr_info['agent_rate'];
                if ($_diff_rate >= 0.0001) {
                    $_parent_gain = $_cp_no_gm_real * $_diff_rate;
                }
            }
            if ($_parent_gain >= 0.01) {
                /* 一级渠道收益修改 wuyonghong */
                $_ao_data['parent_id'] = $_parent_id;
                $_ao_data['parent_rate'] = $_parentr_info['agent_rate'];
                $_ao_data['parent_gain'] = $_parent_gain;
                $_aor_map['order_id'] = $_ao_data['order_id'];
                Db::name('agent_order')->where($_aor_map)->update($_ao_data);
                $_ao_data['agent_id'] = $_parent_id;
                $_ao_data['agent_rate'] = $_parentr_info['agent_rate'];
                $_ao_data['agent_gain'] = $_parent_gain;
                self::agentGain($_ao_data);
            } else {
                /* 一级渠道无收益时，一级渠道记录到表agent_order中 ou */
                $_ao_data['parent_id'] = $_parent_id;
                $_ao_data['parent_rate'] = $_parentr_info['agent_rate'];
                $_ao_data['parent_gain'] = 0;
                $_aor_map['order_id'] = $_ao_data['order_id'];
                Db::name('agent_order')->where($_aor_map)->update($_ao_data);
            }
        } else {
            /* 一级渠道未设定折扣，一级渠道记录到表agent_order中 ou */
            $_ao_data['parent_id'] = $_parent_id;
            $_ao_data['parent_rate'] = 1;
            $_ao_data['parent_gain'] = 0;
            $_aor_map['order_id'] = $_ao_data['order_id'];
            Db::name('agent_order')->where($_aor_map)->update($_ao_data);
        }
    }

    /*
     * 渠道收益表
     */
    private static function agentGain($ao_data) {
        if (empty($ao_data)) {
            return false;
        }
        /* 更新agent_day_gain */
        $_map['date'] = date("Y-m-d", $ao_data['create_time']);
        $_map['agent_id'] = $ao_data['agent_id'];
        $_map['app_id'] = $ao_data['app_id'];
        $_adg_data = Db::name('agent_day_gain')->where($_map)->find();
        if (empty($_adg_data)) {
            $_adg_data = $_map;
            $_adg_data['sum_money'] = $ao_data['amount'];
            $_adg_data['sum_real_money'] = $ao_data['real_amount'];
            $_adg_data['sum_rebate_cnt'] = $ao_data['rebate_cnt'];
            $_adg_data['sum_agent_gain'] = $ao_data['agent_gain'];
            Db::name('agent_day_gain')->insert($_adg_data);
        } else {
            $_adg_data['sum_money'] += $ao_data['amount'];
            $_adg_data['sum_real_money'] += $ao_data['real_amount'];
            $_adg_data['sum_rebate_cnt'] += $ao_data['rebate_cnt'];
            $_adg_data['sum_agent_gain'] += $ao_data['agent_gain'];
            Db::name('agent_day_gain')->update($_adg_data);
        }
        /* 更新agent_gain */
        unset($_map['date']);
        $_agg_data = Db::name('agent_game_gain')->where($_map)->find();
        if (empty($_agg_data)) {
            $_agg_data = $_map;
            /* 获取ag_id */
            $_agg_data['ag_id'] = Db::name('agent_game')->where($_map)->cache(86400)->value('id');
            $_agg_data['sum_money'] = $ao_data['amount'];
            $_agg_data['sum_real_money'] = $ao_data['real_amount'];
            $_agg_data['sum_rebate_cnt'] = $ao_data['rebate_cnt'];
            $_agg_data['sum_agent_gain'] = $ao_data['agent_gain'];
            Db::name('agent_game_gain')->insert($_agg_data);
        } else {
            $_agg_data['sum_money'] += $ao_data['amount'];
            $_agg_data['sum_real_money'] += $ao_data['real_amount'];
            $_agg_data['sum_rebate_cnt'] += $ao_data['rebate_cnt'];
            $_agg_data['sum_agent_gain'] += $ao_data['agent_gain'];
            Db::name('agent_game_gain')->update($_agg_data);
        }
        /* 更新agent_ext */
        unset($_map['app_id']);
        $_ae_data = Db::name('agent_ext')->where($_map)->find();
        if (empty($_ae_data)) {
            $_ae_data = $_map;
            /* 获取ag_id */
            $_ae_data['sum_money'] = $ao_data['amount'];
            $_ae_data['sum_real_money'] = $ao_data['real_amount'];
            $_ae_data['share_total'] = $ao_data['agent_gain'];
            $_ae_data['share_remain'] = $ao_data['agent_gain'];
            $_ae_data['order_cnt'] = 1;
            Db::name('agent_ext')->insert($_ae_data);
        } else {
            $_ae_data['sum_money'] += $ao_data['amount'];
            $_ae_data['sum_real_money'] += $ao_data['real_amount'];
            $_ae_data['share_total'] += $ao_data['agent_gain'];
            $_ae_data['share_remain'] += $ao_data['agent_gain'];
            $_ae_data['order_cnt'] += 1;
            Db::name('agent_ext')->update($_ae_data);
        }
    }

    //未分包情况下处理
    private static function getEmptyrate($agent_id, $app_id) {
        //判断是否是官方渠道
        if ($agent_id <= 1 || empty($agent_id)) {
            return 0;
        }
        //判断是1级渠道还是二级渠道
        $_c_map['id'] = $agent_id;
        $_parent_id = Db::name('users')->where($_c_map)->value('ownerid');
        //游戏设定折扣
        $_g_map['app_id'] = $app_id;
        $_game_rate = Db::name('game_rate')->where($_g_map)->find();
        $_add_rate = Config::get('config.G_ADD_RATE');
        //未上线游戏处理
        if (empty($_game_rate)){
            return 0;
        }
        if ($_parent_id > 1) {
            //二级渠道，检查一级渠道是否设定了折扣
            $_p_map['agent_id'] = $_parent_id;
            $_p_map['app_id'] = $app_id;
            $_p_info = Db::name('agent_game_rate')->where($_p_map)->find();
            if (empty($_p_info)) {
                //一级未设定折扣，先把一级的插入
                $_p_ag_id = self::agentGameset($app_id, $_parent_id);
                //再插入agent_game_rate
                $_add_agr_data = array(
                    "ag_id"            => $_p_ag_id,
                    "agent_id"         => $_parent_id,
                    "app_id"           => $app_id,
                    "agent_rate"       => $_game_rate['agent_rate'],
                    "benefit_type"     => $_game_rate['benefit_type'],
                    "mem_rate"         => $_game_rate['mem_rate'],
                    "first_mem_rate"   => $_game_rate['first_mem_rate'],
                    "mem_rebate"       => $_game_rate['mem_rebate'],
                    "first_mem_rebate" => $_game_rate['first_mem_rebate']
                );
                self::insertAgentgamerate($_add_agr_data);
                //二级渠道插入
                $_agent_game_id = self::agentGameset($app_id, $agent_id);
                $_sec_rate = $_game_rate['agent_rate'] + $_add_rate;
                if ($_sec_rate > 1) {
                    $_sec_rate = 1;
                }
                //再插入agent_game_rate,二级渠道
                $_add_agr_data_er = array(
                    "ag_id"            => $_agent_game_id,
                    "agent_id"         => $agent_id,
                    "app_id"           => $app_id,
                    "agent_rate"       => $_sec_rate,
                    "benefit_type"     => $_game_rate['benefit_type'],
                    "mem_rate"         => $_game_rate['mem_rate'],
                    "first_mem_rate"   => $_game_rate['first_mem_rate'],
                    "mem_rebate"       => $_game_rate['mem_rebate'],
                    "first_mem_rebate" => $_game_rate['first_mem_rebate']
                );
                return self::insertAgentgamerate($_add_agr_data_er);
            } else {
                //一级设定了折扣，取一级折扣加G_ADD_RATE
                $_agent_game_id = self::agentGameset($app_id, $agent_id);
                $_sec_rate = $_p_info['agent_rate'] + $_add_rate;
                if ($_sec_rate > 1) {
                    $_sec_rate = 1;
                }
                //再插入agent_game_rate,二级渠道
                $_add_agr_data_er = array(
                    "ag_id"            => $_agent_game_id,
                    "agent_id"         => $agent_id,
                    "app_id"           => $app_id,
                    "agent_rate"       => $_sec_rate,
                    "benefit_type"     => $_game_rate['benefit_type'],
                    "mem_rate"         => $_game_rate['mem_rate'],
                    "first_mem_rate"   => $_game_rate['first_mem_rate'],
                    "mem_rebate"       => $_game_rate['mem_rebate'],
                    "first_mem_rebate" => $_game_rate['first_mem_rebate']
                );
                return self::insertAgentgamerate($_add_agr_data_er);
            }
        } else {
            //一级渠道
            $_agent_game_id = self::agentGameset($app_id, $agent_id);
            $_add_agr_data = array(
                "ag_id"            => $_agent_game_id,
                "agent_id"         => $agent_id,
                "app_id"           => $app_id,
                "agent_rate"       => $_game_rate['agent_rate'],
                "benefit_type"     => $_game_rate['benefit_type'],
                "mem_rate"         => $_game_rate['mem_rate'],
                "first_mem_rate"   => $_game_rate['first_mem_rate'],
                "mem_rebate"       => $_game_rate['mem_rebate'],
                "first_mem_rebate" => $_game_rate['first_mem_rebate']
            );
            return self::insertAgentgamerate($_add_agr_data);
        }
    }

    private static function agentGameset($app_id, $agent_id) {
        $_ag_map['app_id'] = $app_id;
        $_ag_map['agent_id'] = $agent_id;
        $_agent_game_id = Db::name('agent_game')->where($_ag_map)->value('id');
        if (empty($_agent_game_id)) {
            $_agent_game_map['id'] = $app_id;
            $_initial = Db::name('game')->where($_agent_game_map)->value("initial");
            $agentgame = $_initial."_".$agent_id;
            $_add_agent_game = array(
                "agent_id"    => $agent_id,
                "app_id"      => $app_id,
                "agentgame"   => $agentgame,
                "create_time" => time(),
                "update_time" => time(),
                "status"      => 1
            );
            $_agent_game_id = Db::name('agent_game')->insertGetid($_add_agent_game);
        }
        return $_agent_game_id;
    }

    private static function insertAgentgamerate($data) {
        if (empty($data)) {
            return;
        }
        //再插入agent_game_rate,二级渠道
        $_add_agr_data = array(
            "ag_id"            => $data['ag_id'],
            "agent_id"         => $data['agent_id'],
            "app_id"           => $data['app_id'],
            "agent_rate"       => $data['agent_rate'],
            "benefit_type"     => $data['benefit_type'],
            "mem_rate"         => $data['mem_rate'],
            "first_mem_rate"   => $data['first_mem_rate'],
            "mem_rebate"       => $data['mem_rebate'],
            "first_mem_rebate" => $data['first_mem_rebate'],
            "promote_switch"   => 2,
            "create_time"      => time(),
            "update_time"      => time()
        );
        Db::name('agent_game_rate')->insert($_add_agr_data);
        return $_add_agr_data;
    }
}