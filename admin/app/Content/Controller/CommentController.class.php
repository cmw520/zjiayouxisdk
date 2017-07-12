<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class CommentController extends AdminbaseController {
    private $zone_filenames;
    private $zone_file_upload_fp;
    private $game_obj;

    function _initialize() {
        parent::_initialize();
        $this->zone_filenames = array("banner", "banner2", "download_btn", "gift_btn", "background");
        $this->zone_file_upload_fp = SITE_PATH.'access/upload/mobile/zone/';
        Vendor('HuoShu.Game');
        $this->game_obj = new \HuoShu\Game();
    }

    public function index() {
        $total_size = M('comments')
            ->count();
        $page = $this->page($total_size, 10);
        $items = M('comments')
            ->field("c.*,g.name as game_name,m.username as mem_name")
            ->alias('c')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=c.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members m ON m.id=c.mem_id")
            ->order("c.id desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($items as $k => $v) {
            if (!$v['mem_name']) {
                $items[$k]['mem_name'] = '管理员';
            }
        }
        $this->assign("items", $items);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function del() {
        $id = I('id');
        $r = M('comments')->where(array("id" => $id))->delete();
        if ($r) {
            $this->ajaxReturn(array("error" => "0", "msg" => "删除成功"));
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => "删除失败"));
        }
    }

    public function reply() {
        $cid = I('cid');
        $pre_data = M('comments')->where(array("id" => $cid))->find();
        $this->assign("data", $pre_data);
        $this->display();
    }

    public function reply_post() {
        $cid = I('cid');
        $content = I('content');
        $pre_data = M('comments')->where(array("id" => $cid))->find();
        $data = array();
        $data['content'] = $content;
        $data['mem_id'] = 0;
        $data['to_mem_id'] = $pre_data['mem_id'];
        $data['parentid'] = $cid;
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['app_id'] = $pre_data['app_id'];
        M('comments')->add($data);
        $this->success("回复成功");
    }
}

