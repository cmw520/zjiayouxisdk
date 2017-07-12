<?php
/**
 * IndexController.class.php UTF-8
 *
 * @date    : 2016年3月30日上午11:44:20
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 *
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class IndexController extends MobilebaseController {
    
    /* 首页  */
    public function index() {
		redirect(MOBILESITE.'/app/newwappc/index.html#/home');
		exit;
    }
}