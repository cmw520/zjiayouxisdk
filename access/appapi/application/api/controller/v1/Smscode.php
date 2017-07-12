<?php
/**
 * Smscode.php UTF-8
 * 短信处理接口
 *
 * @date    : 2016年8月25日下午10:10:59
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace app\api\controller\v1;

use app\common\controller\Base;
use think\Db;

class Smscode extends Base {
    function _initialize() {
        parent::_initialize();
    }

    /*
     * 发送手机验证码
     */
    public function create() {
        $mobile = $this->request->post('mobile/s', '');
        $type = $this->request->post('type/d', 0);
        if ($type < 0) {
            return hs_api_responce('400', '参数错误');
        }
        if (empty($mobile)) {
            return hs_api_responce(400, '请填写手机号');
        }
        $mobile = hs_auth_code($mobile, 'DECODE', $this->client_key);
        if (empty($mobile)) {
            return hs_api_responce(400, '请填写手机号');
        }
        $checkExpressions = "/^[1][34578][0-9]{9}$/";
        if (false == preg_match($checkExpressions, $mobile)) {
            return hs_api_responce('400', '手机号格式错误');
        }
        $limit_time = 120; // 设定超时时间 2min
        $data['mobile'] = $mobile;
        $data['type'] = $type;
        // 数据库中查询是否已发送过验证码
        $sms_model = DB::name('sms_log');
        $map['expaire_time'] = array(
            'gt',
            time()
        );
        $map['mobile'] = $mobile;
        $sms_info = $sms_model->where($map)->find();
        if (!empty($sms_info) && 1 == $sms_info['status']) {
            return hs_api_responce('201', '已发送验证码,请稍后再试');
        }
        $data['smscode'] = hs_random_num(4); // 获取随机码
        $fs_data = $this->getSms($mobile, $type, $data['smscode']);
        if (0 != $fs_data['code']) {
            // 短信发送失败
            return hs_api_responce('500', $fs_data['msg']);
        } else {
            $data['create_time'] = time();
            $data['expaire_time'] = $data['create_time'] + $limit_time;
            $data['status'] = 1;
            $rdata['sessionid'] = $sms_model->insertGetId($data);
            if (false == $rdata['sessionid']) {
                return hs_api_responce('500', "短信发送成功，服务器内部错误");
            }
            // 发送成功
            return hs_api_responce('200', $fs_data['msg'], $rdata);
        }
    }

    /*
     * 验证手机验证码
     */
    public function read() {
        $mobile = $this->request->post('mobile/s', '');
        $smscode = $this->request->post('smscode/s', '');
        $sessionid = $this->request->post('sessionid/d', 0);
        $mobile = hs_auth_code($mobile, 'DECODE', $this->client_key);
        if (empty($mobile)) {
            return hs_api_responce(400, '请填写手机号');
        }
        if (empty($smscode)) {
            return hs_api_responce('400', '未填验证码');
        }
        if (empty($sessionid)) {
            return hs_api_responce('400', '还未发送验证码');
        }
        $checkExpressions = "/^[1][34578][0-9]{9}$/";
        if (false == preg_match($checkExpressions, $mobile)) {
            return hs_api_responce('400', '手机号格式错误');
        }
        $sms_model = Db::name('sms_log');
        $sms_info = $sms_model->where(
            array(
                'id' => $sessionid
            )
        )->find();
        if (empty($sms_info)) {
            return hs_api_responce('422', '还未发送验证码');
        }
        if ($sms_info['expaire_time'] < time()) {
            return hs_api_responce('422', '验证码已过期');
        }
        if ($sms_info['smscode'] != $smscode) {
            return hs_api_responce('422', '验证码错误');
        }
        if ($sms_info['status'] == 2) {
            return hs_api_responce('422', '验证码已失效');
        }
        $sms_info['status'] = 2;
        $rs = $sms_model->update($sms_info);
        if ($rs == false) {
            return hs_api_responce('422', '验证码已失效');
        }
        return true;
//         return hs_api_responce('201', '验证成功');
    }

    // 获取短信验证码
    public function getSms($mobile, $type, $sms_code) {
        return $this->alidayuSend($mobile, $type, $sms_code);
        //return $this->aliyunSend($mobile, $type, $sms_code);
        //return $this->juheSend($mobile, $type, $sms_code);
        //return $this->chuanglanSend($mobile, $type, $sms_code);
        //return $this->qixintongSend($mobile, $type, $sms_code);
    }

    /*
     * 获取短信验证码,阿里大鱼
     * $mobile 电话号码
     */
    public function alidayuSend($mobile, $type, $sms_code) {
        include APP_PATH."../extend/taobao/TopSdk.php";
        include APP_PATH."../extend/taobao/top/TopClient.php";
        include APP_PATH."../extend/taobao/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
        // 获取阿里大鱼配置信息
        if (file_exists(APP_PATH."alidayuconfig.php")) {
            $dayuconfig = include APP_PATH."alidayuconfig.php";
        } else {
            $dayuconfig = array();
        }
        if (empty($dayuconfig)) {
            return false;
        }
        $product = $dayuconfig['PRODUCT'];
        $content = array(
            "code"    => "".$sms_code,
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
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($dayuconfig[$smstemp]);
        $resp = $c->execute($req);
        $resp = (array)$resp;
        if (!empty($resp['result'])) {
            $result = (array)$resp['result'];
            $data['code'] = (int)$result['err_code'];
            $data['msg'] = '发送成功';
        } else {
            $data['code'] = (int)$resp['code'];
            // $data['msg'] = $resp['msg'] . $resp['sub_msg'];
            $data['msg'] = '发送失败';
        }
        return $data;
    }

    /*
     * 获取短信验证码,阿里云短信
     * $mobile 电话号码
     */
    public function aliyunSend($mobile, $type, $sms_code) {
        include APP_PATH."../extend/aliyun/aliyun-php-sdk-core/Config.php";
        include APP_PATH."../extend/aliyun/aliyun-php-sdk-sms/Sms/Request/V20160927/SingleSendSmsRequest.php";
        // 获取阿里云短信配置信息
        if (file_exists(APP_PATH."aliyunconfig.php")) {
            $_config = include APP_PATH."aliyunconfig.php";
        } else {
            $_config = array();
        }
        if (empty($_config)) {
            return false;
        }
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $_config['APPKEY'], $_config['APPSECRET']);
        $client = new \DefaultAcsClient($iClientProfile);
        $request = new \SingleSendSmsRequest();
        $request->setSignName($_config['SIGNNAME']);/*签名名称*/
        $request->setTemplateCode($_config['SMSTEMPAUTH']);/*模板code*/
        $request->setRecNum($mobile);/*目标手机号*/
        $_content = array(
            "code"    => ''.$sms_code,
            "product" => ''.$_config['PRODUCT']
        );
        $request->setParamString(json_encode($_content));/*模板变量，数字一定要转换为字符串*/
        try {
            $response = $client->getAcsResponse($request);
            $_rdata['code'] = '0';
            $_rdata['msg'] = '发送成功';
        } catch (\ClientException  $e) {
            $_rdata['code'] = '500';
            $_rdata['msg'] = '短信发送失败';
        } catch (\ServerException  $e) {
            $_rdata['code'] = '500';
            $_rdata['msg'] = '短信发送失败';
        }
        return $_rdata;
    }

    /*
     * 获取短信验证码,聚合短信
     * $mobile 电话号码
     */
    public function juheSend($mobile, $type, $sms_code) {
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        // 获取聚合配置信息
        if (file_exists(APP_PATH."juheconfig.php")) {
            $juheconfig = include APP_PATH."juheconfig.php";
        } else {
            $juheconfig = array();
        }
        if (empty($juheconfig)) {
            return false;
        }
        $tplValue = urlencode("#code#=".$sms_code);
        $smsConf = array(
            'key'       => $juheconfig['APPKEY'], //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => $juheconfig['TEMPLETID'], //您申请的短信模板ID，根据实际情况修改
            'tpl_value' => $tplValue //您设置的模板变量，根据实际情况修改
        );
        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信
        if ($content) {
            $result = json_decode($content, true);
            $error_code = $result['error_code'];
            if ($error_code == 0) {
                $_rdata['code'] = '0';
                $_rdata['msg'] = '发送成功';
            } else {
                $_rdata['code'] = '500';
                $_rdata['msg'] = "短信发送失败";
            }
        } else {
            $_rdata['code'] = '500';
            $_rdata['msg'] = "请求发送短信失败";
        }
        return $_rdata;
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

    /*
     * 获取短信验证码,创蓝短信
     * $mobile 电话号码
     */
    public function chuanglanSend($mobile, $type, $sms_code) {
        include APP_PATH."../extend/msg//ChuanglanSmsApi.php";
        $_c = new \ChuanglanSmsApi();
        $result = $_c->sendSMS($mobile, $sms_code, true);
        $result = $_c->execResult($result);
        if (0 == $result[1]) {
            $_rdata['code'] = '0';
            $_rdata['msg'] = '发送成功';
        } else {
            $_rdata['code'] = '500';
            $_rdata['msg'] = "短信发送失败";
        }
        return $_rdata;
    }

    /*
    * 获取短信验证码,企信通
    * $mobile 电话号码
    */
    public function qixintongSend($mobile, $type, $sms_code) {
        // 获取配置信息
        if (file_exists(APP_PATH."qixintongconfig.php")) {
            $qixintongconfig = include APP_PATH."qixintongconfig.php";
        } else {
            $qixintongconfig = array();
        }
        if (empty($qixintongconfig)) {
            return false;
        }
        $usr = $qixintongconfig['USR'];  //用户名
        $pw = $qixintongconfig['PW'];  //密码
        $tem = $qixintongconfig['TEM'];  //模板类型
        $mob = $mobile;  //手机号,只发一个号码：13800000001。发多个号码：13800000001,13800000002,...N 。使用半角逗号分隔。
        $mt = "验证码".$sms_code."，您正在注册牛刀手游，请妥善保管验证码";  //要发送的短信内容，特别注意：签名必须设置，网页验证码应用需要加添加【图形识别码】。
        $mt = urlencode($mt);//执行URLencode编码  ，$content = urldecode($content);解码
        $sendstring = "usr=".$usr."&pw=".$pw."&mob=".$mob."&mt=".$mt;
        $url = $qixintongconfig['URL'];
        $sendline = $url."?".$sendstring;
        $result = @file_get_contents($sendline);
        if ($result == "00" || $result == "01") {
            $_rdata['code'] = '0';
            $_rdata['msg'] = '发送成功';
        } else {
            $_rdata['code'] = '500';
            $_rdata['msg'] = "短信发送失败";
        }
        return $_rdata;
    }
}
