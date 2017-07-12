<?php
/**
 * RulesAgentGame.class.php UTF-8
 * 渠道游戏折扣限制
 *
 * @date    : 2016年12月16日下午11:42:22
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月16日下午11:42:22
 */
namespace Huosdk\Benefit;
class RulesAgentGame {
    public $mem_rate_region;
    public $mem_rebate_region;
    public $mem_rate_region_txt;
    public $mem_rebate_region_txt;
    public $first_mem_rate_bottom;
    public $mem_rate_bottom;
    public $mem_rate_top;
    public $mem_rebate_bottom;
    public $mem_rebate_top;
    public $first_mem_rebate_top;
    public $mem_rate_set_hint;
    public $first_mem_rate_set_hint;
    public $mem_rebate_set_hint;
    public $first_mem_rebate_set_hint;
    public $hint;

    public function __construct($ag_id, $agent_rate = 0) {
        $this->mem_rate_region = array();
        $this->mem_rebate_region = array();
        $this->mem_rate_bottom = 0;
        $this->mem_rate_top = 1;
        $this->mem_rebate_bottom = 0;
        $this->mem_rebate_top = 900;
        $this->generate_limit($ag_id, $agent_rate);
        $this->mem_rebate_region_txt = "( $this->mem_rebate_bottom , $this->mem_rebate_top )%";
        $this->mem_rate_region_txt = "( $this->mem_rate_bottom , $this->mem_rate_top ]";
        $this->mem_rate_set_hint = "玩家续充折扣必须介于".$this->mem_rate_region_txt;
        $this->first_mem_rate_set_hint = "玩家首充折扣必须介于"."( $this->first_mem_rate_bottom , $this->mem_rate_top ]";
        $this->mem_rebate_set_hint = "玩家续充返利必须介于".$this->mem_rebate_region_txt;
        $this->first_mem_rebate_set_hint = "玩家首充返利必须介于"."( $this->mem_rebate_bottom , $this->first_mem_rebate_top )%";
        $this->hint = array(
            'mem_rate'         => $this->mem_rate_set_hint,
            "first_mem_rate"   => $this->first_mem_rate_set_hint,
            'mem_rebate'       => $this->mem_rebate_set_hint,
            "first_mem_rebate" => $this->first_mem_rebate_set_hint
        );
    }

    public function agent_rate($agent_rate) {
        if (!(($agent_rate > 0) && ($agent_rate <= 1))) {
            return false;
        }
        return true;
    }

    public function check_mem_rate($mem_rate, $first_mem_rate) {
        if (!(($mem_rate > 0) && ($mem_rate <= 1) && ($first_mem_rate > 0) && ($first_mem_rate <= 1))) {
            return "玩家折扣必须在区间(0,1]内";
        }
        if (!($mem_rate >= $first_mem_rate)) {
            return "玩家续充折扣必须小于玩家首充折扣";
        }
        /* 首充不必大于agent_rate */
        if ($first_mem_rate < $this->first_mem_rate_bottom) {
            return $this->first_mem_rate_set_hint;
        }
        if (!($mem_rate >= $this->mem_rate_bottom && $mem_rate <= $this->mem_rate_top)) {
            return $this->mem_rate_set_hint;
        }
        return "ok";
    }

    public function check_mem_rebate($mem_rebate, $first_mem_rebate) {
        if (!(($mem_rebate >= 0) && ($first_mem_rebate >= 0))) {
            return "玩家返利必须大于0";
        }
        if ($mem_rebate > $first_mem_rebate) {
            return "玩家续充返利必须小于玩家首充返利";
        }
        if ($first_mem_rebate > $this->first_mem_rebate_top || $first_mem_rebate < $this->mem_rebate_bottom) {
            return $this->first_mem_rebate_set_hint;
        }
        if (!($mem_rebate >= $this->mem_rebate_bottom && $mem_rebate <= $this->mem_rebate_top)) {
            return $this->mem_rebate_set_hint;
        }
        return "ok";
    }

    public function generate_limit($ag_id, $agent_rate) {
        /**
         * 如果agent rate为0，不能往下进行
         */
        if (empty($ag_id)) {
            return false;
        }
        $_agr_map['ag_id'] = $ag_id;
        $_agr_data = M('agent_game_rate')->where($_agr_map)->find();
        if (empty($_agr_data)) {
            return false;
        }
        $_u_map['id'] = $_agr_data['agent_id'];
        $_parent_id = M('users')->where($_u_map)->getField('ownerid');
        $_pagr_flag = false;
        if (!empty($_parent_id)) {
            $_pagr_map['agent_id'] = $_parent_id;
            $_pagr_map['app_id'] = $_agr_data['app_id'];
            $_pagr_data = M('agent_game_rate')->where($_pagr_map)->find();
            if ($_pagr_data) {
                $_pagr_flag = true;
                $_gr_data = $_pagr_data;
            }
        }
        if (!$_pagr_flag) {
            $_gr_map['app_id'] = $_agr_data['app_id'];
            $_gr_data = M('game_rate')->where($_gr_map)->find();
        }
        if (empty($agent_rate)) {
            $agent_rate = $_agr_data['agent_rate'];
        }
        $this->first_mem_rate_bottom = $_gr_data['first_mem_rate'];
        if ($agent_rate < $this->first_mem_rate_bottom) {
            $this->first_mem_rate_bottom = $agent_rate;
        }
        $this->mem_rate_bottom = $_gr_data['mem_rate'];
        if ($agent_rate < $this->mem_rate_bottom) {
            $this->mem_rate_bottom = $agent_rate;
        }
        if ($agent_rate != 0 && is_numeric($agent_rate) && $agent_rate > 0 && $agent_rate <= 1) {
            $top = round((1 / $agent_rate - 1) * 100);
        } else {
            $top = 900;
        }
        $this->first_mem_rebate_top = $_gr_data['first_mem_rebate'];
        if ($top > $this->first_mem_rebate_top) {
            $this->first_mem_rebate_top = $top;
        }
        $this->mem_rebate_top = $_gr_data['mem_rebate'];
        if ($top > $this->first_mem_rebate_top) {
            $this->first_mem_rebate_top = $top;
        }
    }
}

