<?php
/**
 * Gamelog.php UTF-8
 * 玩家游戏记录
 *
 * @date    : 2016年11月11日下午4:05:22
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午4:05:22
 */
namespace huosdk\log;

use think\Log;
use think\Db;

class Gamelog extends Huolog {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'log\Game Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $table_name string 数据库表名
     */
    public function __construct($table_name) {
        parent::__construct($table_name);
    }

    /**
     * 插入游戏记录
     *
     * @param $data array 需要写入的数据
     */
    public function insert(array $data) {
        $_rs = parent::insert($data);
        if (!$_rs) {
            return false;
        }
        //插入记录后的逻辑
        return true;
    }
}