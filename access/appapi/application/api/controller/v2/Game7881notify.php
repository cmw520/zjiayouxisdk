<?php

namespace app\api\controller\v2;

use app\common\controller\Base7881;
use think\Db;
use think\Loader;
use think\Config;

class Game7881notify extends Base7881
{
    private $notify_class;
    function _initialize() {
        parent::_initialize();
        Loader::import('huosdk.notify', EXTEND_PATH, '.class.php');
        $this->notify_class = new \huosdk\Notify(Config::get('conf_7881'));
        $sign_flag = $this->notify_class->verifyJsonnofity();
        if (!$sign_flag) {
            return hs_7881_responce('ILLEGAL_SIGN', '无效的签名', array());
        }
    }

    // 3、充值接口（合作伙伴提供）
    function charge() {
        $postdata = file_get_contents("php://input");
        $rq_data = json_decode($postdata, true);
        $data = urldecode_array($rq_data);
        $data['bill_id'] = $data['bill_id'];

        //查询此单是否存在 存在则直接通知
        $_map['bill_id'] = $data['bill_id'];
        $_order_id = DB::name('7881_order_log')->where($_map)->value('id');
        if (!empty($_order_id)){
            $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/public/v2/7881/send";
            $limit = 500000;
            $post = "order_id=" . $_order_id;
            $boardurl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL'];
            $this->socketRequest($url, 500000, $post, $boardurl);
            // 成功接收数据,通知7881
            return hs_7881_responce('SUCCESS', '充值请求已经成功接收，正在处理', array());
        }

        $ol_data['goods_id'] = $data['goods_id'];
        $ol_data['goods_name'] = $data['goods_name'];
        $ol_data['goods_stock'] = $data['goods_stock'];
        $ol_data['bill_id'] = $data['bill_id'];
        $ol_data['bill_time'] = $data['bill_time'];
        $ol_data['paid_time'] = $data['paid_time'];
        $ol_data['bill_type'] = $data['bill_type'];
        $ol_data['bill_detail'] = json_encode($data['bill_detail']);
        $ol_data['price'] = empty($data['price'])?0 : $data['price'];
        $ol_data['total_price'] = empty($data['total_price'])?0 : $data['total_price'];
        $mem_id = 0;
        if (2 == $data['bill_type']) {
            $username = $data['bill_detail']['game_account'];
            // 查询mem_id
            $mem_id = DB::name('members')->where(array(
                'username' => $username
            ))->value('id');
            if (empty($mem_id)) {
                $rdata = array();
                $msg = "账号填写错误";
                return hs_7881_responce('ILLEGAL_ARGUMENT', $msg, $rdata);
            }
        } elseif (1 == $data['bill_type']) {
            $userdata['username'] = hs_set_username();
            $password = rand(100000, 1000000);
            $userdata['password'] = hs_pw_encode($password, AUTHCODE);
            $userdata['nickname'] = $userdata['username'];
            $userdata['agentname'] = 'cps7881';   //7881渠道
            $userdata['agent_id'] = '29';           //7881渠道编号
            $userdata['app_id'] = $data['bill_detail']['game_id'];
            $userdata['status'] = 2; // 1 为试玩状态 2为正常状态，3为冻结状态
            $userdata['reg_time'] = time();
            $userdata['update_time'] = $userdata['reg_time'];
            $mem_id = DB::name('members')->insertGetId($userdata);
        }

        $order_id = DB::name('7881_order_log')->insertGetId($ol_data);
        if ($order_id) {
            $o_data['order_id'] = $order_id;
            $o_data['type_id'] = $data['bill_type'];
            $o_data['mem_id'] = $mem_id;
            $o_data['app_id'] = $data['bill_detail']['game_id'];
            $o_data['goods_id'] = $data['goods_id'];
            $o_data['bill_id'] = $data['bill_id'];
            $o_data['goods_stock'] = $data['goods_stock'];
            $o_data['real_amount'] = $ol_data['total_price'];
            $o_data['discount'] = $data['bill_detail']['discount'];
            $o_data['amount'] = $o_data['real_amount'] / $o_data['discount'];
            $o_data['gm_cnt'] = $o_data['amount'];
            $o_data['create_time'] = time();
            $o_data['update_time'] = $o_data['create_time'];

            if (1 == $o_data['type_id']) {
                $o_detail['game_account'] = $userdata['username'];
                $o_detail['game_password'] = $password;
            } else {
                $o_detail['game_account'] = $data['bill_detail']['game_account'];
            }

            $o_data['remark'] = json_encode($o_detail);

            $rs = DB::name('7881_order')->insert($o_data);

            if (1 == $rs) {
                $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/public/v2/7881/send";
                $limit = 500000;
                $post = "order_id=" . $o_data['order_id'];
                $boardurl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REDIRECT_URL'];
                $this->socketRequest($url, 500000, $post, $boardurl);
                // 成功接收数据,通知7881
                return hs_7881_responce('SUCCESS', '充值请求已经成功接收，正在处理', array());
            }
        }
    }
    function socketRequest($url, $limit = 500000, $post = '', $boardurl) {
        $return = '';
        $matches = parse_url($url);
        $host = $matches['host'];
        $script = $matches['path'];
        $port = !empty($matches['port']) ? $matches['port'] : 80;
        if ($post) {
            $out = "POST $script HTTP/1.1\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Accept-Encoding: none\r\n";
            $out .= "User-Agent: Huosdk\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: ' . strlen($post) . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n\r\n";
            $out .= $post;
        } else {
            $out = "GET $script HTTP/1.1\r\n";
            $out .= "Accept: */*\r\n";
            $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Accept-Encoding:\r\n";
            $out .= "User-Agent: Huosdk\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n\r\n";
        }

        $errno = 0;
        $errstr = '';
        $fp = fsockopen($host, $port, $errno, $errstr, 1);
        if (!$fp) {
            return "";
        } else {
            stream_set_blocking($fp, 0);
            @fwrite($fp, $out);

//             while (!feof($fp) && $limit > -1) {
//                 $limit -= 524;
//                 $return .= @fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
//             }

//             $return = preg_replace("/\r\n\r\n/", "\n\n", $return, 1);

//             $strpos = strpos($return, "\n\n");
//             $strpos = $strpos !== FALSE ? $strpos + 2 : 0;
//             $return = substr($return, $strpos);
            @fclose($fp);
            return $return;
        }
    }

