<?php
/**
 * ServiceController.class.php UTF-8
 * 用户中心控制
 *
 * @date    : 2016年7月8日下午2:54:46
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 2.0
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class ServiceController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
    }

    /**
     * 客服中心
     */
    function kefu() {
        $contactmodel = M('game_contact');
        $app_id = $_SESSION['app']['app_id'];
        if (isset($app_id) && !empty($app_id)) {
            $list = $contactmodel->where("app_id = %d ", $app_id)->find();
            if (empty($list)) {
                $list = $contactmodel->where("app_id = %d ", 0)->find();
            }
        } else {
            $list = $contactmodel->where("app_id = %d ", 0)->find();
        }
        $this->assign("contact", $list);
        $this->assign('title', '联系客服');
        $this->display();
    }
}