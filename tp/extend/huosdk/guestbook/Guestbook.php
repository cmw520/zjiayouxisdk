<?php
/**
 * Guestbook.php UTF-8
 * 反馈信息处理
 *
 * @date    : 2017/2/6 14:59
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\guestbook;

use think\Db;
use think\Session;

class Guestbook {
    public function save($data) {
        $_mem_id = Session::get('id', 'user');
        if (empty($_mem_id)) {
            $_data['mem_id'] = 0;
        } else {
            $_data['mem_id'] = $_mem_id;
        }
        $_data['app_id'] = get_val($data, 'gameid', 0);
        $_data['msg'] = get_val($data, 'content', '');
        $_data['full_name'] = get_val($data, 'full_name', '');
        $_data['tel'] = get_val($data, 'linkman', '');
        $_data['title'] = get_val($data, 'gamecontent', '');
        $_data['create_time'] = time();
        $_rs = Db::name('guest_book')->insert($_data);
        if (false === $_rs) {
            return 400;
        }

        return 200;
    }
}