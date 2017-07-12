<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class GameDetailController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $app_id = I("id");
        $this->assign("data", $this->getInfo($app_id));
        $this->display();
    }

    public function getInfo($app_id) {
        $result = array();
        $game_data = M('game')->where(array("id" => $app_id))->find();
        $result['name'] = $game_data['name'];
        $game_info_data = M('game_info')->where(array("app_id" => $app_id))->find();
        $result['description'] = $game_info_data['description'];
        $result['size'] = $game_info_data['size'];
        $result['shots'] = json_decode($game_info_data['image'], true);
        $game_version_data = M('game_version')->where(array("app_id" => $app_id))->find();
        $result['version'] = $game_version_data['version'];

        return $result;
    }
}