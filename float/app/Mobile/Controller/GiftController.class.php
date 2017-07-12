<?php
/**
 * GiftController.class.php UTF-8
 * 礼包管理类
 *
 * @date    : 2016年9月7日下午3:06:32
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class GiftController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
        $this->assign('lbactive', 'active');
        $this->assign('title', '礼包');
    }

    public function index() {
        $app_id = $_SESSION['app']['app_id'];
        $show = I('get.show');
        if (empty($show)) {
            $show = 'giftlist';
        }
        $page = I('get.page', 1);
        $offset = I('get.offset', 5);
        $mem_id = sp_get_current_userid();
        if ($show == "mygift") {
            $gfl_map['gf.app_id'] = $app_id;
            $gfl_map['gfl.mem_id'] = $mem_id;
            $field = "gf.id giftid, gf.app_id gameid,gf.title giftname,gf.total total,gfl.code code";
            $field .= ",gf.remain remain,gf.content content, CONCAT('".$this->staticurl."',g.mobile_icon) icon";
            $field .= ",FROM_UNIXTIME(gf.start_time, '%Y-%m-%d %H:%i:%S') starttime,FROM_UNIXTIME(gf.end_time, '%Y-%m-%d %H:%i:%S') enttime,gf.scope scope";
            $joingift = "LEFT JOIN ".C('DB_PREFIX')."gift gf ON gf.id =gfl.gf_id";
            $joingame = "LEFT JOIN ".C('DB_PREFIX')."game g ON gf.app_id =g.id";
            $gfl_model = M('gift_log');
            $count = $gfl_model->alias('gfl')->join($joingift)->where($gfl_map)->count();
            $limit = ($page - 1) * $offset.','.$offset;
            $giftdata = $gfl_model->alias('gfl')->field($field)->join($joingift)->join($joingame)->where($gfl_map)
                                  ->order(
                                      'gfl.id desc'
                                  )->limit($limit)->select();
            $this->assign('gifts', $giftdata);
            $this->display('mygift');
            exit();
        } else {
            $time = time();
            $field = "gf.id giftid, gf.app_id gameid,gf.title giftname,gf.total total";
            $field .= ",gf.remain remain,gf.content content, CONCAT('".$this->staticurl."',g.mobile_icon) icon";
            $field .= ",FROM_UNIXTIME(gf.start_time, '%Y-%m-%d %H:%i:%S') starttime,FROM_UNIXTIME(gf.end_time, '%Y-%m-%d %H:%i:%S') enttime,gf.scope scope";
            $where['gf.app_id'] = $app_id;
            $where['gf.end_time'] = array(
                'GT',
                $time
            );
            $where['gf.remain'] = array(
                'GT',
                0
            );
            $where['gf.is_delete'] = 2;
            $join = "LEFT JOIN ".C('DB_PREFIX')."game g ON g.id = gf.app_id";
            $giftdata = M('gift')->alias('gf')->join($join)->field($field)->where($where)->order("gf.start_time ASC")
                                 ->select();
            foreach ($giftdata as $k => $v) {
                $gflog_info = $this->getUserGift($v['giftid'], $mem_id);
                if (!empty($gflog_info['code'])) {
                    // 不显示已领取的礼包
                    unset($giftdata[$k]);
                }
            }
            $this->assign('gifts', $giftdata);
            $this->display();
        }
    }

    private function getUserGift($gf_id, $mem_id) {
        if (empty($gf_id) || empty($mem_id)) {
            return array();
        }
        $giftmap['mem_id'] = $mem_id;
        $giftmap['gf_id'] = $gf_id;
        $gflog_info = M('gift_log')->where($giftmap)->find();
        return $gflog_info;
    }

    public function giftAjax() {
        $gfc_model = M('gift_code');
        $gf_model = M('gift');
        $data['b'] = 0;
        $data['a'] = 7;
        $gf_id = I('post.giftid/d');
        if (!empty($gf_id)) {
            $time = time();
            $mem_id = sp_get_current_userid();
            $cnt = M("gift_code")->where(
                array(
                    'gf_id'  => $gf_id,
                    'mem_id' => $mem_id
                )
            )->count();
            // 未领取过礼包才能领取
            if (0 == $cnt) {
                $app_id = $_SESSION['app']['app_id'];
                $rs = M("gift")->where(
                    array(
                        'id'     => $gf_id,
                        'app_id' => $app_id
                    )
                )->setDec('remain');
                if ($rs > 0) {
                    $field = "code, id";
                    $giftdata = $gfc_model->field($field)->where(
                        array(
                            'gf_id'  => $gf_id,
                            'mem_id' => 0
                        )
                    )->find();
                    $rs = $gfc_model->where(
                        array(
                            'id' => $giftdata['id']
                        )
                    )->setField('mem_id', $mem_id);
                    if ($rs) {
                        $data['b'] = $gf_model->where(
                            array(
                                'id'     => $gf_id,
                                'app_id' => $app_id
                            )
                        )->getField('remain');
                        $data['a'] = $giftdata['code'];
                        $this->ajaxReturn($data);
                    }
                } else {
                    $data['a'] = '3';
                }
            } else {
                $data['a'] = '5';
            }
        }
        $this->ajaxReturn($data);
    }

    function ajaxGift() {
        if (IS_AJAX) {
            $gf_id = I('post.giftid/d', 0); // 礼包ID
            if ($gf_id <= 0) {
                $data['info'] = "无此礼包";
                $this->ajaxReturn($data);
            }
            // 查询礼包是否存在
            $gfmap['id'] = $gf_id;
            $gf_info = M('gift')->where($gfmap)->find();
            if (empty($gf_info)) {
                $data['info'] = "无此礼包";
                $this->ajaxReturn($data);
            }
            $mem_id = sp_get_current_userid();
            // 查询礼包详情
            $gflog_info = $this->getUserGift($gf_id, $mem_id);
            if (!empty($gflog_info)) {
                $data['giftcode'] = $gflog_info['code'];
                $data['info'] = "已领取过礼包";
                $data['status'] = 1;
                $this->ajaxReturn($data);
            }
            // 领取礼包
            $giftcode = $this->setUserGift($gf_id);
            if (empty($giftcode)) {
                $data['info'] = "礼包已领完";
                $this->ajaxReturn($data);
            }
            $data['giftcode'] = $giftcode;
            $data['info'] = "礼包领取成功，请领取号码后尽快激活使用!";
            $data['status'] = 1;
            $this->ajaxReturn($data);
        }
    }

    /*
     * 领取礼包
     * 返回礼包码
     */
    private function setUserGift($gf_id) {
        if (empty($gf_id)) {
            return '';
        }
        $mem_id = sp_get_current_userid();
        // 判断是否领取过礼包
        $rdata = $this->getUserGift($gf_id, $mem_id);
        if (!empty($rdata)) {
            return $rdata['code'];
        }
        $giftcode = '';
        $giftmap['id'] = $gf_id;
        // 获取礼包信息
        $gift_info = M('gift')->where($giftmap)->find();
        if ($gift_info['remain'] > 0) {
            // 礼包码数量-1
            $gift_info['remain'] = $gift_info['remain'] - 1;
            M('gift')->save($gift_info);
            $gfc_model = M('gift_code');
            // 查找未被领取的礼包
            $gfcmap['gf_id'] = $gf_id;
            $gfcmap['mem_id'] = 0;
            $user_gift = $gfc_model->where($gfcmap)->find();
            // 更新礼包
            $user_gift['mem_id'] = $mem_id;
            $user_gift['update_time'] = time();
            $rs = $gfc_model->save($user_gift);
            if ($rs > 0) {
                $gf_log['mem_id'] = $mem_id;
                $gf_log['gf_id'] = $gf_id;
                $gf_log['code'] = $user_gift['code'];
                $gf_log['create_time'] = $user_gift['update_time'];
                M('gift_log')->add($gf_log);
                $giftcode = $user_gift['code'];
            }
        }
        return $giftcode;
    }

    function detail() {
        $giftid = I('get.giftid/d', 0);
        $giftdata = $this->getGiftdetail($giftid);
        $this->assign($giftdata);
        $this->display();
    }

    private function getGiftdetail($giftid) {
        $mem_id = sp_get_current_userid();
        $data = array();
        if ($giftid <= 0) {
            return $data;
        }
        $field = array(
            'gf.id'                                      => 'giftid',
            'gf.app_id'                                  => 'gameid',
            'gf.title'                                   => 'giftname',
            'gf.total'                                   => 'total',
            'gf.remain'                                  => 'remain',
            'gf.content'                                 => 'content',
            "CONCAT('".STATICSITE."',g.mobile_icon)"     => 'icon',
            "FROM_UNIXTIME(gf.`start_time`, '%Y-%m-%d')" => 'starttime',
            "FROM_UNIXTIME(gf.`end_time`, '%Y-%m-%d')"   => 'enttime',
            'gf.scope'                                   => 'scope'
        );
        $join = "LEFT JOIN ".C('DB_PREFIX')."game g ON gf.app_id =g.id";
        $map['gf.id'] = $giftid;
        $data = M('gift')->alias('gf')->field($field)->join($join)->where($map)->find();
        if (empty($data)) {
            return $data;
        }
        // 查询玩家是否已经领取过
        if ($mem_id > 0) {
            $gflog_info = $this->getUserGift($giftid, $mem_id);
            if (!empty($gflog_info)) {
                $data['giftcode'] = $gflog_info['code'];
                $data['isget'] = 1;
            }
        }
        return $data;
    }
}