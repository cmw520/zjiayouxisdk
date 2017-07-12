<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class IosGameController extends AdminbaseController {
    protected $game_model;
    protected $gc_model;
    protected $gv_model;
    protected $term_relationships_model;
    protected $terms_model;
    protected $where;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gc_model = M('game_client');
        $this->gv_model = M('game_version');
        $this->where = 1;
    }

    public function edit() {
        $app_id = intval(I('get.id'));
        if ($app_id > 0) {
            $this->gtype(1);
            $this->gameStatus(1);
            $gamedata = $this->game_model->join($join)->where(
                array(
                    'id' => $app_id
                )
            )->find();
            $infodata = M('game_info')->where(
                array(
                    'app_id' => $app_id
                )
            )->find();
            $extdata = M('game_ext')->where(
                array(
                    'app_id' => $app_id
                )
            )->find();
            $verdata = $this->gv_model->where(
                array(
                    'app_id' => $app_id,
                    'status' => 2
                )
            )->order('id desc')->find();
            /*             $gt_model = M('game_gt');
                        $type_ids = $gt_model->where(array(
                            "app_id" => $app_id
                        ))->getField("type_id", true); */
            $type_ids = explode(',', $gamedata['type']);
            $this->assign("type_ids", $type_ids);
            $this->assign('game', $gamedata);
            $this->assign('gameinfo', $infodata);
            $this->assign('extdata', $extdata);
            $this->assign('verdata', $verdata);
            $this->assign("smeta", json_decode($infodata['image'], true));
            $this->display();
        } else {
            $this->error("参数错误");
        }
    }

    public function edit_post() {
        if (IS_POST) {
            $app_id = I('post.gameid/d', 0);
            if (empty($app_id)) {
                $this->error("参数错误！");
            }
            $ver_id = I('post.verid/d', 0);
            /* 获取POST数据 */
            $game_data['id'] = $app_id;  //游戏名称
            $game_data['name'] = I('post.gamename/s');  //游戏名称
            $game_data['category'] = I('post.gcategory/d');  //单机网游
            //
            $game_data['classify'] = I('post.gclassify/d');  //游戏类别
            $game_data['is_hot'] = I('post.hot/d', 0);  //游戏热门程度
            $game_data['listorder'] = I('post.listorder/d', 0);  //游戏热门程度
            $game_data['packagename'] = I('post.packagename/s', '');  //APP包名
            $gametypes = I('post.gtype');  //游戏标签
            $info_data['publicity'] = I('post.oneword/s');  //一句话描述
            $info_data['description'] = I('post.description');  //游戏详细描述
            $info_data['iosurl'] = I('post.iosurl/s');
            $info_data['adxt'] = I('post.adxt/s');  //安卓版适用系统
            $info_data['size'] = I('post.size/s');  //游戏大小
            $info_data['lang'] = I('post.lang/s', '中文');  //游戏语言
            $ext_data['down_cnt'] = I('post.count/d', 0); //下载次数
            $ext_data['star_cnt'] = I('post.starcnt/d', 10); //游戏评分
//             $game_data['status'] = I('post.gstatus/d');  //当前状态
//             $game_data['is_app'] = 2;  //app游戏
            // $game_data['is_own'] = 1;  //app游戏
            $game_data['update_time'] = time();
            $game_data['run_time'] = time();
            $version = I('post.version/s', '');
            $game_data['mobile_icon'] = I('post.logo');
            $game_data['icon'] = I('post.logo');
            $photourl = I('post.photos_url');
            $photoalt = I('post.photos_alt');
            if (!empty($photoalt) && !empty($photourl)) {
                foreach ($photourl as $key => $url) {
                    $photourl = $url;
                    $imagearr[] = array(
                        "url" => $photourl,
                        "alt" => $photoalt[$key]
                    );
                }
            }
            $info_data['image'] = json_encode($imagearr);
            /* 检测输入参数合法性, 游戏名 */
            if (empty($game_data['name'])) {
                $this->error("游戏名为空，请填写游戏名");
                exit();
            }
            /* 检测输入参数合法性, 游戏标签 */
            if (empty($gametypes)) {
                $this->error("请填写游戏标签!");
                exit();
            } else {
                $game_data['type'] = implode(',', $gametypes);
            }
            /* 检测输入参数合法性,游戏简介 */
            if (empty($info_data['publicity'])) {
                $this->error("请填写一句话描述");
                exit();
            }
            /* 检测输入参数合法性,游戏描述 */
            if (empty($info_data['description'])) {
                $this->error("请填写游戏描述");
                exit();
            }
            /* 检测输入参数合法性, 游戏版本  */
            if (empty($version)) {
                $this->error("游戏版本为空，请填写游戏版本");
            } else {
                $checkExpressions = "/^\d+(\.\d+){0,2}$/";
                $len = strlen($version);
                if ($len > 10 || false == preg_match($checkExpressions, $version)) {
                    $this->error("游戏版本号填写错误，数字与小数点组合");
                }
            }
            /* 上线时间 */
//             if (2 == $game_data['status']) {
//                 $game_data['run_time'] = $game_data['update_time'];
//             }
            // 获取游戏名称拼音
//             import('Vendor.Pin');
//             $pin = new \Pin();
//             $game_data['pinyin'] = $pin->pinyin($game_data['name']);
//             $game_data['initial'] = $pin->pinyin($game_data['name'], true);
//             $game_data['initial'] = $game_data['initial'].'_'.$app_id;
            if ($this->game_model->create($game_data)) {
                $rs = $this->game_model->save();
                /* 插入游戏类型 */
//                $hs_game_edit_obj=new \Huosdk\Game\Edit();
//                $hs_game_edit_obj->icon($app_id, $game_data['icon']);
                if ($rs !== false) {
                    $gv_data = $this->gv_model->where(array('app_id' => $app_id))->find();
                    if (empty($gv_data)) {
                        $gv_data['app_id'] = $app_id;
                        $gv_data['version'] = $version;
                        $gv_data['create_time'] = time();
                        $gv_data['update_time'] = $gv_data['create_time'];
                        $gv_id = $this->gv_model->add($gv_data);
                    } else {
                        //游戏版本保存
                        $gv_data['app_id'] = $app_id;
                        //$gv_data['id'] = $ver_id;
                        $gv_data['version'] = $version;
                        $gv_data['update_time'] = $game_data['update_time'];
                        $this->gv_model->save($gv_data);
                    }
                    $ext_data['app_id'] = $app_id;
                    //game_ext保存
                    $ext_model = M('game_ext');
                    $gext_data = $ext_model->where(array('app_id' => $app_id))->find();
                    if (empty($gext_data)) {
                        $ext_model->add($ext_data);
                    } else {
                        $ext_model->save($ext_data);
                    }
                    //game_info 保存
                    $info_model = M('game_info');
                    $info_data['app_id'] = $app_id;
//                     $info_data['mobile_icon'] = $game_data['icon'];
                    $ginfo_data = $info_model->where(array('app_id' => $app_id))->find();
                    if (empty($ginfo_data)) {
                        $info_model->add($info_data);
                    } else {
                        $info_model->save($info_data);
                    }
                    //游戏标签
                    foreach ($gametypes as $k => $val) {
                        $type_data[$k]['app_id'] = $app_id;
                        $type_data[$k]['type_id'] = $val;
                    }
                    $gtmodel = M('game_gt');
                    $gtmodel->where(
                        array(
                            'app_id' => $game_data['id']
                        )
                    )->delete();
                    $gtmodel->addAll($type_data);
                    $this->success("编辑成功！", U("Game/index"));
                }
            } else {
                $this->error($this->game_model->getError());
            }
            $this->success("编辑失败！");
            exit();
        }
        $this->error('页面不存在');
    }

    public function gtype($option = null) {
        $cates = array(
            0 => "全部类型"
        );
        $typedata = M('game_type')->where(
            array(
                'status' => 2
            )
        )->getfield('id, name');
        if (1 == $option) {
            $this->assign('gtypes', $typedata);

            return;
        }
        if (!empty($typedata)) {
            $cates = $cates + $typedata;
        }
        $this->assign('gtypes', $cates);

        return;
    }

    public function gameStatus($option = null) {
        $gamestatus = array(
            '0' => "选择状态",
            '1' => "程序接入",
            '2' => "上线",
            '3' => "下线"
        );
        if (1 == $option) {
            $gamestatus = array(
                '1' => "程序接入",
                '2' => "上线",
                '3' => "下线"
            );
        }
        $this->assign("gamestatus", $gamestatus);

        return;
    }
}

