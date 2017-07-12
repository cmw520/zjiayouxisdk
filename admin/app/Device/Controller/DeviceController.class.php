<?php
/*
**游戏管理
**/
namespace Device\Controller;

use Common\Controller\AdminbaseController;
use ReflectionClass;
use Reflection;
use \Think\Upload;

class DeviceController extends AdminbaseController {
    protected $game_model, $gc_model, $gv_model,$device_model;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gc_model = M('game_client');
        $this->gv_model = M('game_version');
        $this->device_model = M('device');
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
        $this->_dList();
        $this->_get_device();
        $this->display();
    }

   

    /**
     * 设备列表
     */
    
    public function _dList(){
        $brand  = I('brand','','trim');
        $status = I('status/d', 0);
        $where  =array();
        if(isset($brand)&& !empty($brand)){
            array_push($where,"brand like '%$brand%'");
        }
        if(isset($status)&& !empty($status)){
            array_push($where,"status=$status");
        }
        $count = $this->device_model
            ->where($where)
            ->count();

        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $items = $this->device_model           
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
       $this->assign("formget", $_GET);
       $this->assign("items", $items);  
       $this->assign("Page", $page->show('Admin'));
       $this->assign("current_page", $page->GetCurrentPage());    
    }


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
        $brandid = I('brandid/d', 0);
        if ($brandid > 0) {  
            $_g_map['id'] = $brandid;
            $devicedata = $this->device_model->where($_g_map)->find();  
            $file_name  =basename($devicedata['conf_url']); 
            $this->assign('filename',$file_name);    
            $this->assign('devicedata', $devicedata);
            $this->display();
        } else {
            $this->error("参数错误");
        }
    }

    public function add_post() {
        if (IS_POST) {
            /* 获取POST数据 */
            $device_data['brand']    = trim(I('post.brand'));
            $device_data['series']   = I('post.series');
            $device_data['size']     = I('post.size');
            $device_data['rp_width'] = I('post.rp_width');
            $device_data['rp_height'] = I('post.rp_height');
            $device_data['grap_method'] = I('post.grap_method');
            $device_data['straight_method'] = I('post.straight_method');
            $device_data['sale_method'] = I('post.sale_method');
            $conf_file = $_FILES['conf_file'];
            $device_data['status'] = I('post.status');
            /* 检测输入参数合法性, 设备品牌 */
            if (empty($device_data['brand'])) {
                $this->error("设备品牌为空，请填写设备品牌");
            }

             $conf_url = $this->upload_conf();
            if ($conf_url=="") {
                //$this->error($conf_url);
            } else {
                $device_data['conf_url'] = $conf_url;
            }
           
            if ($this->device_model->create($device_data)) {
                $device_id = $this->device_model->add();
                /* 插入游戏类型  */
                if ($device_id > 0) {
                    $this->success("添加成功！", U("Device/index"));
                }
            } else {
                $this->error($this->device_model->getError());
            }
            exit;
        }
        $this->error('页面不存在');
    }

    private function upload_conf() {
        $conf_fp = '';
        if (isset($_FILES['conf_file']) && ($_FILES['conf_file']['name'])) {
            $upload_dir = SITE_PATH.'upload/device_conf/';
            $allow_exts = array("text/plain","application/msword","text/xml");
            $maxSize = 10 * 1024 * 1024;
            if (($_FILES['conf_file']['error'] == UPLOAD_ERR_OK)) { //PHP常量UPLOAD_ERR_OK=0，表示上传没有出错
                $temp_name = $_FILES['conf_file']['tmp_name'];
                $extension = $this->get_extension($_FILES['conf_file']['name']);
                $file_name = "conf_file_".time().".".$extension;
                $size = $_FILES['conf_file']['size'];
                $ext = $_FILES['conf_file']['type'];
                if (in_array($ext, $allow_exts) && $size <= $maxSize) {
                    $new_fp = $upload_dir.$file_name;
                    if (file_exists($new_fp)) {
                        unlink($new_fp);
                    }
                    move_uploaded_file($temp_name, $new_fp);
                    $conf_fp = $new_fp;
                }
            }
        }

        return $conf_fp;
    }

    function upload_conf_oss(){
        $upload = new upload(array(),'Aliyun');
        $upload->savePath='test/';
        $upload->saveName='cmw-124';
        $res=$upload->upload();
        return $res['conf_file']['url'];
    }

    function get_extension($file) {
        return end(explode('.', $file));
    }

    public function edit_post() {
        if (IS_POST) {
            $device_data['id'] = I('brandid/d');
           
	        $device_data['brand']        = trim(I('post.brand'));
            $device_data['series']   = I('post.series');
            $device_data['size']     = I('post.size');
            $device_data['rp_width'] = I('post.rp_width');
            $device_data['rp_height'] = I('post.rp_height');
            $device_data['grap_method'] = I('post.grap_method');
            $device_data['straight_method'] = I('post.straight_method');
            $device_data['sale_method'] = I('post.sale_method');
            $conf_file = $_FILES['conf_file'];
            
               /* 检测输入参数合法性, 设备品牌 */
            if (empty($device_data['brand'])) {
                $this->error("设备品牌为空，请填写设备品牌");
            }

             $conf_url = $this->upload_conf();
            if ($conf_url=="") {
                //$this->error($conf_url);
            } else {
                $device_data['conf_url'] = $conf_url;
            }

            if ($conf_url) {
                M('device')->where(array("id" => $device_data['id']))->setField("conf_url", $conf_url);
            }
       
            $barndid = $device_data['id'];
            unset($device_data['id']);
//         
            $this->device_model->where(array("id" => $barndid))->setField($device_data);
//	       
            $this->success("更新成功！", U("Device/index"));

        } else {
            $this->error('页面不存在');
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

    public function _get_device(){
         $device = M('device')->getField("id,brand");
         $this->assign("device",$device);
    }
}
