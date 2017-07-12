<?php
/**
 * Memlog.php UTF-8
 * 玩家记录
 *
 * @date    : 2016年11月11日下午4:26:51
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年11月11日下午4:26:51
 */
namespace huosdk\log;

use think\Db;

class Memlog extends Huolog {
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
     *
     * @return bool
     */
    public function login(array $data) {
        $_data['mem_id'] = get_val($data, 'mem_id', 0);
        $_data['app_id'] = get_val($data, 'app_id', 0);
        $_data['agentgame'] = get_val($data, 'agentgame', '');
        $_data['imei'] = get_val($data, 'imei', '');
        $_data['deviceinfo'] = get_val($data, 'deviceinfo', '');
        $_data['userua'] = get_val($data, 'userua', '');
        $_data['from'] = get_val($data, 'from', 0);
        $_data['flag'] = get_val($data, 'flag', 0);
        $_data['reg_time'] = get_val($data, 'reg_time', '');
        $_data['login_time'] = get_val($data, 'login_time', time());
        $_data['agent_id'] = get_val($data, 'agent_id', 0);
        $_data['login_ip'] = get_val($data, 'login_ip', '');
        $_data['ipaddrid'] = get_val($data, 'ipaddrid', '');
        $_data['open_cnt'] = get_val($data, 'open_cnt', 0);
        /* 更新mem_game */
        $_rs = $this->setMemgame($_data);
        if (1 == $_rs) {
            $_data['flag'] = 1;
        }
        $_rs = parent::insert($_data);
        if (!$_rs) {
            return false;
        }
        //插入记录后的逻辑
        $this->setMemext($_data);

        /* 1 插入游戏登陆表 mem_game */

        return true;
    }

    /**
     * 玩家游戏更新
     *
     * @param $login_data array 游戏登陆数据
     *
     * @return int
     */
    public function setMemgame($login_data) {
        if (empty($login_data)) {
            return 0;
        }
        $_map['mem_id'] = $login_data['mem_id'];
        $_map['app_id'] = $login_data['app_id'];
        $_mg_info = Db::name('mem_game')->where($_map)->find();
        if (empty($_mg_info)) {
            $_mg_info['mem_id'] = $login_data['mem_id'];
            $_mg_info['app_id'] = $login_data['app_id'];
            $_mg_info['create_time'] = $login_data['login_time'];
            $_mg_info['update_time'] = $login_data['login_time'];
            Db::name('mem_game')->insert($_mg_info);

            return 0;
        } else {
            $_mg_info['update_time'] = $login_data['login_time'];
            Db::name('mem_game')->update($_mg_info);

            return 1;
        }

        return 0;
    }

    /**
     * 玩家最后登录时间
     *
     * @param $login_data array 游戏登陆数据
     *
     * @return bool
     */
    public function setMemext($login_data) {
        if (empty($login_data)) {
            return false;
        }
        $_map['mem_id'] = $login_data['mem_id'];
        $_mext_info = Db::name('mem_ext')->where($_map)->find();
        if (empty($_mext_info)) {
            $_mext_info['mem_id'] = $login_data['mem_id'];
            $_mext_info['last_login_time'] = $login_data['login_time'];
            $_mext_info['game_cnt'] = 1;
            $_mext_info['login_cnt'] = 0;
            $_mext_info['last_login_ip'] = $login_data['login_ip'];
            Db::name('mem_ext')->insert($_mext_info);
        } else {
            $_mext_info['last_login_time'] = $login_data['login_time'];
            if (1 == $login_data['flag']) {
                $_mext_info['game_cnt'] += 1;
            }
            $_mext_info['login_cnt'] += 1;
            $_mext_info['last_login_ip'] = $login_data['login_ip'];
            Db::name('mem_ext')->update($_mext_info);
        }

        return true;
    }

    /**
     * 登陆记录
     *
     * @param        $data array 需要写入的数据
     * @param string $table_name
     */
    public function logout(array $data, $table_name = '') {
//        if (!empty($table_name)) {
//            $this->table_name = $table_name;
//        }
    }
}