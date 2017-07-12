<?php
namespace Core\Controller;

use Common\Controller\AdminbaseController;

class GameController extends AdminbaseController {
    protected $game_model, $gc_model, $gv_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = M("game");
        $this->gc_model = M('game_client');
        $this->gv_model = M('game_version');
    }

    public function getGameClassId($type) {
        $classify = M('game_class')->where(array("name" => $type))->getField("id");

        return $classify;
    }

    public function add() {
        $game_data['name'] = trim(I('post.name'));
        $game_data['classify'] = I('post.classify/d', 3);
        $_gc_map['id'] = $game_data['classify'];
        $type = M('game_class')->where($_gc_map)->getField('name');
        if (empty($type)) {
            $this->ajaxReturn(array("error" => "1", "msg" => "参数错误"));
            exit;
        }
        /**
         * 刚添加游戏的时候，游戏的状态肯定是接入中
         *
         * 严旭
         * 2016-10-28 23:03:02
         */
        $game_data['status'] = 1;
        $current_time = time();
        $game_data['create_time'] = $current_time;
        $game_data['update_time'] = $current_time;
        /* 检测输入参数合法性, 游戏名 */
        if (empty($game_data['name'])) {
            $this->ajaxReturn(array("error" => "1", "msg" => "游戏名为空，请填写游戏名"));
            exit;
        }
        if ('android' == $type) {
            $type = 'and';
        }
        // 获取游戏名称拼音
        import('Vendor.Pin');
        $pin = new \Pin();
        $game_data['gameflag'] = $pin->pinyin($game_data['name']);
        $game_data['pinyin'] = $pin->pinyin($game_data['name'].$type);
        $game_data['initial'] = $pin->pinyin($game_data['name'].$type, true);
        $checkgame = M('game')->where(array('pinyin' => $game_data['pinyin']))->find();
        if (!empty($checkgame)) {
            if ($checkgame['is_delete'] == 1) {
                $this->ajaxReturn(array("error" => "1", "msg" => "亲，该游戏已在删除列表中存在，如若恢复，请在删除列表中还原！"));
                exit;
            }
            $this->ajaxReturn(array("error" => "1", "msg" => "亲，该游戏已存在"));
            exit;
        }
        $version = '1.0';
        if (!$this->game_model->create($game_data)) {
            $this->ajaxReturn(array("error" => "1", "msg" => $this->game_model->getError()));
            exit;
        }
        $app_id = $this->game_model->add();
        /* 插入游戏类型  */
        if ($app_id > 0) {
            $update_data['app_key'] = md5($app_id.md5($game_data['pinyin'].$game_data['create_time']));
            $update_data['initial'] = $game_data['initial'].'_'.$app_id;
            $update_data['id'] = $app_id;
            /* 查询game_id是否存在 */
            $update_data['game_id'] = $this->game_model
                ->where(array('gameflag' => $game_data['gameflag']))
                ->getField('game_id');
            if (empty($update_data['game_id'])) {
                $update_data['game_id'] = $app_id;
            }
            $this->game_model->save($update_data);
            //游戏版本插入
            $gv_data['app_id'] = $app_id;
            $gv_data['version'] = $version;
            $gv_data['create_time'] = $game_data['create_time'];
            $gv_id = $this->gv_model->add($gv_data);
            //client_id 操作
            $gc_data['app_id'] = $app_id;
            $gc_data['version'] = $version;
            $gc_data['client_key'] = md5($version.md5($game_data['initial'].rand(10, 1000)));
            $gc_data['gv_id'] = $gv_id;
            $gc_data['gv_new_id'] = $gv_id;
            $this->gc_model->add($gc_data);
            $this->ajaxReturn(array("error" => "0", "msg" => "添加成功！"));
        }else{
            $this->ajaxReturn(array("error" => "1", "msg" => $this->game_model->getError()));
        }
    }

    public function edit_post() {
        if (IS_POST) {
            $app_id = I('post.appid/d', 0);
            if (empty($app_id)) {
                $this->error("参数错误！");
            }
            $info_data['url'] = I('url/s', '');
            if (empty($info_data['url'])) {
                unset($info_data['url']);
            }
            $info_data['publicity'] = I('post.oneword/s');  //一句话描述
            $info_data['description'] = I('post.description');  //游戏详细描述
            $info_data['androidurl'] = I('post.androidurl/s');  //安卓版下载地址
            $info_data['adxt'] = I('post.adxt/s');  //安卓版适用系统
            $info_data['size'] = I('post.size/s');  //游戏大小
            $info_data['lang'] = I('post.lang/s', '中文');  //游戏语言
            /* 检测输入参数合法性,游戏简介 */
            if (empty($info_data['publicity'])) {
                $this->error("请填写游戏宣传语");
                exit();
            }
            /* 检测输入参数合法性,游戏描述 */
            if (empty($info_data['description'])) {
                $this->error("请填写游戏描述");
                exit();
            }
            /* 检测输入参数合法性, 游戏版本  */
            $version = I('post.version/s', '');
            if (empty($version)) {
                $this->error("游戏版本为空，请填写游戏版本");
            } else {
                $checkExpressions = "/^\d+(\.\d+){0,2}$/";
                $len = strlen($version);
                if ($len > 10 || false == preg_match($checkExpressions, $version)) {
                    $this->error("游戏版本号填写错误，数字与小数点组合");
                }
            }
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
            $_g_map['id'] = $app_id;
            $_game_id = $this->game_model->where($_g_map)->getField('game_id');
            if (empty($_game_id)) {
                $this->error("参数错误");
            }
            unset($_g_map);
            $_g_map['game_id'] = $_game_id;
            /* 获取POST数据 */
            $game_data['teststatus'] = I('post.teststatus/s', '');  //测试与上线状态
            $game_data['category'] = I('post.gcategory/d');  //单机网游
            $game_data['is_hot'] = I('post.hot/d', 1);  //游戏热门程度
            $game_data['update_time'] = time();
            $game_data['icon'] = I('post.thumb/s', '');
            $game_data['mobile_icon'] = $game_data['icon'];
            $info_data['app_id'] = $_game_id;
            $info_data['mobile_icon'] = $game_data['icon'];
//            $info_data['bigimage'] = I('post.bigimage', '');
            $info_data['upinfo'] = I('post.upinfo/s', '');
            $gametypes = I('post.gtype');  //游戏标签
            /* 检测输入参数合法性, 游戏标签 */
            if (empty($gametypes)) {
                $this->error("请填写游戏标签!");
                exit();
            }
            $game_data['type'] = implode(',', $gametypes);
            $_g_map['game_id'] = $_game_id;
            $_g_map['id'] = array('gt', 0);
            $_rs = $this->game_model->where($_g_map)->save($game_data);
            if (false !== $_rs) {
                $gv_data = $this->gv_model->where(array('app_id' => $app_id))->find();
                if (empty($gv_data)) {
                    $gv_data['app_id'] = $app_id;
                    $gv_data['version'] = $version;
                    $gv_data['status'] = 2;
                    $gv_data['create_time'] = time();
                    $gv_data['update_time'] = $gv_data['create_time'];
                    $this->gv_model->add($gv_data);
                } else {
                    //游戏版本保存
                    $gv_data['app_id'] = $app_id;
                    $gv_data['version'] = $version;
                    $gv_data['status'] = 2;
                    $gv_data['update_time'] = $game_data['update_time'];
                    $this->gv_model->save($gv_data);
                }
                $ext_data['app_id'] = $app_id;
                //game_ext保存
                $ext_model = M('game_ext');
                $gext_data = $ext_model->where(array('app_id' => $app_id))->find();
                if (empty($gext_data)) {
                    $gext_data['down_cnt'] = I('post.count/d', 0); //下载次数
                    $gext_data['app_id'] = $app_id;
                    $ext_model->add($gext_data);
                } else {
                    $gext_data['down_cnt'] = I('post.count/d', 0); //下载次数
                    $ext_model->save($gext_data);
                }
                //game_info 保存
                $info_model = M('game_info');
                $_gi_map['app_id'] = $app_id;
                $ginfo_data = $info_model->where($_gi_map)->find();
                if (empty($ginfo_data)) {
                    $info_model->add($info_data);
                } else {
                    $info_model->save($info_data);
                }
                //游戏标签
                foreach ($gametypes as $k => $val) {
                    $type_data[$k]['app_id'] = $_game_id;
                    $type_data[$k]['type_id'] = $val;
                }
                $gtmodel = M('game_gt');
                $gtmodel->where(array('app_id' => $_game_id))->delete();
                $gtmodel->addAll($type_data);
                $this->success("编辑成功！");
                exit;
            }
            $this->success("编辑失败！");
            exit();
        }
        $this->error('页面不存在');
    }
}

