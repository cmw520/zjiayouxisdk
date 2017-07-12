<?php
/**
 * Gmmem.class.php UTF-8
 * 玩家游戏币类
 *
 * @date    : 2016年10月11日下午11:16:07
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : huosdk 7.0
 */
namespace Huosdk;
class Gmmem {
    private $gmname;
    private $app_id;

    public function __construct($app_id) {
        $this->app_id = $app_id;
        $this->gmname = C('CURRENCY_NAME');
        if (!empty($app_id)) {
            $map['app_id'] = $app_id;
            $payname = M('gm')->where($map)->getField('payname');
            if (!empty($payname)) {
                $this->gmname = $payname;
            }
        }
    }

    public function getRemain($mem_id) {
        if (empty($mem_id) || 0 == $this->app_id) {
            return 0;
        }
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $this->app_id;
        $remain = M('gm_mem')->where($map)->getField('remain');
        if (empty($remain)) {
            $remain = 0;
        }
        return $remain;
    }

    public function getGmname() {
        return $this->gmname;
    }

    public function getMemPaycnt($mem_id, $app_id) {
        if (empty($mem_id)) {
            return 0;
        }
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $app_id;
        $cnt = M('gm_charge')->where($map)->count('id');
        if (empty($cnt)) {
            $cnt = 0;
        }
        return $cnt;
    }
}

