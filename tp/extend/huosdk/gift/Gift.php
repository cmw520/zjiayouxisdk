<?php
/**
 * Gift.php UTF-8
 * 礼包处理类
 *
 * @date    : 2016年12月3日下午4:58:28
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月3日下午4:58:28
 */
namespace huosdk\gift;

use think\Config;
use think\Db;
use think\Session;

class Gift {
    private $gift_id;
    private $app_id;

    /**
     * Gift constructor.
     *
     * @param int $app_id  游戏ID
     * @param int $gift_id 礼包ID
     */
    public function __construct($app_id = 0, $gift_id = 0) {
        if (!empty($app_id)) {
            $this->app_id = $app_id;
        }
        if (!empty($gift_id)) {
            $this->gift_id = $gift_id;
        }
    }

    /**
     * 获取礼包数量
     *
     * @param int $app_id
     *
     * @return int
     */
    public function getGiftcnt($app_id = 0) {
        if (!empty($app_id)) {
            $_map['app_id'] = $app_id;
        }
        $_map['is_delete'] = 2;
        $_map['remain'] = ['GT', 0];
        $_map['end_time'] = ['GT', time()];
        $_cnt = Db::name('gift')->where($_map)->count();
        if (empty($_cnt)) {
            $_cnt = 0;
        }

        return $_cnt;
    }

    private function getListfield() {
        $_field = [
            'gf.id'         => 'giftid',
            "gf.app_id"     => 'gameid',
            'gf.title'      => 'giftname',
            'gf.total'      => 'total',
            'gf.remain'     => 'remain',
            "gf.content"    => 'content',
            'gf.start_time' => 'starttime',
            'gf.end_time'   => 'enttime',
            "gf.scope"      => 'scope',
            "gf.func"       => 'func',
        ];
        $_icon_field = [
            "IF(ISNULL(g.mobile_icon),'',CONCAT('".Config::get('domain.STATICSITE')."',g.mobile_icon))" => 'icon'
        ];

        return array_merge($_field, $_icon_field);
    }

    private function getJoin() {
        $_join = [
            [
                Config::get('database.prefix').'game g',
                'g.game_id = gf.app_id AND g.is_app = 2',
                'LEFT'
            ]
        ];

        return $_join;
    }

    public function getDetail($gift_id) {
        if (empty($gift_id)) {
            return null;
        }
        $_field = $this->getListfield();
        $_join = $this->getJoin();
        $_map['gf.id'] = $gift_id;
        $_rdata = Db::name('gift')
                    ->alias('gf')
                    ->field($_field)
                    ->join($_join)
                    ->where($_map)
                    ->find();
        if (empty($_rdata)) {
            return null;
        } else {
            $_mem_id = Session::get('id', 'user');
            if (!empty($_mem_id)) {
                $_gl_map['mem_id'] = $_mem_id;
                $_gl_map['gf_id'] = $gift_id;
                $_rdata['giftcode'] = Db::name('gift_log')
                                        ->alias('gl')
                                        ->where($_gl_map)
                                        ->value('code');
            } else {
                $_rdata['giftcode'] = null;
            }
        }

        return $_rdata;
    }

