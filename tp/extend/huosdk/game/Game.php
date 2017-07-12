<?php
namespace huosdk\game;

use think\Config;
use think\Db;
use think\Log;

class Game {
    private $app_id;
    private $client_id;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'game\Game Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * 构造函数
     *
     * @param $rsa_pri_path string rsa私钥地址
     */
    public function __construct($app_id = 0, $client_id = 0) {
        if (!empty($app_id)) {
            $this->app_id = $app_id;
        }
        if (!empty($client_id)) {
            $this->client_id = $client_id;
        }
    }

    public function getClientkey($client_id = 0) {
        $_client_id = $client_id;
        if (empty($_client_id)) {
            $_client_id = $this->client_id;
        }
        $_map['id'] = $_client_id;
        $_client_key = DB::name('game_client')->where($_map)->value('client_key');

        return $_client_key;
    }

    public function getUpinfo($client_id = 0) {
        $_client_id = $client_id;
        if (empty($_client_id)) {
            $_client_id = $this->client_id;
        }
        $_map['id'] = $_client_id;
        $_ver_id = DB::name('game_client')->where($_map)->value('gv_id');
        if (empty($_ver_id)) {
            return false;
        }
        $_gv_map['id'] = ['gt', $_ver_id];
        $_gv_map['status'] = 2; //并已经上线
        $_ver_data = Db::name('game_version')->where($_gv_map)->order('id desc')->find();
        if (empty($_ver_data) || empty($_ver_data['packageurl'])) {
            return false;
        }
        $_rdata['up_status'] = 2;
        $_rdata['url'] = $_ver_data['packageurl'];
        $_rdata['content'] = $_ver_data['content'];

        return $_rdata;
    }

    public function getGameinfo($app_id = 0) {
        $_app_id = $app_id;
        if (empty($_app_id)) {
            $_app_id = $this->app_id;
        }
        $_game_map['id'] = $_app_id;
        $_game_info = Db::name('game')->where($_game_map)->find();

        return $_game_info;
    }

    /**
     * 获取游戏下载地址
     *
     * @param int $app_id
     * @param int $agent_id
     *
     * @return string 没有获取到返回空串,获取到返回链接
     */
    public function getDownlink($app_id = 0, $agent_id = 0) {
        $_downurl = '';
        if (!empty($agent_id)) {
            $_downurl = $this->getAgDownlink($app_id, $agent_id);
        }
        if (empty($_downurl)) {
            $_gv_map['app_id'] = $app_id;
            $_gv_map['status'] = 2;
            $_packageurl = Db::name('game_version')->where($_gv_map)->order('id desc')->value('packageurl');
            if (!empty($_packageurl)) {
                $_downurl = Config::get('domain.DOWNSITE').DS.$_packageurl;
            }
        }

        return $_downurl;
    }

    public function getAgDownlink($app_id = 0, $agent_id) {
        if (empty($app_id) || empty($agent_id)) {
            return '';
        }
        $_map['agent_id'] = $agent_id;
        $_map['app_id'] = $app_id;
        /* 查询agentid */
        $_downurl = Db::name('agent_game')->cache(60)->where($_map)->value('url');
        if (empty($_downurl)) {
            return '';
        } else {
            /* 获取最新版本信息 */
            $_gv_map['app_id'] = $app_id;
            $_gv_map['status'] = 2;
            $_new_ver_data = Db::name('game_version')->where($_gv_map)->order('id desc')->find();
            if (empty($_new_ver_data)) {
                return '';
            }
            if (!strpos($_downurl, DS.$_new_ver_data['id'].DS)) {
                return '';
            }

            return Config::get('domain.DOWNSITE').DS.$_downurl;
        }
    }

