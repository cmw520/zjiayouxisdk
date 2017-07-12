<?php
namespace Game\Controller;

use Common\Controller\AdminbaseController;

class PaywayController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $name = I("name", "");
        $data = M('payway_game')->distinct(true)->getField("app_id", true);
        if (!empty($name)) {
            $where['name'] = array("like", "%$name%");
            $data = M('game')->where($where)->getField("id", true);
        }
        foreach ($data as $v) {
            $payways = M('payway_game')
                ->alias('a')
                ->join("LEFT JOIN ".C('DB_PREFIX')."payway p ON p.id=a.pw_id")
                ->where(array("app_id" => $v))
                ->getField('realname', true);
            $paydata[$v]['payway'] = implode(',', $payways);
            $paydata[$v]['appid'] = $v;
        }
        $this->assign("name", $name);
        $games = M('game')->getField("id,name");
        $pays = M('payway')->where(array("status" => 2))->getField("id,realname");
        $this->assign("paydata", $paydata);
        $this->assign("games", $games);
        $this->assign("pays", $pays);
        $this->display();
    }

    function delete() {
        $appid = I("appid");
        $rs = M("payway_game")->where(array("app_id" => $appid))->delete();
        if ($rs) {
            $this->success("删除成功.");
            exit;
        }
        $this->error("删除失败.");
        exit;
    }

    function edit() {
        $appid = I("appid");
        $gamename = M('game')->where(array("id" => $appid))->getField("name");
        $selectdata = M('payway_game')->where(array("app_id" => $appid))->getField("pw_id", true);
        $payways = M('payway')->where(array("status" => 2))->getField("id,realname", true);
        $this->assign("gamename", $gamename);
        $this->assign("appid", $appid);
        $this->assign("selectdata", $selectdata);
        $this->assign("payways", $payways);
        $this->display();
    }

    function edit_post() {
        $appid = I("appid/d", 0);
        $paytypes = I("paytypeid");
        if ($appid <= 0) {
            $this->error("参数错误");
            exit;
        }
        $rs = M("payway_game")->where(array("app_id" => $appid))->delete();
        if ($rs) {
            foreach ($paytypes as $vo) {
                $data['app_id'] = $appid;
                $data['pw_id'] = $vo;
                $rrs = M("payway_game")->data($data)->add();
            }
            $this->success("修改成功.");
            exit;
        }
        $this->error("修改失败.");
        exit;
    }

    function add() {
        $settedgames = M('payway_game')->distinct(true)->getField("app_id", true);
        $map['is_delete'] = array("eq", 2);
        $map['status'] = array("eq", 2);
        if (!empty($settedgames)) {
            $map['id'] = array("not in", $settedgames);
        }
        $games = M("game")->where($map)->getField("id,name", true);
        $payways = M('payway')->where(array("status" => 2))->getField("id,realname", true);
        $this->assign("games", $games);
        $this->assign("payways", $payways);
        $this->display();
    }

    function add_post() {
        $appid = I("app_id/d", 0);
        if ($appid <= 0) {
            $this->error("亲，您还为选择游戏哦.");
            exit;
        }
        $paytypes = I("paytypeid");
        if (empty($paytypes)) {
            $this->error("亲，您还未选择支付方式哦.");
            exit;
        }
        foreach ($paytypes as $vo) {
            $data['app_id'] = $appid;
            $data['pw_id'] = $vo;
            $rrs = M("payway_game")->data($data)->add();
        }
        $this->success("恭喜您，支付设置成功.");
        exit;
    }
}

