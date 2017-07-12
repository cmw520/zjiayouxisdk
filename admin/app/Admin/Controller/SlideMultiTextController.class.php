<?php
namespace Admin\Controller;
class SlideMultiTextController extends SlideBaseController {
    function _initialize() {
        parent::_initialize();
        $this->slidecat_type_id = 2;
    }

    function index() {
        $this->_status();
        $this->_game();
        $this->_cates();
        $where = "sc.cat_type_id = $this->slidecat_type_id  ";
        $cid = 0;
        if (isset($_POST['cid']) && $_POST['cid'] != "") {
            $cid = $_POST['cid'];
            $where .= " AND sl.slide_cid=$cid";
        }
        $this->assign("slide_cid", $cid);
        $slides = $this->slide_model
            ->field("sl.*,sc.cat_name")
            ->alias("sl")
            ->join("LEFT JOIN ".C("DB_PREFIX")."slide_cat sc ON sc.cid=sl.slide_cid")
            ->where($where)
            ->order("sl.listorder ASC")
            ->select();
        $this->formatTargetObject($slides);
        $this->assign('slides', $slides);
        $this->display();
    }

    function add() {
        $this->setSelectAreas();
        $this->_game(true, '', 2, '', 2);
        $categorys = $this->slidecat_model
            ->field("cid,cat_name")
            ->where("cat_status!=0 AND cat_type_id = $this->slidecat_type_id ")
            ->select();
        $this->assign("categorys", $categorys);
        $this->display();
    }

    function add_post() {
        if (IS_POST) {
            if ($this->slide_model->create()) {
                $_POST['slide_pic'] = sp_asset_relative_url($_POST['slide_pic']);
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
            ->where("cat_status!=0 AND cat_type_id = $this->slidecat_type_id")
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