<?php
/**
 * ZxController.class.php UTF-8
 * 新闻公告控制器
 * @date: 2016-11-04 10:23:27
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author : wangchuang <wangchuang@huosdk.com>
 * @version : Zx 1.0
 */

namespace Agent\Controller;
use Common\Controller\AgentbaseController;

class VipController extends AgentbaseController {
    protected $game_vip_model;
    
    function _initialize() {
        parent::_initialize();
        $this->game_vip_model = M('game_vip');
    } 
    
    // 返回游戏等级的数据信息
    public function index() {
        $where['app_id'] = I('app_id');
        $content = $this->game_vip_model->where($where)->getField('content');
        
        if ($content) {
            echo json_encode(array('status'=>1,'content'=>$content));
        } else {
            echo json_encode(array('status'=>0));
        }
        
    }
}