<?php
/*
**游戏管理
**/
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class GameController extends AdminbaseController {
    protected $game_model, $gc_model, $gv_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gc_model = M('game_client');
        $this->gv_model = M('game_version');
    }

    /*
     * 获取对接参数
     */
    public function get_param() {
        $app_id = I('appid', 0);
        $param = $this->game_model->field('id app_id, name gamename, app_key')->where(array('id' => $app_id))->find();
        $client = $this->gc_model->field('id client_id,client_key')->where(array('app_id' => $app_id))->order('id DESC')
                                 ->find();
        $data = array_merge($param, $client);
        $this->assign('params', $data);
        $this->display();
    }

    /**
     * 游戏列表
     */
    public function index() {
        $this->_game(false, null, null, 2, null, 3, true);
        $this->_game_from();
        $this->_game_status();
        $this->_gList();
        $this->display();
    }

    /**
     * 删除列表
     */
    public function delindex() {
        $this->_game_status();
        $this->_gList(true);
        $this->display();
    }

    /**
     * 游戏列表
     */
    public function _gList($is_delete = false) {
        $status = I('status/d', 0);
        $name = I('name', '', 'trim');
        $_classify = I('classify/d', 0);
        $app_id = I('app_id', 0, 'trim');
        if ($is_delete) {
            $where_ands = array('g.is_delete=1');
        } else {
            $where_ands = array('g.is_delete=2');
        }
        array_push($where_ands, " g.is_own = 2 ");
        if (isset($name) && !empty($name)) {
            array_push($where_ands, "g.name like '%$name%'");
        }
        if (isset($status) && !empty($status)) {
            array_push($where_ands, "g.status = $status");
        }
        if (isset($app_id) && !empty($app_id)) {
            array_push($where_ands, "g.game_id = $app_id");
            $name = $this->game_model->where(array('id' => $app_id))->getField('name');
        }
        if (empty($_classify)) {
            $where_ands['_string'] = "(g.classify=3 OR g.classify BETWEEN 300 AND 399)";
        } else {
            array_push($where_ands, "g.classify = $_classify");
        }
        $where = join(" AND ", $where_ands);
        $count = $this->game_model
            ->alias('g')
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "g.*, gv.packageurl,gi.mobile_icon m_icon";
        $items = $this->game_model
            ->alias('g')
            ->field($field)
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_version gv ON gv.app_id=g.id AND gv.status=2")
            ->join("LEFT JOIN ".C('DB_PREFIX')."game_info gi ON gi.app_id=g.game_id")
            ->where($where)
            ->order("g.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach ($items as $_k => $_v) {
            if (!empty($_v['m_icon'])) {
                if (!strpos($_v['m_icon'], 'upload')) {
                    $items[$_k]['icon'] = '/upload/image/'.$_v['m_icon'];
                } else {
                    $items[$_k]['icon'] = $_v['m_icon'];
                }
            }
        }
        $this->assign("formget", $_GET);
        $this->assign("items", $items);
        $this->assign("status", $status);
        $this->assign("name", $name);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    /*
     * 添加游戏
     */
    public function add() {
        $this->_game_status(1);
        $this->display();
    }

    /*
     * 编辑游戏
     */
    public function edit() {
        $app_id = I('id/d', 0);
        if ($app_id > 0) {
            $this->_game_type(0);
            $this->_game_status(1);
            $_g_map['id'] = $app_id;
            $gamedata = $this->game_model->join($join)->where($_g_map)->find();
            if (empty($gamedata['game_id'])) {
                $this->error("游戏不存在");
            }
            $has_ios = 0;
            $has_and = 0;
            if (4 == $gamedata['classify']) {
                $has_ios = '1';
            }
            if ('3' == substr($gamedata['classify'], 0, 1)) {
                $has_and = '1';
            }
            $this->assign('has_ios', $has_ios);
            $this->assign('has_and', $has_and);
            $_gi_map['app_id'] = $gamedata['game_id'];
            $infodata = M('game_info')->where($_gi_map)->find();
            $_gv_map = $_gi_map;
            $_gv_map['status'] = 2;
            $verdata = $this->gv_model->where($_gv_map)->order('id desc')->find();
            if (!empty($infodata['bigimage']) && false === strpos($infodata['bigimage'], 'upload')) {
                $infodata['bigimage'] = '/upload/image/'.$infodata['bigimage'];
            }
            if (!empty($infodata['mobile_icon']) && false === strpos($infodata['mobile_icon'], 'upload')) {
                $infodata['mobile_icon'] = '/upload/image/'.$infodata['mobile_icon'];
            }
            if (!empty($gamedata['icon']) && false === strpos($gamedata['icon'], 'upload')) {
                $infodata['icon'] = '/upload/'.$gamedata['icon'];
            }
            $type_ids = explode(',', $gamedata['type']);
            $this->assign("type_ids", $type_ids);
            $this->assign('game', $gamedata);
            $this->assign('gameinfo', $infodata);
            $this->assign('verdata', $verdata);
            $this->assign("smeta", json_decode($infodata['image'], true));
            $this->display();
        } else {
            $this->error("参数错误");
        }
    }

    public function add_post() {
        if (IS_POST) {
            /* 获取POST数据 */
            $game_data['name'] = trim(I('post.gamename'));
            $version = I('post.version/s', '1.0');
            $game_data['status'] = I('post.gstatus', 1);
            $game_data['create_time'] = time();
            $game_data['update_time'] = $game_data['create_time'];
            $iconfile = $_FILES['logo'];
            $game_benefit_data['agent_rate'] = I('post.agent_rate');
            $game_benefit_data['game_rate'] = I('post.game_rate');
            /* 检测输入参数合法性, 游戏名 */
            if (empty($game_data['name'])) {
                $this->error("游戏名为空，请填写游戏名");
            }
            $checkgame = $this->game_model->where(array('name' => $game_data['name']))->find();
            if (!empty($checkgame)) {
                if ($checkgame['is_delete'] == 1) {
                    $this->error("亲，该游戏已在删除列表中存在，如若恢复，请在删除列表中还原！");
                    exit;
                }
                $this->error("亲，该游戏已添加");
                exit;
            }
            /* 检测输入参数合法性, 游戏分成比例  */
            if (empty($game_benefit_data['game_rate'])) {
                $this->error("CP分成比例为空，请填写CP分成比例");
            } else {
                $game_benefit_data['game_rate'] = (float)$game_benefit_data['game_rate'];
                if (empty($game_benefit_data['game_rate']) || $game_benefit_data['game_rate'] < 0
                    || $game_benefit_data['game_rate'] > 1
                ) {
                    $this->error("CP分成比例填写错误，请填写正确比例");
                }
            }
            /* 检测输入参数合法性, 渠道分成比例  */
            if (empty($game_benefit_data['agent_rate'])) {
                $this->error("渠道分成比例为空，请填写渠道分成比例");
            } else {
                $game_benefit_data['agent_rate'] = (float)$game_benefit_data['agent_rate'];
                if (empty($game_benefit_data['agent_rate']) || $game_benefit_data['agent_rate'] < 0
                    || $game_benefit_data['agent_rate'] > 1
                ) {
                    $this->error("渠道分成比例填写错误，请填写正确比例");
                }
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
            /* 检测输入参数合法性, 游戏LOGO */
            if (empty($iconfile['name'])) {
                $this->error("游戏LOGO为空!");
            }
            // 获取游戏名称拼音
            import('Vendor.Pin');
            $pin = new \Pin();
            $game_data['pinyin'] = $pin->pinyin($game_data['name']);
            $game_data['initial'] = $pin->pinyin($game_data['name'], true);
            /* 上传LOGO 文件 */
            $logoinfo = $this->upload($iconfile, '', 'logo'.$game_data['initial'].'_'.$game_data['create_time']);
            if (0 == $logoinfo['status']) {
                $this->error($logoinfo['msg']);
            } else {
                $game_data['icon'] = $logoinfo['msg'];
            }
            if ($this->game_model->create($game_data)) {
                $app_id = $this->game_model->add();
                /* 插入游戏类型  */
                if ($app_id > 0) {
                    $hs_benefit_obj = new \Huosdk\Benefit();
                    $hs_benefit_obj->set_app_benefit_info($app_id, $game_benefit_data);
                    $update_data['app_key'] = md5($app_id.md5($game_data['pinyin'].$game_data['create_time']));
                    $update_data['initial'] = $game_data['initial'].'_'.$app_id;
                    $update_data['id'] = $app_id;
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
                    $this->success("添加成功！", U("Game/index"));
                }
            } else {
                $this->error($this->game_model->getError());
            }
            exit;
        }
        $this->error('页面不存在');
    }

    private function upload_icon($app_id) {
        $image_fp = '';
        if (isset($_FILES['icon']) && ($_FILES['icon']['name'])) {
            $upload_dir = SITE_PATH.'upload/';
            $allow_exts = array("image/jpeg", "image/jpg", "image/png");
            $maxSize = 10 * 1024 * 1024;
            if (($_FILES['icon']['error'] == UPLOAD_ERR_OK)) { //PHP常量UPLOAD_ERR_OK=0，表示上传没有出错
                $temp_name = $_FILES['icon']['tmp_name'];
                $extension = $this->get_extension($_FILES['icon']['name']);
                $file_name = "icon_".$app_id.".".$extension;
                $size = $_FILES['icon']['size'];
                $ext = $_FILES['icon']['type'];
                if (in_array($ext, $allow_exts) && $size <= $maxSize) {
                    $new_fp = $upload_dir.$file_name;
                    if (file_exists($new_fp)) {
                        unlink($new_fp);
                    }
                    move_uploaded_file($temp_name, $new_fp);
                    $image_fp = '/upload/'.$file_name;
                }
            }
        }

        return $image_fp;
    }

    function get_extension($file) {
        return end(explode('.', $file));
    }

    public function edit_post() {
        if (IS_POST) {
            $game_data['id'] = I('appid/d');
            /* 获取POST数据 */
//	        $game_data['name']        = trim(I('post.gamename'));
//	        $game_data['agent_rate']  = I('post.agent_rate');
//	        $game_data['game_rate']   = I('post.game_rate');
// 	        $version   = I('post.version');
            $game_data['update_time'] = time();
            /* 检测输入参数合法性, 游戏ID */
            if (empty($game_data['id'])) {
                $this->error("参数错误");
            }
            /* 检测输入参数合法性, 游戏名 */
//	        if (empty($game_data['name'])) {
//	            $this->error("游戏名为空，请填写游戏名");
//	        }
            /* 检测输入参数合法性, 游戏分成比例  */
//	        if (empty($game_data['game_rate'])) {
//	            $this->error("CP分成比例为空，请填写CP分成比例");
//	        }else{
//	            $game_data['game_rate'] = (float)$game_data['game_rate'];
//	            if (empty($game_data['game_rate'])  || $game_data['game_rate'] < 0 || $game_data['game_rate'] > 1){
//	                $this->error("CP分成比例填写错误，请填写正确比例");
//	            }
//	        }
            /* 检测输入参数合法性, 渠道分成比例  */
//	        if (empty($game_data['agent_rate'])) {
//	            $this->error("渠道分成比例为空，请填写渠道分成比例");
//	        }else{
//	            $game_data['agent_rate'] = (float)$game_data['agent_rate'];
//	            if (empty($game_data['agent_rate']) || $game_data['agent_rate'] < 0 || $game_data['agent_rate'] > 1){
//	                $this->error("渠道分成比例填写错误，请填写正确比例");
//	            }
//	        }
//            if(empty($game_data['rebate'])){
//                $this->error("游戏默认自然流水返点不能为空");
//            }else{
//                $rebate=(float)$game_data['rebate'];
//                if($rebate<0 || $rebate>1){
//                    $this->error("游戏默认自然流水返点必须是介于0和1之间的小数");
//                }                
//            }
            $hs_upload_obj = new \Huosdk\Upload();
            $appid = $game_data['id'];
            $image_fp = $hs_upload_obj->image_upload("icon");
            if ($image_fp) {
                M('game')->where(array("id" => $appid))->setField("icon", $image_fp);
            }
            /* 检测输入参数合法性, 游戏版本  */
// 	        if (empty($version)) {
// 	            $this->error("游戏版本为空，请填写游戏版本");
// 	        }else{
// 	            $checkExpressions = "/^\d+(\.\d+){0,2}$/";
// 	            $len = strlen($version);
// 	            if ($len>10 || false == preg_match($checkExpressions, $version)){
// 	                $this->error("游戏版本号填写错误，数字与小数点组合");
// 	            }
// 	        }
            $app_id = $game_data['id'];
//            $hs_benefit_obj=new \Huosdk\Benefit();
//            
//            $game_benefit_data=array();
//            $game_benefit_data['agent_rate']=$game_data['agent_rate'];
//            $game_benefit_data['game_rate']=$game_data['game_rate'];
//            $hs_benefit_obj->set_app_benefit_info($app_id,$game_benefit_data);
            $game_self_data = array();
//            $game_self_data['name']=$game_data['name'];
            $game_self_data['update_time'] = $game_data['update_time'];
            $this->game_model->where(array("id" => $appid))->setField("update_time", time());
//	        if($this->game_model->create($game_self_data)){
//	            $rs = $this->game_model->save();
            /* 更新游戏版本  */
// 	            if($rs>0){
// 	                //游戏版本插入
// 	                $gv_data['app_id'] = $game_data['id'];
// 	                $gv_data['version'] = $version;
// 	                $gv_data['update_time'] = $game_data['update_time'];
// 	                $rs = $this->gv_model->where(array('app_id'=>$app_id))->save($gv_data);
// 	                //client_id 操作
// 	                $gc_data['app_id'] = $game_data['id'];
// 	                $gc_data['version'] = $version;
// 	                $this->gc_model->where(array('app_id'=>$app_id))->save($gc_data);
            $this->success("更新成功！", U("Game/index"));
// 	            }
//	        }else{
//	            $this->error($this->game_model->getError());
//	        }
        } else {
            $this->error('页面不存在');
        }
    }

    /**
     * 添加游戏回调
     */
    public function addurl() {
        $appid = I("appid");
        $games = $this->game_model->where("id = %d", $appid)->find();
        if ($games) {
            $this->assign("games", $games);
        } else {
            $this->error("请生成参数对接后,再添加回调");
        }
        $this->assign("games", $games);
        $this->display();
    }

    /**
     * 渠道添加游戏回调
     */
    public function addurl_post() {
        $appid = I("appid");
        $cpurl = I("post.cpurl", "", "trim");
        if (empty($cpurl)) {
            $this->error("请填写回调地址");
        }
        $checkExpressions = '|^http://|';
        $httpsExpressions = '|^https://|';
        if (false == preg_match($checkExpressions, $cpurl) && false == preg_match($httpsExpressions, $cpurl)) {
            $this->error("请输入正确的回调地址http://或者https://开头");
        }
        $g_data['id'] = $appid;
        $g_data['update_time'] = time();
        $g_data['cpurl'] = $cpurl;
        $rs = $this->game_model->where(array('id' => $appid))->save($g_data);
        if (false != $rs) {
            $this->success("添加成功！", U("Game/index"));
            exit;
        } else {
            $this->error("添加失败！");
        }
    }

    /**
     * 修改游戏回调
     */
    public function editurl() {
        $appid = I("appid");
        $games = $this->game_model->where("id = %d", $appid)->find();
        $this->assign("games", $games);
        $this->display();
    }

    /**
     * 修改游戏回调
     */
    public function editurl_post() {
        $appid = I("appid");
        $cpurl = I("post.cpurl", "", "trim");
        if (empty($cpurl)) {
            $this->error("请填写回调地址");
        }
        $checkExpressions = '|^http://|';
        $httpsExpressions = '|^https://|';
        if (false == preg_match($checkExpressions, $cpurl) && false == preg_match($httpsExpressions, $cpurl)) {
            $this->error("请输入正确的回调地址http://或者https://开头");
        }
        $g_data['id'] = $appid;
        $g_data['update_time'] = time();
        $g_data['cpurl'] = $cpurl;
        $rs = $this->game_model->where(array('id' => $appid))->save($g_data);
        if (false != $rs) {
            $this->success("修改成功！", U("Game/index"));
            exit;
        } else {
            $this->error("修改失败！");
        }
    }

    /**
     * 添加游戏母包
     */
    public function addpackageurl() {
        $appid = I("appid");
        $games = $this->gv_model->where("app_id=%d", $appid)->find();
        $initial = $this->game_model->where("id = %d", $appid)->getField('initial');
        if (empty($games)) {
            $this->error("请生成参数对接后,再添加游戏母包地址");
        }
        $opt = md5(md5($initial.$initial).'resub');
        $pinyin = base64_encode($initial);
        $agentgame = base64_encode($initial);
        $opt = base64_encode($opt);
        $data_string = array('p' => $pinyin, 'a' => $agentgame, 'o' => $opt);
        $data_string = json_encode($data_string);
        $url = DOWNIP."/sub.php";
        $cnt = 0;
        $flag = false;
        while (1) {
            $return_content = base64_decode(http_post_data($url, $data_string));
            if (!is_int($return_content) && strlen($return_content) > 20) {
                $flag = true;
                break;
            }
            if (0 < $return_content || 3 == $cnt) {
                break;
            }
            $cnt++;
        }
        //若存在则更新地址
        if (true == $flag) {
            $games['packageurl'] = $initial.'/'.$initial.'.apk';
            $this->gv_model->save($games);
            $apkdata = (array)json_decode($return_content);
            if (!empty($apkdata)) {
                if (empty($apkdata['size'])) {
                    $apkdata['size'] = 0;
                }
                //游戏版本插入
                $games['version'] = $apkdata['vername'];
                $games['size'] = $apkdata['size'];
                $gv_id = $this->gv_model->save($games);
                $gi_info = M('game_info')->where(array('app_id' => $appid))->find();
                $gi_info['androidurl'] = DOWNSITE.''.$games['packageurl'];
                $gi_info['size'] = format_file_size($apkdata['size']);
                if (empty($gi_info['app_id'])) {
                    $downurl['android']['local'] = $gi_info['androidurl'];
                    $gi_info['downurl'] = json_encode($downurl);
                    $gi_info['app_id'] = $appid;
                    M('game_info')->add($gi_info);
                } else {
                    M('game_info')->save($gi_info);
                }
                //游戏报名插入
                $this->game_model->where(array('id' => $appid))->setField('packagename', $apkdata['pakagename']);
            }
            $this->redirect('sdk/game/index');
        }
        $this->assign('initial', $initial);
        $this->assign("games", $games);
        $this->display();
    }

    /**
     * 渠道添加游戏母包
     */
    public function addpackageurl_post() {
        $appid = I("appid/d");
        $gv_id = I("id/d");
        if (empty($appid)) {
            $this->error("参数错误");
        }
        if (empty($gv_id)) {
            $this->error("参数错误");
        }
        $packageurl = I("post.packageurl", "", "trim");
        if (empty($packageurl)) {
            $this->error("请填写回调地址");
        }
        $checkExpressions = '|^http://|';
        $httpsExpressions = '|^https://|';
        if (false == preg_match($checkExpressions, $packageurl)
            && false == preg_match(
                $httpsExpressions, $packageurl
            )
        ) {
            $this->error("请输入正确的游戏母包地址http://或者https://开头");
        }
        $g_data['id'] = $gv_id;
        $g_data['app_id'] = $appid;
        $g_data['packageurl'] = $packageurl;
        $rs = $this->gv_model->where(array('id' => $gv_id))->save($g_data);
        if (false != $rs) {
            $this->success("添加成功！", U("Game/index"));
            exit;
        } else {
            $this->error("地址已存在，添加失败！");
        }
    }

    /**
     * 修改游戏母包
     */
    public function editpackageurl() {
        $appid = I("appid");
        $games = $this->gv_model->where("app_id = %d", $appid)->order('id desc')->find();
        $this->assign("games", $games);
        $this->display();
    }

    /**
     * 修改游戏母包POST
     */
    public function editpackageurl_post() {
        $appid = I("appid");
        $gv_id = I("id/d");
        $packageurl = I("post.packageurl", "", "trim");
        if (empty($packageurl)) {
            $this->error("请填写母包地址");
        }
        $checkExpressions = '|^http://|';
        $httpsExpressions = '|^https://|';
        if (false == preg_match($checkExpressions, $packageurl)
            && false == preg_match(
                $httpsExpressions, $packageurl
            )
        ) {
            $this->error("请输入正确的母包地址http://或者https://开头");
        }
        $g_data['id'] = $gv_id;
        $g_data['update_time'] = time();
        $g_data['packageurl'] = $packageurl;
        $rs = $this->gv_model->where(array('id' => $gv_id))->save($g_data);
        if (false != $rs) {
            $this->success("修改成功！", U("Game/index"));
            exit;
        } else {
            $this->error("修改失败！");
        }
    }

    /**
     * 删除游戏
     */
    public function delGame() {
        $id = I('id', 0);
        $data['is_delete'] = 1;
        $rs = $this->game_model->where("id = %d", $id)->save($data);
        if ($rs) {
            $this->success("删除成功", U("Game/delindex", array('appid' => $id)));
            exit;
        }
        $this->error('删除失败.');
    }

    /**
     * 还原游戏
     */
    public function restoreGame() {
        $id = I('id/d', 0);
        $data['is_delete'] = 2;
        $rs = $this->game_model->where("id = %d", $id)->save($data);
        if ($rs) {
            $this->success("还原成功", U("Game/index", array('appid' => $id)));
            exit;
        }
        $this->error('请求失败.');
    }

    /**
     * 游戏状态处理
     */
    public function set_status() {
        $id = I('id', 0);
        $status = I('status', 0);
        if (empty($status)) {
            $this->error("状态错误");
        }
        if (2 == $status) {
            $g_data = $this->game_model->where(array('id' => $id))->find();
            if (empty($g_data['cpurl'])) {
                $this->error("请填写回调地址");
            }
            $gv_id = $this->gc_model->where(array('app_id' => $id))->getField('gv_id');
            $packageurl = $this->gv_model->where(array('id' => $gv_id))->getField('packageurl');
            if (empty($packageurl)) {
                $this->error("请上传母包");
            }
            $data['run_time'] = time();
        }
        $data['status'] = $status;
        $rs = $this->game_model->where("id = %d", $id)->save($data);
        if ($rs) {
            $this->success("状态切换成功", U("Game/index", array('appid' => $id)));
            exit;
        } else {
            $this->error('状态切换失败.');
        }
    }

    /**
     * 设置是否在app中显示
     */
    public function set_appstatus() {
        $id = I('id', 0);
        $status = I('appstatus', 0);
        if (empty($status)) {
            $this->error("状态错误");
        }
        $map['id'] = $id;
        $data['is_app'] = $status;
        $rs = $this->game_model->where($map)->save($data);
        if ($rs) {
            $this->success("APP中显示成功", U("Newapp/Game/index", array('appid' => $id)));
            exit;
        } else {
            $this->error('APP中显示失败');
        }
    }

    /**
     **游戏下拉列表
     **/
    public function _game_status($option = null) {
        if (empty($option)) {
            $cates = array(
                "0" => "全部",
                "1" => "游戏接入中",
                "2" => "已上线",
                "3" => "已下线",
                "4" => "已删除",
            );
        } elseif (1 == $option) {
            $cates = array(
                "1" => "游戏接入中",
            );
        } else {
            $cates = array(
                "1" => "游戏接入中",
                "2" => "已上线",
                "3" => "已下线",
            );
        }
        $this->assign("gamestatues", $cates);
    }
}
