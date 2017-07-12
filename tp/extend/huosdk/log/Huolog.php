<?php
/**
 * Huolog.php UTF-8
 * 玩家游戏记录
 *
 * @date    : 2016年11月11日下午4:05:22
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午4:05:22
 */
namespace huosdk\log;

use think\Db;
use think\Log;

class Huolog {
    private $table_name;

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
    public function __construct($table_name = '') {
        if (!empty($table_name)) {
            $this->table_name = $table_name;
        }
    }

    /**
     * 插入游戏记录
     *
     * @param $data array 需要写入的数据
     *
     * @return bool
     */
    public function insert(array $data) {
        try {
            $_rs = Db::name($this->table_name)->insert($data);
            if (!$_rs) {
                return false;
            }
        } catch (Exception $e) {
            $this->_error($this->table_name.'写入数据出错'.json_encode($data).':'.$e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param array $data 需要写入的数据
     *
     * @return bool|int|string 插入记录 并获取id
     */
    public function insertGetId(array $data) {
        try {
            $_id = Db::name($this->table_name)->insertGetId($data);
            if (!$_id) {
                return false;
            }
        } catch (Exception $e) {
            $this->_error($this->table_name.'写入数据出错'.json_encode($data).':'.$e->getMessage());

            return false;
        }

        return $_id;
    }
}