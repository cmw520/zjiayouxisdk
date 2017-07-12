<?php
namespace Huosdk;
class Game {
    private $visit_log_model;

    public function __construct() {
//        $this->visit_log_model=M('visit_log');
    }

    public function get_all_games() {
    }

    public function getAppIdByAppName($name) {
        return M('game')->where(array("name" => $name))->getField("id");
    }

    public function serverList() {
        //获取页码和每页显示条数
        $rows = I("rwos", 10); //获取每页显示条数
        $model = M('web_server');
        $where = array();
        $total = $model->where($where)->count();//总条数
        $page = new \Think\Page($total, $rows);
        //获取开服的信息
        $items = $model
            ->field("ws.*,g.name,g.icon,gt.name as type,gv.size")
            ->alias("ws")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_type gt ON  gt.id = g.type")
            ->where($where)
            ->order("ws.id DESC")
            ->limit($page->firstRow.','.$page->listRows)->select();
        return array(
            "items" => $items,
            "page"  => $page->show('Admin')
        );
    }

    public function serverList_tiny($start = 0, $limit = 0) {
        $model = M('web_server');
        $where = array();
        //获取开服的信息
        $items = $model
            ->field("ws.*,g.name,g.icon,g.initial,gt.name as type_name,gv.size")
            ->alias("ws")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_type gt ON  gt.id = g.type")
            ->where($where)
            ->order("ws.id DESC")
            ->limit($start, $limit)
            ->select();
        foreach ($items as $key => $val) {
            $items["$key"]["app_url"] = $this->get_game_package_url($val['initial']);
        }
        return array(
            "items" => $items,
        );
    }

    public function getAppDownloadUrlByAppId($app_id) {
        $initial = M('game')->where(array("id" => $app_id))->getField("initial");
        return $this->get_game_package_url($initial);
    }

    public function get_game_package_url($initial) {
        return DOWNSITE.$initial."/".$initial.".apk";;
    }

    public function dj_items_old() {
        $model = M('web_server');
        $where = array();
        $where['category'] = "1";
        //获取开服的信息
        $items = $model
            ->field("ws.*,g.name,g.icon,g.initial,gt.name as type_name,gv.size")
            ->alias("ws")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id = ws.app_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_type gt ON  gt.id = g.type")
            ->where($where)
            ->order("ws.id DESC")
            ->limit(10)
            ->select();
        foreach ($items as $key => $val) {
            $items["$key"]["app_url"] = $this->get_game_package_url($val['initial']);
        }
        return array(
            "items" => $items,
        );
    }

    public function dj_items($start = 0, $limit = 0) {
        $model = M('game');
        $where = array();
        $where['category'] = "1";
        $items = $model
            ->where($where)
            ->order("id DESC")
            ->limit($start, $limit)
            ->select();
        foreach ($items as $key => $val) {
            $items["$key"] = $this->get_app_info($val['id']);
        }
        return array(
            "items" => $items,
        );
    }

    public function dj_items_count() {
        $model = M('game');
        $where = array();
        $where['category'] = "1";
        $c = $model
            ->where($where)
            ->order("id DESC")
            ->count();
        return $c;
    }

    public function all_category() {
        $model = M('type');
        $items = $model->select();
        return array(
            "items" => $items,
        );
    }

    public function all_game_category() {
        $model = M('type');
        $items = $model->where(array("obj" => "1"))->order("`order` desc")->select();
        return array(
            "items" => $items,
        );
    }

    public function all_news_category() {
        $model = M('type');
        $items = $model->where(array("obj" => "2"))->select();
        return array(
            "items" => $items,
        );
    }

    public function all_tag() {
        $model = M('tag');
        $items = $model->select();
        return $items;
    }

    public function all_game_tag() {
        $model = M('tag');
        $items = $model->where(array("obj" => "1"))->select();
        return $items;
    }

    public function get_game_tags($app_id) {
        $model = M('tag_match');
        $items = $model->where(array("app_id" => "$app_id", "obj" => "1"))->select();
        return $items;
    }

    public function get_news_tags($news_id) {
        $model = M('tag_match');
        $items = $model->where(array("app_id" => "$news_id", "obj" => "2"))->select();
        return $items;
    }

    public function get_game_cate_ids($id) {
        return $this->get_cate_ids($id, "1");
    }

    public function get_news_cate_ids($id) {
        return $this->get_cate_ids($id, "2");
    }

