<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class PostController extends AdminbaseController {
    private $model;
    private $game_obj;

    function _initialize() {
        parent::_initialize();
        $this->model = M('web_posts');
        Vendor('HuoShu.Game');
        $this->game_obj = new \HuoShu\Game();
    }

    public function index() {
        $count = $this->model->count();
        $page = $this->page($count, 10);
        $items = $this->model
            ->field("wb.*,g.name as app_name")
            ->alias("wb")
            ->order("id desc")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=wb.app_id")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign("items", $items);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    public function del() {
        $id = I('id');
        M('web_posts')->where(array("id" => $id))->delete();
        $this->success("删除成功");
    }

    public function add() {
        $this->_game(true, 2, 2);
        $cates = $this->game_obj->all_news_category();
        $tags = $this->game_obj->all_news_tag();
        $this->assign("cates", $cates['items']);
        $this->assign("tags", $tags);
        $this->display();
    }

    public function add_post() {
        Vendor("HuoShu.Upload");
        $upload_obj = new \HuoShu\Upload();
        $fp = $upload_obj->image_upload("image");
        $result = $this->model->add(
            array(
                "post_title"   => $_POST['title'],
                "post_content" => htmlspecialchars_decode($_POST['content']),
                "post_type"    => $_POST['type'],
                "istop"        => $_POST['is_top'],
                "app_id"       => $_POST['app_id'],
                "post_status"  => "2",
                "image"        => $fp,
                "create_time"  => time(),
                "update_time"  => time()
            )
        );
        $obj_id = $result;
        $this->game_obj->handle_cates($obj_id, "news");
        $this->game_obj->handle_tags($obj_id, "news");
        $this->success("添加成功");
    }

    public function edit() {
        $id = I('id');
        $data = M('web_posts')->where(array("id" => $id))->find();
        $this->assign("data", $data);
        $this->assign("app_id", $data['app_id']);
        $cates = $this->game_obj->all_news_category();
        $tags = $this->game_obj->get_news_tags();
        $this->assign("tags", $tags);
        $cate_txt = $this->game_obj->getNewsCateTxt($id);
        $this->assign("cate_txt", $cate_txt);
        $tag_txt = $this->game_obj->getNewsTagTxt($id);
        $this->assign("tag_txt", $tag_txt);
        $this->_game(true, 2, 2);
        $this->display();
    }

    public function edit_post() {
        $obj_id = I('id');
        Vendor("HuoShu.Upload");
        $upload_obj = new \HuoShu\Upload();
        $fp = $upload_obj->image_upload("image");
        if ($fp != '') {
            $this->model->where(array("id" => $obj_id))->setField("image", $fp);
        }
        $this->model->where(array("id" => $obj_id))->save(
            array(
                "post_title"   => $_POST['title'],
                "post_content" => htmlspecialchars_decode($_POST['content']),
                "post_type"    => $_POST['type'],
                "istop"        => $_POST['is_top'],
                "app_id"       => $_POST['app_id'],
                "update_time"  => time()
            )
        );
        $this->game_obj->handle_cates($obj_id, "news");
        $this->game_obj->handle_tags($obj_id, "news");
        $this->success("修改成功");
    }
}