    // 5 充值结果查询接口（合作伙伴提供）
    function queryOrder() {
        $data = file_get_contents("php://input");
        $rq_data = json_decode($data, true);
        if (empty($rq_data['bill_id'])) {
            return hs_7881_responce('ILLEGAL_ARGUMENT', '订单号错误');
        }
        $map['bill_id'] = $rq_data['bill_id'];
        $o_info = DB::name('7881_order')->where($map)->find();
        if (empty($o_info)) {
            return hs_7881_responce('ILLEGAL_ARGUMENT', '订单号错误');
        }

        if (2 != $o_info['status'] && 2 != $o_info['status_7881']) {
            return hs_7881_responce('ILLEGAL_ARGUMENT', '订单号错误');
        }

        $rdata = DB::name('7881_order_log')->where($map)->find();

        $rdata['bill_detail'] = json_decode($rdata['bill_detail'], true);
        $rdata['op_time'] = hs_get_millisecond();

        // 订单返回接口详细信息
        $notice_detail = json_decode($o_info['remark'], true);
        foreach ($notice_detail as $key => $val) {
            $rdata['bill_detail'][$key] = $val;
        }

        return hs_7881_responce('SUCCESS', '查询成功', $rdata);
    }

    // 执行结果通知
    function result_notify() {
        $order_id = $this->request->get('order_id');
        if (empty($order_id)) {
            return false;
        }

        // 获取订单情况
        $map['order_id'] = $order_id;
        $o_info = M('7881_order')->where($map)->find();
        if (empty($o_info)) {
            return false;
        }

        $rdata['bill_id'] = $o_info['bill_id'];
        $rdata['op_time'] = $o_info['update_time'] . '000';
        $rdata['bill_detail'] = json_decode($o_info['remark'], true);

        // 通知成功
        if (2 == $o_info['status'] && 2 == $o_info['status_7881']) {
            return $rdata;
        }

        // 1 首充号 2 续充号
        if (1 != $o_info['type_id'] && 2 != $o_info['type_id']) {
            return false;
        }

        if (1 == $o_info['type_id']) {
            // 获取充值记录
            $gm_map['app_id'] = $o_info['app_id'];
            $gm_map['mem_id'] = $o_info['mem_id'];
            $gm_info = DB::name('gm_mem')->where($gm_map)->find();
            if (!empty($gm_info)) {
                return $rdata;
            }

            $gmc_data['order_id'] = hs_set_orderid();
            $gmc_data['flag'] = 6;
            $gmc_data['admin_id'] = 0;
            $gmc_data['app_id'] = $o_info['app_id'];
            $gmc_data['mem_id'] = $o_info['mem_id'];
            $gmc_data['money'] = $o_info['amount'];
            $gmc_data['real_amount'] = $o_info['real_amount'];
            $gmc_data['gm_cnt'] = $o_info['gm_cnt'];
            $gmc_data['rebate_cnt'] = 0;
            $gmc_data['discount'] = $o_info['discount'];
            $gmc_data['payway'] = '7881';
            $gmc_data['ip'] = '';
            $gmc_data['status'] = 1;
            $gmc_data['create_time'] = time();
            $gmc_data['update_time'] = $gmc_data['create_time'];
            $gmc_data['remark'] = "7881首充";

            $gmc_id = DB::name('gm_charge')->insertGetId($gmc_data);
            if ($gmc_id) {
                $gm_data['mem_id'] = $o_info['mem_id'];
                $gm_data['app_id'] = $o_info['app_id'];
                $gm_data['sum_money'] = $o_info['real_amount'];
                $gm_data['total'] = $o_info['gm_cnt'];
                $gm_data['remain'] = $o_info['gm_cnt'];
                $gm_data['update_time'] = time();
                $gm_rs = DB::name('gm_mem')->insertGetId($gm_data);
                if (false != $gm_rs) {
                    DB::name('gm_charge')->where(array(
                        'id' => $gmc_id
                    ))->setField('status', 2);
                    DB::name('7881_order')->where($map)->setField('status', 2);
                    return $rdata;
                }
            }
            return false;
        } else {
            // 续充号处理
            $gm_map['app_id'] = $o_info['app_id'];
            $gm_map['mem_id'] = $o_info['mem_id'];
            $gm_data = DB::name('gm_mem')->where($gm_map)->find();
            if (empty($gm_data)) {
                return false;
            }

            $gmc_data['order_id'] = hs_set_orderid();
            $gmc_data['flag'] = 6;
            $gmc_data['admin_id'] = 0;
            $gmc_data['app_id'] = $o_info['app_id'];
            $gmc_data['mem_id'] = $o_info['mem_id'];
            $gmc_data['money'] = $o_info['amount'];
            $gmc_data['real_amount'] = $o_info['real_amount'];
            $gmc_data['gm_cnt'] = $o_info['gm_cnt'];
            $gmc_data['rebate_cnt'] = 0;
            $gmc_data['discount'] = $o_info['discount'];
            $gmc_data['payway'] = '7881';
            $gmc_data['ip'] = '';
            $gmc_data['status'] = 1;
            $gmc_data['create_time'] = time();
            $gmc_data['update_time'] = $gmc_data['create_time'];
            $gmc_data['remark'] = "7881续充";

            $gmc_id = DB::name('gm_charge')->insertGetId($gmc_data);
            if ($gmc_id) {
                $gm_data['sum_money'] += $o_info['real_amount'];
                $gm_data['total'] += $o_info['gm_cnt'];
                $gm_data['remain'] += $o_info['gm_cnt'];
                $gm_data['create_time'] = time();
                $gm_data['update_time'] = $gm_data['create_time'];
                $gm_rs = DB::name('gm_mem')->save($gm_data);
                if (false != $gm_rs) {
                    DB::name('gm_charge')->where(array(
                        'id' => $gmc_id
                    ))->setField('status', 2);
                    DB::name('7881_order')->where($map)->setField('status', 2);
                    return $rdata;
                }
            } else {
                return false;
            }
        }
    }

