<?php
/* 
 *  @time 2017-1-22 17:55:08
 *  @author 严旭
 */
namespace Newapp\Controller;

use Admin\Controller\SlideBaseController;

class CouponController extends SlideBaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->model = M('slide');
        $this->assign("admin_module_name", "卡券说明管理");
        $this->slidecat_type_id = 5;
    }

    public function index() {
        $this->_status();
        $items = $this->getList();
        $this->assign("items", $items);
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = $this->model
            ->alias("sl")
            ->field("sl.*,sc.cat_name")
            ->join("LEFT JOIN ".C("DB_PREFIX")."slide_cat sc ON sc.cid=sl.slide_cid")
            ->where(array("sc.cat_idname" => "carddesc"))
            ->where($where)
            ->order("sl.listorder ASC")
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
            ->where("cat_status!=0 AND cat_type_id = $this->slidecat_type_id AND `cat_idname` = 'carddesc' ")
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
            ->where("cat_status!=0 AND cat_type_id = $this->slidecat_type_id AND `cat_idname` = 'carddesc' ")
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