    public function find_games_by_cateid($cateid, $start = 0, $limit = 10) {
        $where = "g.id IN (SELECT obj_id FROM ".C('DB_PREFIX')."type_match WHERE tid=$cateid )";
        $items = M('game')
            ->alias('g')
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=g.id")
            ->where($where)
            ->limit($start, $limit)
            ->order("g.pv desc")
            ->select();
        $result = array();
        foreach ($items as $k => $v) {
            $result[$k] = $this->get_app_info($v['id']);
        }
        return $result;
    }

    public function getNewsCateTxt($id) {
        $cates = $this->all_news_category();
        $cates = $cates['items'];
        $cate_ids = $this->get_news_cate_ids($id);
        $cate_txt = '';
        foreach ($cates as $cate) {
            $c_txt = '';
            if (in_array($cate['id'], $cate_ids)) {
                $c_txt = " checked='checked' ";
            }
            $cate_txt .= "<input name='cates[]' type='checkbox' $c_txt value='".$cate['id']."' />".$cate['name']
                         ."&nbsp; &nbsp;";
        }
        return $cate_txt;
    }

    public function getRelatedGames($app_id, $limit = 4) {
        $cateids = $this->get_game_cate_ids($app_id);
        if ($cateids) {
            $cate_id = $cateids[0];
            $apps = $this->find_games_by_cateid($cate_id, 0, $limit);
            return $apps;
        }
    }

    public function getNewsTagTxt($id) {
        $tags = $this->all_news_tag();
        $news_tags = $this->get_news_tags($id);
        $news_tag_list = array();
        foreach ($news_tags as $tag) {
            $news_tag_list[] = $tag['tag_id'];
        }
        $tag_txt = '';
        foreach ($tags as $tag) {
            $c_txt = '';
            if (in_array($tag['id'], $news_tag_list)) {
                $c_txt = " checked='checked' ";
            }
            $tag_txt .= "<input name='tags[]' type='checkbox' $c_txt value='".$tag['id']."' />".$tag['name']
                        ."&nbsp; &nbsp;";
        }
        return $tag_txt;
    }

    public function get_cate_ids($id, $obj_type) {
        $model = M('type_match');
        $items = $model->field("tid")->where(array("obj_id" => "$id", "obj" => "$obj_type"))->select();
        $result = array();
        foreach ($items as $item) {
            $result[] = $item['tid'];
        }
        return $result;
    }

    public function set_game_description($appid, $des) {
        $model = M('game_info');
        $where = array("app_id" => $appid);
        $exist = $model->where($where)->find();
        if ($exist) {
            $model->where($where)->setField("description", $des);
        } else {
            $model->add(array("app_id" => $appid, "description" => $des));
        }
    }

    public function set_game_qqgroup($appid, $qqgroup) {
        $model = M('game_contact');
        $where = array("app_id" => $appid);
        $exist = $model->where($where)->find();
        if ($exist) {
            $model->where($where)->setField("qqgroup", $qqgroup);
        } else {
            $model->add(array("app_id" => $appid, "qqgroup" => $qqgroup));
        }
    }

    public function set_game_packageurl($appid, $v) {
        $model = M('game_version');
        $where = array("app_id" => $appid);
        $exist = $model->where($where)->find();
        if ($exist) {
            $model->where($where)->setField("packageurl", $v);
        } else {
            $model->add(array("app_id" => $appid, "packageurl" => $v));
        }
    }

    public function set_game_cps_packageurl($appid, $v) {
        $model = M('game');
        $where = array("id" => $appid);
        $exist = $model->where($where)->find();
        if ($exist) {
            $model->where($where)->setField("cps_package", $v);
        }
    }

    public function get_game_description($appid) {
        $model = M('game_info');
        $where = array("app_id" => $appid);
        return $model->where($where)->getField("description");
    }

    public function get_game_shots($appid) {
        $model = M('game_shots');
        $where = array("app_id" => $appid);
        $rs = $model->field("shot")->where($where)->select();
        $result = array();
        foreach ($rs as $r) {
            $result[] = $r['shot'];
        }
        return $result;
    }

    public function all_news_tag() {
        $model = M('tag');
        $items = $model->where(array("obj" => "2"))->select();
        return $items;
    }

    public function app_id_exists($app_id) {
        return M('game')->where(array("id" => $app_id))->find();
    }

    public function addVisitLog($mem_id, $app_id, $path_app, $path_model, $path_controller, $path_method) {
        M('visit_log')->add(
            array(
                "create_time"     => time(),
                "mem_id"          => $mem_id,
                "from"            => "mobile",
                "obj_id"          => $app_id,
                "type"            => "game",
                "path_app"        => $path_app,
                "path_model"      => $path_model,
                "path_controller" => $path_controller,
                "path_method"     => $path_method
            )
        );
    }

