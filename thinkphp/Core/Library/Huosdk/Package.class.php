<?php
namespace Huosdk;
class Package {
    private $ag_model, $game_model;

    public function __construct() {
        $this->ag_model = M('agent_game');
        $this->game_model = M("game");
    }

    function pack($ag_id) {
        if (empty($ag_id)) {
            return array("error" => "1", "msg" => "参数错误");
        }
        $ag_info = $this->ag_model->where(array('id' => $ag_id))->find();
        $game_info = $this->game_model->where(array('id' => $ag_info['app_id']))->find();
        $opt = md5(md5($game_info['initial'].$ag_info['agentgame']).'resub');
        $initial = base64_encode($game_info['initial']);
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
        if (0 <= $return_content) {
            $updatedata['url'] = $game_info['initial'].'/'.$ag_info['agentgame'].".apk";;
            $updatedata['update_time'] = time();
            $rs = $this->ag_model->where("id=%d", $ag_id)->save($updatedata);
            return array("error" => "0", "msg" => "分包成功");
        } else if (-6 == $return_content) {
            return array("error" => "1", "msg" => "拒绝访问");
        } else if (-4 == $return_content) {
            return array("error" => "1", "msg" => "验证错误");
        } else if (-3 == $return_content) {
            return array("error" => "1", "msg" => "请求数据为空");
        } else if (-2 == $return_content) {
            return array("error" => "1", "msg" => "分包失败");
        } else if (-1 == $return_content) {
            return array("error" => "1", "msg" => "无法创建文件,打包失败.");
        } else if (-5 == $return_content) {
            return array("error" => "1", "msg" => "游戏原包不存在");
        } else {

            return array("error" => "1", "msg" => "请求数据失败");
        }
        return array("error" => "1", "msg" => "分包记录添加失败");
    }

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
        if (200!=$return_code){
            $return_content = base64_encode(-1000);
        }

        return $return_content;
    }
}
