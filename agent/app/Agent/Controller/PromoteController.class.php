<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class PromoteController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function plink() {
        $agent_id = $_SESSION['agent_id'];
        $this->assign("agent_id", $agent_id);
        $mylink = $this->current_site_domain.U('Front/Mem/reg')."/agent_id/".$agent_id;
        $this->assign("mylink", $mylink);
        $fp = '';
        if ($this->check_app_exist()) {
            $fp = DOWNSITE.$this->get_app_url();
        }
        $this->assign("fp", $fp);
        $this->display();
    }

    public function get_app_url() {
//        $url=DOWNIP."/sdkgame/app_100/app_100_".$agent_id.".apk";
        $agent_id = $_SESSION['agent_id'];
        $where = array();
        $where['app_id'] = 100;
        $where['agent_id'] = $agent_id;
        $url = M('agent_game')->where($where)->getField("url");
        return $url;
    }

    public function check_app_exist() {
        $url = $this->get_app_url();
        if ($url) {
            return true;
        } else {
            return false;
        }
    }

    public function dopack_remote() {
//        $agid=M('agent_game')->where(array("app_id"=>100,"agent_id"=>$_SESSION['agent_id']))->getField("id");
        $agid = M('agent_game')->where(array("app_id" => 100, "agent_id" => $_SESSION['agent_id']))->getField("id");
        if (!$agid) {
            $data = array();
            $data['app_id'] = 100;
            $data['agent_id'] = $_SESSION['agent_id'];
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['agentgame'] = "app_100_".$_SESSION['agent_id'];
//            $data['url']=time();
            $agid = M('agent_game')->add($data);
        }
        $this->_do_package($agid);
    }

    function get_newest_version_id($app_id) {
        $_map['app_id'] = $app_id;
        $_map['status'] = 2;
        $results = M('game_version')->where($_map)->order("id desc")->select();
        if ($results) {
            return $results[0]['id'];
        }
    }

    function _do_package($ag_id, $option = 1) {
        if (empty($ag_id)) {
            $this->_ajax_return('参数错误ag', $option);
        }
        $game_model = M("game");
        $ag_model = M('agent_game');
        $ag_info = $ag_model->where(array('id' => $ag_id))->find();
        $game_info = $game_model->where(array('id' => $ag_info['app_id']))->find();
        $newest_ver_id = $this->get_newest_version_id($ag_info['app_id']);
        $opt = md5(md5($game_info['initial']."/".$newest_ver_id.$ag_info['agentgame']).'resub');
        $initial = base64_encode($game_info['initial']."/".$newest_ver_id);
//	    $initial = base64_encode($game_info['initial']);
        $agentgame = base64_encode($ag_info['agentgame']);
        $opt = base64_encode($opt);
        $data_string = array('p' => $initial, 'a' => $agentgame, 'o' => $opt);
        $data_string = json_encode($data_string);
        $url = DOWNIP."/sub.php";
        $cnt = 0;
        while (1) {
            $return_content = base64_decode(self::http_post_data($url, $data_string));
            if (0 < $return_content || 3 == $cnt) {
                break;
            }
            $cnt++;
        }
        if (0 < $return_content) {
            $updatedata['url'] = $game_info['initial'].'/'.$newest_ver_id.'/'.$ag_info['agentgame'].".apk";
            $updatedata['update_time'] = time();
            $rs = $ag_model->where("id=%d", $ag_id)->save($updatedata);
            if ($option == 1) {
                $this->ajaxReturn(array('success' => true, 'msg' => '申请app成功，请复制链接推广'), 'JSON');
            } else {
                //$this->success("分包成功");
                $this->_ajax_return("更新成功", $option);
            }
            exit;
        } else if (-6 == $return_content) {
            $this->_ajax_return("拒绝访问", $option);
            exit;
        } else if (-4 == $return_content) {
            $this->_ajax_return("验证错误", $option);
            exit;
        } else if (-3 == $return_content) {
            $this->_ajax_return("请求数据为空", $option);
            exit;
        } else if (-2 == $return_content) {
            $this->_ajax_return("分包失败", $option);
            exit;
        } else if (-1 == $return_content) {
            $this->_ajax_return("无法创建文件,打包失败.", $option);
            exit;
        } else if (-5 == $return_content) {
            $this->_ajax_return("游戏原包不存在.", $option);
            exit;
        } else {
            $this->_ajax_return("请求数据失败.", $option);
            exit;
        }
        $this->_ajax_return("分包记录添加失败！", $option);
        exit;
    }

    /*
	 * 向下载服务器发送请求数据
	 */
    function http_post_data($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                   'Content-Type: application/json; charset=utf-8',
                   'Content-Length: '.strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $return_content;
    }

    function keymodify(){
        $_ackey = I('post.accode/s','');
        $_randnum = I('post.randnum/s','');
        if (empty($_randnum) || empty($_ackey)){
            $this->error("请输入邀请码");
        }

        $_len = mb_strlen($_ackey,'utf8');
        if ($_len > 10){
            $this->error("请输入数字、中文、英文或其组合，少于10字符");
        }
        $_u_map['id'] = $_SESSION['agent_id'];

        $_u_data['user_activation_key'] = $_ackey;
        $_u_info  = M('users')->where($_u_data)->find();
        if (!empty($_u_info)){
            $this->error("邀请码已存在,请重新输入");
        }

        $_rs = M('users')->where($_u_map)->save($_u_data);
        if (false !== $_rs){
            $_SESSION['user_activation_key'] = $_ackey;
            $this->success("修改成功");
        }else{
            $this->error("修改失败");
        }
    }

    function _ajax_return($msg, $option = 1) {
        if (1 == $option) {
            $this->ajaxReturn(array('msg' => $msg), 'JSON');
            exit;
        } else {
            $this->error($msg);
        }
    }
}
