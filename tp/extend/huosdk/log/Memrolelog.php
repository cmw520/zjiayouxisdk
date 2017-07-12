<?php
/**
 * Memrolelog.php UTF-8
 * 玩家角色记录表
 *
 * @date    : 2016年11月15日下午2:27:50
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月15日下午2:27:50
 */
namespace huosdk\log;

use think\Log;
use think\Db;
use think\Session;

class Memrolelog extends Huolog {
    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'log\Memrolelog Error:'.$msg;
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
     * 插入游戏角色数据
     *
     * @param $data [type] int 插入类型
     * @param $data [money] double 此次充值金额
     */
    public function insert(array $data) {
        $_data['mem_id'] = Session::get('id', 'user');
        $_data['app_id'] = Session::get('app_id', 'app');
//         $_data['experience'] = Session::get('experience', 'app');
        $_data['attach'] = '';
        $_data['type'] = $data['type'];
        $_data['server_id'] = Session::get('server_id', 'role');
        $_data['server_name'] = Session::get('server_name', 'role');
        $_data['role_id'] = Session::get('role_id', 'role');
        $_data['role_name'] = Session::get('role_name', 'role');
        $_data['role_level'] = Session::get('role_level', 'role');
        $_data['role_vip'] = Session::get('role_vip', 'role');
        $_data['party_name'] = Session::get('party_name', 'role');
        $_data['role_balence'] = Session::get('role_balence', 'role');
        $_data['money'] = $data['money'];
        $_data['rolelevel_ctime'] = Session::get('rolelevel_ctime', 'role');
        $_data['rolelevel_mtime'] = Session::get('rolelevel_mtime', 'role');
        $_data['create_time'] = time();
        $_rs = parent::insert($_data);
        if (!$_rs) {
            return false;
        }
        //插入记录后的逻辑
        /* 1 更新角色表 */
        return true;
    }

    public function insertbyData(array $data) {
        $_rs = parent::insert($data);
        if (!$_rs) {
            return false;
        }
        //插入记录后的逻辑
        /* 1 更新角色表 */
        return true;
    }
}