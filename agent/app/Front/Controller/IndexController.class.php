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

class IndexController extends AgentPublicController {
    function _initialize() {
        parent::_initialize();
    }

    public function index2() {
        if (is_logged_in()){
            redirect(U('Agent/Money/recharge_member'));
        }
        redirect("/public/agent/front/index.html#/home");
        // redirect(U('Front/index/index3'));
        exit;
//        echo md5("dj123456"."M@x^@!e@($");
        $banners = M('web_media')->where(array("name" => "agent_site_pics"))->select();
        $this->assign("banners", $banners);
        $hs_obj = new \Huosdk\Game();
        $items = $hs_obj->get_game_list(0, 12);
        $this->assign("hot_game", $items);
        $server_items = $hs_obj->serverList_tiny(0, 10);
        $this->assign("server_list", $server_items['items']);
        $contact_data = M('web_aboutus')->where(array("title" => "合作联系"))->find();
        $this->assign("contact_txt", $contact_data['content']);
        $this->display();
    }

    public function getHotGameLists() {
        $model = M('options');
        $data = $model->where(array("option_name" => "index_page_hot_games"))->getField("option_value");
        if (!$data) {
            return array();
        }
        $items = json_decode($data, true);

        return $items;
    }

    private function get_hot_games() {
        $items = $this->getHotGameLists();
        $lists = join(",", $items);
        $records = array();
        if ($lists) {
//            $records = M('game')->join(->where("id IN ($lists)")->select();
            $field = "g.*,gi.mobile_icon m_icon";
            $records = M('game')
                ->alias('g')
                ->field($field)
                ->join("LEFT JOIN ".C('DB_PREFIX')."game_info gi ON gi.app_id=g.game_id")
                ->where("id IN ($lists)")
                ->order("g.id DESC")
                ->select();
            foreach ($records as $_k => $_v) {
                if (!empty($_v['m_icon'])) {
                    if (false === strpos($_v['m_icon'],'upload')) {
                        $records[$_k]['icon'] = '/upload/image/'.$_v['m_icon'];
                    } else {
                        $records[$_k]['icon'] = $_v['m_icon'];
                    }
                } elseif (false === strpos( $_v['icon'],'upload')) {
                    $records[$_k]['icon'] = '/upload/'.$_v['icon'];
                }
            }
        }

        return $records;
    }

    public function getServerList($start = 0, $limit = 0) {
        $model = M('web_server');
        $items = $model
            ->field("ws.*,g.name as game_name")
            ->alias("ws")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ws.app_id")
            ->where(array("ws.is_delete" => 2, "ws.start_time" => array("egt", time())))
            ->limit($start, $limit)
            ->order("ws.start_time asc")
            ->select();

        return $items;
    }

    public function getServerList_json() {
        $model = M('options');
        $data = $model->where(array("option_name" => "inde_page_serverlist"))->getField("option_value");
        if (!$data) {
            return array();
        }
        arsort($data);
        $items = json_decode($data, true);

//        print_r($items);
        return $items;
    }

    public function index3() {
        if (is_logged_in()){
            redirect(U('Agent/Money/recharge_member'));
        }
        redirect("/public/agent/front/index.html#/home");
        exit;
//        echo md5("dj123456"."M@x^@!e@($");
        $banners = M('web_media')->where(array("name" => "agent_site_pics"))->select();
        $this->assign("banners", $banners);
        $hs_obj = new \Huosdk\Game();
//        $items=$hs_obj->get_game_list(0,12);   
        $items = $this->get_hot_games();
        $this->assign("hot_game", $items);
//        $server_count=count($this->getServerList());
//        $server_page=new \Think\Page($server_count,8);
//        $server_page->setConfig("theme", "%UP_PAGE% %DOWN_PAGE%");
//        $server_page->setConfig("prev", "上一页");
//        $server_page->setConfig("next", "下一页");
//        $server_items=$this->getServerList($server_page->firstRow,$server_page->listRows);
//
////        $this->assign("server_list",$server_items);
////        $this->assign("server_paging",$server_page->show());
        $contact_data = M('web_aboutus')->where(array("title" => "合作联系"))->find();
        $this->assign("contact_txt", $contact_data['content']);
        $this->display();
    }

    public function ServerList() {
        $start = 0;
        if (isset($_GET['p']) && ($_GET['p'])) {
            $start = ($_GET['p'] - 1) * 7;
        }
        $server_count = count($this->getServerList());
        $max_p = ceil($server_count / 7);
//        $server_page=new \Think\Page($server_count,8);
//        $server_page->setConfig("theme", "%UP_PAGE% %DOWN_PAGE%");
//        $server_page->setConfig("prev", "上一页");
//        $server_page->setConfig("next", "下一页");
        $server_items = $this->getServerList($start, 7);
        $this->assign("server_list", $server_items);
        $items_txt = '';
        foreach ($server_items as $key => $value) {
            $items_txt .= '<div class="row server_item">';
            $items_txt .= '<div class="col-xs-4">'.$value['game_name']." </div>";
            $items_txt .= '<div class="col-xs-4">'.$value['sername']." </div>";
            $items_txt .= '<div class="col-xs-4">'.date("Y-m-d H:i:s", $value['start_time'])." </div>";
            $items_txt .= '</div>';
        }
//        $server_paging=$server_page->show();
        $this->ajaxReturn(
            array("error" => "0",
                  "msg"   => json_encode(
                      array("items" => $items_txt, "max_p" => $max_p)
                  ))
        );
    }

    /* 首页  */
    public function index() {
        redirect(U('Front/index/index3'));
        exit;
        $model = M('tui_news');
        $news = $model->order("id desc")->limit(4)->select();
        $this->assign("news", $news);
        $game_model = M('tui_games');
        $hot_games = $game_model
            ->field("g.name,g.icon,g.id")
            ->alias("tg")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=tg.game_id")
            ->where(array("tag" => "hot"))
            ->limit(4)
            ->select();
        $about_online_games = $game_model
            ->field("g.name,g.icon,g.id")
            ->alias("tg")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game g ON g.id=tg.game_id")
            ->where(array("tag" => "about_online"))
            ->limit(4)
            ->select();
        $this->assign("hot_games", $hot_games);
        $this->assign("about_online_games", $about_online_games);
        $this->display();
    }
}