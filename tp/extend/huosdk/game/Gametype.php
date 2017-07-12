<?php
/**
 * Gametype.php UTF-8
 * 游戏类别说明
 *
 * @date    : 2017/1/16 18:19
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\game;

use think\Log;
use think\Db;
use think\Config;

class Gametype {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'game\Gametype Error:'.$msg;
        Log::record($_info, 'error');
    }

    public function getTypebyId($typeids) {
        if (empty($typeids)) {
            return '';
        }
        $_type_arr = explode(',', $typeids);
        /*
         * 获取游戏类型
         */
        $_map['status'] = 2;
        $_gt_arr = Db::name('game_type')->where($_map)->cache(86400)->column('name', 'id');
        $_rdata = array();
        foreach ($_type_arr as $_val) {
            if (!empty($_gt_arr[$_val])) {
                array_push($_rdata, $_gt_arr[$_val]);
            }
        }
        return implode(',', $_rdata);
    }

    public function getTypelist(){
        /* 查询第一级类别 */
        $_map['status'] = 2;
        $_map['parentid'] = 0;
        $_field = [
            'id'         => 'typeid',
            'name'   => 'typename',
            "CONCAT('".Config::get('domain.STATICSITE')."',image)" => 'icon'

        ];
        $_first_types  = Db::name('game_type')->field($_field)->where($_map)->select();
        if (empty($_first_types)){
            return null;
        }
        $_rdata['count'] = count($_first_types);
        $_rdata['list'] = $_first_types;

        foreach ($_first_types as $_key => $_val){
            $_map['parentid'] = $_val['typeid'];
            $_second_types  = Db::name('game_type')->field($_field)->where($_map)->select();
            if (empty($_second_types)){
                $_rdata['list'][$_key]['subcount'] = 0;
                $_rdata['list'][$_key]['sublist'] = null;
            }else{
                $_rdata['list'][$_key]['subcount'] = count($_second_types);
                $_rdata['list'][$_key]['sublist'] = $_second_types;
            }
        }
        return $_rdata;
    }
}