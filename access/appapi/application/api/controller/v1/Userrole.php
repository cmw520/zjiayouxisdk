<?php

/**
 * Usergame.php UTF-8
 * 用户游戏处理接口
 * @date: 2016年8月18日下午9:46:57
 * 
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\api\controller\v1;

use app\common\controller\Base;
use think\Db;

class Userrole extends Base
{
    private $key;
    function _initialize() {
        $this->key = "dc84ca832a9839a338acae806e34a268";
        parent::_initialize();
    }
    
    /*
     * 请求角色列表
     */
    public function index() {
        $map = $this->checkParam();
        $map['page'] = $this->request->get('page/d', 1); // 页
        $map['offset'] = $this->request->get('offset/d', 10); // 每页请求数量，默认为10
        $rdata = $this->getUrList($map);        
        
        if (empty($rdata['count'])) {
            return hs_api_responce(404, '无记录');
        }
        
        return hs_api_responce(200, '请求成功', $rdata);
    }
    public function checkParam() {
        $data['username'] = $this->request->get('username/s', ''); // username
        $data['gameid'] = $this->request->get('gameid/d', 0); // gameid
        $data['server'] = $this->request->get('server/s', ''); // server
        $data['role'] = $this->request->get('role/s', ''); // role
        $data['timestamp'] = $this->request->get('timestamp/d', 0); // timestamp
        $sign = $this->request->get('sign/s', ''); // sign
        $data['appid'] = $this->app_id;
        $data['clientid'] = $this->client_id;
        if (empty($data['timestamp'])) {
            return hs_api_responce('400', '时间戳错误');
        }
        if (empty($sign)) {
            return hs_api_responce('400', '签名为空');
        }
        if (empty($data['username'])) {
            return hs_api_responce('400', '用户名为空');
        }
        
        // IP验证
        // $ip = $this->request->ip();
        // if ($ip != ''){
            // return hs_api_responce('400', '非法请求');
        // }
        
        $this->verifySign($data, $sign);

        $this->mem_id = DB::name('members')->where(array(
            'username' => $data['username']
        ))->value('id');
        if (empty($this->mem_id)) {
            return hs_api_responce('400', '用户名非法');
        }
        
        $map['mem_id'] = $this->mem_id;
        if (!empty($data['gameid'])) {
            $map['app_id'] = $data['gameid'];
        }
        if (!empty($data['server'])) {
            $map['server'] = $data['server'];
        }
        if (!empty($data['role'])) {
            $map['role'] = $data['role'];
        }
        return $map;
    }
    
    /*
     * 获取游戏详情
     */
    public function read() {
        $map = $this->checkParam();
        
        $money = DB::name('mg_role')->where($map)->sum('money');
        if (empty($money)) {
            $money = 0;
        }
        // 获取总金额
        $rdata['money'] = $money;
        return hs_api_responce(200, '请求成功', $rdata);
    }
    
    // 获取游戏列表
    private function getUrList(array $where = array()) {
        $page = $where['page'];
        $offset = $where['offset'];
        $map = $where;
        unset($map['page']);
        unset($map['offset']);
        
        $field = array(
            'mr.mem_id' => 'userid', 
            'mr.app_id' => 'gameid', 
            'mr.server' => 'server', 
            'mr.role' => 'role', 
            'mr.level' => 'level', 
            'mr.money' => 'money', 
            'mr.experience' => 'experience', 
            "FROM_UNIXTIME(mr.update_time, '%Y-%m-%d %T')" => 'time' 
        );
        $data = array();
        $count = DB::name('mg_role')->alias('mr')->where($map)->count();
        if ($count > 0) {
            $m = ($page - 1) * $offset;
            $limit = $m . ',' . $offset;
            $order = 'id desc';
            $data = DB::name('mg_role')->alias('mr')->field($field)->where($map)->order($order)->limit($limit)->select();
            $count = count($data);
        }
        
        $rdata['count'] = $count;
        $rdata['list'] = $data;
        return $rdata;
    }
    

    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    
    function buildRequestMysign($para) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para);
    
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
    
        //生成签名结果
        $arg  = "";
        while (list ($key, $val) = each ($para_sort)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
    
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        $sign = md5($arg.'&key='.$this->key);
        return $sign;
    }
    
    function verifySign(array $data, $sign) {
        $vsign = $this->buildRequestMysign($data);
        if ($vsign != $sign){
            return hs_api_responce(400, '签名错误');
        }
    }
}
