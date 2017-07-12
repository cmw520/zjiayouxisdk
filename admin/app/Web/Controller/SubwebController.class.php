<?php
/**
 * 平台充值管理
 *
 * @author
 */
namespace Web\Controller;

use Common\Controller\AdminbaseController;

class SubwebController extends AdminbaseController {
    /**
     * 游戏列表
     */
    public function webList() {
        $this->subwebList();
        $this->display();
    }

    /**
     * 子站列表
     */
    public function subwebList() {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $appid = I("appid", 0);
        //组装搜索条件
        $wher_array = array();
        $where = " is_delete='2'";
        if ($appid != 0) {
            $where .= " AND app_id like '%s'";
            array_push($wher_array, "%{$appid}%");
        }
        $result = array();
        $gs_model = M('gameSubsite');
        $result["total"] = $total = $gs_model->where($where, $wher_array)->count();
        $page = $this->page($result["total"], $rows);
        $items = $gs_model->field('app_id,create_time,website')->where($where, $wher_array)->order('app_id DESC')
                          ->limit($page->firstRow.','.$page->listRows)->select();
        $game_model = M('game');
        $gamelist = $game_model->field('id,name')->where("is_delete = 2")->select(); //查询游戏列表
        //把游戏的信息放在以该游戏的id为键的数组中
        foreach ($gamelist as $key => $val) {
            $gamename[$val['id']] = $val;
        }
        //把游戏名放到对应的游戏ID的$items中
        foreach ($items as $key => $val) {
            $items[$key]['game'] = $gamename[$val['app_id']]['name'];
        }
        $this->assign('items', $items);
        $this->assign('gamelist', $gamelist);
        $this->assign("page", $page->show('Admin'));
    }

    //获取游戏列表
    public function gameList() {
        $game_model = M('game');
        $gamelist = $game_model->where("is_delete=2")->select(); //查询游戏列表
        $this->assign("gamelist", $gamelist);
    }

    //子站添加
    public function addSub() {
        $this->gameList();
        $this->display();
    }

    //子站添加
    public function addSub_post() {
        $subweb_data['app_id'] = I('appid');
        $subweb_data['website'] = WEBSITE."/index.php/Web/Substation/index/gameid/".I('appid');
        $subweb_data['lboneurl'] = I('lboneurl');
        $subweb_data['lbtwourl'] = I('lbtwourl');
        $subweb_data['lbthreeurl'] = I('lbthreeurl');
        $subweb_data['lbfoururl'] = I('lbfoururl');
        $subweb_data['bbs_url'] = I('luntanurl');
        $subweb_data['gift_url'] = I('libao');
        $subweb_data['glurlone'] = I('glurlone');
        $subweb_data['glurltwo'] = I('glurltwo');
        $subweb_data['glurlthree'] = I('glurlthree');
        $subweb_data['glurlfour'] = I('glurlfour');
        $subweb_data['guildurl'] = I('guildurl');
        $subweb_data['tgurl'] = I('tgurl');
        $subweb_data['activityurl'] = I('activityurl');
        $subweb_data['noviceurl'] = I('noviceurl');
        $subweb_data['screenshoturl'] = I('screenshoturl');
        $subweb_data['is_delete'] = 2;
        $subweb_data['create_time'] = time();
        if ($subweb_data['app_id'] == 0 || empty($subweb_data['website'])) {
            $this->error("请填写完整参数。");
            exit();
        }
        $wher_array = array();
        $gs_model = M("gameSubsite");
        $where = " app_id = '%s'";
        $checkgameid = $subweb_data['app_id'];
        array_push($wher_array, $checkgameid);
        $checkgame = $gs_model->where($where, $wher_array)->select();
        if ($checkgame) {
            $this->error("该游戏已添加了子站，请不要重复添加。");
            exit();
        }
        $bannerinfo = $_FILES['banner'];
        $up_infoa = $_FILES['imagea'];
        $up_infob = $_FILES['imageb'];
        $up_infoc = $_FILES['imagec'];
        $up_infod = $_FILES['imaged'];
        $gla = $_FILES['gongluea'];
        $glb = $_FILES['gonglueb'];
        $glc = $_FILES['gongluec'];
        $gld = $_FILES['gonglued'];
        $banner = '';
        $time = time();
        if ($bannerinfo['name'][0] != '') {
            $banner = $this->checkImage($bannerinfo, $time);
        }
        if ($banner != '') {
            $subweb_data['banner'] = $banner;
        }
        //图1
        if (!empty($up_infoa['name'])) {
            $lunbotua = $this->checkImage($up_infoa, $time + 1);
        }
        if ($lunbotua != '') {
            $subweb_data['lunbotua'] = $lunbotua;
        }
        //图2
        if (!empty($up_infob['name'])) {
            $lunbotub = $this->checkImage($up_infob, $time + 2);
        }
        if ($lunbotub != '') {
            $subweb_data['lunbotub'] = $lunbotub;
        }
        //图3
        if (!empty($up_infoc['name'])) {
            $lunbotuc = $this->checkImage($up_infoc, $time + 3);
        }
        if ($lunbotuc != '') {
            $subweb_data['lunbotuc'] = $lunbotuc;
        }
        //图4
        if (!empty($up_infod['name'])) {
            $lunbotud = $this->checkImage($up_infod, $time + 4);
        }
        if ($lunbotud != '') {
            $subweb_data['lunbotud'] = $lunbotud;
        }
        //攻略1
        if (!empty($gla['name'])) {
            $gluea = $this->checkImage($gla, $time + 10);
        }
        if ($gluea != '') {
            $subweb_data['gongluetua'] = $gluea;
        }
        //攻略2
        if (!empty($glb['name'])) {
            $glueb = $this->checkImage($glb, $time + 11);
        }
        if ($glueb != '') {
            $subweb_data['gongluetub'] = $glueb;
        }
        //攻略3
        if (!empty($glc['name'])) {
            $gluec = $this->checkImage($glc, $time + 12);
        }
        if ($gluec != '') {
            $subweb_data['gongluetuc'] = $gluec;
        }
        //攻略4
        if (!empty($gld['name'])) {
            $glued = $this->checkImage($gld, $time + 13);
        }
        if ($glued != '') {
            $subweb_data['gongluetud'] = $glued;
        }
        if ($lastInsId = $gs_model->add($subweb_data)) {
            $this->success("子站添加成功。");
            exit();
        } else {
            $this->error("添加失败。");
            exit();
        }
    }

