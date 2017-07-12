<?php
/**
 * IndexController.class.php UTF-8
 *
 * @date    : 2016年3月30日上午11:44:20
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 *
 */
namespace Front\Controller;

use Common\Controller\AgentPublicController;

class ApiGameController extends AgentPublicController {
    protected $game_model;
    protected $options_model;
    protected $game_type_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = M('game');
        $this->options_model = M('options');
        $this->game_type_model = M('game_type');
    }

    // 获取游戏游戏列表
    function getList() {
        // 获取热门游戏
        $data['hot'] = $this->getHotGame();
        // 获取推荐游戏
        $data['rec'] = $this->getRecommendGame();
        echo $this->myJson($data);
    }

    // 热门游戏
    private function getHotGame() {
        return $this->getGameData('index_page_hot_games');
    }

    // 获取游戏的数据
    private function getGameData($option_name) {
        $field = "type, name, icon";
        $json_ids = $this->options_model
            ->where(array('option_name' => $option_name))->getField('option_value');
        $ids = json_decode($json_ids, true);
        $where['id'] = array('in', $ids);
        $data = $this->game_model->field($field)->where($where)->select();
        $types = $this->getAllTypes();
        foreach ($data as $key => $val) {
            $type = array_shift(explode(',', $val['type']));
            $data[$key]['type'] = $types[$type];
            // 添加默认图片 这里的默认图片需要修改
            if (empty($data[$key]['icon'])) {
                $data[$key]['icon'] = '/upload/20161209/584a70957fcb7.png';
            }
        }

        return $data;
    }

    // 推荐游戏
    private function getRecommendGame() {
        return $this->getGameData('index_page_recommend_games');
    }

    // 获取所有的游戏的标签
    protected function getAllTypes() {
        return $this->game_type_model->getField('id, name');
    }

    // 格式化数据
    function myJson($data) {
        header('Content-Type:application/json; charset=utf-8');

        return json_encode($data);
    }
}