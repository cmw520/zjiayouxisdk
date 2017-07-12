<?php
/**
 * Rate.class.php UTF-8
 * 折扣类
 *
 * @date    : 2016年10月11日下午11:16:07
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : huosdk 7.0
 */
namespace Huosdk;
class Rate {
    private $defaultrate
        = array(
            'agent_rate'       => 1,
            'benefit_type'     => 0,
            'mem_rate'         => 1,
            'first_mem_rate'   => 1,
            'mem_rebate'       => 0,
            'first_mem_rebate' => 0,
        );
    private $game_rate;
    private $agent_rate;
    private $app_id;

    public function __construct($app_id) {
        $this->app_id = $app_id;
        if (!empty($app_id)) {
            $map['app_id'] = $app_id;
            $gr_data = M('game_rate')->where($map)->find();
            if ($gr_data) {
                $this->game_rate = $gr_data['game_rate'];
                if (2 == $gr_data['promote_switch'] && 0 != $gr_data['benefit_type']) {
                    $this->agent_rate = $gr_data['agent_rate'];
                    $this->defaultrate['agent_rate'] = $gr_data['agent_rate'];
                    $this->defaultrate['benefit_type'] = $gr_data['benefit_type'];
                    $this->defaultrate['mem_rate'] = $gr_data['mem_rate'];
                    $this->defaultrate['first_mem_rate'] = $gr_data['first_mem_rate'];
                    $this->defaultrate['mem_rebate'] = $gr_data['mem_rebate'];
                    $this->defaultrate['first_mem_rebate'] = $gr_data['first_mem_rebate'];
                }
            }
        }
    }

    public function getAgentAllrate($agent_id, $app_id = 0) {
        if (empty($app_id)) {
            $app_id = $this->app_id;
        }
        if (empty($agent_id)) {
            return $this->defaultrate;
        }
        $map['agent_id'] = $agent_id;
        $map['app_id'] = $app_id;
        $agr_data = M('agent_game_rate')->where($map)->find();
        if (empty($agr_data) || 2 != $agr_data['promote_switch']) {
            return $this->defaultrate;
        } else {
            $data['agent_rate'] = $agr_data['agent_rate'];
            $data['benefit_type'] = $agr_data['benefit_type'];
            $data['mem_rate'] = $agr_data['mem_rate'];
            $data['first_mem_rate'] = $agr_data['first_mem_rate'];
            $data['mem_rebate'] = $agr_data['mem_rebate'];
            $data['first_mem_rebate'] = $agr_data['first_mem_rebate'];
            return $data;
        }
    }

    public function getMemrate($agent_id, $app_id = 0, $mem_id, $type = 3) {
        $rdata = array(
            'benefit_type' => $this->defaultrate['benefit_type'],
            'mem_rate'     => $this->defaultrate['mem_rate'],
            'mem_rebate'   => $this->defaultrate['mem_rebate']
        );
        if (empty($app_id) || empty($mem_id)) {
            $app_id = $this->app_id;
        }
        if (empty($agent_id)) {
            return $rdata;
        }
        $map['agent_id'] = $agent_id;
        $map['app_id'] = $app_id;
        $agent_all_rate = $this->getAgentAllrate($agent_id, $app_id);
        $agr_data = M('agent_game_rate')->where($map)->find();
        $rdata['benefit_type'] = $agr_data['benefit_type'];
        if (3 == $type) {
            //sdk充值游戏
            $payclass = new \Huosdk\Pay($app_id);
            $pay_cnt = $payclass->getMemPaycnt($mem_id, $app_id);
        } else {
            $gmclass = new \Huosdk\Gmmem($app_id);
            $pay_cnt = $gmclass->getMemPaycnt($mem_id, $app_id);
        }
        if (empty($pay_cnt)) {
            $rdata['mem_rate'] = $agr_data['first_mem_rate'];
            $rdata['mem_rebate'] = $agr_data['first_mem_rebate'];
        } else {
            $rdata['mem_rate'] = $agr_data['mem_rate'];
            $rdata['mem_rebate'] = $agr_data['mem_rebate'];
        }
        return $rdata;
    }

    public function getDefautAllrate() {
        return $this->defaultrate;
    }

    public function getGamerate() {
        return $this->game_rate;
    }
}