    /**
     * @param array $map    输入参数
     * @param int   $page   页码
     * @param int   $offset 每页展示数量
     * @param int   $is_get 展示内容 0 表示全部展示 1 表示只展示未领取的礼包  2 表示已领取的礼包
     *
     * @return array 如果为空返回null,如果不为空返回count,list
     */
    public function getList(array $map, $page = 1, $offset = 10, $is_get = 0) {
        if (!empty($map['is_hot'])) {
            $_map['gf.is_hot'] = $map['is_hot'];
        }
        if (!empty($map['is_rmd'])) {
            $_map['gf.is_rmd'] = $map['is_rmd'];
        }
        if (!empty($map['is_luxury'])) {
            $_map['gf.is_luxury'] = $map['is_luxury'];
        }
        if (!empty($map['app_id'])) {
            $_map['gf.app_id'] = $map['app_id'];
        }
        if (!empty($map['isnew'])) {
            $_order = " gf.id DESC";
        } else {
            $_order = " gf.end_time ASC";
        }
        $_map['g.is_delete'] = 2; /* 未被删除游戏 */
        $_map['gf.is_delete'] = 2; /* 未被删除的礼包 */
        $_map['gf.end_time'] = ['gt', time()]; /* 选取不过期的游戏礼包 */
        $_map['gf.remain'] = ['gt', 0]; /* 选取不过期的游戏礼包 */
        $_join = $this->getJoin();
        /* 判断玩家是否登录 */
        $_mem_id = Session::get('id', 'user');
        $_field = $this->getListfield();
        if (!empty($_mem_id)) {
            /* 只显示已领取的礼包 */
            if (2 == $is_get) {
                $_map['gl.mem_id'] = $_mem_id;
            }
            /* 只显示未领取的礼包 */
            if (1 == $is_get) {
                $_map['gl.mem_id'] = 'null';
            }
            $_own_field = [
                "gl.code" => 'giftcode',
            ];
            $_field = array_merge($_field, $_own_field);
            $_own_join = [
                Config::get('database.prefix').'gift_log gl',
                'gl.gf_id = gf.id AND gl.mem_id='.$_mem_id,
                'LEFT'
            ];
            array_push($_join, $_own_join);
        }
        $_rdata['count'] = Db::name('gift')
                             ->alias('gf')
                             ->join($_join)
                             ->where($_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_page = $page." , ".$offset;
            $_list = Db::name('gift')
                       ->alias('gf')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                /* 判断礼包是否已被领取 */
                if (empty($_mem_id)) {
                    foreach ($_list as $_k => $_v) {
                        $_list[$_k]['giftcode'] = null;
                    }
                }
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    /**
     * 领取礼包
     *
     * @param int $gift_id 礼包ID
     *
     * @return array|false|null|\PDOStatement|string|\think\Model
     */
    public function setGift($gift_id) {
        $_mem_id = Session::get('id', 'user');
        if (empty($gift_id) || empty($_mem_id)) {
            return null;
        }
        $_gift_data = $this->getDetail($gift_id);
        if (empty($_gift_data)) {
            return null;
        }
        if (!empty($_gift_data['giftcode']) || 0 == $_gift_data['remain']) {
            return $_gift_data;
        }
        if ($_gift_data['remain'] > 0) {
            /* 礼包码数量-1 */
            $_gf_data['id'] = $gift_id;
            $_gf_data['remain'] = $_gift_data['remain'] - 1;
            Db::name('gift')->update($_gf_data);
            $_gfc_model = Db::name('gift_code');
            // 查找未被领取的礼包
            $_gfc_map = [
                'gf_id'  => $gift_id,
                'mem_id' => 0
            ];
            $_user_gift = $_gfc_model->where($_gfc_map)->find();
            $_user_gift['mem_id'] = $_mem_id;
            $_user_gift['update_time'] = time();
            $_rs = $_gfc_model->update($_user_gift);
            if (false !== $_rs) {
                $_gf_log['mem_id'] = $_mem_id;
                $_gf_log['gf_id'] = $gift_id;
                $_gf_log['code'] = $_user_gift['code'];
                $_gf_log['create_time'] = $_user_gift['update_time'];
                Db::name('gift_log')->insert($_gf_log);
                $_gift_data['giftcode'] = $_user_gift['code'];
                $_gift_data['remain'] = $_gift_data['remain'] - 1;

                return $_gift_data;
            }
        }

        return null;
    }

    /**
     * @param int $mem_id  玩家ID
     * @param int $gift_id 礼包ID
     *
     * @return int
     */
    public function getMemgiftcnt($mem_id = 0, $gift_id = 0) {
        if (empty($mem_id)) {
            return 0;
        }
        $_map['mem_id'] = $mem_id;
        if (!empty($gift_id)) {
            $_map['gf_id'] = $gift_id;
        }
        $_count = Db::name('gift_log')->where($_map)->count();
        if (empty($_count)) {
            return 0;
        }

        return ceil($_count);
    }
}