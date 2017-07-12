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

class ZxController extends AgentbaseController {

    function _initialize() {
        parent::_initialize();
        $this->row = empty($this->row) ? 2 : $this-row;
    }
    
    public function index() {
        $model=M('tui_news');
        $count=$model->count();
        $page=$this->page($count,$this->row);
        
        $news=$model
        ->field('tn.*')
        ->alias('tn')
        //                ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
        //                ->join("LEFT JOIN ".C('DB_PREFIX')."users u ON u.id=ag.agent_id")
        ->order("tn.id desc")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();  
        
        $this->assign("news",$news);
        $this->assign("Page", $page->show('Admin'));
	    $this->assign("current_page", $page->GetCurrentPage());
        $this->display();
    }
    
    public function get_content() {
        $id = I('id');
        $content=M('tui_news')->where(array('id'=>$id))->getField('content');
        if ($content) {
            echo json_encode(array('status'=>1,'content'=>$content));
        } else {
            echo json_encode(array('status'=>0));
        }
    }
}