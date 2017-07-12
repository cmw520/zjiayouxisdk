<?php
/**
 * 首页轮播图管理
 *
 * @author
 */
namespace Wap\Controller;

use Common\Controller\AdminbaseController;

class IndexController extends AdminbaseController {
    // 移动端轮播图
    public function getSlides() {
        redirect(U('Admin/Slide/index'));
        exit;
    }

    // 资讯模块
    public function getPosts() {
        redirect(U('Newapp/News/index'));
        exit;
    }

    // 游戏开服管理
    public function getServerList() {
        redirect(U('Web/Game/serverList'));
        exit;
    }
    
    //wap logo管理
    public function app_logo() {
        redirect(U('Newapp/Version/index'));
        exit;
    }
}