    /**
     * 编辑子站信息
     */
    public function editSub_post() {
        $subweb_id = I('app_id');
        $subweb_data['app_id'] = I('app_id');
        $subweb_data['website'] = WEBSITE."/index.php/Web/Substation/index/gameid/".I('app_id');
        $subweb_data['lboneurl'] = I('lboneurl');
        $subweb_data['lbtwourl'] = I('lbtwourl');
        $subweb_data['lbthreeurl'] = I('lbthreeurl');
        $subweb_data['bbs_url'] = I('luntanurl');
        $subweb_data['gift_url'] = I('libao');
        $subweb_data['glurlone'] = I('glurlone');
        $subweb_data['glurltwo'] = I('glurltwo');
        $subweb_data['glurlthree'] = I('glurlthree');
        $subweb_data['glurlfour'] = I('glurlfour');
        $subweb_data['guildurl'] = I('guildurl');
        $subweb_data['tgurl'] = I('tgurl');
        $subweb_data['activityurl'] = I('activityurl');
        $subweb_data['noviceurl'] = I('noviceurl');
        $subweb_data['screenshoturl'] = I('screenshoturl');
        $subweb_data['is_delete'] = 2;
        $subweb_data['create_time'] = time();
        if ($subweb_data['app_id'] == 0 || empty($subweb_data['website'])) {
            $this->error("请填写完整参数。");
            exit();
        }
        $bannerinfo = $_FILES['banner'];
        $up_infoa = $_FILES['imagea'];
        $up_infob = $_FILES['imageb'];
        $up_infoc = $_FILES['imagec'];
        $up_infod = $_FILES['imaged'];
        $gla = $_FILES['gongluea'];
        $glb = $_FILES['gonglueb'];
        $glc = $_FILES['gongluec'];
        $gld = $_FILES['gonglued'];
        $banner = '';
        $time = time();
        if ($bannerinfo['name'][0] != '') {
            $banner = $this->checkImage($bannerinfo, $time);
        }
        if ($banner != '') {
            $subweb_data['banner'] = $banner;
        }
        //图1
        if (!empty($up_infoa['name'])) {
            $lunbotua = $this->checkImage($up_infoa, $time + 1);
        }
        if ($lunbotua != '') {
            $subweb_data['lunbotua'] = $lunbotua;
        }
        //图2
        if (!empty($up_infob['name'])) {
            $lunbotub = $this->checkImage($up_infob, $time + 2);
        }
        if ($lunbotub != '') {
            $subweb_data['lunbotub'] = $lunbotub;
        }
        //图3
        if (!empty($up_infoc['name'])) {
            $lunbotuc = $this->checkImage($up_infoc, $time + 3);
        }
        if ($lunbotuc != '') {
            $subweb_data['lunbotuc'] = $lunbotuc;
        }
        //图4
        if (!empty($up_infod['name'])) {
            $lunbotud = $this->checkImage($up_infod, $time + 4);
        }
        if ($lunbotud != '') {
            $subweb_data['lunbotud'] = $lunbotud;
        }
        //攻略1
        if (!empty($gla['name'])) {
            $gluea = $this->checkImage($gla, $time + 11);
        }
        if ($gluea != '') {
            $subweb_data['gongluetua'] = $gluea;
        }
        //攻略2
        if (!empty($glb['name'])) {
            $glueb = $this->checkImage($glb, $time + 12);
        }
        if ($glueb != '') {
            $subweb_data['gongluetub'] = $glueb;
        }
        //攻略3
        if (!empty($glc['name'])) {
            $gluec = $this->checkImage($glc, $time + 13);
        }
        if ($gluec != '') {
            $subweb_data['gongluetuc'] = $gluec;
        }
        //攻略4
        if (!empty($gld['name'])) {
            $glued = $this->checkImage($gld, $time + 14);
        }
        if ($glued != '') {
            $subweb_data['gongluetud'] = $glued;
        }
        $gs_model = M("gameSubsite");
        /*
         * 修改保存
         */
        if ($subweb_id != 0) {
            //修改数据
            $update = $gs_model->where(array('app_id' => $subweb_id))->save($subweb_data);//update
            if ($update) {
                $this->success("修改成功。");
                exit();
            } else {
                $this->error("修改失败。");
                exit();
            }
        }
        $this->error("未选择编辑游戏。");
        exit();
    }

