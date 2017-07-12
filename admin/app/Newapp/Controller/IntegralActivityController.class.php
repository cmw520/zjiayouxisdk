<?php
/*
 *  @time 2017-1-23 11:18:26
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class IntegralActivityController extends AdminbaseController {
    public $model;

    function _initialize() {
        parent::_initialize();
        $this->assign("admin_module_name", "积分活动管理");
        $this->model = M('integral_activity');
    }

    public function index() {
        $where = array();
        $h_obj = new \Huosdk\Where();
        $h_obj->get_simple_like($where, "act_name", "ia.act_name");
        $allitems = $this->getList($where);
        $count = count($allitems);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0, $switch_flag = true) {
        $items = $this->model
            ->alias("ia")
            ->field("ia.*")
            ->where($where)
            ->where(array("ia.is_delete" => "2"))
            ->order("ia.id DESC")
            ->limit($start, $limit)
            ->select();
        $limit_data = array("1" => "限制", "2" => "不限制");
        foreach ($items as $key => $value) {
            $items[$key]['start_time'] = date("Y-m-d H:i:s", $value['start_time']);
            $items[$key]['end_time'] = date("Y-m-d H:i:s", $value['end_time']);
            if (empty($value['start_time']) && $switch_flag) {
                $items[$key]['start_time'] = "--";
            } else {
                $items[$key]['start_time'] = date("Y-m-d H:i:s", $value['start_time']);
            }
            if (empty($value['end_time']) && $switch_flag) {
                $items[$key]['end_time'] = "长期";
            } else {
                $items[$key]['end_time'] = date("Y-m-d H:i:s", $value['end_time']);
            }
            if (empty($value['limit_cnt']) && $switch_flag) {
                $items[$key]['limit_cnt'] = "不限制次数";
            }
            $items[$key]['limit_agent_txt'] = $limit_data[$value['limit_agent']];
        }

        return $items;
    }

    public function add() {
        $this->display();
    }

    public function getChargeList() {
        $_map['is_delete'] = 2;
        $items = M('integral_activity_pay')
            ->alias("iap")
            ->field("iap.*")
            ->where($_map)
            ->order("iap.end_money ASC")
            ->select();

        return $items;
    }

    public function edit($id = 0) {
        if (0 == $id) {
            $id = I('id');
        }
        $data_set = $this->getList(array("ia.id" => $id), 0, 0, false);
        if ('tguser' == $data_set[0]['act_code']) {
            $data_set[0]['max_user'] = $data_set[0]['limit_cnt'];
        }
        $this->assign("data", $data_set[0]);
        if ('charge' == $data_set[0]['act_code']) {
            $_chargelist = $this->getChargeList();
            $this->assign('chargedata', $_chargelist);
            $this->display('IntegralActivity/editCharge');
        } else {
            $this->display('IntegralActivity/edit');
        }
    }

    public function editPost() {
        $id = I('id');
        $data = $_POST;
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        $this->model->where(array("id" => I('id')))->save($data);
        $this->success("成功");
    }

    public function editCharge() {
        $id = I('post.id/d', 0);
        if (empty($id)) {
            $this->error('参数错误');
        }
        $_ia_data['id'] = $id;
        $_ia_data['limit_agent'] = I('limit_agent/s', 1); //默认限制
        $_ia_data['update_time'] = time();
        $_rs = $this->model->save($_ia_data);
        if (false === $_rs) {
            $this->error("修改失败");
        }
        $_itgids = I('post.itgid');
        $_gitg = I('post.gitg');
        foreach ($_itgids as $_k => $_v) {
            $_iap_data['id'] = $_v;
            $_iap_data['give_integral'] = $_gitg[$_k];
            $_rs = M('integral_activity_pay')->save($_iap_data);
            if (false === $_rs) {
                break;
            }
        }
        if (false === $_rs) {
            $this->error('修改失败');
        }
        $this->edit($id);
    }

    public function addPost() {
        $data = $_POST;
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 2;
        $this->model->add($data);
        $this->success("添加成功");
    }

    public function delete() {
//        $this->model->where(array("id" => I('id')))->setField("is_delete", "1");
        $this->success("删除成功");
    }
}
