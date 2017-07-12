<?php
/**
 * Game.php UTF-8
 * 游戏处理接口
 *
 * @date    : 2016年8月18日下午9:46:57
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\api\controller\v1;

use app\api\model\Game as GameModel;
use app\common\controller\Base;
use think\Db;

class Game extends Base {
    private $game_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = DB::name('game');
    }

    /*
     * 请求游戏列表
     */
    public function index() {
        $map['hot'] = $this->request->get('hot/d', 0); // 1 热门 2 精品
        $map['category'] = $this->request->get('category/d', 0); // 1 单机 2 网游
        $map['type'] = $this->request->get('type/d', 0); // 游戏类型id
        // $map['classify'] = $this->request->get('classify/d', 0); // 1其他 2 正版
        $map['cnt'] = $this->request->get('cnt/d', 0); // 获取数量
        $map['rand'] = $this->request->get('rand/d', 0); // 1 表示随机获取
        $map['page'] = $this->request->get('page/d', 1); // 页
        $map['offset'] = $this->request->get('offset/d', 0); // 每页请求数量，默认为10
        $map['rebate'] = $this->request->get('rebate/d', 0); // 是否是返利游戏
        if (2 == $map['rebate']) {
            $map['rebate'] = 2;
        } else {
            unset($map['rebate']);
        }
        $rdata = $this->getGameList($map);
        if (empty($rdata['count'])) {
            return hs_api_responce(404, '无记录');
        }

        return hs_api_responce(200, '请求成功', $rdata);
    }

    /*
     * 获取游戏详情
     */
    public function read() {
        $map['id'] = $this->request->get('gameid/d', 0); // 获取游戏ID
        if (empty($map['id'])) {
            return hs_api_responce(400, '参数错误');
        }
        $field = "g.id gameid,CONCAT('".$this->staticurl."',g.mobile_icon) icon,g.name gamename,g.type ";
        $field .= ",g.run_time runtime,g.category category,g.is_hot hot ";
        $field .= ",IFNULL(ge.down_cnt,0) downcnt,IFNULL(ge.star_cnt,0) score ";
        $field .= ",IFNULL(ge.distype,0) distype,ge.discount discount,ge.rebate rebate ";
        $field .= ",IFNULL(ge.like_cnt,0) likecnt,IFNULL(ge.share_cnt,0) sharecnt ";
        $field .= ",gi.androidurl downlink,gi.publicity oneword,gi.size size, gi.image image";
        $field .= ",gi.lang lang, gi.adxt sys, gi.description disc ";
        $join = [
            [
                'game_ext ge',
                'g.id=ge.app_id',
                'LEFT'
            ],
            [
                'game_info  gi',
                'g.id =gi.app_id',
                'LEFT'
            ]
        ];
        $this->game_model = Db::name('game');
        $game_info = $this->game_model->alias('g')->field($field)->join($join)->where($map)->find();
        $gc_model = DB::name('game_client');
        $game_client_info = $gc_model->alias('gc')->field("gc.gv_id verid, gc.version vername")->where(
            array(
                'app_id' => $map['id']
            )
        )->find();
        if (!empty($game_client_info)) {
            $game_info = array_merge($game_info, $game_client_info);
        }
        $game_info['image'] = $this->getShot($map['id']);
        if (empty($game_info['image'])) {
            $game_info['image'] = null;
        }
        // 获取礼包数量
        $game_info['giftcnt'] = $this->getGiftcntbyGameid($map['id']);
        $ratedata = $this->getmembenefit();
        $game_info['benefit_type'] = $ratedata['benefit_type'];
        $game_info['rate'] = $ratedata['rate'];
        $rdata = $game_info;

        return hs_api_responce(200, '请求成功', $rdata);
    }

    //获取充值比率
    public function getmembenefit() {
        $data['benefit_type'] = 0;
        $data['rate'] = 0;
        $mem_id = $this->mem_id;
        if ($mem_id <= 0) {
            return $data;
        }
        $map['id'] = $mem_id;
        $memdata = DB::name('members')->where($map)->find();
        $gameid = $this->request->get('gameid/d', 0); // 获取游戏ID
        if (empty($gameid)) {
            return hs_api_responce(400, '参数错误');
        }
        //判断是否返利，$benefit_type为2为返利
        $benefitdata = DB::name('agent_game_rate')->where(
            array("agent_id" => $memdata['agent_id'], "app_id" => $gameid)
        )->field("benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate")->find();
        if (empty($benefitdata)) {
            $benefitdata = DB::name('game_rate')->where(array("app_id" => $gameid))->field(
                "benefit_type,mem_rate,first_mem_rate,mem_rebate,first_mem_rebate"
            )->find();
        }
        //首冲跟续冲
        $map['mem_id'] = $mem_id;
        $map['app_id'] = $gameid;
        $map['status'] = 2;
        //flag为4代表APP充值
        $map['flag'] = 4;
        $map['payway'] = array("neq", "0");
        $gcmap = array();
        $gcmap['mem_id'] = $mem_id;
        $gcmap['app_id'] = $gameid;
        $gcmap['status'] = 2;
        //flag为4代表APP充值
        $gcmap['flag'] = 4;
        $gcmap['payway'] = array("neq", "0");
        $checkfirst = DB::name('gm_charge')->where($gcmap)->find();
        //is_first为1代表首冲
        if ($checkfirst) {
            $data['is_first'] = 0;
        } else {
            $data['is_first'] = 1;
        }
        $data['buy_type'] = 0;
        //benefit_type为1折扣，2为返利
        if ($benefitdata['benefit_type'] == 2) {
            $data['rate'] = 0;
            if (!empty($checkfirst)) {
                $data['rate'] = !empty($benefitdata['mem_rebate']) ? $benefitdata['mem_rebate'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['first_mem_rebate']) ? $benefitdata['first_mem_rebate'] : 0;
            }
            $data['benefit_type'] = 2;
        } else if ($benefitdata['benefit_type'] == 1) {
            $data['rate'] = 1;
            if (!empty($checkfirst)) {
                $data['rate'] = !empty($benefitdata['mem_rate']) ? $benefitdata['mem_rate'] : 0;
            } else {
                $data['rate'] = !empty($benefitdata['first_mem_rate']) ? $benefitdata['first_mem_rate'] : 0;
            }
            $data['benefit_type'] = 1;
        }

        return $data;
    }

    // 获取游戏闪屏图
    public function readShot() {
        $gameid = $this->request->get('gameid/d', 0); // 获取游戏ID
        if (empty($gameid)) {
            return hs_api_responce(400, '参数错误');
        }
        $rdata = $this->getShot();
        if (empty($rdata)) {
            return hs_api_responce(404, '无截图');
        }

        return hs_api_responce(200, '获取截图成功', $rdata);
    }

    // 获取礼包数量
    public function getGiftcntbyGameid($app_id = 0) {
        $app_id = (int)$app_id;
        if ($app_id <= 0) {
            return 0;
        }
        $map['app_id'] = $app_id;
        $map['is_delete'] = 2;
        $map['end_time'] = array(
            '>',
            time()
        );

        return DB::name('gift')->where($map)->count();
    }

    /*
     * 标识 请求类型 生成路由规则 对应操作方法（默认）
     * save get game save
     */
    public function save() {
        $rdata = '';

        return hs_api_responce(404, '接口不存在', $rdata);
    }

    /*
     * 标识 请求类型 生成路由规则 对应操作方法（默认）
     * create GET game/create create
     */
    public function create() {
        $rdata = '';

        return hs_api_responce(404, '接口不存在', $rdata);
    }

    /*
     * 标识 请求类型 生成路由规则 对应操作方法（默认）
     * edit GET game/:id/edit edit
     */
    public function edit($id) {
        $rdata = '';

        return hs_api_responce(404, '接口不存在', $rdata);
    }

    /*
     * 标识 请求类型 生成路由规则 对应操作方法（默认）
     * update PUT game/:id update
     * 游戏更新
     */
    public function update($id) {
        $app_id = $id;
        $agent = $this->request->put('agent/d', 0);
        $like = $this->request->put('like/d', 0);
        $star = $this->request->put('star/d', 0);
        $down = $this->request->put('download/d', 0);
        $userid = $this->request->put('userid/d', 0);
        $app_ext_model = Db::name('game_ext');
        $ext_data = $app_ext_model->where('app_id', $id)->find();
        if (empty($ext_data)) {
            $ext_data['app_id'] = $id;
            $ext_data['down_cnt'] = 0;
            $ext_data['install_cnt'] = 0;
            $ext_data['reg_cnt'] = 0;
            $ext_data['star_cnt'] = 0;
            $ext_data['like_cnt'] = 0;
            $ext_data['share_cnt'] = 0;
            $app_ext_model->insert($ext_data);
        }
        if (1 == empty($down)) {
            $ext_data['down_cnt'] += 1;
        }
        if (1 == empty($data['like'])) {
            $ext_data['like_cnt'] += 1;
        }
        if (1 == empty($star)) {
            $ext_data['star_cnt'] += 1;
        }
        $rdata['likecnt'] = $ext_data['like_cnt'] + rand(10, 10000);
        $rdata['starcnt'] = $ext_data['star_cnt'] + rand(10, 10000);
        $rdata['downcnt'] = $ext_data['down_cnt'] + rand(10, 10000);

        return hs_api_responce(201, '请求成功', $rdata);
    }

    /*
     * 标识 请求类型 生成路由规则 对应操作方法（默认）
     * delete DELETE game/:id delete
     */
    public function delete($id) {
        $rdata = '';

        return hs_api_responce(404, '接口不存在', $rdata);
    }

    // 获取游戏列表
    private function getGameList(array $where = array()) {
        $rdata = array();
        $map['g.is_app'] = 2; // app中上线的游戏
        $map['g.is_delete'] = 2; // 伪删除游戏不显示
        // $map['g.status'] = 2; // 游戏上线才显示
        $data = array();
        $page = $where['page'];
        $offset = 10;
        if (!empty($where['hot'])) {
            $map['g.is_hot'] = $where['hot'];
        }
        if (!empty($where['category'])) {
            $map['g.category'] = $where['category'];
        }
        if (!empty($where['classify'])) {
            $map['g.classify'] = $where['classify'];
        }
        if (!empty($where['offset'])) {
            $offset = $where['offset'];
        }
        $game_model = new GameModel();
        $field = "g.id gameid,CONCAT('".$this->staticurl."',g.mobile_icon) icon,g.name gamename,g.type ";
        $field .= ",g.run_time runtime,g.category category,g.is_hot hot ";
        $field .= ",IFNULL(ge.down_cnt,0) downcnt,IFNULL(ge.star_cnt,0) score ";
        $field .= ",IFNULL(ge.distype,0) distype,ge.discount discount,ge.rebate rebate ";
        $field .= ",IFNULL(ge.like_cnt,0) likecnt,IFNULL(ge.share_cnt,0) sharecnt ";
        $field .= ",gi.androidurl downlink,gi.publicity oneword,gi.size ";
        if (empty($where['type'])) {
            $join = [
                [
                    'game_ext ge',
                    'g.id=ge.app_id',
                    'LEFT'
                ],
                [
                    'game_info  gi',
                    'g.id =gi.app_id',
                    'LEFT'
                ]
            ];
        } else {
            $join = [
                [
                    'game_gt ggt',
                    'g.id=ggt.app_id',
                    'RIGHT'
                ],
                [
                    'game_ext ge',
                    'g.id=ge.app_id',
                    'LEFT'
                ],
                [
                    'game_info  gi',
                    'g.id =gi.app_id',
                    'LEFT'
                ]
            ];
            $map['ggt.type_id'] = $where['type'];
        }
        $count = $game_model->alias('g')->join($join)->where($map)->count();
        if ($count > 0) {
            $m = ($page - 1) * $offset;
            if (!empty($where['cnt'])) {
                $offset = $where['cnt'];
                if ($count > $offset) {
                    $m = rand(0, $count - $offset);
                } else {
                    $m = 0;
                }
            }
            $limit = $m.','.$offset;
            $data = $game_model
                ->alias('g')
                ->field($field)
                ->join($join)
                ->where($map)
                ->order('listorder desc')
                ->limit($limit)
                ->select();
            $count = count($data);
            if ($count > 0) {
                //设置折扣返利
                $this->setBenefit($data);
            }
        }
        $rdata['count'] = $count;
        $rdata['game_list'] = $data;

        return $rdata;
    }

    private function setBenefit(array &$data) {
        if (empty($data)) {
            return;
        }
        //获取所有游戏id
        $idarr = array();
        foreach ($data as $key => $val) {
            $idarr[] = $val['gameid'];
        }
        $ids = implode(',', $idarr);
        $map['app_id'] = ['in', $ids];
        $map['promote_switch'] = 2;
        $bearr = Db::name('game_rate')->where($map)->column('benefit_type,mem_rate,mem_rebate', 'app_id');
        foreach ($data as $key => $val) {
            if (empty($bearr[$val['gameid']])) {
                $bearr[$val['gameid']]['benefit_type'] = 0;
                $bearr[$val['gameid']]['mem_rate'] = 1;
                $bearr[$val['gameid']]['mem_rebate'] = 0;
            }
            $data[$key]['distype'] = $bearr[$val['gameid']]['benefit_type'];
            $data[$key]['discount'] = $bearr[$val['gameid']]['mem_rate'];
            $data[$key]['rebate'] = $bearr[$val['gameid']]['mem_rebate'];
            $data[$key]['giftcnt'] = $this->getGiftcntbyGameid($val['gameid']);
        }

        return;
    }

    public function getRecList(array $where = array()) {
        $rdata = array();
        $map['g.is_app'] = 2; // app中上线的游戏
        $map['g.is_delete'] = 2; // 伪删除游戏不显示
        $data = array();
        $page = $where['page'];
        $offset = $where['offset'];
        if (!empty($where['category'])) {
            $map['g.category'] = $where['category'];
        }
        $field = array(
            'g.id'                                         => 'gameid',
            "CONCAT('".$this->staticurl."',g.mobile_icon)" => 'icon',
            'g.name'                                       => 'gamename',
            'g.type'                                       => 'type',
            'gi.size'                                      => 'size',
            'g.category'                                   => 'category',
            'IFNULL(ge.down_cnt,0)'                        => 'downcnt',
            'IFNULL(ge.distype,0)'                         => 'distype',
            'IFNULL(ge.discount,1)'                        => 'discount',
            'IFNULL(ge.rebate,0)'                          => 'rebate',
            'gi.androidurl'                                => 'downlink',
            'gi.publicity'                                 => 'oneword',
            'IFNULL(ge.star_cnt,10)'                       => 'score',
            'g.run_time'                                   => 'runtime',
            'g.packagename'                                => 'packagename',
            "CONCAT('".$this->staticurl."',gr.image)"      => 'bigimage',
        );
        $join = [
            [
                'game g',
                'g.id=gr.app_id',
                'LEFT'
            ],
            [
                'game_ext ge',
                'gr.app_id=ge.app_id',
                'LEFT'
            ],
            [
                'game_info  gi',
                'gr.app_id =gi.app_id',
                'LEFT'
            ]
        ];
        $gr_model = DB::name('game_recmd');
        $count = $gr_model->alias('gr')->join($join)->where($map)->count();
        if ($count > 0) {
            $m = ($page - 1) * $offset;
            $limit = $m.','.$offset;
            $order = "gr.listorder desc";
            $data = $gr_model->alias('gr')->field($field)->join($join)->where($map)->order($order)->limit($limit)
                             ->select();
            $count = count($data);
        }
        $rdata['count'] = $count;
        $rdata['game_list'] = $data;

        return $rdata;
    }

    /*
     * 游戏下载接口
     */
    public function down() {
        $data['ver_id'] = $this->request->get('verid/d', 0);
        $data['mem_id'] = $this->mem_id;
        $data['app_id'] = $this->request->get('gameid/d', 0);
        $data['agentname'] = $this->agent;
        $data['agent_id'] = $this->agent_id;
        $data['openudid'] = $this->request->get('openudid', '');
        $data['deviceid'] = $this->request->get('deviceid', '');
        $data['devicetype'] = $this->request->get('devicetype', '');
        $data['deviceinfo'] = $this->request->get('deviceinfo', '');
        $data['idfa'] = $this->request->get('idfa/s', '');
        $data['idfv'] = $this->request->get('idfv/s', '');
        $data['mac'] = $this->request->get('mac/s', '');
        $data['resolution'] = $this->request->get('resolution/s', '');
        $data['network'] = $this->request->get('network/s', '');
        $data['userua'] = $this->request->get('userua/s', '');
        $data['create_time'] = time();
        $data['ip'] = $this->request->ip();
        if (empty($data['app_id'])) {
            return hs_api_responce(404, '未找到下载地址');
        }
        $rs = DB::name('game_downlog')->insert($data);
        $from = $this->request->get('from/d', 0);
        if (empty($from)) {
            return hs_api_responce(404, '未找到下载地址');
        }
        if (3 == $from) {
            $url = 'androidurl';
        } elseif (4 == $from) {
            $url = 'iosurl';
        } else {
            $url = 'url';
        }
        $rdata['url'] = DB::name('game_info')->where(
            array(
                'app_id' => $data['app_id']
            )
        )->value($url);
        if (empty($rdata['url'])) {
            return hs_api_responce(404, '未找到下载地址');
        }
        //更新下载数据
        DB::name('game_ext')->where('app_id', $data['app_id'])->setInc('down_cnt');
        $rdata['downcnt'] = DB::name('game_ext')->where('app_id', $data['app_id'])->value('down_cnt');
        // 修复
        if (strpos($rdata['url'], "http") != 0) {
            if (strpos($rdata['url'], "/") === 0) {
                $rdata['url'] = $this->downurl.'/'.$rdata['url'];
            } else {
                $rdata['url'] = $this->downurl.$rdata['url'];
            }
        }

        return hs_api_responce(200, '请求成功', $rdata);
    }

    // 获取游戏截图
    public function getShot($app_id = 0) {
        if (empty($app_id)) {
            return array();
        }
        $image = DB::name('game_info')->where(
            array(
                'app_id' => $app_id
            )
        )->value('image');
        if (empty($image)) {
            return array();
        }
        $image = json_decode($image, true);
        if (empty($image)) {
            return array();
        }
        foreach ($image as $k => $v) {
            if (!empty($v['url'])) {
                if (strpos($v['url'], "/") === 0) {
                    $v['url'] = $this->staticurl.$v['url'];
                }
                $data[] = $v['url'];
            }
        }

        return $data;
    }
}
