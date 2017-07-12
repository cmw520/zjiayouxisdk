<?php
/**
 * Slide.php UTF-8
 * 闪屏图或轮播图信息
 * 广告信息
 *
 * @date    : 2016年12月3日下午1:55:01
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月3日下午1:55:01
 */
namespace huosdk\slide;

use think\Config;
use think\Db;
use think\Log;

class Slide {
    private $slide_type;

    /**
     * @param        $msg
     * @param string $level
     */
    private function _error($msg, $level = 'error') {
        $_info = 'slide\Slide Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * Slide constructor.
     *
     * @param string $type 类别
     */
    public function __construct($type = '') {
        if (!empty($type)) {
            $this->slide_type = $type;
        }
    }

    /**
     * @param string $type
     * @param int    $limit
     * @param string $order
     *
     * @return false|null|\PDOStatement|string|\think\Collection
     */
    public function getSlide($type = '', $limit = 4, $order = "listorder ASC") {
        $_type = $type;
        if (empty($_type)) {
            $_type = $this->slide_type;
        }
        if (empty($_type)) {
            return null;
        }
        $_sc_model = DB::name("SlideCat");
        $_join = [
            [
                Config::get('database.prefix').'slide s',
                's.slide_cid=sc.cid',
                'LEFT'
            ]
        ];
        $_field = [
            'slide_name'                                               => 'name',
            'app_id'                                                   => 'gameid',
            "CONCAT('".Config::get('domain.STATICSITE')."',slide_pic)" => 'image',
            'slide_url'                                                => 'url',
            'slide_des'                                                => 'disc',
            'slide_content'                                            => 'content'
        ];
        $_map['cat_idname'] = $_type;
        $_map['slide_status'] = 2;
        $_rdata = $_sc_model
            ->alias('sc')
            ->field($_field)
            ->join($_join)
            ->where($_map)
            ->order($order)
            ->limit('0,'.$limit)
            ->select();
        if (empty($_rdata)) {
            return null;
        }

        return $_rdata;
    }

    /**
     * 获取闪屏图
     *
     * @param $type STRING 闪屏图类别
     *
     * @return array(img,gameid,url) OR NULL
     */
    public function getSplash($type = '') {
        $_type = $type;
        if (empty($_type)) {
            $_type = $this->slide_type;
        }
        if (empty($_type)) {
            return null;
        }
        $_sc_model = DB::name("SlideCat");
        $_join = [
            [
                Config::get('database.prefix').'slide s',
                's.slide_cid=sc.cid',
                'LEFT'
            ]
        ];
        $_field = [
            "CONCAT('".Config::get('domain.STATICSITE')."',slide_pic)" => 'img',
            'app_id'                                                   => 'gameid',
            'slide_url'                                                => 'url',
        ];
        $_map['cat_idname'] = $_type;
        $_map['slide_status'] = 2;
        $_rdata = $_sc_model->alias('sc')->field($_field)->join($_join)->where($_map)->find();
        if (empty($_rdata)) {
            return null;
        }

        return $_rdata;
    }

    /**
     * 获取广告或者轮播图位置
     *
     * @param $cid 广告位置ID
     *
     * @return null  返回列表
     */
    public function getDetail($cid) {
        if (empty($cid)) {
            return null;
        }
        $_slide_model = Db::name('slide');
        $_map['slide_cid'] = $cid;
        $_rdata['count'] = $_slide_model->where($_map)->count();
        if ($_rdata['count'] > 0) {
            $_field = [
                'slide_name'                                               => 'name',
                'type_id'                                                  => 'type',
                'target_id'                                                => 'target',
                "slide_url"                                                => 'url',
                "CONCAT('".Config::get('domain.STATICSITE')."',slide_pic)" => 'image',
                'slide_content'                                            => 'content',
                'slide_des'                                                => 'desc',
            ];
            $_rdata['list'] = $_slide_model->field($_field)->where($_map)->select();
        } else {
            return null;
        }

        return $_rdata;
    }

    public function getNews() {
        $_type = $this->slide_type;
        if (empty($_type)) {
            return null;
        }
        $_map['slide_cid'] = Db::name('slide_cat')->where('cat_idname', $_type)->value('cid');
        if (empty($_map['slide_cid'])) {
            return null;
        }
        $_slide_model = Db::name('slide');
        $_field = [
            'slide_name'    => 'title',
            'slide_content' => 'content'
        ];
        $_rdata = $_slide_model->field($_field)->where($_map)->find();

        return $_rdata;
    }

    /**
     * 获取广告列表
     *
     * @param string $type
     *
     * @return array|null 返回数据
     */
    public function getList($type = '') {
        $_slide_type = $type;
        if (empty($type)) {
            $_slide_type = $this->slide_type;
        }
        if (empty($_slide_type)) {
            return null;
        }
        $_map['cat_idname'] = ['like', $_slide_type.'%'];
        $_sc_data = Db::name('slide_cat')->where($_map)->select();
        if (empty($_sc_data)) {
            return null;
        }
        $_rdata = array();
        foreach ($_sc_data as $_k => $_v) {
            $_rdata[$_v['cat_idname']] = $this->getDetail($_v['cid']);
        }

        return $_rdata;
    }
}