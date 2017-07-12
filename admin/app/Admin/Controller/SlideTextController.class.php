<?php
namespace Admin\Controller;
class SlideTextController extends SlideBaseController {
    function _initialize() {
        parent::_initialize();
        $this->slidecat_type_id = 4;
    }

    function index() {
        $this->_status();
        $this->_game();
//        $count=count($this->getList());
//        $page=$this->page($count,30);
//        $items=$this->getList(array(),$page->firstRow,$page->listRows);
        $items = $this->getList();
        $this->assign("items", $items);
//        $this->assign("page",$page->show('Admin'));
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = M('slide')
            ->alias("sl")
            ->field("sl.*,sc.cat_name")
            ->join("LEFT JOIN ".C("DB_PREFIX")."slide_cat sc on sc.cid=sl.slide_cid")
            ->where("sc.cat_type_id = $this->slidecat_type_id ")
            ->where($where)
            ->order("sl.slide_id desc")
            ->limit($start, $limit)
            ->select();
        $this->formatTargetObject($items);

        return $items;
    }

    function add() {
        $this->setSelectAreas();
        $this->_game(true, '', 2, '', 2);
        $categorys = $this->slidecat_model
            ->field("cid,cat_name")
            ->where("cat_status!=0")
            ->where("cat_type_id = $this->slidecat_type_id ")
            ->select();
        $this->assign("categorys", $categorys);
        $this->display();
    }

    function add_post() {
        if (IS_POST) {
            if ($this->slide_model->create()) {
                if ($this->slide_model->add() !== false) {
                    $this->success("添加成功！", U("slide/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->slide_model->getError());
            }
        }
    }

    function edit() {
        $this->_game(true, '', 2, '', 2);
        $categorys = $this->slidecat_model
            ->field("cid,cat_name")
            ->where("cat_status!=0")
            ->where("cat_type_id = $this->slidecat_type_id ")
            ->select();
        $id = intval(I("get.id"));
        $slide = $this->slide_model->where("slide_id=$id")->find();
        $this->setSelectAreas($slide['type_id'], $slide['target_id']);
        $this->assign($slide);
        $this->assign("categorys", $categorys);
        $this->display();
    }

    function edit_post() {
        if (IS_POST) {
            if ($this->slide_model->create()) {
                $_POST['slide_pic'] = sp_asset_relative_url($_POST['slide_pic']);
                if ($this->slide_model->save() !== false) {
                    $this->success("保存成功！", U("slide/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->slide_model->getError());
            }
        }
    }
}