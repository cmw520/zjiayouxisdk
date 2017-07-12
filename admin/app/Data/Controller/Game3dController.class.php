<?php
/**
 * 游戏3d数据
 * 
 *
 * @author chenmingwei
 * @email 641189992@qq.com
 * @time  20170710
 *
 */
namespace Data\Controller;

use Common\Controller\AdminbaseController;

class Game3dController extends AdminbaseController {
    protected $game3dmodel;

    function _initialize() {
        parent::_initialize();
        $this->game3dmodel=M('game3d_data');
    }

    public function game3dindex() {
        $device_id = I('get.device_id');
        $game_name =I('get.game_name');

        $this->assign('device_id',$device_id);
        $this->assign('game_name',$game_name);

        $data=$this->_get_data($device_id,$game_name);
        $alldata=$this->_set_data($data);
        $devcie =$this->_get_device();

        $count = count($alldata);
        $rows  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
        $page  = $this->page($count, $rows);


        $newdata=$this->_set_data($data,$page->firstRow,$page->listRows);

        $this->assign('devcie',$devcie);
        $this->assign('newdata',$newdata);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());

        $this->display();
    }

    public function _get_data($device_id="",$game_name=""){
        $where=array();
        if($device_id){
            array_push($where,"c_game3d_data.device_id=$device_id");
        }
       
        if($game_name!=""){
             array_push($where,"c_game.name like "."'%$game_name%'");
        }
       // print_r($where);
       // exit();
        $data=$this->game3dmodel->join('c_game on c_game.id=c_game3d_data.game_id')->field('c_game3d_data.id,c_game3d_data.game_id,c_game3d_data.device_id,c_game3d_data.parameter,c_game.name')->where($where)->select();

     
        return $data;
    }

    public function _set_data($data=array(),$start="",$length=""){
        if(empty($data)){
            return "";
        }else{
            $newdata=array();
            foreach($data as $key=>$val){
                $newdata[$val['game_id']]['name']=$val['name'];
                $newdata[$val['game_id']]['game_id']=$val['game_id'];
                $newdata[$val['game_id']]['parameter'][]=$val['parameter'];



            }
        }

        foreach($newdata as $k=>$v){
             $newdata[$k]['a1']=0;
             $newdata[$k]['b1']=0;
             $newdata[$k]['c1']=0;
             $newdata[$k]['d1']=0;
             $newdata[$k]['e1']=0;
            foreach($v['parameter'] as $i=>$n){       
                if(0<=$n&&$n<20){    
                    $newdata[$k]['a1']++;
                }elseif(20<=$n&&$n<40){
                     $newdata[$k]['b1']++;
                }elseif(40<=$n&&$n<60){
                     $newdata[$k]['c1']++;
                }elseif(60<=$n&&$n<80){
                     $newdata[$k]['d1']++;
                }elseif(80<=$n&&$n<=100){
                     $newdata[$k]['e1']++;
                }

            }

            unset($newdata[$k]['parameter']);

        }

      //print_r($newdata);

     

        if($length){
             return    array_slice($newdata,$start,$length);
        }else{
             return $newdata;
        }
       
    }


    function _get_device(){
        $device =  $this->game3dmodel->field('device_id')->group('device_id')->select();
        return $device;
    }
    
  
    function graph(){
        $data=$_GET;
        $arr=array((int)$data['a1'],(int)$data['b1'],(int)$data['c1'],(int)$data['d1'],(int)$data['e1']);
        $json=json_encode($arr);
       // print_r($json);
       
        $this->assign('name',$data['name']);
        $this->assign('data',$json);
        $this->display();

    }
}