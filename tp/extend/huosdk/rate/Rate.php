<?php
/**
 * Rate.php UTF-8
 * 折扣类
 *
 * @date    : 2016年11月16日下午6:09:12
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月16日下午6:09:12
 */
namespace huosdk\rate;

use think\Db;
use think\Config;

class Rate {
    private $defaultrate
        = array(
            'agent_rate'       => 1,
            'benefit_type'     => 0,
            'mem_rate'         => 1,
            'first_mem_rate'   => 1,
            'mem_rebate'       => 0,
            'first_mem_rebate' => 0
        );
    private $game_rate;
    private $agent_rate;
    private $app_id;

    public function __construct($app_id) {
        $this->app_id = $app_id;
        if (!empty($app_id)) {
            $_map['app_id'] = $app_id;
            $gr_data = Db::name('game_rate')->where($_map)->find();
            if ($gr_data) {
                $this->game_rate = $gr_data['game_rate'];
                if (2 == $gr_data['promote_switch'] && 0 != $gr_data['benefit_type']) {
                    $this->agent_rate = $gr_data['agent_rate'];
                    $this->defaultrate['agent_rate'] = $gr_data['agent_rate'];
                    $this->defaultrate['benefit_type'] = $gr_data['benefit_type'];
                    if (1 == $this->defaultrate['benefit_type']
                        && (1 == Config::get('config.G_DISCONT_TYPE')
                            || 3 == Config::get('config.G_DISCONT_TYPE'))
                    ) {
                        $this->defaultrate['mem_rate'] = $gr_data['mem_rate'];
                        $this->defaultrate['first_mem_rate'] = $gr_data['first_mem_rate'];
                        $this->defaultrate['mem_rebate'] = 0;
                        $this->defaultrate['first_mem_rebate'] = 0;
                    } else if (2 == $this->defaultrate['benefit_type']
                               && (2 == Config::get('config.G_DISCONT_TYPE')
                                   || 3 == Config::get('config.G_DISCONT_TYPE'))
                    ) {
                        $this->defaultrate['mem_rate'] = 1;
                        $this->defaultrate['first_mem_rate'] = 1;
                        $this->defaultrate['mem_rebate'] = $gr_data['mem_rebate'];
                        $this->defaultrate['first_mem_rebate'] = $gr_data['first_mem_rebate'];
                    } else {
                        $this->defaultrate['benefit_type'] = 0;
                        $this->defaultrate['mem_rate'] = 1;
                        $this->defaultrate['first_mem_rate'] = 1;
                        $this->defaultrate['mem_rebate'] = 0;
                        $this->defaultrate['first_mem_rebate'] = 0;
                    }
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
        $_map['agent_id'] = $agent_id;
        $_map['app_id'] = $app_id;
        $agr_data = Db::name('agent_game_rate')->where($_map)->find();
        if (empty($agr_data) || 2 != $agr_data['promote_switch']) {
            return $this->defaultrate;
        } else {
            $data['agent_rate'] = $agr_data['agent_rate'];
            $data['benefit_type'] = $agr_data['benefit_type'];
            if (1 == $data['benefit_type']) {
                $data['mem_rate'] = $agr_data['mem_rate'];
                $data['first_mem_rate'] = $agr_data['first_mem_rate'];
                $data['mem_rebate'] = 0;
                $data['first_mem_rebate'] = 0;
            } else {
                $data['mem_rate'] = 1;
                $data['first_mem_rate'] = 1;
                $data['mem_rebate'] = $agr_data['mem_rebate'];
                $data['first_mem_rebate'] = $agr_data['first_mem_rebate'];
            }
            return $data;
        }
    }

    public function getMemPaycnt($mem_id, $app_id) {
        if (empty($mem_id) || empty($app_id)) {
            return 0;
        }
        $_map['mem_id'] = $mem_id;
        $_map['app_id'] = $app_id;
        $_map['status'] = PAYSTATUS_SUCCESS;
        $cnt = Db::name('pay')->where($_map)->count('id');
        if (empty($cnt)) {
            $cnt = 0;
        }
        return $cnt;
    }

    public function getGmPaycnt($mem_id, $app_id) {
        if (empty($mem_id)) {
            return 0;
        }
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $app_id;
        $cnt = Db::name('gm_charge')->where($map)->count('id');
        if (empty($cnt)) {
            $cnt = 0;
        }
        return $cnt;
    }

    public function getMemrate($agent_id, $app_id = 0, $mem_id, $type = 3) {
        if (empty($app_id) || empty($mem_id)) {
            $app_id = $this->app_id;
        }
        if (!empty($agent_id)) {
            $_map['agent_id'] = $agent_id;
            $_map['app_id'] = $app_id;
            $agr_data = $this->getAgentAllrate($agent_id, $app_id);
        } else {
            $agr_data = $this->defaultrate;
        }
        $rdata['benefit_type'] = $agr_data['benefit_type'];
        if (3 == $type) {
            // sdk充值游戏
            $pay_cnt = $this->getMemPaycnt($mem_id, $app_id);
        } else {
            $pay_cnt = $this->getGmPaycnt($mem_id, $app_id);
        }
        $rdata['isfirst'] = 0;
        if (empty($pay_cnt)) {
            $rdata['mem_rate'] = $agr_data['first_mem_rate'];
            $rdata['mem_rebate'] = $agr_data['first_mem_rebate'];
            $rdata['isfirst'] = 1;
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

