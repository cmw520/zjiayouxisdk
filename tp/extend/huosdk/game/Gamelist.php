<?php
/**
 * Gamelist.php UTF-8
 * 游戏列表类 所有游戏都在此查询
 *
 * @date    : 2017/1/16 15:30
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\game;

use think\Config;
use think\Db;
use think\Log;

class Gamelist {
    /**
     * @param string $msg
     * @param string $level
     */
    private function _error($msg = '', $level = 'error') {
        $_info = 'game\Gamelist Error:'.$msg;
        Log::record($_info, 'error');
    }

    private function getListfield() {
        $_field = [
            'g.id'                          => 'gameid',
            "IFNULL(gi.mobile_icon,g.icon)" => 'icon',
            'g.name'                        => 'gamename',
            'g.type'                        => 'type',
            "IFNULL(gi.size,'')"            => 'size',
            "g.is_hot"                      => 'hot',
            "g.is_new"                      => 'isnew',
            'g.category'                    => 'category',
            "IFNULL(gi.androidurl,'')"      => 'downlink',
            "IFNULL(gi.publicity,'')"       => 'oneword',
            "g.run_time"                    => 'runtime',
            "IFNULL(g.packagename,'')"      => 'packagename'
        ];

        return $_field;
    }

    public function testList(array $game_map, $page, $offset) {
        $_base_field = $this->getListfield();
        $_own_field = [
            'gt.id'         => 'testid',
            'gt.testdesc'   => 'testdesc',
            'gt.start_time' => 'starttime',
            'gt.status'     => 'status'
        ];
        $_field = array_merge($_base_field, $_own_field);
        $_own_map['gt.is_delete'] = 2;
        $_own_map['gt.start_time'] = ['gt', time()];
        $_map = array_merge($_own_map, $game_map);
        $_join = [
            [
                Config::get('database.prefix').'game g',
                'g.id=gt.app_id',
                'LEFT'
            ],
            [
                Config::get('database.prefix').'game_info gi',
                'g.game_id =gi.app_id',
                'LEFT'
            ]
        ];
        $_order = " start_time ASC ";
        $_page = $page." , ".$offset;
        $_rdata['count'] = Db::name('game_test')
                             ->alias('gt')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if (0 < $_rdata['count']) {
            $_list = Db::name('game_test')
                       ->alias('gt')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $this->setAssigninfo($_list);
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    public function serverList(array $game_map, $page, $offset) {
        $_base_field = $this->getListfield();
        $_own_field = [
            'gs.id'         => 'serid',
            'gs.ser_name'   => 'sername',
            'gs.ser_desc'   => 'serdesc',
            'gs.status'     => 'status',
            'gs.start_time' => 'starttime'
        ];
        $_field = array_merge($_base_field, $_own_field);
        $_own_map['gs.is_delete'] = 2;
        $_start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $_own_map['gs.start_time'] = ['gt', $_start_time];
        $_map = array_merge($_own_map, $game_map);
        $_join = [
            [
                Config::get('database.prefix').'game g',
                'g.id=gs.app_id',
                'LEFT'
            ],
            [
                Config::get('database.prefix').'game_info gi',
                'g.game_id =gi.app_id',
                'LEFT'
            ]
        ];
        $_order = " start_time ASC ";
        $_page = $page." , ".$offset;
        $_rdata['count'] = Db::name('game_server')
                             ->alias('gs')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if (0 < $_rdata['count']) {
            $_list = Db::name('game_server')
                       ->alias('gs')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $this->setAssigninfo($_list);
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    public function remdList(array $game_map, $page, $offset, $hasgift = 0) {
        $_base_field = $this->getListfield();
        $_own_field = [];
        $_field = array_merge($_base_field, $_own_field);
        $_own_map['gr.is_delete'] = 2;
        $_map = array_merge($_own_map, $game_map);
        $_join = [
            [
                Config::get('database.prefix').'game g',
                'g.id=gr.app_id',
                'LEFT'
            ],
            [
                Config::get('database.prefix').'game_info gi',
                'g.game_id =gi.app_id',
                'LEFT'
            ]
        ];
        $_order = " g.listorder DESC ";
        $_page = $page." , ".$offset;
        $_rdata['count'] = Db::name('game_recmd')
                             ->alias('gr')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if (0 < $_rdata['count']) {
            if (!empty($hasgift) && 2 == $hasgift) {
                $_page = "1,".$_rdata['count'];
            }
            $_list = Db::name('game_recmd')
                       ->alias('gr')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $this->setAssigninfo($_list);
                if (!empty($hasgift) && 2 == $hasgift) {
                    $_cnt  = 0;
                    $_gift_list = array();
                    foreach ($_list as $_key => $_val) {
                        if ($_val['giftcnt'] > 0) {
                            $_gift_list[] = $_val;
                            $_cnt ++;
                        }
                        if ($_cnt >= $offset){
                            break;
                        }
                    }
                    unset($_list);
                    $_rdata['count'] = count($_gift_list);
                    if ($_rdata['count'] <= 0) {
                        $_list = null;
                    } else {
                        $_list = $_gift_list;
                    }
                }
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    public function typeList(array $game_map, $page, $offset, $type = 0) {
        $_and_type = '';
        if (!empty($type)) {
            $_and_type = " AND ggt.type_id=".$type;
        }
        $_join = [
            [
                Config::get('database.prefix').'game_gt ggt',
                'g.game_id=ggt.app_id '.$_and_type,
                'LEFT'
            ],
            [
                Config::get('database.prefix').'game_info gi',
                'g.game_id =gi.app_id',
                'LEFT'
            ]
        ];
        $_own_map = [
            'ggt.type_id' => $type
        ];
        $_map = array_merge($_own_map, $game_map);
        $_count = Db::name('game')
                    ->alias('g')
                    ->join($_join)
                    ->where($_map)
                    ->count();
        if ($_count > 0) {
            $_field = $this->getListfield();
            $_order = " g.listorder DESC ";
            $_page = $page." , ".$offset;
            $_list = Db::name('game')
                       ->alias('g')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            $_rdata['count'] = $_count;
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $this->setAssigninfo($_list);
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    public function gameList(array $game_map, $page, $offset, $_rand_flag = false) {
        $_join = [
            [
                Config::get('database.prefix').'game_info gi', 'g.game_id = gi.app_id', 'LEFT'
            ]
        ];
        $_map = $game_map;
        $_count = Db::name('game')
                    ->alias('g')
                    ->where($_map)
                    ->count();
        $_field = $this->getListfield();
        /* 游戏数量大于0 才返回数据 */
        if ($_count > 0) {
            $_start = ($page - 1) * $offset;
            if ($_rand_flag && $_count > $offset) {
                $_start = rand(0, $_count - $offset);
            }
            $_limit = $_start.','.$offset;
            $_order = " g.listorder DESC ";
            $_list = Db::name('game')
                       ->alias('g')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->limit($_limit)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $this->setAssigninfo($_list);
                $_rdata['list'] = $_list;
            }
            $_rdata['count'] = $_count;
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    private function setAssigninfo(array &$gamelist) {
        if (empty($gamelist)) {
            return false;
        }
        $_gtype_class = new Gametype();
        foreach ($gamelist as $_k => $_v) {
            $gamelist[$_k]['type'] = $_gtype_class->getTypebyId($_v['type']);
            /* 查询礼包数量 */
            $_gift_class = new \huosdk\gift\Gift();
            $gamelist[$_k]['giftcnt'] = $_gift_class->getGiftcnt($_v['gameid']);
            /* 解析ICON */
            if (strpos($_v['icon'], "/") === 0) {
                $gamelist[$_k]['icon'] = Config::get('domain.STATICSITE').$_v['icon'];
            } else {
                $gamelist[$_k]['icon'] = Config::get('domain.STATICSITE').'/upload/image/'.$_v['icon'];
            }
        }

        return true;
    }
}