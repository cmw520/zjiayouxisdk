<?php

namespace app\api\controller\v2;

use app\common\controller\Base7881;
use think\Db;
use think\Log;
use think\Loader;
use think\Config;

class Game7881 extends Base7881
{
    private $sub_class;
    function _initialize() {
        parent::_initialize();
        header("Content-Type: text/html; charset=utf-8");
        date_default_timezone_set('PRC');
        Loader::import('huosdk.submit', EXTEND_PATH, '.class.php');
        $this->sub_class = new \huosdk\Submit(Config::get('conf_7881'));
    }

    //执行结果通知
    function result_notify($order_id){
        if (empty($order_id)){
            $order_id = $this->request->post('order_id');
        }

        if (empty($order_id)){
            return false;
        }

        //获取订单情况
        $map['order_id'] = $order_id;
        $o_info = DB::name('7881_order')->where($map)->find();
        if (empty($o_info)){
            return false;
        }

        $rdata['order_id'] = $order_id;
        $rdata['bill_id'] = $o_info['bill_id'];
        $rdata['op_time'] = $o_info['update_time'].'000';
        $rdata['bill_detail'] = json_decode($o_info['remark'],true);

        //通知成功
        if (2 == $o_info['status'] && 2 == $o_info['status_7881']){
            return $rdata;
        }

        // 1 首充号  2 续充号
        if (1 != $o_info['type_id'] && 2 != $o_info['type_id']){
            return false;
        }
        if (1 == $o_info['type_id']){
            //获取充值记录
            $gm_map['app_id'] = $o_info['app_id'];
            $gm_map['mem_id'] = $o_info['mem_id'];
            $gm_info = DB::name('gm_mem')->where($gm_map)->find();
            if (!empty($gm_info)){
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
            if ($gmc_id){
                $gm_data['mem_id'] = $o_info['mem_id'];
                $gm_data['app_id'] = $o_info['app_id'];
                $gm_data['sum_money'] = $o_info['real_amount'];
                $gm_data['total'] = $o_info['gm_cnt'];
                $gm_data['remain'] = $o_info['gm_cnt'];
                $gm_data['create_time'] = time();
                $gm_data['update_time'] = $gm_data['create_time'];
                $gm_rs = DB::name('gm_mem')->insertGetId($gm_data);
                if (false != $gm_rs){
                    DB::name('gm_charge')->where(array('id'=>$gmc_id))->setField('status',2);
                    DB::name('7881_order')->where($map)->setField('status',2);
                    return $rdata;
                }
            }
            return false;
        }else{
            //续充号处理
            $gm_map['app_id'] = $o_info['app_id'];
            $gm_map['mem_id'] = $o_info['mem_id'];
            $gm_data = DB::name('gm_mem')->where($gm_map)->find();
            if (empty($gm_data)){
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
            if ($gmc_id){
                $gm_data['sum_money'] += $o_info['real_amount'];
                $gm_data['total'] += $o_info['gm_cnt'];
                $gm_data['remain'] += $o_info['gm_cnt'];
                $gm_data['update_time'] = time();
                $gm_rs = DB::name('gm_mem')->update($gm_data);
                if (false != $gm_rs){
                    DB::name('gm_charge')->where(array('id'=>$gmc_id))->setField('status',2);
                    DB::name('7881_order')->where($map)->setField('status',2);
                    DB::name('7881_order')->where($map)->setField('update_time',time());
                    return $rdata;
                }
            }else{
                return false;
            }
        }
    }

    //结果推送
    function result_push($order_id = ''){
        $data = $this->result_notify($order_id);
        if (false == $data){
            echo 'false';
        }else{
            $this->send_data('SUCCESS','充值成功',$data);
            echo 'success';
        }

    }

    function send_data($code, $msg, $data){
        $order_id = $data['order_id'];
        unset($data['order_id']);

        $data_string = $this->formart_rdata($code, $msg, $data);
        $url = Config::get('conf_7881.url');
        $i = 0;
        Log::write($url.'?'.$data_string,'error');
        while($i <=3){
            $returnjson = $this->sub_class->httpJsonPost($url, $data_string);
            $return_data = json_decode($returnjson,true);
            if ('SUCCESS' == strtoupper($return_data['code'])){
                $map['order_id'] = $order_id;
                DB::name('7881_order')->where($map)->setField('status_7881',2);
                break;
            }
            sleep(1);
            $i ++;
        }
    }

    function formart_rdata($code, $msg, $data) {
        $rdata = $data;
        $rdata['code'] =  $code;
        $rdata['message'] =  $msg;
        $rdata['sign_type'] =  "RSA";
        $rdata['version'] =  "v1.0";
        $rdata['partner_id'] =  Config::get('conf_7881.partner_id');
        $rdata['timestamp'] =  hs_get_millisecond();

        $rdata["sign"] = $this->sub_class->build7881sign($rdata); // 参见 《7881 对外合作接口-接口规约和接口签名验签机制》之 接口签名和验签机制 部分的约定
        $rdata = urlencode_array($rdata);

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
