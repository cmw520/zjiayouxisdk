<?php
/**
 * 礼包管理中心
 *
 * @author
 *
 */
namespace IosApp\Controller;

use Common\Controller\AdminbaseController;

class AppcdkeyController extends AdminbaseController {
    protected $game_model;

    function _initialize() {
        parent::_initialize();
    }

    /**
     * 礼包列表
     */
    public function index() {
        $this->redirect('Sdk/Cdkey/giftlist');
        exit();
    }
}