    public function getAllGames($limit = 0) {
        return M('game')->where(array("is_delete" => "2"))->order("id desc")->limit($limit)->select();
    }

    public function getGameRank() {
        return M('game')->where(array("is_delete" => "2"))->order("pv desc")->limit(10)->select();
    }

    public function getGameStaticById($app_id, $action) {
        return $this->visit_log_model->where(array("type" => "game", "obj_id" => $app_id, "action" => "$action"))
                                     ->count();
    }

    public function GetAllGameStatistics() {
        return M('game')
            ->field(
                "(select count(*) from c_visit_log where (`type` = 'game')  AND (`action`='view')),"
                ."(select count(*) from c_visit_log where (`type` = 'game')  AND (`action`='download'))"
            )
            ->select();
//        return array();
    }

    public function GetAllGameStatistics_old() {
        $apps = $this->getAllGames();
        foreach ($apps as $key => $app) {
            $down_count = $this->getGameStaticById($app['id'], "download");
            $view_count = $this->getGameStaticById($app['id'], "view");
            $apps[$key]['data'] = array(
                "down_count" => $down_count,
                "view_count" => $view_count
            );
        }
        return $apps;
    }

    public function addMobileGameVisitLog($mem_id, $app_id, $path_app, $path_model, $path_controller, $path_method) {
        M('visit_log')->add(
            array(
                "create_time"     => time(),
                "mem_id"          => $mem_id,
                "from"            => "mobile",
                "obj_id"          => $app_id,
                "type"            => "game",
                "path_app"        => $path_app,
                "path_model"      => $path_model,
                "path_controller" => $path_controller,
                "path_method"     => $path_method,
                "action"          => "view"
            )
        );
    }

    public function addMobileGameDownloadLog($mem_id, $app_id, $path_app, $path_model, $path_controller, $path_method) {
        M('visit_log')->add(
            array(
                "create_time"     => time(),
                "mem_id"          => $mem_id,
                "from"            => "mobile",
                "obj_id"          => $app_id,
                "type"            => "game",
                "path_app"        => $path_app,
                "path_model"      => $path_model,
                "path_controller" => $path_controller,
                "path_method"     => $path_method,
                "action"          => "download"
            )
        );
    }

    public function get_app_info($app_id) {
        $model = M('game');
        $where = array("g.id" => $app_id);
        $info = $model
            ->field(
                "g.name,g.icon,g.initial,g.test_status,g.id,g.cps_package,g.category,"
                ."gv.size,gv.version,g.pv,g.download_count,gc.qq,gc.qqgroup,gv.packageurl"
            )
            ->alias("g")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id = g.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_contact gc ON  gc.app_id = g.id")
            ->where($where)
            ->find();
        $info["app_url"] = $this->get_game_package_url($info['initial']);
        $info['type'] = $this->get_game_first_type($app_id);
        $info['type_all'] = $this->get_game_all_type_txt($app_id);
        $static_obj = new \Huosdk\Statistics();
        $info['statistics'] = $static_obj->getFake($info['id']);
        return $info;
    }

    public function get_app_basic_info($app_id) {
        $model = M('game');
        $where = array("g.id" => $app_id);
        $info = $model
            ->field("g.name,g.icon,g.initial,g.id,g.category,gc.qq,gc.qqgroup")
            ->alias("g")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_contact gc ON  gc.app_id = g.id")
            ->where($where)
            ->find();
        return $info;
    }

    public function get_app_benefit_info($app_id) {
        $model = M('game');
        $where = array("g.id" => $app_id);
        $info = $model
            ->field("g.benefit_type,g.agent_first,g.agent_refill,g.mem_first,g.mem_refill")
            ->alias("g")
            ->where($where)
            ->find();
        return $info;
    }

    public function find_games_by_tag_name($name, $limit = 10, $start = 0) {
        $data = M('tag')->where(array("name" => $name, "obj" => "1"))->find();
        if ($data) {
            $tagid = $data['id'];
            $where = "g.id IN (SELECT app_id FROM ".C('DB_PREFIX')."tag_match WHERE tag_id=$tagid )";
            if ($limit >= 1) {
                $items = M('game')
                    ->alias('g')
                    ->where($where)
                    ->limit($start, $limit)
                    ->select();
            } else {
                $items = M('game')
                    ->alias('g')
                    ->where($where)
                    ->select();
            }
            foreach ($items as $key => $val) {
                $items["$key"] = $this->get_app_info($val['id']);
            }
            return $items;
        } else {
            return array();
        }
    }