    /**
     * 显示游戏编辑页面
     */
    public function editSub() {
        $subweb_id = I("id");
        if ($subweb_id) {
            $gs_model = M('gameSubsite');
            $webdata = $gs_model->where("app_id='".$subweb_id."' ")->select();//查询选中记录信息，用于编辑
            $game_model = M('game');
            $where = 1;
            $wher_array = array();
            $where .= " and app_id like '%s'";
            $app_id = $webdata[0]['app_id'];
            array_push($wher_array, "%$app_id%");
            $game = $game_model->field("id,name")->select();
            $arimage = explode(",", $webdata[0]['lunbotu']);
            $glimage = explode(",", $webdata[0]['gongluetu']);
            $this->assign("gameid", $app_id);
            $this->assign('glimage', $glimage);
            $this->assign('arimage', $arimage);
            $this->assign('game', $game);
            $this->assign('webdata', $webdata[0]);
        }
        $this->display();
    }

    /**
     * 删除子站
     */
    public function delSub() {
        $subweb_id = I("id");
        if ($subweb_id != '') {
            $gs_model = M('gameSubsite');
            //伪删除信息
            $webdel = $gs_model->where("app_id='".$subweb_id."' ")->delete();//update
            if ($webdel) {
                //$this->insertLog($_COOKIE['mgadmin2015'],2, 'SubwebAction.class.php', 'webList', time(),"删除了子站ID:".$subweb_id);
                $this->success("删除成功。");
                exit();
            } else {
                $this->error("删除失败。");
                exit();
            }
        }
    }

    /**
     * 上传图片
     */
    public function checkImage($up_info, $time) {
        $arrType = array('image/jpg', 'image/gif', 'image/png', 'image/bmp', 'image/pjpeg', 'image/jpeg');
        $max_size = '5242880';      // 最大文件限制（单位：byte）
        $upfile = C('UPLOADPATH')."image/";; //图片目录路径
        $fname = $up_info['name'];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { //判断提交方式是否为POST
            if (!is_uploaded_file($up_info['tmp_name'])) { //判断上传文件是否存在
                $this->error('文件不存在.');
                exit();
            }
            if ($up_info['size'] > $max_size) {  //判断文件大小是否大于500000字节
                $this->error("上传文件太大.");
                exit();
            }
            if (!in_array($up_info['type'], $arrType)) {  //判断图片文件的格式
                $this->error("上传文件格式不对.");
                exit();
            }
            if (!file_exists($upfile)) {  // 判断存放文件目录是否存在
                mkdir($upfile, 0777, true);
            }
            $imageSize = getimagesize($up_info['tmp_name']);//图片大小
            $img = $imageSize[0].'*'.$imageSize[1];
            $ftypearr = explode('.', $fname);
            $ftype = $ftypearr[1];//图片类型
            $fname = $time.'.'.$ftype;
            $picName = $upfile.$fname;
            if (file_exists($picName)) {
                $this->error("同文件名已存在.");
                exit();
            }
            if (!move_uploaded_file($up_info['tmp_name'], $picName)) {
                $this->error("移动文件出错.");
                exit();
            }
        }

        return $fname;
    }
}