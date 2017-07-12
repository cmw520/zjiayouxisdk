<?php
namespace Huosdk\Benefit;
class RulesGame {
    public $mem_rate_region;
    public $mem_rebate_region;
    public $mem_rate_region_txt;
    public $mem_rebate_region_txt;
    public $mem_rate_bottom;
    public $mem_rate_top;
    public $mem_rebate_bottom;
    public $mem_rebate_top;
    public $mem_rate_set_hint;
    public $first_mem_rate_set_hint;
    public $mem_rebate_set_hint;
    public $first_mem_rebate_set_hint;
    public $hint;

    public function __construct($agent_rate) {
        $this->mem_rate_region = array();
        $this->mem_rebate_region = array();
        $this->mem_rate_bottom = 0;
        $this->mem_rate_top = 1;
        $this->mem_rebate_bottom = 0;
        $this->mem_rebate_top = 900;
        $this->generate_limit($agent_rate);
        $this->mem_rebate_region_txt = "( $this->mem_rebate_bottom , $this->mem_rebate_top )%";
        $this->mem_rate_region_txt = "( $this->mem_rate_bottom , $this->mem_rate_top ]";
        $this->mem_rate_set_hint = "玩家续充折扣必须介于".$this->mem_rate_region_txt;
        $this->first_mem_rate_set_hint = "玩家首充折扣必须介于".$this->mem_rate_region_txt;
        $this->mem_rebate_set_hint = "玩家续充返利必须介于".$this->mem_rebate_region_txt;
        $this->first_mem_rebate_set_hint = "玩家首充返利必须介于".$this->mem_rebate_region_txt;
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
            return "玩家续充折扣必须大于玩家首充折扣";
        }
        /* 首充不必大于agent_rate */
        if ($first_mem_rate > $this->mem_rate_top) {
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
        if ($first_mem_rebate < $this->mem_rebate_bottom || $first_mem_rebate > $this->mem_rebate_top) {
            return $this->first_mem_rebate_set_hint;
        }
        if (!($mem_rebate >= $this->mem_rebate_bottom && $mem_rebate <= $this->mem_rebate_top)) {
            return $this->mem_rebate_set_hint;
        }
        return "ok";
    }

    public function generate_limit($agent_rate) {
        /**
         * 如果agent rate为0，不能往下进行
         */
        if (!$agent_rate) {
            return false;
        }
        $this->mem_rate_bottom = floatval($agent_rate);
        if ($agent_rate != 0 && is_numeric($agent_rate) && $agent_rate > 0 && $agent_rate <= 1) {
            $top = floatval(number_format((1 / $agent_rate - 1) * 100, 2, '.', ''));
        } else {
            $top = 900;
        }
        $this->mem_rebate_top = $top;
    }
}

