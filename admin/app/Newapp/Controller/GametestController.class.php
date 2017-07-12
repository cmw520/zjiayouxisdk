<?php
/* 
 *  @time 2017-1-19 11:10:19
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class GametestController extends AdminbaseController {
    public $obj;
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->obj = new \Huosdk\UI\Filter();
        $this->model = M('game_test');
    }

    public function index() {
        $this->assign("app_select", $this->obj->app_select());
        $where = array();
        $f_obj = new \Huosdk\Where();
        $f_obj->get_simple($where, "app_id", "so.app_id");
        $count = count($this->getList($where));
        $page = $this->page($count, 10);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("page", $page->show('Admin'));
        $this->assign("formget", $_GET);
        $this->display();
    }

    public function add() {
        $this->assign("app_select", $this->obj->app_select());
        $this->display();
    }

    public function edit() {
        $id = I('id');
        $info = $this->getList(array("so.id" => $id), 0, 1);
        $this->assign("data", $info[0]);
        $_GET['app_id'] = $info[0]['app_id'];
        $this->assign("app_select", $this->obj->app_select());
        $status_choose_txt = $this->statusInput($info[0]['status']);
        $this->assign("status_choose_txt", $status_choose_txt);
        $this->display();
    }

    public function addPost() {
        $this->commonFilter();
        $status = I('status');
        $app_id = I('app_id');
        $testdesc = I('testdesc');
        $start_time = I('start_time');
        if ($this->prevExist($app_id)) {
            $this->error("此游戏已经有开测信息");
        }
        $data = array();
        $data['app_id'] = $app_id;
        $data['status'] = $status;
        $data['testdesc'] = htmlspecialchars($testdesc);
        $data['start_time'] = strtotime($start_time);
        $data['is_delete'] = 2;
        $add_result = $this->model->add($data);
        if (!$add_result) {
            $this->error("添加失败");
        }
        $this->success("添加成功", U('Newapp/Gametest/index'));
    }

    public function prevExist($app_id) {
        return $this->model->where(array("app_id" => $app_id))->find();
    }

    public function commonFilter() {
        if (!I('app_id')) {
            $this->error("请选择游戏");
        }
        if (!I('start_time')) {
            $this->error("请输入开服时间");
        }
        if (!I('testdesc')) {
            $this->error("请输入开服描述");
        }
        if (!I('status')) {
            $this->error("请选择开服状态");
        }
    }

    public function editPost() {
        $id = I('id');
        if (!$id) {
            $this->error("参数有误");
        }
        $this->commonFilter();
        $data = array();
        $data['app_id'] = I('app_id');
        $data['status'] = I('status');
        $data['testdesc'] = (I('testdesc'));
        $data['start_time'] = strtotime(I('start_time'));
        $result = $this->model->where(array("id" => $id))->save($data);
        if (!$result) {
            $this->error("修改失败");
        }
        $this->success("修改成功", U('Newapp/Gametest/index'));
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = $this->model->alias('so')
                             ->field("g.name as game_name,g.icon as game_icon,so.*")
                             ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=so.app_id")
                             ->where(array("so.is_delete" => 2))
                             ->where($where)
                             ->limit($start, $limit)
                             ->order("so.id desc")
                             ->select();
        $status_data = array("1" => "删档内测", "2" => "不删档内测");
        foreach ($items as $key => $value) {
            $items[$key]['start_time'] = date("Y-m-d H:i:s", $value['start_time']);
            $items[$key]['testdesc_striped'] = mb_substr(($value['testdesc']), 0, 20);
            $items[$key]['status_txt'] = $status_data[$value['status']];
        }

        return $items;
    }

    public function deletePost() {
        $id = I('id');
        $this->model->where(array("id" => $id))->setField("is_delete", 1);
        $this->success("删除成功");
    }

    public function statusInput($status) {
        $status_data = array("1" => "删档内测", "2" => "不删档内测");
        $txt = '';
        foreach ($status_data as $key => $value) {
            $checked = '';
            if ($status == $key) {
                $checked = "checked='checked'";
            }
            $txt .= "<input type='radio' name='status' $checked value='$key' />".$value;
        }

        return $txt;
    }
}