<?php
/**
 * wap 网站接口
 *
 * @time   2017年2月21日10:58:10
 * @author 王闯
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class AppApiDevController extends MobilebaseController {
    private $image_prefix;
    private $shots_image_prefix;
    private $row;                   // 每页显示条数
    private $game_size;             // 游戏大小
    private $public_game;           // 公益游戏
    private $new_game;              // 新游戏
    private $key_game;              // 精品游戏
    private $sms_config;

    function _initialize() {
        parent::_initialize();
        $this->image_prefix = WEBSITE."/upload/logo/";
        $this->shots_image_prefix = WEBSITE."/upload/shot/";
        $this->row = 10;
        $this->game_size = '125 MB';
        $this->public_game = '公益';
        $this->new_game = '新游';
        $this->key_game = '精品';
        if (file_exists(SITE_PATH."conf/sms/setting.php")) {
            $_config = include SITE_PATH."conf/sms/setting.php";
        } else {
            $_config = array();
        }
        foreach ($_config as $_key => $_val) {
            if ($_val > 0) {
                $this->sms_config[$_key] = $_val;
            }
        }
    }

    // 获取分页类的条件字符串
    public function getPageString($row = 0) {
        $page = I('page/d', 1);
        $row = empty($row) ? $this->row : $row;
        $start = ($page - 1) * $row;
        return $start.','.$row;
        //$this->response($start.','.$row);
    }

    // 获取文章资讯
    public function getIosAppPosts() {
        $limit_str = $this->getPageString();
        $field = "id,smeta icon,post_type,";
        $field .= "post_title as title,post_modified as time,post_content as content";
        $items = M('posts')
            ->field($field)
            ->where(array('post_status' => "2"))
            ->limit($limit_str)
            ->order('post_modified desc')
            ->select();
        foreach ($items as $k => $v) {
            $items[$k]['label'] = $this->getPostType($v['post_type']);
            $items[$k]['time'] = date("Y-m-d", $v['time']);
            $items[$k]['content'] = strip_tags($v['content'], "<p><img><a>");
            $items[$k]['intro'] = mb_substr(strip_tags($v['content']), 0, 100);
            $items[$k]['icon'] = json_decode($v['icon'], true)['thumb'];
        }
        $this->response($items);
    }

    // 获取文章类型
    protected function getPostType($post_type = 0) {
        if (empty($post_type)) {
            $post_type = 0;
        }
        $types = ['0' => '所有', '1' => '新闻', '2' => '活动', '3' => '攻略'];
        return $types[$post_type];
    }

    // 获取精品游戏
    public function keyApps() {
        $limit_str = $this->getPageString(4);
        $items = $this->keyAppsList(array(), $limit_str);
        $this->response($items);
    }

    // 获取精品游戏的列表数据
    private function keyAppsList($where = array(), $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['gt.name'] = array("EQ", $this->key_game);
        $items = M('game')
            ->alias("g")
            ->field("g.id,g.icon,g.name,e.down_cnt clicknum")
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_gt t on t.app_id = g.id')
            ->join('left join '.C('DB_PREFIX').'game_type gt on gt.id=t.type_id')
            ->where($where)
            ->where($map)
            ->order("g.listorder desc")
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCatesSingle($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
        }
        return $items;
    }

    // 获取新游戏的列表数据
    public function newApps() {
        $where = array();
        $limit_str = $this->getPageString(5);
        $items = $this->newAppsList($where, $limit_str);
        $this->response($items);
    }

    // 获取新游戏的数据
    protected function newAppsList($where = array(), $limit_str = '') {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['i.lanmu'] = array("EQ", 3);
        $items = M('game')
            ->alias("g")
            ->field("g.id,g.icon,g.name,e.down_cnt clicknum")
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_info i on i.app_id = g.id')
            ->where($where)
            ->where($map)
            ->order("g.listorder desc")
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
            $items[$key]['size'] = $this->getGameSize($value['id']);
            $gift_cnt = M("gift")->where(array("app_id"=>$value['id']))->count();
            $items[$key]['gift_cnt'] = $gift_cnt ? $gift_cnt : 0;
        }
        return $items;
    }

    // 获取游戏的大小
    private function getGameSize($appid) {
        $size = M('game_info')->where(array('app_id' => $appid))->getField('size');
        return empty($size) ? $this->game_size : $size;
    }

    // 获取公益游戏
    public function GetGYApps() {
        $limit_str = $this->getPageString();
        $items = $this->GYAppsList(array(), $limit_str);
        $this->response($items);
    }

    // 获取公益游戏列表
    public function GYAppsList($where = array(), $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['gt.name'] = array("EQ", $this->public_game);
        $items = M('game')
            ->alias('g')
            ->field('g.id,g.mobile_icon icon,g.name,e.down_cnt clicknum')
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_gt t on t.app_id = g.id')
            ->join('left join '.C('DB_PREFIX').'game_type gt on gt.id=t.type_id')
            ->where($map)
            ->order('g.create_time desc')
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
            $items[$key]['size'] = $this->getGameSize($value['id']);
        }
        return $items;
    }

    // 获取热门游戏
    public function rankHotApps() {
        $limit_str = $this->getPageString();
        $items = $this->hotAppsList(array(), $limit_str);
        $this->response($items);
    }

    // 获取热门游戏数据
    private function hotAppsList($where = array(), $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['i.lanmu'] = array("EQ", 2);
        $items = M('game')
            ->alias('g')
            ->field('g.id,g.mobile_icon icon,g.name,e.down_cnt clicknum')
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('c_game_info i on i.app_id = g.id')
            ->where($map)
            ->limit($limit_str)
            ->order('g.listorder DESC')
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
            $items[$key]['size'] = $this->getGameSize($value['id']);
            $gift_cnt = M("gift")->where(array("app_id"=>$value["id"]))->count();
            $items[$key]['gift_cnt'] = $gift_cnt ? $gift_cnt : 0;
        }
        return $items;
    }

    // 新游戏排行榜
    public function rankNewApps() {
        $limit_str = $this->getPageString();
        $items = $this->newAppsList(array(), $limit_str);
        $this->response($items);
    }

    // 获取所有礼包的信息
    public function gift() {
        $limit_str = $this->getPageString();
        $where['gf.is_delete'] = 2;
        $items = $this->getGiftList($where, $limit_str);
        $this->response($items);
    }

    // 获取所有礼包的信息的数据
    public function getGiftList($where = array(), $limit_str = '0,5') {
        $map['g.status'] = array("ELT", 2);
        $map['gf.is_delete'] = array("EQ", 2);
        $items = M('gift')
            ->field("gf.id,gf.title as name, FORMAT((gf.remain/gf.total)*100,0) as remain,g.icon")
            ->alias("gf")
            ->where($where)
            ->where($map)
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gf.app_id")
            ->order("gf.id desc")
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['icon'] = WEBSITE.$value['icon'];
        }
        return $items;
    }

    // 获取单个礼包的详细信息
    public function giftDetail() {
        $id = I('id');
        $item = $this->getGiftInfo($id);
        $this->response($item);
    }

    // 获取单个礼包的详细信息的数据
    public function getGiftInfo($id) {
        $item = M('gift')
            ->field(
                "gf.id,gf.title as name, FORMAT((gf.remain/gf.total)*100,0) as remain,"
                ."g.icon,gf.content,"
                ."gf.start_time as start_time,gf.end_time as end_time,gf.app_id"
            )
            ->alias("gf")
            ->where(array("gf.id" => $id))
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gf.app_id")
            ->find();
        $item['icon'] = WEBSITE.$item['icon'];
        $item['start_time'] = date("Y-m-d H:i:s", $item['start_time']);
        $item['end_time'] = date("Y-m-d H:i:s", $item['end_time']);
        $item['content'] = nl2br($item['content']);
        return $item;
    }

    // 获取开服列表信息
    public function getServerList() {
        // 开服数据
        $field = "name, FROM_UNIXTIME(start_time, '%Y-%m-%d %H:%m:%s') start_time, icon";
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d')) + 86400;
        // 今日开服数据
        $limit_str = $this->getPageString(5);
        $where_today['start_time'] = array('between', $start_time.','.$end_time);
        //$where_today['w.status'] = 1;// 1、开服  2、开测
        $items['today_list'] = $this->getServerListData($field, $where_today, $limit_str);
        // 即将开服数据
        $limit_str = $this->getPageString(5);
        $where_before['start_time'] = array('gt', $end_time);
        //$where_before['w.status'] = 1;// 1、开服  2、开测
        $items['about_list'] = $this->getServerListData($field, $where_before, $limit_str);
        // 已经开服数据
        $limit_str = $this->getPageString(5);
        $where_after['start_time'] = array('lt', $start_time);
        //$where_after['w.status'] = 1;// 1、开服  2、开测
        $items['already_list'] = $this->getServerListData($field, $where_after, $limit_str);
        // 开测数据
        // 今日开测数据
        $limit_str = $this->getPageString(5);
        $where_today['start_time'] = array('between', $start_time.','.$end_time);
        //$where_today['w.status'] = 2;// 1、开服  2、开测
        $test_items['today_list'] = $this->getServerListData($field, $where_today, $limit_str);
        // 即将开测数据
        $limit_str = $this->getPageString(5);
        $where_before['start_time'] = array('gt', $end_time);
        //$where_before['w.status'] = 2;// 1、开服  2、开测
        $test_items['about_list'] = $this->getServerListData($field, $where_before, $limit_str);
        // 已经开测数据
        $limit_str = $this->getPageString(5);
        $where_after['start_time'] = array('lt', $start_time);
        //$where_after['w.status'] = 2;// 1、开服  2、开测
        $test_items['already_list'] = $this->getServerListData($field, $where_after, $limit_str);
        $this->response(array('error' => '0', 'msg' => [$items, $test_items]));
    }

    // 获取开服数据
    private function getServerListData($field, $where, $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $items = M('webServer')
            ->alias('w')
            ->field($field)
            ->join('left join '.C('DB_PREFIX').'game g on g.id=w.app_id')
            ->where($where)
            ->where($map)
            ->limit($limit_str)
            ->select();
        return $items;
    }

    // 获取文章资讯
    public function getPostList($where = array(), $limit_str) {
        $items = M('posts')
            ->field(
                "id,concat('".WEBSITE
                ."/upload/posts/',smeta) as icon,post_title as title,post_modified as time,post_content as content"
            )
            ->where(array('post_status' => "2"))
            ->where($where)
            ->limit($limit_str)
            ->order('post_modified desc')
            ->select();
        foreach ($items as $k => $v) {
            $items[$k]['label'] = $this->getAppCatesSingle($v['id']);
            $items[$k]['time'] = date("Y-m-d", $v['time']);
            $items[$k]['content'] = strip_tags($v['content'], "<p><img><a>");
            $items[$k]['intro'] = mb_substr(strip_tags($v['content']), 0, 100);
        }
        return $items;
    }

    // 退出
    public function logout() {
        unset($_SESSION['user']);
        session_destroy();
        $this->response(array("error" => "0", "msg" => "退出成功"));
    }

    // 找回密码
    public function findpwd() {
        if (empty(I("phone_code"))) {
            $this->response(array("error" => "1", "msg" => "请输入验证码"));
        }
        if ($_SESSION['phone_verify_code'] != I('phone_code')) {
            $this->response(array("error" => "1", "msg" => "验证码错误"));
        }
        $phone = I('phone');
        if (!$this->validePhone($phone)) {
            $this->response(array("error" => "1", "msg" => "手机号格式不正确"));
        }
        $exist = M('members')->where("`mobile` = '$phone'")->find();
        if (!$exist) {
            $this->response(array("error" => "1", "msg" => "手机号不存在"));
        }
        $data = array();
        $data['password'] = member_password(I('password'));
        $data['update_time'] = time();
        M('members')->where(array("mobile" => $phone))->save($data);
        $this->saveSess($exist['id']);
        $this->response(array("error" => "0", "msg" => $exist['id']));
    }

    // 获取玩家的登录状态
    public function loginState() {
        if (!isset($_SESSION['user'])) {
            $this->response(array("state" => "false", "user" => ""));
        }
        $this->response(array("state" => "true", "user" => $this->getUserInfo($_SESSION['user']['id'])));
    }

    // 获取公司信息
    public function getCompanyAbout() {
        $data = M('web_aboutus')->where(array("title" => "关于我们"))->find();
        if ($data) {
            $result = strip_tags($data['content'], "<p><a><span>");
        } else {
            $result = "";
        }
        $this->response($result);
    }

    // 获取渠道的联系的信息
    public function getContactInfo() {
        $data = M('game_contact')->where("app_id = %d ", 0)->find();
        $txt = '';
        if ($data) {
            $txt .= "<p>电话：".$data['tel']."</p>"
                    ."<p>QQ：".$data['qq']."</p>"
                    ."<p>微信：".$data['weixin']."</p>"
                    ."<p>邮件：".$data['email']."</p>"
                    ."<p>QQ群：".$data['qqgroup']."</p>";
        }
        $this->response($txt);
    }

    // 获取玩家的礼包信息
    public function myGiftCodes() {
        if (!isset($_SESSION['user'])) {
            // $this->response(array("error"=>"1","msg"=>"需要登录"));
            $this->response(array());
        }
        $mem_id = $_SESSION['user']['id'];
        $where["gc.mem_id"] = $mem_id;
        $limit_str = $this->getPageString();
        $items = $this->getMyGiftCodes($where, $limit_str);
        $this->response($items);
    }

    // 获取玩家的礼包详细信息
    public function getMyGiftCodes($where = array(), $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $items = M('gift_code')
            ->field("gc.code,gf.title as name,g.icon")
            ->alias("gc")
            ->where($where)
            ->where($map)
            ->join("LEFT JOIN ".C("DB_PREFIX")."gift gf ON gf.id=gc.gf_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gf.app_id")
            ->order("gc.id desc")
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['icon'] = WEBSITE.$value['icon'];
        }
        return $items;
    }

    // 获取首页轮播图的信息
    public function getSlides() {
        // 判断c_slide_cat 表中是否存在 wap轮播图
        $id = M('slide_cat')->where(array('cat_name' => 'wap轮播图'))->getField('cid');
        if (empty($id)) {
            $data['cat_name'] = 'wap轮播图';
            $data['cat_idname'] = 'wapindex';
            $data['cat_remark'] = 'wap轮播图';
            $id = M('slide_cat')->data($data)->add();
        }
        $items = M('slide')
            ->field("slide_pic image,slide_url url, target_id app_id")
            ->where(array("slide_cid" => $id, "slide_status" => "2"))
            ->order("listorder asc")
            ->limit(0, 4)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['image'] = WEBSITE.$value['image'];
        }
        $this->response($items);
    }

    // 获取玩家的信息
    public function getUserInfo($mid) {
        $result = M('members')
            //->field("id,username,head_img as icon")
            // debug Android 这边没有头像的字段 暂时采用默认的图片
            ->field("id,username")
            ->where(array("id" => $mid))
            ->find();
        if ($result) {
            $result['gift_count'] = $this->getMyGiftCodeCount($mid);
            if (!$result['icon']) {
                $result['icon'] = WEBSITE.'/upload/logo/'."touxiang.jpg";
            }
            return $result;
        }
    }

    // 获取玩家的礼包码数量
    public function getMyGiftCodeCount($mem_id) {
        if (!isset($_SESSION['user'])) {
            return 0;
        }
        $where = array();
        if (!$mem_id) {
            $mem_id = $_SESSION['user']['id'];
        }
        $where["gc.mem_id"] = $mem_id;
        $result = M('gift_code')
            ->alias("gc")
            ->where($where)
            ->count();
        return $result;
    }

    // 领取礼包码
    public function getGiftCode() {
        if (!isset($_SESSION['user'])) {
            $this->response(array("error" => "1", "msg" => "登陆后才能领取礼包"));
        }
        $gf_id = $_GET['gift_id'];
        $items = M('gift_code')
            ->where(array("gf_id" => $gf_id, "_string" => "`mem_id` IS NULL OR `mem_id` = 0"))
            ->order("id desc")
            ->select();
        if (!$items) {
            $this->response(array("error" => "1", "msg" => "礼包已抢光"));
        }
        $prev_items = M('gift_code')
            ->where(array("gf_id" => $gf_id, "mem_id" => $_SESSION['user']['id']))
            ->select();
        if ($prev_items) {
            $this->response(array("error" => "1", "msg" => "你已经领取过了，不要太贪心哦"));
        }
        $data = array();
        $data['update_time'] = time();
        $data['mem_id'] = $_SESSION['user']['id'];
        M('gift_code')->where(array("id" => $items[0]['id']))->save($data);
        //要更新礼包的剩余数量
        M('gift')->where(array("id" => $gf_id))->setDec("remain");
        $this->response(array("error" => "0", "msg" => "礼包码领取成功: ".$items[0]['code']."，已存入卡箱"));
    }

    // 验证手机号
    private function validePhone($mobile) {
        return preg_match("/^1[34578]{1}\d{9}$/", $mobile);
    }

    // 验证用户名
    private function valideUsername($v) {
        return preg_match("/^[a-zA-Z0-9]{6,30}$/", $v);
    }

    // 发送短信接口
    public function sendPhoneCode() {
        if (IS_POST) {
            $mobile = I("post.mobile/s");
            if (!preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
                $this->response(array('error' => "1", 'msg' => '请输入正确的手机号码'));
            }
            // 判断缓存时间
            if (isset($_SESSION['aforgettime'])) {
                session_id();
                $_SESSION['aforgettime'] = null;
            } else {
                $_SESSION['aforgettime'] = date("Y-m-d H:i:s");
            }
            $mcode = mt_rand(10000, 99999);
            $_SESSION['phone_verify_code'] = $mcode; // 将content的值保存在session中
            $_rdata = $this->smsSelect($mobile, '', $mcode);
//            // 发送短信接口，（跃动科技短信）
//            $resp = $this->codeApiMovek($mobile, $mcode);
//            $return_result = simplexml_load_string($resp);
//            $r_message = (string)$return_result->message;
//            if ($return_result->returnstatus === "Faild") {
//                $return_result = array("error" => "1", "msg" => $r_message);
//            } else {
//                $return_result = array("error" => "0", "msg" => "发送成功，请尽快输入");
//            }
            if ($_rdata) {
                $return_result = array("error" => "0", "msg" => "发送成功，请尽快输入");
            } else {
                $return_result = array("error" => "0", "msg" => "发送失败");
            }
            $this->response($return_result);
        }
    }

    // 判断手机号码是否存在，并发送短信 找回密码时用到
    public function sendPhoneCodeToExist() {
        $phone = I("post.mobile/s");
        $exist = M('members')->where("`mobile` = '$phone'")->find();
        if (!$exist) {
            $this->response(array("error" => "1", "msg" => "手机号不存在"));
        }
        $this->sendPhoneCode();
    }

    // 发送短信（跃动科技短信）
    public function codeApiMovek($phone, $code) {
        $post_data = array();
        $post_data['userid'] = '####';//改为自己的id
        $post_data['account'] = '#####';
        $post_data['password'] = '######';
        $post_data['content'] = iconv("UTF-8", "UTF-8", "【6533游戏】尊敬的用户，您的短信验证码是 $code ，请在30分钟内输入，谢谢！");
        $post_data['mobile'] = $phone;
        $post_data['sendtime'] = ''; //不定时发送，值为0，定时发送，输入格式YYYYMMDDHHmmss的日期值
        $url = 'http://218.244.136.70:8888/sms.aspx?action=send';
        $o = '';
        foreach ($post_data as $k => $v) {
            $o .= "$k=".urlencode($v).'&';
        }
        $post_data = substr($o, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $result = curl_exec($ch);
        return $result;
    }

    //短信选择
    public function smsSelect($phone, $type = "", $code) {
        if (file_exists(SITE_PATH."conf/sms/setting.php")) {
            $setconfig = include SITE_PATH."conf/sms/setting.php";
            $i = 1;
            foreach ($setconfig as $k => $v) {
                if ($v > 0) {
                    $sendtype = $i;
                    break;
                }
                $i += 1;
            }
        } else {
            $sendtype = 1;
        }
        if (1 == $sendtype) {
            $al_rs = $this->send_alidayu_sms_code($phone, $code, $type);
        } else if (2 == $sendtype) {
            $al_rs = $this->send_ytx_sms_code($phone, $code, $type);
        } else if (3 == $sendtype) {
            $al_rs = $this->send_shangxun_sms_code($phone, $code, $type);
        } else if (4 == $sendtype) {
            $al_rs = $this->send_juhe_sms_code($phone, $code, $type);
        } else if (5 == $sendtype) {
            $al_rs = $this->send_chuanglan_sms_code($phone, $code, $type);
        } else {
            $al_rs = $this->send_alidayu_sms_code($phone, $code, $type);
        }
        return $al_rs;
    }

    /**
     * 发送 创蓝短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_chuanglan_sms_code($phone, $code, $type) {
        include LIB_PATH."Huosdk/sms/ChuanglanSmsApi.class.php";
        $req = new \ChuanglanSmsApi();
        $result = $req->sendSMS($phone, $code, true);
        $result = $req->execResult($result);
        if (0 == $result[1]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送 容联云通讯 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     *
     * @return boole 是否发送成功
     */
    private function send_ytx_sms_code($phone, $code) {
        // 获取容联云配置信息
        if (file_exists(SITE_PATH."conf/sms/yuntongxun.php")) {
            $ytx_config = include SITE_PATH."conf/sms/yuntongxun.php";
        } else {
            return false;
        }
        if (empty($ytx_config)) {
            return false;
        }
        // 请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';
        // 请求端口
        $serverPort = '8883';
        // REST版本号
        $softVersion = '2013-12-26';
        // 主帐号
        $accountSid = $ytx_config['RONGLIAN_ACCOUNT_SID'];
        // 主帐号Token
        $accountToken = $ytx_config['RONGLIAN_ACCOUNT_TOKEN'];
        // 应用Id
        $appId = $ytx_config['RONGLIAN_APPID'];
        $rest = new \Org\Xb\Rest($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $result = $rest->sendTemplateSMS(
            $phone, array(
            $code,
            5
        ), 59939
        );
        if ($result == null) {
            return false;
        }
        if ($result->statusCode != 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 发送 容联云通讯 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_alidayu_sms_code($phone, $code, $type) {
        include VENDOR_PATH."taobao/TopSdk.php";
        include VENDOR_PATH."taobao/top/TopClient.php";
        include VENDOR_PATH."taobao/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
        // 获取阿里大鱼配置信息
        if (file_exists(SITE_PATH."conf/sms/alidayu.php")) {
            $dayuconfig = include SITE_PATH."conf/sms/alidayu.php";
        } else {
            return false;
        }
        if (empty($dayuconfig)) {
            return false;
        }
        $product = $dayuconfig['PRODUCT'];
        $content = array(
            "code"    => "".$code,
            "product" => $product
        );
        $smstemp = 'SMSTEMPAUTH';
        if ($type == 1) {
            $smstemp = 'SMSTEMPREG';
        }
        $c = new \TopClient();
        $c->appkey = $dayuconfig['APPKEY'];
        $c->secretKey = $dayuconfig['APPSECRET'];
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend($dayuconfig['SETEXTEND']);
        $req->setSmsType($dayuconfig['SMSTYPE']);
        $req->setSmsFreeSignName($dayuconfig['SMSFREESIGNNAME']);
        $req->setSmsParam(json_encode($content));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($dayuconfig[$smstemp]);
        $resp = $c->execute($req);
        $resp = (array)$resp;
        if (!empty($resp['result'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送 商讯短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_shangxun_sms_code($phone, $code, $type) {
        // 获取商讯短信配置信息
        if (file_exists(SITE_PATH."conf/sms/shangxun.php")) {
            $sx_config = include SITE_PATH."conf/sms/shangxun.php";
        } else {
            return false;
        }
        if (empty($sx_config)) {
            return false;
        }
        $msg = urlencode(self::content($code));
        $name = $sx_config['SMS_ACC'];
        $pwd = $sx_config['SMS_PWD'];
        $url = $sx_config['SMS_URL'];
        $ret = file_get_contents($url."?name=$name&pwd=$pwd&dst=$phone&msg=$msg");
        $result = explode("&", $ret);
        $num = explode("=", $result[0]);
        if (0 == $num[1]) {
            return true;
        } else {
            return false;
        }
    }

    protected static function content($capcha) {
        return str_replace("#code#", $capcha, self::auto_read(self::$template, "GBK"));
    }

    /**
     * 自动解析编码读入文件
     *
     * @param string $str     字符串
     * @param string $charset 读取编码
     *
     * @return string 返回读取内容
     */
    private static function auto_read($str, $charset = 'UTF-8') {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return mb_convert_encoding($str, $charset, $item);
            }
        }
        return "";
    }

    /**
     * 发送 juhe短信 验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @param int    $type  发送类型
     *
     * @return boole 是否发送成功
     */
    private function send_juhe_sms_code($phone, $code, $type) {
        // 获取商讯短信配置信息
        if (file_exists(SITE_PATH."conf/sms/juhe.php")) {
            $jh_config = include SITE_PATH."conf/sms/juhe.php";
        } else {
            return false;
        }
        if (empty($jh_config)) {
            return false;
        }
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $tplValue = urlencode("#code#=".$code);
        $smsConf = array(
            'key'       => $jh_config['APPKEY'], //您申请的APPKEY
            'mobile'    => $phone, //接受短信的用户手机号码
            'tpl_id'    => $jh_config['TEMPLETID'], //您申请的短信模板ID，根据实际情况修改
            'tpl_value' => $tplValue //您设置的模板变量，根据实际情况修改
        );
        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信
        if ($content) {
            $result = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 请求接口返回内容
     *
     * @param  string $url    [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int    $ipost  [是否采用POST形式]
     *
     * @return  string
     */
    public function juhecurl($url, $params = false, $ispost = 0) {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt(
            $ch, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22'
        );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    // 注册
    public function reg() {
        if (empty(I('phone_code'))) {
            $this->response(array("error" => "1", "msg" => "请输入验证码"));
        }
        if ($_SESSION['phone_verify_code'] != I('phone_code')) {
            $this->response(array("error" => "1", "msg" => "验证码错误"));
        }
        $phone = I('phone');
        if (!$this->validePhone($phone)) {
            $this->response(array("error" => "1", "msg" => "手机号格式不正确"));
        }
        $phone = I('phone');
        $duplicate = M('members')->where("`mobile` = '$phone' OR `username` = '$phone' ")->find();
        if ($duplicate) {
            $this->response(array("error" => "1", "msg" => "此手机号已被使用"));
        }
        $sex_txt = I('sex');
        if ($sex_txt == "male") {
            $sex = 1;
        } else if ($sex_txt == "female") {
            $sex = 2;
        }
        $data = array();
        $data['username'] = $phone;
        $data['sex'] = $sex;
        $data['password'] = member_password(I('password'));
        $data['pay_pwd'] = member_password(I('password'));
        $data['mobile'] = $phone;
        $data['nickname'] = $phone;
        $data['reg_time'] = time();
        $data['update_time'] = time();
        $data['regist_ip'] = get_client_ip(0, true);
        $data['from'] = 1; //1 ANDROID、2 H5、3 IOS
        $data['status'] = 2; //1 为试玩状态 2为正常状态，3为冻结状态
        $data['app_id'] = 0;
        $data['agent_id'] = 0;
        $user_id = M('members')->add($data);
        $this->saveSess($user_id);
        $this->response(array("error" => "0", "msg" => $user_id));
    }

    public function regNormal() {
        $phone = I('phone');
        if (!$this->valideUsername($phone)) {
            $this->response(array("error" => "1", "msg" => "用户名格式不正确"));
        }
        $duplicate = M('members')->where("`mobile` = '$phone' OR `username` = '$phone' ")->find();
        if ($duplicate) {
            $this->response(array("error" => "1", "msg" => "此用户名已被使用"));
        }
        $sex_txt = I('sex');
        if ($sex_txt == "male") {
            $sex = 1;
        } else if ($sex_txt == "female") {
            $sex = 2;
        }
        /*
         * 数据库中创建用户
         * 2017-01-10 12:07:19
         * 严旭
         */
        $data = array();
        $data['username'] = $phone;
        $data['sex'] = $sex;
        $data['password'] = member_password(I('password'));
        $data['nickname'] = $phone;
        $data['ip'] = get_client_ip();
        $data['reg_time'] = time();
        $data['update_time'] = time();
        $data['last_login_time'] = time();
        $data['last_login_ip'] = get_client_ip(0, true);
        $data['from'] = 4;
        $data['status'] = 1;
        $data['app_id'] = 0;
        $data['agent_id'] = 0;
        $data['flag'] = 2;
        $user_id = M('members')->add($data);
        $this->saveSess($user_id);
        $this->response(array("error" => "0", "msg" => "注册成功"));
    }

    // 用户登录、注册、找回密码之后保存session
    protected function saveSess($user_id) {
        $data = M('members')->where(array('id' => $user_id))->find();
        $_SESSION['user'] = $data;
    }

    // 搜索游戏
    public function search() {
        $keyword = I('keyword');
        $where = array("g.name" => array("like", "%$keyword%"),"is_delete" => 2);
        $limit_str = $this->getPageString();
        $items = $this->appsList($where, $limit_str);
        $this->response($items);
    }

    // 获取搜索的数据
    public function appsList($where, $limit_str) {
        $items = M('game')
            ->alias("g")
            ->field("g.id,g.icon,g.name,e.down_cnt clicknum")
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            // ->join('left join '.C('DB_PREFIX').'game_gt t on t.app_id = g.id')
            // ->join('left join '.C('DB_PREFIX').'game_type gt on gt.id=t.type_id')
            ->where($where)
            //->where(array("g.status"=>2))
            ->order("g.listorder desc")
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
            $items[$key]['size'] = $this->getGameSize($value['id']);
        }
        return $items;
    }

    // 获取游戏的分类
    public function cates() {
        $items = M('game_type')
            ->field("id,name,image as icon")
            ->alias('gt')
            ->order('id asc')
            ->select();
        foreach ($items as $key => $value) {
            if ($value['icon']) {
                $items[$key]['icon'] = WEBSITE.$value['icon'];
            } else {
                $items[$key]['icon'] = WEBSITE.'/upload/logo/'."12.png";
            }
            $map['g.status'] = array("ELT", 2);
            $map['g.is_delete'] = array("EQ", 2);
            $map['gg.type_id'] = array("EQ", $value['id']);
            $map['gi.lanmu'] = array('NEQ',1);
            $items[$key]['num'] = M('game_gt')
                ->alias('gg')
                ->where($map)
                ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=gg.app_id")
                ->join("LEFT JOIN ".C("DB_PREFIX")."game_info gi ON g.id=gi.app_id")
                ->count();
        }
        $this->response($items);
    }

    // 检查玩家是否登录
    public function checkLogin() {
        $enc_pass = member_password(I('password'));
        $result = M('members')
            //->field("id,username,head_img as icon")
            // debug Android 这边没有头像的字段
            ->field("id,username")
            ->where(array("username" => I('username'), "password" => $enc_pass))->find();
        if ($result) {
            $_SESSION['user'] = $result;
            $result['gift_count'] = $this->getMyGiftCodeCount($result['id']);
            if (!$result['icon']) {
                $result['icon'] = $this->image_prefix."touxiang.jpg";
            }
            $this->response(array("error" => "0", "msg" => $result));
        } else {
            $this->response(array("error" => "1", "msg" => "用户名或密码错误"));
        }
    }

    public function response($data) {
        header("Access-Control-Allow-Origin:*");
        /*星号表示所有的域都可以接受，*/
        header("Access-Control-Allow-Methods:GET,POST");
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    // 获取游戏的类型
    private function getAppCates($appid) {
        $type_list = M('game')->where(array("id" => $appid))->getField("type");
        if ($type_list) {
            $items = M('game_type')
                ->field("name")
                ->where(array("id" => array("IN", $type_list)))
                ->select();
            $txt = '';
            foreach ($items as $key => $value) {
                $txt .= " ".$value['name'];
            }
            return $txt;
        }
    }

    // 获取游戏的标签
    protected function getAppCatesArr($appid) {
        $type_list = M('game')->where(array("id" => $appid))->getField("type");
        if ($type_list) {
            $items = M('game_type')
                ->field("name")
                ->where(array("id" => array("IN", $type_list)))->select();
            return $items;
        }
    }

    public function onlineNewApps() {
        $page = 1;
        if (isset($_GET['page']) && ($_GET['page'])) {
            $page = $_GET['page'];
        }
        $where = array("g.classify" => 1);
        $num_per_page = 10;
        $start = ($page - 1) * $num_per_page;
        $limit = $num_per_page;
        $items = $this->newAppsList($where, $start, $limit);
        $this->response($items);
    }

    public function onlineHotApps() {
        $page = 1;
        if (isset($_GET['page']) && ($_GET['page'])) {
            $page = $_GET['page'];
        }
        $where = array("g.classify" => 1);
        $num_per_page = 10;
        $start = ($page - 1) * $num_per_page;
        $limit = $num_per_page;
        $items = $this->hotAppsList($where, $start, $limit);
        $this->response($items);
    }

    // 获取分类中的新游戏
    public function cateNewApps() {
        $where = array();
        $limit_str = $this->getPageString(10);
        $items = $this->cateNewAppsList($where, $limit_str);
        $this->response($items);
    }

    // 获取分类中的热门游戏
    public function cateHotApps() {
        $where = array();
        $limit_str = $this->getPageString(10);
        $items = $this->cateHotAppsList($where, $limit_str);
        $this->response($items);
    }

    public function keyAppsDj() {
        $where = array("classify" => 2);
        $items = $this->keyAppsListDj($where, '0,2');
        $this->response($items);
    }

    public function keyAppsListDj($where = array(), $limit_str) {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $items = M('game')
            ->field("id,icon,name,listorder clicknum")
            ->where($where)
            ->where($map)
            ->order("id desc")
            ->limit($limit_str)->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCatesSingle($value['id']);
            $items[$key]['icon'] = $this->image_prefix.$value['icon'];
        }
        return $items;
    }

    // 获取热门游戏的列表
    public function cateNewAppsList($where = array(), $limit_str = '') {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['t.type_id'] = array("EQ",I('get.cateid'));
        $map['i.lanmu'] = array("EQ",3);
        $items = M('game')
            ->alias('g')
            ->field('g.id,g.mobile_icon icon,g.name,i.bigimage,i.url,i.publicity,i.url,e.down_cnt clicknum')
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_gt t on t.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_info i on i.app_id = g.id')
            ->where($where)
            ->where($map)
            ->order('g.listorder DESC')
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = $value['icon'];
        }
        return $items;
    }

    // 获取热门游戏的列表
    public function cateHotAppsList($where = array(), $limit_str = '') {
        $map['g.status'] = array("ELT", 2);
        $map['g.is_delete'] = array("EQ", 2);
        $map['t.type_id'] = array("EQ",I('get.cateid'));
        $map['i.lanmu'] = array("EQ",2);
        $items = M('game')
            ->alias('g')
            ->field('g.id,g.mobile_icon icon,g.name,i.bigimage,i.url,i.publicity,i.url,e.down_cnt clicknum')
            ->join('left join '.C('DB_PREFIX').'game_ext e on e.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_gt t on t.app_id=g.id')
            ->join('left join '.C('DB_PREFIX').'game_info i on i.app_id = g.id')
            ->where($where)
            ->where($map)
            ->order('g.listorder DESC')
            ->limit($limit_str)
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCates($value['id']);
            $items[$key]['catesArr'] = $this->getAppCatesArr($value['id']);
            $items[$key]['icon'] = $value['icon'];
        }
        return $items;
    }

    public function test() {
        echo 'aa';
        exit;
        $items = $this->getAppCates("100012");
        print_r($items);
    }

    // 获取游戏的单个类型
    protected function getAppCatesSingle($appid) {
        $type_list = M('game')->where(array("id" => $appid))->getField("type");
        if ($type_list) {
            $items = M('game_type')
                ->field("name")
                ->where(array("id" => array("IN", $type_list)))
                ->find();
            return $items['name'];
        }
    }

    // 第三方登录
    public function loginThird1() {
        $type = I('type');
        $type_ori = I('type_ori');
        if ($type_ori !== "2" && $type_ori !== '3') {
            $this->response(array("error" => "1", "msg" => "第三方登录方式非法"));
        }
        if ($type_ori == "2") {
            $type = 'qq';
        } else if ($type_ori == "3") {
            $type = 'wx';
        }
//        if($type!=="qq" && $type!=='wx'){
//            $this->response(array( "error"=>"1","msg"=>"第三方登录方式非法".json_encode($_POST) ));
//        }
        $open_id = I('open_id');
        $access_token = I('access_token');
        if (!$open_id || !$access_token) {
            $this->response(array("error" => "1", "msg" => "参数有误"));
        }
        $thirdlogin_obj = new \Huosdk\ThirdLogin\Base();
        $prev_info = $thirdlogin_obj->userExist($type, $open_id, $access_token);
        if ($prev_info) {
            $member_info = $prev_info;
        } else {
            $member_info = $thirdlogin_obj->createUser($type, $open_id, $access_token);
        }
        $this->loginAfter($member_info);
    }

    public function loginAfter($member_info) {
        $member_info['icon'] = $member_info['head_img'];
        $_SESSION['user'] = $member_info;
        if (!$member_info['icon']) {
            $member_info['icon'] = $this->image_prefix."touxiang.jpg";
        }
        $member_info['gift_count'] = $this->getMyGiftCodeCount($member_info['id']);
        $this->response(array("error" => "0", "msg" => $member_info));
    }

    // 登录微信
    public function doLoginWx($open_id, $access_token, $user_info) {
        $thirdlogin_obj = new \Huosdk\ThirdLogin\Base();
        $prev_info = $thirdlogin_obj->userExistWx($open_id, $access_token, $user_info);
        if ($prev_info) {
            return $prev_info;
        } else {
            $added_user = $thirdlogin_obj->createUserWx($open_id, $access_token, $user_info);
            return $added_user;
        }
    }

    // 第三方登录
    public function loginThird() {
        $type = I('type');
        $type_ori = I('type_ori');
        if ($type_ori !== "2" && $type_ori !== '3') {
            $this->response(array("error" => "1", "msg" => "第三方登录方式非法"));
        }
        if ($type_ori == "2") {
            $type = 'qq';
        } else if ($type_ori == "3") {
            $type = 'wx';
        }
//        if($type!=="qq" && $type!=='wx'){
//            $this->response(array( "error"=>"1","msg"=>"第三方登录方式非法".json_encode($_POST) ));
//        }
        $open_id = I('open_id');
        $access_token = I('access_token');
        $debug_file = fopen("/home/logs/app_login_log.txt", "a+");
        fwrite($debug_file, date("Y-m-d H:i:s")."\r\n $type $open_id $access_token \r\n");
        if (!$open_id || !$access_token) {
            $this->response(array("error" => "1", "msg" => "参数有误"));
        }
        if ($type == "qq") {
            $unionid = $open_id;
        } else if ($type == "wx") {
            $user_info = $this->thirdLoginWxGetInfo($open_id, $access_token);
            $unionid = $user_info['unionid'];
        }
        $o_data = M('oauth_member')->where(array("openid" => $unionid))->find();
        if ($o_data) {
            /**
             * 如果oauth_member有这个信息，说明用户已经通过此第三方登录方式登录过
             * 可以直接获取用户的信息
             * 2017-01-11 21:46:14
             * 严旭
             */
            $member_id = $o_data['mid'];
            $member_info = M('members')->field("id,username,head_img as icon")->where(array("id" => $member_id))->find(
            );
            M('oauth_member')->where(array("openid" => $open_id))->setField("access_token", $access_token);
        } else {
            /**
             * 如果里面没有信息，说明用户是第一次通过此第三方登录方式登录
             * 要创建这个用户的信息再返回
             */
            $current_max_id = M('members')->max("id") + 1;
            $username = $type.$current_max_id.rand(0, 1000);
            if ($type == "qq") {
                $user_info = $this->thirdLoginQQGetInfo($open_id, $access_token);
            } else if ($type == "wx") {
                $user_info = $this->thirdLoginWxGetInfo($open_id, $access_token);
            }
            fwrite($debug_file, "".json_encode($user_info)." \r\n");
            if (!$user_info) {
                $this->response(array("error" => "1", "msg" => "获取用户个人信息失败"));
            }
            $default_password = "123456";
            $data = array(
                'username'        => $username,
                'nickname'        => $user_info['nickname'],
                'head_img'        => $user_info['headimgurl'],
                'password'        => member_password($default_password),
                'last_login_ip'   => get_client_ip(0, true),
                'reg_time'        => time(),
                'from'            => 2,
                'last_login_time' => time(),
                'status'          => 1,
                'app_id'          => 0,
                'agent_id'        => 0,
                'flag'            => 3,
                'login_device'    => get_deviceinfo()
            );
            $added_member_id = M('members')->add($data);
            $data_om = array(
                "mid"             => $added_member_id,
                // 特别说明，对于微信而言，要在app和网站中同步登录，必须用unionid
                // 严旭
                // 2017-01-21 11:46:48
                "openid"          => $unionid,
                "access_token"    => $access_token,
                "from"            => $type,
                "head_img"        => $user_info['headimgurl'],
                "name"            => $user_info['nickname'],
                'last_login_ip'   => get_client_ip(0, true),
                'last_login_time' => time(),
                'login_times'     => 1,
                'create_time'     => time(),
            );
            M('oauth_member')->add($data_om);
            $member_info = M('members')->field("id,username,head_img as icon")->where(array("id" => $added_member_id))
                                       ->find();
        }
        fwrite($debug_file, "".json_encode($o_data)." \r\n");
        fclose($debug_file);
        $_SESSION['user'] = $member_info;
        if (!$member_info['icon']) {
            $member_info['icon'] = $this->image_prefix."touxiang.jpg";
        }
        $member_info['gift_count'] = $this->getMyGiftCodeCount($member_info['id']);
        $this->response(array("error" => "0", "msg" => $member_info));
    }

    // 登录第三方QQ
    public function thirdLoginQQGetInfo($open_id, $access_token) {
//        $info_url = "https://graph.qq.com/user/get_user_info?access_token=".$access_token."&oauth_consumer_key=".$this->_qqappid."&openid=".$open_id;
//        $info_url = "https://graph.qq.com/user/get_user_info?access_token=".$access_token."&openid=".$open_id;
//		$user_info = json_decode(file_get_contents($info_url));
//        if(!$user_info){
//            return false;
//        }
//        $user_info = get_object_vars($user_info);
//        
//        $result=array();
//        $result['headimgurl']=$user_info['figureurl_qq_1'];
//        $result['nickname']=$user_info['nickname'];
//        return $result;
        $qq_appid = "101375586";
        $info_url = "https://graph.qq.com/user/get_user_info?access_token=".$access_token."&oauth_consumer_key="
                    .$qq_appid."&openid=".$open_id;
        $user_info = json_decode(file_get_contents($info_url));
        $user_info = get_object_vars($user_info);
        $result = array();
        $result['headimgurl'] = $user_info['figureurl_2'];
        $result['nickname'] = $user_info['nickname'];
        return $result;
    }

    // 登录第三方的微信信息
    public function thirdLoginWxGetInfo($open_id, $access_token) {
        //获取用户个人信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$open_id
                         .'&lang=zh_CN';
        //转成对象
        $ori_userinfo = file_get_contents($user_info_url);
        $user_info = json_decode($ori_userinfo);
        if (isset($user_info->errcode)) {
            return false;
        }
        $user_info = get_object_vars($user_info);
        $result = array();
        $result['headimgurl'] = $user_info['headimgurl'];
        $result['nickname'] = $user_info['nickname'];
        $result['unionid'] = $user_info['unionid'];
        $debug_file = fopen("/home/logs/3rd.txt", "a+");
        fwrite($debug_file, $ori_userinfo."\r\n");
        fclose($ori_userinfo);
        return $result;
    }

    public function getIosAppPostDetail() {
        $id = $_GET['id'];
        $items = M('posts')
            ->field(
                "id,concat('".WEBSITE."/upload/posts/',smeta) as icon,"
                ."post_title as title,post_modified as time,post_content as content"
            )
            ->where(array("id" => $id))
            ->order('post_modified desc')
            ->select();
        foreach ($items as $k => $v) {
            $items[$k]['time'] = date("Y-m-d", $v['time']);
        }
        $m_content = preg_replace("/style=.+?['|\"]/i", "", strip_tags($items[0]['content'], "<p><a><img>"));
        $this->response(
            "<p style='font-size:18px;margin-bottom:10px;border-bottom:1px solid #ccc;font-weight:bold;'>"
            .$items[0]['title']."</p>".
            "<p style='font-size:12px;margin-bottom:10px;color:#333;'>".$items[0]['time']."</p>".$m_content
        );
    }

    // 获取游戏的详细信息
    public function getAppDetailInfo() {
        $id = $_GET['id'];
        $field = "g.id,g.icon,g.name,e.down_cnt clicknum,i.description,i.androidurl as download_url,i.image as shots";
        $items = M('game')
            ->alias('g')
            ->field($field)
            ->join('left join '.C('DB_PREFIX').'game_info i on g.id=i.app_id')
            ->join('left join '.C('DB_PREFIX').'game_ext e on g.id=e.app_id')
            ->where(array("id" => $id))
            ->order("id desc")
            ->select();
        foreach ($items as $key => $value) {
            $items[$key]['cates'] = $this->getAppCatesSingle($value['id']);
            $items[$key]['icon'] = WEBSITE.$value['icon'];
            $images = json_decode($value['shots'], true);
            if (!empty($images)) {
                foreach ($images as $k => $v) {
                    $shots_arr[] = array("image" => WEBSITE.$v['url']);
                }
                $items[$key]['shots'] = $shots_arr;
            } else {
                $items[$key]['shots'] = array();
            }
        }
        $gift_data = $this->getGiftList(array("gf.app_id" => $id), '0,5');
        $news_data = $this->getPostList(array("app_id" => $id), '0,5');
        $result = array();
        $result['game_info'] = $items[0];
        $result['gift_info'] = $gift_data;
        $result['news_info'] = $news_data;
        $this->response($result);
    }

    public function getCaptchaImg() {
        $length = 4;
        if (isset($_GET['length']) && intval($_GET['length'])) {
            $length = intval($_GET['length']);
        }
        //设置验证码字符库
        $code_set = "";
        if (isset($_GET['charset'])) {
            $code_set = trim($_GET['charset']);
        }
        $use_noise = 0;
        if (isset($_GET['use_noise'])) {
            $use_noise = intval($_GET['use_noise']);
        }
        $use_curve = 1;
        if (isset($_GET['use_curve'])) {
            $use_curve = intval($_GET['use_curve']);
        }
        $font_size = 25;
        if (isset($_GET['font_size']) && intval($_GET['font_size'])) {
            $font_size = intval($_GET['font_size']);
        }
        $width = 0;
        if (isset($_GET['width']) && intval($_GET['width'])) {
            $width = intval($_GET['width']);
        }
        $height = 0;
        if (isset($_GET['height']) && intval($_GET['height'])) {
            $height = intval($_GET['height']);
        }
        /* $background="";
        if (isset($_GET['background']) && trim(urldecode($_GET['background'])) && preg_match('/(^#[a-z0-9]{6}$)/im', trim(urldecode($_GET['background'])))){
            $background=trim(urldecode($_GET['background']));
        } */
        //TODO ADD Backgroud param!
        $config = array(
            'codeSet'  => !empty($code_set) ? $code_set : "2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY",
            // 验证码字符集合
            'expire'   => 1800,            // 验证码过期时间（s）
            'useImgBg' => false,           // 使用背景图片
            'fontSize' => !empty($font_size) ? $font_size : 25,              // 验证码字体大小(px)
            'useCurve' => $use_curve === 0 ? false : true,           // 是否画混淆曲线
            'useNoise' => $use_noise === 0 ? false : true,            // 是否添加杂点
            'imageH'   => $height,               // 验证码图片高度
            'imageW'   => $width,               // 验证码图片宽度
            'length'   => !empty($length) ? $length : 4,               // 验证码位数
            'bg'       => array(243, 251, 254),  // 背景颜色
            'reset'    => true,           // 验证成功后是否重置
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    // 获取玩过的游戏
    public function getPlayedGame() {
        $items = array();
        if (isset($_SESSION['user']) && ($_SESSION['user']['id'])) {
            $mem_id = $_SESSION['user']['id'];
            $obj = new \Huosdk\Module\PlayedGame\Base();
            $items = $obj->getList($mem_id);
        }
        $this->response($items);
    }

    //获取精品 公益服广告图
    public function get_abs() {
        $type = I('type');
        $condition = [];
        $pic_url = '';
        if ($type == 1) {//精品
            $condition['cat_idname'] = 'delicate_game';
            $pic_url = './static/img/ads/1.jpg';
            $t_id = 0;
        } else {//公益
            $condition['cat_idname'] = 'welfare_game';
            $pic_url = './static/img/ads/2.jpg';
            $t_id = 0;
        }
        $record = M('slide_cat')->where($condition)->find();
        if (!empty($record)) {
            $pic_record = M('slide')->where(['slide_cid' => $record['cid']])->find();
            if (!empty($pic_record)) {
                $pic_url = $pic_record['slide_pic'];
                $t_id = $pic_record['target_id'];
            }
        }
        $data = [
            'pic_url'   => $pic_url,
            'target_id' => $t_id
        ];
        $this->response($data);
    }
    
    //获取底部applogo跟下载地址
    public function getAppInfo() {
        $_game_model = M("game");
        $condition = [
            'g.id' => C('APP_APPID'), //C('APP_APPID')
        ];
        $_field = "g.icon logo,gv.packageurl";
        $info = $_game_model
            ->alias("g")
            ->join('left join '.C('DB_PREFIX').'game_version gv on gv.app_id=g.id')
            ->field($_field)
            ->where($condition)
            ->find();
        if (empty($info)) {
            $info['logo'] = './static/img/asset/logo_single.png';
            $info['packageurl'] = '';
        }
        $this->response($info);
    }
}
