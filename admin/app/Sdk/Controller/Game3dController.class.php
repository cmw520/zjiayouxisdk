<?php
/*
**游戏管理
**/
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;
use DOMDocument;
use ReflectionClass;
use Reflection;
use ReflectionObject;
class Game3dController extends AdminbaseController {
    protected $game_model, $gc_model, $gv_model,$game_3deffect;

    function _initialize() {
        parent::_initialize();
        $this->game_model = D("Common/Game");
        $this->gc_model = M('game_client');
        $this->gv_model = M('game_version');
        $this->game_3deffect= M('game_3deffect');
    }

  

    /*
     * 编辑游戏
     */
    public function edit() {
        $app_id = I('id/d', 0);
        if ($app_id > 0) {    
            $_g_map['game_id'] = $app_id;
            $gamedata = $this->game_3deffect->where($_g_map)->select();
           // echo $this->game_3deffect->getLastSql();
            $this->assign('game_id', $app_id);
            $this->assign('game', $gamedata);
            $this->display();
        } else {
            $this->error("参数错误");
        }
    }

   
    private function upload_conf($app_id) {
        $conf_fp = '';
        if (isset($_FILES['conf']) && ($_FILES['conf']['name'])) {
            $upload_dir = SITE_PATH.'upload/conf';
            $allow_exts = array("text/plain", "doc", "text/xml");
            $maxSize = 10 * 1024 * 1024;
            if (($_FILES['conf']['error'] == UPLOAD_ERR_OK)) { //PHP常量UPLOAD_ERR_OK=0，表示上传没有出错
                $temp_name = $_FILES['conf']['tmp_name'];
                $extension = $this->get_extension($_FILES['conf']['name']);
                $file_name = "conf_".$app_id.".".$extension;
                $size = $_FILES['conf']['size'];
                $ext = $_FILES['conf']['type'];
                if (in_array($ext, $allow_exts) && $size <= $maxSize) {
                    $new_fp = $upload_dir.'/'.$file_name;
                    if (file_exists($new_fp)) {
                        unlink($new_fp);
                    }
                    move_uploaded_file($temp_name, $new_fp);
                    $conf_fp = '/upload/conf/'.$file_name;
                }
            }
        }

        return $conf_fp;
    }

    function get_extension($file) {
        return end(explode('.', $file));
    }

    public function edit_post() {
        if (IS_POST) {

            $appid = I('appid/d');    
            
            $default_parallax=I('default_parallax');
            $conf_fp = $this->upload_conf($appid);

            $res=$this->check($appid);

           
            
            if($res){
                     $this->update_data($appid,$default_parallax,$conf_fp);
                     $this->success("修改成功！", U("Newapp/Game/index"));

            }else{
                    if ($conf_fp) {
                       
                        if($this->add_data($appid,$default_parallax,$conf_fp)){
                             $this->success("新增成功！", U("Newapp/Game/index"));  
                         }else{
                            $this->error('数据录入失败');
                         }

                       
                }else{
                    $this->error('请上传配置文件');
                }
            }

        } else {
            $this->error('页面不存在');
        }
    }

    public function read_conffile($url=""){
        if($url!=""){
            $file=SITE_PATH.$url;
          
            if(file_exists($file)&&is_readable($file)&&simplexml_load_file($file)){
                $xml=simplexml_load_file($file);
               
                $arr=$this->xmlToArr($xml,'ZpGameCfg');

                return $arr['ZpGameCfg']['Scene'];
           
           
            }else{
               return false;
           }

       }else{
        return false;
       }
    }

    public function check($appid=""){
        if($appid!=""){
                 $_g_map['game_id'] = $appid;
                 //print_r($_g_map);
                 $res = $this->game_3deffect->where($_g_map)->select();
                 
                 if(!empty($res)){
                    return $res;
                 }else{
                    return "";
                 
                 }
        }else{
            return "";
        }
            

    }

    public function add_data($appid,$default_parallax,$conf_fp){
        $game_data=array();
        $conf_data=$this->read_conffile($conf_fp);

      
      
        foreach($conf_data as $k=>$v){
            $game_data[$k]['game_id']=$appid;
            $game_data[$k]['config_url']=$conf_fp;
            $game_data[$k]['default_parallax']=$default_parallax;
            $game_data[$k]['scene_id']=$v['attributes']['SceneID'];
            $game_data[$k]['camera_method']=$v['attributes']['CameraCtlType'];
            $game_data[$k]['eyes_distance']=$v['attributes']['Pupil'];
            $game_data[$k]['plane_distance']=$v['attributes']['Intersection'];

        }

       

       
        $row=$this->game_3deffect->addAll($game_data);
        //var_dump($row);
        //echo $this->game_3deffect->getLastSql();
       // exit();
       // 
       return $row;
    }

    public function update_data($appid,$default_parallax,$conf_fp){
        $where =array('game_id'=>$appid);
        if($conf_fp){
            $this->game_3deffect->where($where)->delete();
            $this->add_data($appid,$default_parallax,$conf_fp);

        }else{
            
            $data['default_parallax']=$default_parallax;
            $this->game_3deffect->where($where)->save($data);
        }

    }

    function xmlToArr ($xml, $root = false) {

        if (!$xml->children()) {
        return (string) $xml;
        }
        $array = array();
        foreach ($xml->children() as $element => $node) {
        $totalElement = count($xml->{$element});
        if (!isset($array[$element])) {
        $array[$element] = "";
        }
        // Has attributes
        if ($attributes = $node->attributes()) {
        $data = array(
        'attributes' => array(),
        'value' => (count($node) > 0) ? $this->__xmlToArr($node, false) : (string) $node
        );
        foreach ($attributes as $attr => $value) {
        $data['attributes'][$attr] = (string) $value;
        }
        if ($totalElement > 1) {
        $array[$element][] = $data;
        } else {
        $array[$element][0] = $data;
        }
        // Just a value
        } else {
        if ($totalElement > 1) {
        $array[$element][] = $this->__xmlToArr($node, false);
        } else {
        $array[$element][0] = $this->__xmlToArr($node, false);
        }
        }
        }
        if ($root) {
        return array($xml->getName() => $array);
        } else {
        return $array;
        }

    } 



}