    public function getGamedetail($app_id = 0, $from = 3) {
        $_app_id = $app_id;
        if (empty($_app_id)) {
            $_app_id = $this->app_id;
        }
        if (empty($_app_id)) {
            return null;
        }
        $_join = [
            [
                Config::get('database.prefix').'game_ext ge',
                'g.id =ge.app_id',
                'LEFT'
            ],
            [
                Config::get('database.prefix').'game_info gi',
                'g.game_id =gi.app_id',
                'LEFT'
            ]
        ];
        $_field = [
            'g.id'                          => 'gameid',
            "IFNULL(gi.mobile_icon,g.icon)" => 'icon',
            'g.name'                        => 'gamename',
            'g.type'                        => 'type',
            "IFNULL(gi.size,'')"            => 'size',
            "g.is_hot"                      => 'hot',
            "g.is_new"                      => 'isnew',
            'g.category'                    => 'category',
            'g.teststatus'                  => 'teststatus',
            "IFNULL(ge.down_cnt,'0')"       => 'downcnt',
            "IFNULL(gi.androidurl,'')"      => 'androidurl',
            "IFNULL(gi.iosurl,'')"          => 'iosurl',
            "IFNULL(gi.publicity,'')"       => 'oneword',
            "IFNULL(gi.description,'')"     => 'desc',
            "IFNULL(ge.star_cnt,'0')"       => 'score',
            "IFNULL(ge.like_cnt,'0')"       => 'likecnt',
            "IFNULL(ge.share_cnt,'0')"      => 'sharecnt',
            "IFNULL(gi.iosxt,'')"           => 'iosxt',
            "IFNULL(gi.adxt,'')"            => 'adxt',
            "IFNULL(gi.upinfo,'')"          => 'upinfo',
            "g.run_time"                    => 'runtime',
            "IFNULL(g.packagename,'')"      => 'packagename',
            "IFNULL(gi.image,'')"           => 'image'
        ];
        $_map['g.id'] = $_app_id;
        $_game_info = Db::name('game')
                        ->alias('g')
                        ->field($_field)
                        ->join($_join)
                        ->where($_map)
                        ->find();
        if (empty($_game_info)) {
            return null;
        }
        if (3 == $from) {
            $_game_info['downlink'] = $_game_info['androidurl'];
            $_game_info['sys'] = $_game_info['adxt'];
        } elseif (4 == $from) {
            $_game_info['downlink'] = $_game_info['iosurl'];
            $_game_info['sys'] = $_game_info['iosxt'];
        }
        unset($_game_info['androidurl']);
        unset($_game_info['iosurl']);
        unset($_game_info['iosxt']);
        unset($_game_info['adxt']);
        /* 解析ICON */
        if (strpos($_game_info['icon'], "/") === 0) {
            $_game_info['icon'] = Config::get('domain.STATICSITE').$_game_info['icon'];
        } else {
            $_game_info['icon'] = Config::get('domain.STATICSITE').'/upload/image/'.$_game_info['icon'];
        }
        /* 解析游戏截图 */
        $_game_info['image'] = $this->getShot($_game_info['image']);
        /* 获取游戏开服信息 */
        $_ser_class = new Gameserver();
        $_serlist = $_ser_class->getSeverlist($app_id);
        $_game_info['servercnt'] = 0;
        $_game_info['serlist'] = null;
        if (!empty($_serlist)) {
            $_game_info['servercnt'] = count($_serlist);
            $_game_info['serlist'] = $_serlist;
        }
        /* 将typeid转化为名字 */
        $_gtype_class = new Gametype();
        $_game_info['type'] = $_gtype_class->getTypebyId($_game_info['type']);
        /* 获取游戏版本 */
        $_gv_map['app_id'] = $app_id;
        $_gv_map['status'] = 2;
        $_gv_order = "id DESC ";
        $_game_info['version'] = Db::name('game_version')
                                   ->where($_gv_map)
                                   ->order($_gv_order)
                                   ->limit(1)
                                   ->value('version');
        $_rdata = $_game_info;

        return $_rdata;
    }

    // 获取游戏截图
    public function getShot($image) {
        if (empty($image)) {
            return null;
        }
        $image = json_decode($image, true);
        if (empty($image)) {
            return null;
        }
        $data = null;
        foreach ($image as $k => $v) {
            if (!empty($v['url'])) {
                if (strpos($v['url'], "/") === 0) {
                    $v['url'] = Config::get('domain.STATICSITE').$v['url'];
                }
                $data[] = $v['url'];
            }
        }

        return $data;
    }

    public function getGameList($where) {
        $_map['g.is_app'] = 2; /* app中上线的游戏 */
        $_map['g.is_delete'] = 2; /* 伪删除游戏不显示 */
        /* 热门游戏 */
        if (isset($where['hot']) && 2 == $where['hot']) {
            $_map['g.is_hot'] = $where['hot'];
        }
        /* 1 单机 2 网游 */
        if (isset($where['category']) && !empty($where['category'])) {
            $_map['g.category'] = $where['category'];
        }
        /* 是否新游 */
        if (isset($where['isnew']) && 2 == $where['isnew']) {
            $_map['g.is_new'] = 2;
        }
        /* 搜索游戏名称 */
        if (!empty($where['name'])) {
            $_map['g.name'] = ['like', '%'.$where['name'].'%'];
        }
        /* 游戏来源 1-WEB、2-WAP、3-Android、4-IOS、5-WP */
        if (isset($where['from']) && 3 == $where['from']) {
            $_map['g.classify'] = [
                ['=', 3],
                ['between', '300,399'],
                'or'
            ];
        } elseif (isset($where['from']) && 4 == $where['from']) {
            $_map['g.classify'] = 4;
        }
        $_page = 1;
        if (!empty($where['page'])) {
            $_page = $where['page'];
        }
        $_offset = 10;
        if (!empty($where['offset'])) {
            $_offset = $where['offset'];
        }
        $_glist_class = new Gamelist();
        /* 是否开测 2 开测游戏 1 普通 0 */
        if (isset($where['test']) && 2 == $where['test']) {
            unset($_map['g.is_app']); /* app中测试游戏 */
            return $_glist_class->testList($_map, $_page, $_offset);
        }
        /* 是否开服 2 开服游戏 1 普通 0 */
        if (isset($where['server']) && 2 == $where['server']) {
            return $_glist_class->serverList($_map, $_page, $_offset);
        }
        /* 是否推荐 2 推荐 1 普通 0 所有 */
        if (isset($where['remd']) && 2 == $where['remd']) {
            if (empty($where['hasgift'])) {
                $where['hasgift'] = 1;
            }

            return $_glist_class->remdList($_map, $_page, $_offset, $where['hasgift']);
        }
        if (!empty($where['type'])) {
            return $_glist_class->typeList($_map, $_page, $_offset);
        }
        /* 是否随机推荐 */
        $_rand_flag = false;
        if (!empty($where['rand']) && !empty($where['cnt'])) {
            $_rand_flag = true;
            $_offset = $where['cnt'];
        }
        /* 筛选游戏 */
        if (!empty($where['appstr'])) {
            $_map['g.id'] = ['in', $where['appstr']];
        }

        return $_glist_class->gameList($_map, $_page, $_offset, $_rand_flag);
    }
}