<?php

/**
 * Sub.php UTF-8
 * 分包类
 *
 * @date    : 2016年12月8日下午10:09:43
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 * @modified: 2016年12月8日下午10:09:43
 */
class Sub {
    private $rootpath; /* 源路径 */
    private $srcpath; /* 源路径 */
    private $destpath; /* 目标路径 */
    private $byteSize;
    private $system; /* 系统 ios:ios and:android */
    private $agentgame;
    private $pinyin;
    private $ip;
    private $sign;
    private $prj_id;
    private $downurl;
    private $iparr;
    private $imageurl;  /* logo路径 */
    private $redicturl; /* webclip跳转路径 */
    private $oldgame_arr        = array();

    public function __construct($url = '', $prj_id = '', $downurl = '', $iparr = array()) {
        $this->prj_id = $prj_id;
        $this->downurl = $downurl;
        $this->iparr = $iparr;
        $_urldata = file_get_contents("php://input");
        $_urldata = get_object_vars(json_decode($_urldata));
        $this->pinyin = base64_decode($_urldata['p']);
        $this->agentgame = base64_decode($_urldata['a']);
        $this->sign = base64_decode($_urldata['o']);
        $this->ip = $this->getIp();
        $this->checkPackage($this->pinyin);
        $this->rootpath = $url;
        $this->imageurl = isset($_urldata['image']) ? $_urldata['image'] : '';
        $this->redicturl = isset($_urldata['rurl']) ? $_urldata['rurl'] : '';
        if (empty($url)) {
            $this->rootpath = dirname(__DIR__).'/sdkgame/';
        }
        $_pinyinarr = explode('/', $this->pinyin);
        if ('ios' == $this->system) {
            $this->srcpath = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$_pinyinarr[0].'.ipa';
            $this->destpath = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$this->agentgame.'.ipa';
        } else {
            $this->srcpath = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$_pinyinarr[0].'.apk';
            $this->destpath = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$this->agentgame.'.apk';
        }
    }

    function subPack() {
        /* 1 校验来源合法性 返回 -6 */
        if (false === $this->checkIp()) {
            return -6;
        }
        /* 生成mobileconfig文件 */
        if (!empty($this->imageurl)) {
            return $this->createWebclip();
        }
        /* 2 检查参数是否合法 返回 -3 */
        if (false === $this->checkParam()) {
            return -3;
        }
        /* 3 检查签名是否合法 返回-4 */
        if (false === $this->checkToken()) {
            return -4;
        }
        /* 4 检查母包是否存在 返回-5 若是检查母包 */
        $_rs = $this->checkSrc();
        if (0 !== $_rs) {
            return $_rs;
        }
        /* 5 检查目标包是否存在 若目标包为母包 则返回目标包信息 */
        $_rs = $this->checkDest();
        if (0 !== $_rs) {
            return $_rs;
        }
        /* 6 若为android 则创建目标包 */
        $_rs = $this->createDest();
        return $_rs;
    }

    /* 1 校验来源合法性 返回 -6 */
    function checkIp() {
        $_rdata = false;
        if (in_array($this->ip, $this->iparr)) {
            $_rdata = true;
        }
        return $_rdata;
    }

    /* 2 检查参数是否合法 返回 -3 */
    function checkParam() {
        if (empty($this->pinyin) || empty($this->agentgame)) {
            return false;
        }
        return true;
    }

    /* 3 检查签名是否合法 返回-4 */
    function checkToken() {
        $_sign = md5(md5($this->pinyin.$this->agentgame).'resub');
        if ($_sign != $this->sign) {
            return false;
        }
        return true;
    }

    /* 4 检查母包是否存在 */
    function checkSrc() {
        if (!file_exists($this->srcpath)) {
            if (!file_exists($this->rootpath.$this->pinyin)) {
                mkdir($this->rootpath.$this->pinyin, 0777, true);
            }
            if ($this->srcpath == $this->destpath) {
                return 1;
            }
            return -5; // 游戏原包不存在
        }
        return 0;
    }

    /* 5 检查目标包是否存在 若目标包为母包 则返回目标包信息 */
    function checkDest() {
        if (file_exists($this->destpath)) {
            if ($this->srcpath == $this->destpath) {
                $_data = $this->getPackageinfo($this->srcpath);
                return $_data;
            }
            return 2; // 已分包
        }
        return 0;
    }

    /* 6 分包 */
    function createDest() {
        if ('ios' == $this->system) {
            /* ios包不需要复制包 */
            return 1;
        }
        if (!copy($this->srcpath, $this->destpath)) {
            return -1;
        }
        $_rs = $this->writeTodest();
        return $_rs;
    }

