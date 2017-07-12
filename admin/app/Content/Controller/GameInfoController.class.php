<?php
namespace Content\Controller;

use Common\Controller\AdminbaseController;

class GameInfoController extends AdminbaseController {
    private $game_obj;

    function _initialize() {
        parent::_initialize();
        $this->game_obj = new \HuoShu\Game();
    }

    public function edit() {
        $app_id = $_GET['id'];
//        $data=M('game')
//                ->field("g.name,gv.size,gv.version,g.id,g.icon,gc.qqgroup,g.test_status,gv.packageurl")
//                ->alias('g')
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON g.id=gv.app_id")
//                ->join("LEFT JOIN ".C("DB_PREFIX")."game_contact gc ON g.id=gc.app_id")
//                ->where(array("g.id"=>$app_id))
//                ->find();
        $data = $this->game_obj->get_app_info($app_id);
        $this->assign("data", $data);
        $test_status_txt = '';
        $test_status_now = $data['test_status'];
        $test_status_arr = array(
            "1" => "删档测试",
            "2" => "即将测试",
            "3" => "公测",
            "4" => "不删档测试");
        foreach ($test_status_arr as $k => $v) {
            $checked = '';
            if ($test_status_now == $k) {
                $checked = 'checked';
            }
            $test_status_txt .= "<input type='radio' name='test_status' value='$k' $checked />$v ";
        }
        $this->assign("test_status_txt", $test_status_txt);
        //获取并显示所有标签，对于游戏中已经存在的标签，标识为checked
        $tags = $this->game_obj->all_game_tag();
        $game_tags = $this->game_obj->get_game_tags($app_id);
        $game_tag_list = array();
        foreach ($game_tags as $game_tag) {
            $game_tag_list[] = $game_tag['tag_id'];
        }
        $game_tag_txt = '';
        foreach ($tags as $tag) {
            $c_txt = '';
            if (in_array($tag['id'], $game_tag_list)) {
                $c_txt = " checked='checked' ";
            }
            $game_tag_txt .= "<input name='tags[]' type='checkbox' $c_txt value='".$tag['id']."' />".$tag['name']
                             ."&nbsp; &nbsp;";
        }
        $this->assign("game_tag_txt", $game_tag_txt);
        //获取并显示所有分类，对于已经存在的，标识为checked
        $cates = $this->game_obj->all_game_category();
        $cates = $cates['items'];
        $game_cate_ids = $this->game_obj->get_game_cate_ids($app_id);
        $game_cate_txt = '';
        foreach ($cates as $cate) {
            $c_txt = '';
            if (in_array($cate['id'], $game_cate_ids)) {
                $c_txt = " checked='checked' ";
            }
            $game_cate_txt .= "<input name='cates[]' type='checkbox' $c_txt value='".$cate['id']."' />".$cate['name']
                              ."&nbsp; &nbsp;";
        }
        $this->assign("game_cate_txt", $game_cate_txt);
        $this->assign("category_txt", $this->_category_txt($data['category']));
        $this->assign("description", $this->game_obj->get_game_description($app_id));
        $this->assign("shots", $this->game_obj->get_game_shots($app_id));
        $this->display();
    }

    public function _category_txt($category) {
        $txt = '';
        if ($category == 1) {
            $c1 = " checked ";
            $c2 = " ";
        } else if ($category == 2) {
            $c1 = "  ";
            $c2 = " checked ";
        }
        $txt .= '<input type="radio" name="category" value="1" '.$c1.' /> 单机';
        $txt .= '<input type="radio" name="category" value="2"  '.$c2.'/> 网游';

        return $txt;
    }

