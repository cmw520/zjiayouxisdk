<?php
/**
 * Goods.php UTF-8
 * 商品处理类
 *
 * @date    : 2017/1/22 14:50
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\ goods;

use huosdk\common\Commonfunc;
use huosdk\integral\Memitg;
use think\Config;
use think\Db;
use think\Session;

class Goods {
    private $goods_id;
    private $app_id;

    /**
     * Gift constructor.
     *
     * @param int $app_id   游戏ID
     * @param int $goods_id 礼包ID
     */
    public function __construct($app_id = 0, $goods_id = 0) {
        if (!empty($app_id)) {
            $this->app_id = $app_id;
        }
        if (!empty($goods_id)) {
            $this->goods_id = $goods_id;
        }
    }

    private function getListfield() {
        $_field = [
            'g.goods_id'                                                    => 'goodsid',
            "g.goods_name"                                                  => 'goodsname',
            'g.is_real'                                                     => 'is_real',
            'g.store_count'                                                 => 'total',
            "g.remain_count"                                                => 'remain',
            'g.market_price'                                                => 'market_price',
            'g.goods_intro'                                                 => 'goods_intro',
            "g.goods_content"                                               => 'goods_content',
            "CONCAT('".Config::get('domain.STATICSITE')."',g.original_img)" => 'original_img',
            "ceil(g.integral)"                                              => 'integral'
        ];

        return $_field;
    }

    public function getDetail($goods_id) {
        if (empty($goods_id)) {
            return null;
        }
        $_field = $this->getListfield();
        $_map['g.goods_id'] = $goods_id;
        $_rdata = Db::name('goods')
                    ->alias('g')
                    ->field($_field)
                    ->where($_map)
                    ->find();
        if (empty($_rdata)) {
            return null;
        }

        return $_rdata;
    }

    /**
     * @param array $map    输入参数
     * @param int   $page   页码
     * @param int   $offset 每页展示数量
     *
     * @return array 如果为空返回null,如果不为空返回count,list
     */
    public function getList(array $map, $page = 1, $offset = 10) {
        if (!empty($map['is_real'])) {
            $_map['is_real'] = $map['is_real'];
        }
        $_map['g.on_time'] = ['<', time()];
        $_map['g.is_on_sale'] = 2;
//        $_map['c.total_num'] = ['gt', 0]; /* 剩余数量大于0 */
        $_order = "goods_id DESC";
        $_rdata['count'] = Db::name('goods')
                             ->alias('g')
                             ->where($_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_field = $this->getListfield();
            $_page = $page." , ".$offset;
            $_list = Db::name('goods')
                       ->alias('g')
                       ->field($_field)
                       ->where($_map)
                       ->order($_order)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            }
            $_rdata['list'] = $_list;
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    /**
     * @param int $mem_id 玩家ID
     * @param int $page   第几页
     * @param int $offset 每页显示数量
     *
     * @param int $_flag
     *
     * @return null
     * @internal param int $is_real
     *
     */
    public function getMemlist($mem_id, $page = 1, $offset = 10, $_flag = 3) {
        if (empty($mem_id)) {
            return null;
        }
        $_map['io.mem_id'] = $mem_id;
        if (3 == $_flag) {
            $_map['io.flag'] = 3;
        } else {
            $_map['io.flag'] = 4;
        }
        $_field = $this->getListfield();
        $_own_field = [
            'io.shipping_status' => 'shippingstatus',
            'io.shipping_name'   => 'shippingname',
            'io.invoice_no'      => 'invoice_no',
            'io.consignee'       => 'consignee',
            'io.admin_note'      => 'note'
        ];
        $_field = array_merge($_field, $_own_field);
        $_join = [
            [
                Config::get('database.prefix').'goods g',
                'g.goods_id =io.goods_id AND io.mem_id='.$mem_id,
                'LEFT'
            ]
        ];
        $_rdata['count'] = Db::name('integral_order')
                             ->alias('io')
                             ->join($_join)
                             ->where($_map)
                             ->count();

        if ($_rdata['count'] > 0) {
            $_page = $page." , ".$offset;
            $_list = Db::name('integral_order')
                       ->alias('io')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->page($_page)
                       ->select();
            if (empty($_list)) {
                $_rdata['list'] = null;
            } else {
                $_rdata['list'] = $_list;
            }
        } else {
            $_rdata = null;
        }

        return $_rdata;
    }

    /**
     * 积分兑换商品
     *
     * @param int $goods_id 商品ID
     *
     * @return array|false|null|\PDOStatement|string|\think\Model
     */
    public function memGetgoods($goods_id) {
        $_mem_id = Session::get('id', 'user');
        if (empty($goods_id) || empty($_mem_id)) {
            return null;
        }
        $_g_data = $this->getDetail($goods_id);
        if (empty($_g_data)) {
            return null;
        }
        $_goods_remain = $this->getRemain($goods_id);
        if (empty($_goods_remain)) {
            return null;
        }
        /* 先扣取玩家积分 */
        $_itg_class = new  Memitg($_mem_id);
        $_rs = $_itg_class->decrease($_mem_id, $_g_data['integral']);
        if (false == $_rs) {
            /* 扣除玩家积分失败 */
            return null;
        }
        $this->doAfterget($_mem_id, $goods_id, $_g_data['integral']);
        $_g_data['remain'] -= 1;
        $_g_data['myintegral'] = $_itg_class->get();

        return $_g_data;
    }

    /**
     *
     * 商品剩余数量
     *
     * @param int $goods_id 商品ID
     * @param int $app_id   游戏ID  0表示不限制游戏
     *
     * @return int 返回商品剩余数量
     */
    public function getRemain($goods_id, $app_id = 0) {
        if (empty($goods_id)) {
            return 0;
        }
        $_map['g.on_time'] = ['<', time()];
        $_map['g.is_on_sale'] = 2;
        $_map['g.goods_id'] = $goods_id;
        $_remain = Db::name('goods')->alias('g')->where($_map)->value('remain_count');
        if (empty($_remain)) {
            return 0;
        }

        return $_remain;
    }

    public function decGoodscnt($goodsid) {
        Db::name('goods')->where('goods_id', $goodsid)->setDec('remain_count');
    }

    public function doAfterget($mem_id, $goodsid, $money = 0) {
        /* 商品已领取数量增加 */
        $this->decGoodscnt($goodsid);
        $_g_map['goods_id'] = $goodsid;
        $_g_info = Db::name("goods")->where($_g_map)->find();
        if (empty($_g_info)) {
            return false;
        }
        $_io_data['flag'] = 4;
        if (1 == $_g_info['is_real']) {
            $_io_data['flag'] = 3;
        }
        $_io_data['mem_id'] = $mem_id;
        $_io_data['goods_id'] = $goodsid;
        $_io_data['order_id'] = Commonfunc::setOrderid($mem_id);
        $_io_data['integral'] = $_g_info['integral'];
        $_io_data['status'] = 2;
        $_io_data['shipping_status'] = 1;
        $_io_data['create_time'] = time();
        $_ma_map['mem_id'] = $mem_id;
        $_ma_map['is_default'] = 2;
        $_ma_map['is_delete'] = 2;
        $_mem_address = Db::name('mem_address')->where($_ma_map)->find();
        if (!empty($_mem_address)) {
            $_io_data['consignee'] = $_mem_address['consignee'];
            $_io_data['country'] = $_mem_address['country'];
            $_io_data['province'] = $_mem_address['province'];
            $_io_data['city'] = $_mem_address['city'];
            $_io_data['district'] = $_mem_address['district'];
            $_io_data['town'] = $_mem_address['town'];
            $_io_data['address'] = $_mem_address['address'];
            $_io_data['zipcode'] = $_mem_address['zipcode'];
            $_io_data['mobile'] = $_mem_address['mobile'];
        }
        $_rs = Db::name('integral_order')->insert($_io_data);
        if (false === $_rs) {
            return false;
        }

        return true;
    }
}