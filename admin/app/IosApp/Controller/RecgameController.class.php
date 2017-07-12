<?php
/**
 * RecgameController.class.php UTF-8
 * 推荐游戏
 *
 * @date    : 2016年9月26日上午2:09:11
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : SDK 2.0
 */
namespace IosApp\Controller;

use Common\Controller\AdminbaseController;

class RecgameController extends AdminbaseController {
    protected $game_model;
    protected $gr_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gr_model = M('game_recmd');
    }

    function index() {
        $this->_game(true, '', 2);
        $where['is_delete'] = 2;
        $app_id = I('post.appid/d', 0);
        if (!empty($app_id)) {
            $where['app_id'] = $app_id;
            $this->assign('appid', $app_id);
        }
        $games = $this->gr_model->where($where)->order("listorder DESC")->select();
        $this->assign('recgames', $games);
        $this->display();
    }

    function add() {
        $this->_game(false, '', 2);
        $this->display();
    }

    function add_post() {
        if (IS_POST) {
            $gr_data['app_id'] = I('appid/d', 0);
            $gr_data['image'] = I('image/s', '');
            $gr_data['status'] = I('status/d', 2);
            $gr_data['listorder'] = I('listorder/d', 0);
            $gr_data['create_time'] = time();
            $gr_data['update_time'] = $gr_data['create_time'];
            $gr_data['is_delete'] = 2;
            if (empty($gr_data['app_id'])) {
                $this->error("请选择游戏");
            }
            if (empty($gr_data['image'])) {
                $this->error("请添加图片");
            }
            // $gr_data['image'] = sp_asset_relative_url($gr_data['image']);
            if ($this->gr_model->add($gr_data) !== false) {
                $this->success("添加成功！", U("Recgame/index"));
            } else {
                $this->error("添加失败！");
            }
        }
    }

    function edit() {
        $this->_game(false);
        $id = I('id/d', 0);
        $recgame = $this->gr_model->where("id=$id")->find();
        $this->assign($recgame);
        $this->display();
    }

    function edit_post() {
        if (IS_POST) {
            $gr_data['id'] = I('id/d', 0);
            $gr_data['app_id'] = I('appid/d', 0);
            $gr_data['image'] = I('image/s', '');
            $gr_data['status'] = I('status/d', 2);
            $gr_data['listorder'] = I('listorder/d', 0);
            $gr_data['create_time'] = time();
            $gr_data['update_time'] = $gr_data['create_time'];
            $gr_data['is_delete'] = 2;
            if (empty($gr_data['app_id'])) {
                $this->error("请选择游戏");
            }
            if (empty($gr_data['image'])) {
                $this->error("请添加图片");
            }
            // $gr_data['image'] = sp_asset_relative_url($gr_data['image']);
            if ($this->gr_model->save($gr_data) !== false) {
                $this->success("修改成功！", U("Recgame/index"));
            } else {
                $this->error("修改失败！");
            }
        }
    }

    function delete() {
        if (isset($_POST['ids'])) {
            $ids = implode(",", $_POST['ids']);
            $data['is_delete'] = 1;
            $rs = $this->gr_model->where("id in ($ids)")->save($data);
            if ($rs !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        } else {
            $id = intval(I("get.id"));
            $data['is_delete'] = 1;
            $rs = $this->gr_model->where("id = $id")->save($data);
            if ($rs !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    function toggle() {
        if (isset($_POST['ids']) && $_GET["display"]) {
            $ids = implode(",", $_POST['ids']);
            $data['status'] = 2;
            if ($this->gr_model->where("id in ($ids)")->save($data) !== false) {
                $this->success("显示成功！");
            } else {
                $this->error("显示失败！");
            }
        }
        if (isset($_POST['ids']) && $_GET["hide"]) {
            $ids = implode(",", $_POST['ids']);
            $data['status'] = 1;
            if ($this->gr_model->where("id in ($ids)")->save($data) !== false) {
                $this->success("隐藏成功！");
            } else {
                $this->error("隐藏失败！");
            }
        }
    }

    // 隐藏
    function ban() {
        $id = intval($_GET['id']);
        $data['status'] = 1;
        if ($id) {
            $rst = $this->gr_model->where("id in ($id)")->save($data);
            if ($rst) {
                $this->success("推荐游戏隐藏成功！");
            } else {
                $this->error('推荐游戏隐藏失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    // 显示
    function cancelban() {
        $id = intval($_GET['id']);
        $data['status'] = 2;
        if ($id) {
            $result = $this->gr_model->where("id in ($id)")->save($data);
            if ($result) {
                $this->success("推荐游戏显示成功！");
            } else {
                $this->error('推荐游戏显示失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    // 排序
    public function listorders() {
        $status = parent::_listorders($this->gr_model);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
}