    public function find_games_by_tag_name_count($name) {
        $data = M('tag')->where(array("name" => $name, "obj" => "1"))->find();
        if ($data) {
            $tagid = $data['id'];
            $where = "g.id IN (SELECT app_id FROM ".C('DB_PREFIX')."tag_match WHERE tag_id=$tagid )";
            $count = M('game')
                ->alias('g')
                ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=g.id")
                ->where($where)
                ->count();
            return $count;
        } else {
            return 0;
        }
    }

    public function index_tc_items() {
        $news = $this->find_news_by_tag_name("头条", 4);
        foreach ($news as $key => $v) {
            $tname = $this->get_news_first_tag($v['id']);
            $news[$key]["tagname"] = mb_substr($tname, 0, 2, 'UTF-8');
        }
        $result = array_slice($news, 1, 3);
        return $result;
    }

    public function index_tc_top_item() {
        $news = $this->find_news_by_tag_name("头条", 1);
        $result = $news[0];
        return $result;
    }

    public function get_news_first_tag($id) {
        $r = M('tag_match')->where(array("app_id" => $id, "obj" => "2"))->find();
        $tagid = $r['tag_id'];
        return M('tag')->where(array("id" => $tagid))->getField("name");
    }

    public function get_game_first_type($id) {
        $r = M('type_match')->where(array("obj_id" => $id, "obj" => "1"))->find();
        $tid = $r['tid'];
        return M('type')->where(array("id" => $tid))->getField("name");
    }

    public function get_game_all_type_txt($id) {
        $rs = M('type_match')
            ->field("t.name")
            ->alias("tm")
            ->where(array("tm.obj_id" => $id, "tm.obj" => "1"))
            ->join("LEFT JOIN ".C("DB_PREFIX")."type t ON t.id=tm.tid")
            ->select();
        $result = '';
        foreach ($rs as $k => $v) {
            $result .= " ".$v['name'];
        }
        return $result;
    }

    public function find_news_by_tag_name($name, $limit = 10, $start = 0) {
        $data = M('tag')->where(array("name" => $name, "obj" => "2"))->find();
        if ($data) {
            $tagid = $data['id'];
//            $field="g.*,gv.version,gv.size";
            $where = "wp.id IN (SELECT app_id FROM ".C('DB_PREFIX')."tag_match WHERE tag_id=$tagid )";
            if ($limit >= 1) {
                $items = M('web_posts')->alias('wp')
                                       ->field("wp.*,g.icon")
                                       ->where($where)
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=wp.app_id")
                                       ->limit($start, $limit)
                                       ->order("wp.id desc")
                                       ->select();
            } else {
                $items = M('web_posts')->alias('wp')
                                       ->field("wp.*,g.icon,g.name")
                                       ->where($where)
                                       ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=wp.app_id")
                                       ->order("wp.id desc")
                                       ->select();
            }
            return $items;
        } else {
            return array();
        }
    }

    public function IncGameDownloadCount($app_id) {
        M('game')->where(array("id" => $app_id))->setInc("download_count");
    }

    public function IncGamePv($app_id) {
        M('game')->where(array("id" => $app_id))->setInc("pv");
    }

    public function handle_tags($obj_id, $obj_type_txt) {
        if ($obj_type_txt == "game") {
            $obj_type = 1;
        } else if ($obj_type_txt == "news") {
            $obj_type = 2;
        } else {
            return;
        }
        $tags = I('tags');
        if ($tags) {
            $dataList = array();
            foreach ($tags as $tag) {
                $dataList[] = array("app_id" => $obj_id, "tag_id" => $tag, "obj" => $obj_type);
            }
            M('tag_match')->where(array("app_id" => $obj_id, "obj" => $obj_type))->delete();
            M('tag_match')->addAll($dataList);
        }
    }

    public function handle_cates($obj_id, $obj_type_txt) {
        if ($obj_type_txt == "game") {
            $obj_type = 1;
        } else if ($obj_type_txt == "news") {
            $obj_type = 2;
        } else {
            return;
        }
        $cates = I('cates');
        if ($cates) {
            $dataList = array();
            foreach ($cates as $cate) {
                $dataList[] = array("obj_id" => $obj_id, "tid" => $cate, "obj" => $obj_type);
            }
            M('type_match')->where(array("obj_id" => $obj_id, "obj" => $obj_type))->delete();
            M('type_match')->addAll($dataList);
        }
    }

//    public function edit_cates($obj_id,$obj_type_txt){
//        if($obj_type_txt=="game"){
//            $obj_type=1;
//        }else if($obj_type_txt=="news"){
//            $obj_type=2;
//        }else{
//            return;
//        }
//        $cates=I('cates');
//
//        if($cates){
//
//            $dataList=array();
//            foreach($cates as $cate){
//                $dataList[]=array("obj_id"=>$obj_id,"tid"=>$cate,"obj"=>$obj_type);
//            }
//            M('type_match')->where(array("obj_id"=>$obj_id,"obj"=>$obj_type))->delete();
//            M('type_match')->addAll($dataList);
//        }
//    }
    public function getAllWyGames() {
        return $this->getAllGameWithCategory(2);
    }

