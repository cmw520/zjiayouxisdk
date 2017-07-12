<?php
namespace Huosdk;
class AgentLoginLog {
    public function add($agent_id) {
        $ip = get_client_ip();
        $t = time();
        $data = array();
        $data['user_id'] = $agent_id;
        $data['ip'] = $ip;
        $data['type'] = 1;
        $data['login_time'] = $t;
        $data['deviceinfo'] = $_SERVER["HTTP_USER_AGENT"];
        $data['addr'] = $this->get_ip_attribution($ip);
        M('admin_login_log')->add($data);
        M('users')->where(array("id" => $agent_id))->setField("last_login_time", date("Y-m-d H:i:s", $t));
    }

    public function getListCount($agent_id, $where_extra = array()) {
        $model = M('admin_login_log');
        $where = array();
        $where['a.user_id'] = $agent_id;
        $count = $model
            ->field("a.ip as login_ip,a.login_time")
            ->alias("a")
            ->where($where)
            ->count();
        return $count;
    }

    public function getListItems($agent_id, $where_extra = array(), $start = 0, $limit = 0) {
        $model = M('admin_login_log');
        $where = array();
        $where['a.user_id'] = $agent_id;
        $items = $model
            ->field("a.ip as login_ip,a.login_time")
            ->alias("a")
            ->where($where)
            ->limit($start, $limit)
            ->order("a.id desc")
            ->select();
        return $items;
    }

    private function get_ip_attribution($ip) {
        $ipurl = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        // $data  = @file_get_contents($ipurl);
        $data = $this->get_http_content($ipurl, array(), 'GET', 2);
        if ($data) {
            $ipobj = json_decode($data);
            if (0 == $ipobj->code) {
                $iparr = array($ipobj->data);
                $addr = $ipobj->data->country.$ipobj->data->region.$ipobj->data->city.' '.$ipobj->data->isp;
            } else {
                $addr = "不明地址";
            }
        } else {
            $addr = "不明地址";
        }
        return $addr;
    }

    function get_http_content($url, $postData = array(), $method = 'GET', $timeout = 30) {
        $data = '';
        if (!empty($url)) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //30秒超时
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                if (strtoupper($method) == 'POST') {
                    $curlPost = is_array($postData) ? http_build_query($postData) : $postData;
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
                }
                $data = curl_exec($ch);
                curl_close($ch);
            } catch (Exception $e) {
                $data = null;
            }
        }
        return $data;
    }
}