    private function image_upload_icon() {
        $image_fp = '';
        if (isset($_FILES['icon']) && ($_FILES['icon']['name'])) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 10 * 1024 * 1024;// 设置附件上传大小
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath = SITE_PATH.'/upload/'; // 设置附件上传根目录
            $upload->autoSub = false;
            $upload->replace = true;
            $info = $upload->uploadOne($_FILES['icon']);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                $image_fp = $info['savename'];
            }
        }

        return $image_fp;
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
                    $image_fp = $file_name;
                }
            }
        }

        return $image_fp;
    }

    function get_extension($file) {
        return end(explode('.', $file));
    }

    private function upload_shots($app_id) {
        $shots = $_FILES['shot'];
        if (isset($shots) && ($shots['name'])) {
//            $n=count($shots['name']);
            $upload_dir = SITE_PATH.'upload/shot/';
            $allow_exts = array("image/jpeg", "image/jpg", "image/png");
            $maxSize = 10 * 1024 * 1024;
            $ok_files = array();
            foreach ($shots['error'] as $key => $error) {
                if (($error == UPLOAD_ERR_OK)) { //PHP常量UPLOAD_ERR_OK=0，表示上传没有出错
                    $temp_name = $shots['tmp_name'][$key];
                    $extension = $this->get_extension($shots['name'][$key]);
                    $file_name = "shot_".$app_id."_".($key + 1).".".$extension;
                    $size = $shots['size'][$key];
                    $ext = $shots['type'][$key];
                    if (in_array($ext, $allow_exts) && $size <= $maxSize) {
                        $new_fp = $upload_dir.$file_name;
                        if (file_exists($new_fp)) {
                            unlink($new_fp);
                        }
                        $mv_result = move_uploaded_file($temp_name, $new_fp);
                        if ($mv_result) {
                            $ok_files[] = array("app_id" => $app_id, "shot" => $file_name);
                        }
                    }
                }
            }
            M('game_shots')->where(array("app_id" => $app_id))->delete();
            M('game_shots')->addAll($ok_files);
        }
    }

    public function setGvRecord($appid, $field, $value) {
        $pre_data = M('game_version')->where(array("app_id" => $appid))->find();
        if ($pre_data) {
            M('game_version')->where(array("app_id" => $appid))->setField($field, $value);
        } else {
            M('game_version')->add(
                array("app_id" => $appid, $field => $value, "create_time" => time(), "update_time" => time())
            );
        }
    }

    public function edit_post() {
        $appid = I('app_id');
        $where = array();
        $where['id'] = $appid;
        M('game')->where($where)->save(array("name" => I('name')));
        if (isset($_POST['test_status']) && ($_POST['test_status'] != '')) {
            M('game')->where($where)->save(
                array(
                    "test_status" => I('test_status'), "test_time" => time()
                )
            );
        }
        if (isset($_POST['category']) && ($_POST['category'] != '')) {
            M('game')->where($where)->setField("category", I('category'));
        }
        if (isset($_POST['version']) && ($_POST['version'] != '')) {
            $this->setGvRecord($appid, "version", $_POST['version']);
        }
        if (isset($_POST['size']) && ($_POST['size'] != '')) {
            $this->setGvRecord($appid, "size", $_POST['size']);
        }
        if (isset($_POST['qqgroup']) && ($_POST['qqgroup'] != '')) {
            $this->game_obj->set_game_qqgroup($appid, $_POST['qqgroup']);
        }
        if (isset($_POST['description']) && ($_POST['description'] != '')) {
            $this->game_obj->set_game_description($appid, $_POST['description']);
        }
        if (isset($_POST['cps_package']) && ($_POST['cps_package'] != '')) {
            $this->game_obj->set_game_cps_packageurl($appid, $_POST['cps_package']);
        }
        $tags = I('tags');
        if ($tags) {
            $dataList = array();
            foreach ($tags as $tag) {
                $dataList[] = array("app_id" => $appid, "tag_id" => $tag, "obj" => "1");
            }
            M('tag_match')->where(array("app_id" => $appid, "obj" => "1"))->delete();
            M('tag_match')->addAll($dataList);
        }
        $cates = I('cates');
        if ($cates) {
            $dataList = array();
            foreach ($cates as $cate) {
                $dataList[] = array("obj_id" => $appid, "tid" => $cate, "obj" => "1");
            }
            M('type_match')->where(array("obj_id" => $appid, "obj" => "1"))->delete();
            M('type_match')->addAll($dataList);
        }
        $image_fp = $this->upload_icon($appid);
        if ($image_fp) {
            M('game')->where(array("id" => $appid))->setField("icon", $image_fp);
        }
        $this->upload_shots($appid);
        $this->success("成功");
    }
}