    // 结果推送
    function result_push() {
        $data = $this->result_notify();
        if (false == $data) {
            return;
        }
    }
    function send_data($code, $msg, $data) {
        $data_string = formart_rdata($code, $msg, $data);
        Loader::import('huosdk.submit', EXTEND_PATH, '.class.php');
        $sub_class = new \huosdk\Submit($this->huosdk_config);

        $url = $this->huosdk_config['url'];
        $i = 0;
        while ($i <= 3) {
            $returnjson = $sub_class->httpJsonPost($url, $data_string);
            $return_data = json_decode($returnjson, true);
            if ('SUCCUSS' == strtoupper($return_data['code'])) {
                $map['order_id'] = $rdata['order_id'];
                DB::name('7881_order')->where($map)->setField('status_7881', 2);
                break;
            }
            sleep(1000);
            $i++;
        }
    }
    function formart_rdata($code, $msg, $data) {
        $rdata = $data;
        $rdata['code'] = $code;
        $rdata['message'] = $msg;
        $rdata['sign_type'] = "RSA";
        $rdata['version'] = "v1.0";
        $rdata['partner_id'] = $this->huosdk_config['partner_id'];
        $rdata['timestamp'] = hs_get_millisecond();

        Loader::import('huosdk.submit', EXTEND_PATH, '.class.php');
        $sub_class = new \huosdk\Submit($this->huosdk_config);

        $rdata["sign"] = $sub_class->buildRequestMysign($rdata); // 参见 《7881 对外合作接口-接口规约和接口签名验签机制》之 接口签名和验签机制 部分的约定

        $data_string = json_encode($rdata);
        return $data_string;
    }

    // 验证签名
    function verifySign() {
        Loader::import('huosdk.notify', EXTEND_PATH, '.class.php');

        // 获取配置
        $config = array();
        //
        $notify_class = new \huosdk\Notify($config);

        $sign_flag = $notify_class->verifyNotify();
        return $sign_flag;
    }
}
