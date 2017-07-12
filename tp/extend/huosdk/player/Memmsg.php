<?php
/**
 * Memmsg.php UTF-8
 * 玩家消息
 *
 * @date    : 2017/2/8 10:52
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\player;

use think\Config;
use think\Db;

class Memmsg extends Member {
    /**
     * Memmsg constructor.
     *
     * @param int $mem_id
     */
    public function __construct($mem_id) {
        parent::__construct($mem_id);
    }

    /**
     * 消息删除
     *
     * @param $msg_id 删除的消息ID
     *
     * @return bool true 伪删除成功 false 伪删除失败
     */
    public function delete($msg_id) {
        $_mm_map['mem_id'] = $this->getMemid();
        if (empty($msg_id) || empty($_mm_map['mem_id'])) {
            return false;
        }
        $_mm_map['message_id'] = $msg_id;
        $_isreaded = Db::name('mem_message')->where($_mm_map)->count();
        if ($_isreaded <= 0) {
            $_mdata = Db::name('message')
                        ->alias('msg')
                        ->where('id', $msg_id)
                        ->find();
            $_mm_info['message_id'] = $msg_id;
            $_mm_info['mem_id'] = $_mm_map['mem_id'];
            $_mm_info['type'] = $_mdata['type'];
            $_mm_info['status'] = 1;
            $_mm_info['is_delete'] = 1;
            $_mm_info['create_time'] = time();
            $_rs = Db::name('mem_message')->insert($_mm_info);
        } else {
            $_rs = Db::name('mem_message')->where($_mm_map)->setField('is_delete', 1);
        }
        if (false === $_rs) {
            return false;
        }

        return true;
    }

    public function read($msg_id) {
        $_mm_map['mem_id'] = $this->getMemid();
        if (empty($msg_id) || empty($_mm_map['mem_id'])) {
            return null;
        }
        $_field = $this->getListfield();
        $_field['msg.message'] = 'content';
        $_map['id'] = $msg_id;
        $_rdata = Db::name('message')
                    ->alias('msg')
                    ->field($_field)
                    ->where($_map)
                    ->find();
        if (empty($_rdata)) {
            return null;
        }
        //判断消息是否读取
        $_mm_map['message_id'] = $msg_id;
        $_mm_info = Db::name('mem_message')->where($_mm_map)->find();
        if (empty($_mm_info['status'])) {
            $_mm_info['message_id'] = $_rdata['msgid'];
            $_mm_info['mem_id'] = $_mm_map['mem_id'];
            $_mm_info['type'] = $_rdata['type'];
            $_mm_info['status'] = 2;
            $_mm_info['create_time'] = time();
            Db::name('mem_message')->insert($_mm_info);
        } elseif (1 == $_mm_info['status']) {
            $_mm_info['status'] = 2;
            $_mm_info['create_time'] = time();
            Db::name('mem_message')->update($_mm_info);
        }

        return $_rdata;
    }

    private function getListfield() {
        $_field = [
            'msg.id'        => 'msgid',
            'msg.title'     => 'title',
            'msg.app_id'    => 'gameid',
            'msg.type'      => 'type',
            'msg.send_time' => 'createtime',
        ];

        return $_field;
    }

    /**
     * @param $page
     * @param $offset
     *
     * @return null
     */
    public function getList($page, $offset) {
        $_mem_id = $this->getMemid();
        $_join = [
            [
                Config::get('database.prefix').'mem_message mm',
                'mm.message_id=msg.id AND mm.mem_id='.$_mem_id,
                'LEFT'
            ]
        ];
        $_map['msg.mem_id'] = ['in', '0,'.$_mem_id];
        $_extra_map = " mm.is_delete =2  OR mm.is_delete is null";
        $_rdata['count'] = Db::name('message')
                             ->alias('msg')
                             ->join($_join)
                             ->where($_map)
                             ->where($_extra_map)
                             ->count();
        if ($_rdata['count'] > 0) {
            $_field = $this->getListfield();
            $_field['mm.status'] = 'readed';
            $_page = $page." , ".$offset;
            $_order = "msg.id DESC ";
            $_list = Db::name('message')
                       ->alias('msg')
                       ->field($_field)
                       ->join($_join)
                       ->where($_map)
                       ->where($_extra_map)
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

    private function setAssigninfo(array &$list) {
        if (empty($list)) {
            return false;
        }
        foreach ($list as $_k => $_v) {
            if (empty($_v['readed']) || 2 != $_v['readed']) {
                $list[$_k]['readed'] = 1;
            }
        }

        return true;
    }

    /**
     * 判断是否有未读消息
     *
     * @return int true 有  false  无
     */
    public function hasNew() {
        $_mem_id = $this->getMemid();
        $_join = [
            [
                Config::get('database.prefix').'mem_message mm', 'mm.message_id=msg.id', 'LEFT'
            ]
        ];
        $_map['msg.mem_id'] = ['in', '0,'.$_mem_id];
        $_map['mm.status'] = ['<>', '2'];
        $_cnt = Db::name('message')
                  ->alias('msg')
                  ->join($_join)
                  ->where($_map)
                  ->count();
        if ($_cnt > 0) {
            return true;
        } else {
            return false;
        }
    }
}