<?php
/**
 * 礼包管理中心
 *
 * @author
 */
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class GiftController extends AdminbaseController {
    protected $game_model, $gift_model, $gfc_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gift_model = M('gift');
        $this->gfc_model = M('gift_code');
    }

    /**
     * 礼包列表
     */
    public function giftList() {
        $this->_game();
        $this->_gfList();
        $this->display('Gift/index');
    }

    /**
     **礼包列表
     */
    public function _gfList() {
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $title = I('title');
        $gameid = I('appid');
        $page = 1;
        $offset = ($page - 1) * $rows;
        $result = array();
        $where = "is_delete =2";
        $where_arr = array();
        if (isset($title) && $title != '') {
            $where .= " and title='%s'";
            array_push($where_arr, $title);
            $this->assign('title', $title);
        }
        if (isset($gameid) && $gameid > 0) {
            $where .= " and app_id=%d";
            array_push($where_arr, $gameid);
            $this->assign('appid', $gameid);
        }
        $result["total"] = $this->gift_model->where($where, $where_arr)->count();
        $page = $this->page($result["total"], $rows);
        $giftlist = $this->gift_model->where($where, $where_arr)->order("id DESC")->limit(
            $page->firstRow.','.$page->listRows
        )->select();
        $this->assign('giftlist', $giftlist);
        $this->assign("Page", $page->show('Admin'));
    }

    /**
     *
     * 删除礼包
     */
    public function del() {
        $gift_id = I('id/d');
        if ($gift_id > 0) {
            //伪删除信息
            $rs = $this->gift_model->where("id=%d", $gift_id)->setField('is_delete', 1);
            if ($rs) {
                $this->success("删除成功", U("Gift/giftList"));
                exit;
            } else {
                $this->error("删除失败");
                exit;
            }
        }
        $this->error("参数错误");
    }

    private function setGiftfield($field, $uptext, $downtext) {
        if ($_GET['ids']) {
            $_POST['ids'] = (array)$_GET['ids'];
        }
        if ((2 != $_GET[$field] && 1 != $_GET[$field]) || empty($_POST['ids'])) {
            $this->error("参数错误！！");
        }
        if (2 == $_GET[$field]) {
            $_text = $uptext;
        } else {
            $_text = $downtext;
        }
        $data[$field] = $_GET[$field];
        $gift_ids = join(",", $_POST['ids']);
        if ($this->gift_model->where("id in ($gift_ids)")->save($data) !== false) {
            $this->success($_text."成功！");
        } else {
            $this->error($_text."失败！");
        }
    }

    /**
     * 豪华
     */
    public function luxury() {
        $this->setGiftfield('is_luxury', '豪华礼包设置', '取消礼包设置');
    }

    /**
     * 热门
     */
    public function hot() {
        $this->setGiftfield('is_hot', '热门礼包设置', '取消热门设置');
    }

    /**
     * 推荐
     */
    public function recommend() {
        $this->setGiftfield('is_rmd', '推荐礼包设置', '取消推荐设置');
    }

    public function add() {
        $this->_game(false);
        $this->display();
    }

    /**
     * 添加礼包
     */
    public function add_post() {
        if (IS_POST) {
            //获取数据
            $gf_data['app_id'] = I('appid/d');
            $gf_data['title'] = I('title', '');
            $gf_data['condition'] = I('condition/d', 0);
            $gf_data['is_rmd'] = I('rmd/d', 1);
            $gf_data['is_luxury'] = I('luxury/d', 1);
            $gf_data['is_hot'] = I('hot/d', 1);
            $gf_data['content'] = I('content', '');
            $gf_data['func'] = I('func', '');
            $gf_data['start_time'] = strtotime(I('starttime'));
            $gf_data['end_time'] = strtotime(I('endtime'));
            $gf_data['create_time'] = time();
            if (empty($gf_data['app_id']) || empty($gf_data['title']) || empty($gf_data['content'])
                || empty($gf_data['end_time'])
                || empty($gf_data['start_time'])
            ) {
                $this->error("请填写完数据后再提交");
            }
            if (0 == $gf_data['app_id']) {
                $this->error("请选择正确的游戏");
            }
            //插入数据
            $code = $this->trimall(I('code/s', ''));
            if (preg_match("/[\x7f-\xff]/", $code)) {
                $this->error("礼包码中请勿使用中文");
            }
            $codearr = explode("\n", $code);
            foreach ($codearr as $val) {
                $val = $this->trimall($val);
                if (empty($val)) {
                    continue;
                }
                $dataList[] = array('gf_id' => $gf_id, 'code' => $val);
            }
            $gf_data['total'] = count($dataList);
            $gf_data['remain'] = $gf_data['total'];
            if (empty($gf_data['total'])) {
                $this->error("请填写正确礼包码");
            }
            if ($this->gift_model->create($gf_data)) {
                $gf_id = $this->gift_model->add();
                foreach ($dataList as $k => $v) {
                    $dataList[$k]['gf_id'] = $gf_id;
                }
                $this->gfc_model->addAll($dataList);
                $this->success("添加成功!", U("Gift/giftList"));
                exit;
            } else {
                $this->error("添加失败");
                exit;
            }
        }
        $this->error("参数错误");
    }

    /**
     * @param $str 字符串
     *
     * @return mixed
     */
    function trimall($str) {
        $qian = array(" ", "　", "\t");
        $hou = array("", "", "", "", "");

        return str_replace($qian, $hou, $str);
    }

    public function edit() {
        $id = I("get.id/d", 0);
        if (empty($id)) {
            $this->error("参数错误");
        }
        $map['id'] = $id;
        $giftlist = $this->gift_model->where($map)->find();
        $list = $this->gfc_model->field("code")->where(array("gf_id" => $id))->select();
        foreach ($list as $k => $v) {
            $codestr .= $v['code']."\n";
        }
        $giftlist['code'] = $codestr;
        $this->_game();
        $this->assign($giftlist);
        $this->display();
    }

    /**
     * 修改礼包
     */
    public function edit_post() {
        $gf_id = I('id/d');
        if (empty($gf_id)) {
            $this->error("参数错误");
        }
        //获取数据
        $gf_data['id'] = $gf_id;
//			$gf_data['app_id'] = I('appid');
//			$gf_data['title'] = I('title');
//        $gf_data['app_id'] = I('appid/d');
//        $gf_data['title'] = trim(I('title/s', ''));
        $gf_data['condition'] = I('condition/d', 0);
        $gf_data['is_rmd'] = I('rmd/d', 1);
        $gf_data['is_luxury'] = I('luxury/d', 1);
        $gf_data['is_hot'] = I('hot/d', 1);
        $gf_data['content'] = I('content', '');
        $gf_data['func'] = I('func', '');
        $gf_data['start_time'] = strtotime(I('starttime'));
        $gf_data['end_time'] = strtotime(I('endtime'));
        $gf_data['update_time'] = time();
        //修改数据
        if ($this->gift_model->create($gf_data)) {
            $update = $this->gift_model->save();//update
            $code_str = I('code_more/s', '');
            if ($code_str) {
                $this->add_code($code_str, $gf_id);
            }
            if ($update) {
                $this->success("更新成功!", U("Gift/giftList"));
                exit;
            }
        }
        $this->error("修改失败");
        exit;
    }

    public function add_code($code_str, $gf_id) {
        $code = $this->trimall($code_str);
        if (preg_match("/[\x7f-\xff]/", $code)) {
            return array("error" => "1", "msg" => "礼包码中请勿使用中文");
        }
        $codearr = explode("\n", $code);
        $dataList = array();
        foreach ($codearr as $val) {
            if (empty($val)) {
                continue;
            }
            $dataList[] = array('gf_id' => $gf_id, 'code' => $val);
        }
        $add_cnt = count($dataList);
        if (empty($add_cnt)) {
            return array("error" => "1", "msg" => "请填写正确礼包码");
        }
        M('gift_code')->addAll($dataList);
        M('gift')->where(array("id" => $gf_id))->setInc("remain", $add_cnt);
        M('gift')->where(array("id" => $gf_id))->setInc("total", $add_cnt);

        return array("error" => "0", "msg" => "添加成功");
    }
}