    function writeTodest() {
        $_arr = explode('_', $this->agentgame);
        $_cnt = count($_arr);
        if ($_cnt >= 2) {
            if (in_array($_arr[$_cnt - 2], $this->oldgame_arr)) {
                return $this->oldSub();
            }
            $huomark = md5("p".$this->prj_id."g".$_arr[$_cnt - 2]."a".$_arr[$_cnt - 1]);
        } else {
            $huomark = md5("p".$this->prj_id.$this->agentgame);
        }
        $en_ag = $this->rsa_pri_encrypt($this->agentgame);
        $channelname = "META-INF/gamechannel";
        $huosdk = "META-INF/huosdk_".$huomark;
        $zip = new ZipArchive();
        if ($zip->open($this->destpath) === true) {
            $_rs = $zip->addFromString(
                $channelname, json_encode(
                                array(
                                    'agentgame' => $en_ag
                                )
                            )
            );
            $zip->addFromString(
                $huosdk, json_encode(
                           array(
                               'agentgame' => $huomark
                           )
                       )
            );
            if (false === $_rs) {
                unlink($this->destpath);
                return -2;
            }
            $zip->close();
            $_rs = 1;
        } else {
            $_rs = -2;
        }
        return $_rs;
    }

    function rsa_pri_encrypt($prestr) {
        $private_key_path = dirname(dirname(__DIR__))."/key/rsa_private_key.pem";
        $private_key = file_get_contents($private_key_path);
        $private_key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("-----END RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("\n", "", $private_key);
        $private_key = "-----BEGIN RSA PRIVATE KEY-----".PHP_EOL.wordwrap($private_key, 64, "\n", true).PHP_EOL
                       ."-----END RSA PRIVATE KEY-----";
        $pkeyid = openssl_get_privatekey($private_key);
        if ($pkeyid) {
            openssl_private_encrypt($prestr, $encodestr, $pkeyid);
        } else {
            return false;
        }
        openssl_free_key($pkeyid);
        $encodestr = base64_encode($encodestr);
        return $encodestr;
    }

    function checkPackage($pinyin) {
        if (empty($pinyin)) {
            $_pinyin = $this->pinyin;
        } else {
            $_pinyin = $pinyin;
        }
        /* pinyin ios_demoios_101/5 */
        $_parr = explode('/', $_pinyin);
        $_iarr = explode('_', $_parr[0]);
        $this->system = 'and';
        $_cnt = count($_iarr);
        if ($_cnt >= 2) {
            if ('ios' == strtolower(substr($_iarr[$_cnt - 2], -3))) {
                $this->system = 'ios';
            }
        }
    }

    public function getIp($type = 0, $adv = false) {
        $type = $type ? 1 : 0;
        $ip = null;
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long
            ? array(
                $ip,
                $long
            )
            : array(
                '0.0.0.0',
                0
            );
        return $ip[$type];
    }

    /* 获取包体信息 */
    function getPackageinfo($file) {
        if ('ios' == $this->system) {
            $_rdata = $this->getIpainfo($file);
        } else {
            $_rdata = $this->getApkinfo($file);
        }
        return json_encode($_rdata);
    }

    function getApkinfo($file) {
        include 'ApkParser.php';
        include 'FilesizeHelper.php';
        $data = array();
        $appObj = new \ApkParser();
        $fz = new FilesizeHelper();
        $res = $appObj->open($file);
        $data['appname'] = $appObj->getAppName(); // 应用名称
        $data['pakagename'] = $appObj->getPackage(); // 应用包名
        $data['vername'] = $appObj->getVersionName(); // 版本名称
        $data['verid'] = $appObj->getVersionCode(); // 版本代码
        $data['size'] = $fz->getFileSize($file, false);
        return $data;
    }

    function getIpainfo($file) {
        include 'IpaParser.php';
        include 'FilesizeHelper.php';
        /* 生成 XML文件 */
        $_rdata['size'] = '0K';
        $appObj = new IpaParser();
        $res = $appObj->parse($file);
        $data['appname'] = $appObj->getAppName();
        $data['pakagename'] = $appObj->getPackage();
        $data['vername'] = $appObj->getVersion();
        $data['verid'] = '1'; // 版本代码
        $fz = new FilesizeHelper();
        $data['size'] = $fz->getFileSize($file, false);
        $downurl = $this->downurl.$this->pinyin.'/'.$this->agentgame.'.ipa';
        $path = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$this->agentgame.'.plist';
        $appObj->createPlist($path, $downurl, $data);
        return $data;
    }

    function createWebclip() {
        if (empty($this->imageurl) || empty($this->redicturl)) {
            return -1;
        }
        include 'IpaParser.php';
        /* 生成 XML文件 */
        $_rdata['size'] = '0K';
        $appObj = new IpaParser();
        $res = $appObj->parse($this->srcpath);
        $data['appname'] = $appObj->getAppName();
        $data['pakagename'] = $appObj->getPackage();
        $downurl = $this->downurl.$this->pinyin.'/'.$this->agentgame.'.ipa';
//        $path = $this->rootpath.$this->pinyin.DIRECTORY_SEPARATOR.$this->agentgame.'.mobileconfig';
        $path = $this->rootpath.'ios.mobileconfig';
        $appObj->createWebclip($path, $this->imageurl, $this->redicturl, $data);

        return 1;
    }

    function oldSub() {
        $agentgame = $this->agentgame;
        $newfile = $this->destpath;

        $channelname = "META-INF/gamechannel";
        $zip = new ZipArchive;
        if ($zip->open($newfile) === true) {
            $zip->addFromString($channelname, json_encode(array('agentgame' => $agentgame)));
            $zip->close();
            $return = 1;
        } else {
            $return = -2;
        }

        return $return;
    }
}