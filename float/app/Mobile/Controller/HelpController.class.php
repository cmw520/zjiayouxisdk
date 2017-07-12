<?php
/**
 * 礼包管理中心
 *
 * @author
 *
 */
namespace Mobile\Controller;

use Common\Controller\HomebaseController;

class HelpController extends HomebaseController {
    /**
     * 帮助中心
     */
    public function index() {
        $this->assign('title', '客服中心');
        $this->display();
    }
}