    public function getAllWyGamesDownloadOrder() {
        return $this->getAllGameWithCategory(2, "ga.download_count");
    }

    public function get_catename_by_cateid($cateid) {
        return M('type')->where(array("id" => $cateid))->getField("name");
    }

    public function getAllGameWithCategory($type, $order = 'gi.total') {
        $items = M('game')
            ->field("ga.id,ga.name,ga.icon,gi.total as giftcount,ga.download_count,ga.pv,ga.initial")
            ->alias("ga")
            ->join("LEFT JOIN ".C("DB_PREFIX")."gift gi ON gi.app_id=ga.id")
            ->where(array("category" => $type))
            ->order("$order desc")
            ->select();
        foreach ($items as $k => $item) {
            if (!$item['giftcount']) {
                $items[$k]['giftcount'] = 0;
            }
        }
        foreach ($items as $key => $val) {
            $items["$key"]["app_url"] = $this->get_game_package_url($val['initial']);
        }
        return $items;
    }

    public function getGameInfo($id) {
        return M('game')->where(array("id" => $id))->find();
    }

    public function getGameGift($gid) {
        $r = M('gift')
            ->field("gi.id,gi.title,gi.start_time,gi.total,gi.remain,ga.name,ga.icon")
            ->alias("gi")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game ga ON gi.app_id=ga.id")
            ->where(array("gi.app_id" => $gid, "gi.is_delete" => 2))
            ->select();
        return $r;
    }

    public function getAllDjGames() {
        return $this->getAllGameWithCategory(1);
    }

    public function getGameArticleWithCate($app_id) {
    }

    public function find_game_news_by_cate_name($app_id, $catename) {
        $cateid = M('type')->where(array("obj" => "2", "name" => $catename))->getField("id");
        $news_id_list = M('type_match')->where(array("tid" => $cateid, "obj" => "2"))->getField("obj_id", true);
        if ($news_id_list) {
            $news_id_list_txt = join(",", $news_id_list);
            $items = M('web_posts')->where("app_id=$app_id AND id IN ($news_id_list_txt)")->select();
            return $items;
        } else {
            return array();
        }
    }

    public function getGameTodayOnline() {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $where = array();
        $where['status'] = 2;
        $where['update_time'] = array(array("gt", $today), array("lt", $tomorrow));
        return M('game')->where($where)->order("update_time desc")->select();
    }

    public function getGameAboutOnline() {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $where = array();
        $where['status'] = 2;
        $where['update_time'] = array("gt", $tomorrow);
        return M('game')->where($where)->order("update_time desc")->select();
    }

    public function getGameKfTodayOnline() {
        $where = array();
        $where['ws.status'] = 2;
        return M('web_server')
            ->field("ws.*,g.name,g.icon,g.pv,g.download_count")
            ->alias('ws')
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ws.app_id")
            ->order("ws.start_time desc")
            ->select();
    }

    public function getGameKfAboutOnline() {
        $where = array();
        $where['ws.status'] = 1;
        return M('web_server')
            ->field("ws.*,g.name,g.icon,g.pv,g.download_count")
            ->alias('ws')
            ->where($where)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=ws.app_id")
            ->order("ws.start_time desc")
            ->select();
    }

    public function HotGames() {
        return M('game')->order("pv desc")->select();
    }

    public function newGames() {
        return M('game')->order("create_time desc")->select();
    }

    public function gameInfoComplete($id) {
        return M('game')
            ->alias("g")
            ->where(array("id" => $id))
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id")
            ->join("")
            ->select();
    }

    public function get_game_list($start = 0, $limit = 0) {
        $items = M('game')
            ->where(array("is_hot" => "2", "is_delete" => "2", "status" => "2"))
            ->limit($start, $limit)
            ->order("update_time desc")
            ->select();
        $this->set_app_items_default_icon($items);
        return $items;
    }

    public function set_app_items_default_icon(&$data) {
        $default_icon = "default_app_icon.png";
        foreach ($data as $k => $v) {
            $file = SITE_PATH."/upload/".$v['icon'];
            if (!$v['icon'] || !file_exists($file)) {
                $data[$k]['icon'] = "/upload/".$default_icon;
            }
        }
    }
}

