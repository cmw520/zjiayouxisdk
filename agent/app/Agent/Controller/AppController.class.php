<?php
/**
 * LoginController.class.php UTF-8
 * 登录接口
 *
 * @date    : 2016年7月21日下午8:27:36
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class AppController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function toAppStatistics() {
        $this->display();
    }

    public function app() {
        if (isset($_GET['agent_id']) && is_numeric($_GET['agent_id']) && ($_GET['agent_id'] >= 1)) {
            $this->app_getcontent();
            $this->display();
        }
    }

    private function app_getcontent() {
        $agent_id = $_GET['agent_id'];
        $where = array();
        $model = M("agent_game");
        $where["agent_id"] = $agent_id;
        $where["check_status"] = "2";
        $where["in_app"] = "2";
        $count = $model->alias('ag')
                       ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
                       ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id ")
                       ->where($where)->count();
        $Page = new \Think\Page($count, 10);
        $show = $Page->show();// 分页显示输出
        $games = $model
            ->field("ag.*,g.name,gv.version,g.icon,gt.name as gametype")
            ->alias('ag')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=ag.app_id")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_type gt ON gt.id=g.type")
            ->where($where)
            ->order("update_time desc")
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        /*
        * 如果图片不存在，用默认图片代替
        * 2016-08-10 11:08:21 严旭
        */
        foreach ($games as $key => $game) {
            $fp = SITE_PATH.'upload/'.$game['icon'];
            if (!file_exists($fp)) {
                $games["$key"]['icon'] = "/upload/default_app_icon.png";
            }
        }
        $this->assign("total_count", $count);
        $this->assign("Page", $show);
        $this->assign("games", $games);
    }
}