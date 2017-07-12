<?php
/**
 * Rsa.php UTF-8
 * 火速类库 RSA 加密与解密
 *
 * @date     : 2016年11月9日下午4:51:51
 *
 * @license  这不是一个自由软件，未经授权不许任何使用和传播。
 * @author   : wuyonghong <wyh@huosdk.com>
 * @version  : HUOSDK 7.0
 * @modified : 2016年11月9日下午4:51:51
 */
namespace huosdk\common;

use think\Log;

class Rsa {
    private $pub_key = null;
    private $pri_key = null;

    /**
     * 自定义错误处理
     *
     * @param msg 输出的文件
     */
    private function _error($msg, $level = 'error') {
        $_info = 'RSA Error:'.$msg;
        Log::record($_info, 'error');
    }

    /**
     * Rsa constructor.
     *
     * @param string $public_key_file  公钥文件（验签和加密时传入）
     * @param string $private_key_file 私钥文件（签名和解密时传入）
     */
    public function __construct($public_key_file = '', $private_key_file = '') {
        if (empty($public_key_file)) {
            $public_key_file = CONF_PATH.'extra/key/rsa_private_key.pem';
        }
        $this->_getPublicKey($public_key_file);
        if (empty($private_key_file)) {
            $private_key_file = CONF_PATH."extra/key/rsa_private_key.pem";
        }
        $this->_getPrivateKey($private_key_file);
    }

    /**
     *
     * 生成钥匙对
     *
     * @param        $path * @param $path
     * @param string $name 文件创建名称
     *
     * @return bool 成功返回true 失败返回 false
     */
    public function createKey($path, $name = '') {
        if (empty($path)) {
            $this->_error("create error,must set the keys save path");

            return false;
        }
        if (!is_dir($path)) {
            mkdir($path);
        }
        $_pri_key = '';
        $config = array("private_key_bits" => 1024
        );
        $_ri = openssl_pkey_new($config);
        $_pri_path = $path.DIRECTORY_SEPARATOR.$name.'rsa_private_key.pem';
        $this->_backOld($_pri_path);
        $_rs = openssl_pkey_export_to_file($_ri, $_pri_path);
        if (!$_rs) {
            $this->_error("openssl_pkey_export_to_file error!");

            return false;
        }
        $_pub_path = $path.DIRECTORY_SEPARATOR.$name.'rsa_public_key.pem';
        $this->_backOld($_pub_path);
        $_pgd = openssl_pkey_get_details($_ri);
        $_pub_key = $_pgd['key'];
        if (empty($_pub_key)) {
            $this->_error("openssl_pkey_get_public error!");

            return false;
        }
        $_pub_key_path = $path.DIRECTORY_SEPARATOR.$name.'rsa_public_key.pem';
        $_rs = $this->_writeKeytoFile($_pub_key_path, $_pub_key);
        if (false == $_rs) {
            $this->_error('create private key error!');

            return false;
        }

        return true;
    }

    private function _backOld($file_path) {
        if (file_exists($file_path)) {
            $_old_path = $file_path.'.'.time().'.back';
            $_rs = copy($name, $_old_path);
            if (!$_rs) {
                return false;
            }
        }

        return true;
    }

    private function _writeKeytoFile($path, $data) {
        if (file_exists($path)) {
            // 备份原有key
            $_old_path = $path.'.'.time().'.back';
            $_rs = copy($path, $_old_path);
            if (!$_rs) {
                return false;
            }
        }

        return true;
    }

    /**
     * 读取文件内容
     *
     * @param $file RSA公钥或私钥文件路径
     *
     * @return string 编码后的内容 失败返回false.
     */
    private function _readFile($file) {
        $_ret = false;
        if (!file_exists($file)) {
            $this->_error("The file {$file} is not exists");
        } else {
            $_ret = file_get_contents($file);
        }

        return $_ret;
    }

    /**
     * 编码数据
     *
     * @param $data 待编码数据
     * @param $code 编码类型
     *
     * @return string 编码后的内容 失败返回false.
     */
    private function _encode($data, $code) {
        switch (strtolower($code)) {
            case 'base64' :
                $data = base64_encode(''.$data);
                break;
            case 'hex' :
                $data = bin2hex($data);
                break;
            case 'bin' :
            default :
        }

        return $data;
    }

    /**
     * 解码数据
     *
     * @param $data 待解码数据
     * @param $code 解码类型
     *
     * @return string 解码后的内容 失败返回false.
     */
    private function _decode($data, $code) {
        switch (strtolower($code)) {
            case 'base64' :
                $data = base64_decode($data);
                break;
            case 'hex' :
                $data = $this->_hex2bin($data);
                break;
            case 'bin' :
            default :
        }

        return $data;
    }

    /**
     * 获取公钥证书资源
     *
     * @param $public_key_path string 公钥文件路径
     */
    private function _getPublicKey($public_key_path) {
        $_key_content = $this->_readFile($public_key_path);
        // 以下为了初始化公钥，保证公钥不管是带格式还是不带格式都可以通过验证。
        $_key_content = str_replace("-----BEGIN PUBLIC KEY-----", "", $_key_content);
        $_key_content = str_replace("-----END PUBLIC KEY-----", "", $_key_content);
        $_key_content = str_replace("\r\n", "", $_key_content);
        $_key_content = str_replace("\n", "", $_key_content);
        $_key_content = '-----BEGIN PUBLIC KEY-----'.PHP_EOL.wordwrap($_key_content, 64, "\n", true).PHP_EOL
                        .'-----END PUBLIC KEY-----';
        if ($_key_content) {
            $this->pub_key = openssl_get_publickey($_key_content);
        }
    }

    /**
     * 获取私钥证书资源
     *
     * @param $private_key_path string 公钥文件路径
     */
    private function _getPrivateKey($private_key_path) {
        $_key_content = $this->_readFile($private_key_path);
        // 以下为了初始化公钥，保证私钥不管是带格式还是不带格式都可以通过验证。
        $_key_content = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $_key_content);
        $_key_content = str_replace("-----END RSA PRIVATE KEY-----", "", $_key_content);
        $_key_content = str_replace("\n", "", $_key_content);
        $_key_content = "-----BEGIN RSA PRIVATE KEY-----".PHP_EOL.wordwrap($_key_content, 64, "\n", true).PHP_EOL
                        ."-----END RSA PRIVATE KEY-----";
        if ($_key_content) {
            $this->pri_key = openssl_get_privatekey($_key_content);
        }
    }

    /**
     * 私钥生成签名
     *
     * @param $data string 待签名字符串
     * @param $code string 签名编码（base64/hex/bin）
     *
     * @return 签名值
     */
    public function sign($data, $code = 'base64') {
        $_ret = false;
        if ($this->pri_key) {
            $_signature = '';
            $_ssl_sign_flag = openssl_sign($data, $_signature, $this->pri_key);
            if (false != $_ssl_sign_flag) {
                $_ret = $this->_encode($_signature, $code);
            }
        }

        return $_ret;
    }

    /**
     * 公钥验证签名
     *
     * @param $data 需要签名的字符串
     * @param $sign 签名结果
     * @param $code 签名编码（base64/hex/bin）
     *
     * @return bool
     */
    public function verify($data, $sign, $code = 'base64') {
        $_ret = false;
        $sign = $this->_decode($sign, $code);
        if (false !== $sign && $this->pub_key) {
            $_verify_sign_flag = openssl_verify($data, $sign, $this->pub_key);
            switch ($_verify_sign_flag) {
                case 1 :
                    $_ret = true;
                    break;
                case 0 :
                case -1 :
                default :
                    $_ret = false;
            }
        }

        return $_ret;
    }

    /**
     * rsa公钥加密
     *
     * @param $data    待加密字符串
     * @param $code    密文编码（base64/hex/bin）
     * @param $padding 填充方式 目前仅支持OPENSSL_PKCS1_PADDING
     *
     * @return string 密文 失败则返回 false
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING) {
        $_ret = false;
        $_check_pad_flag = $this->_checkPadding($padding, 'encode');
        if (!$_check_pad_flag) {
            $this->_error('encrypt padding error:_checkPadding');
        } else {
            $_return = '';
            for ($_i = 0; $_i < strlen($data) / 128; $_i++) {
                $subdata = substr($data, $_i * 128, 128);
                $_encrypt_flag = openssl_public_encrypt($subdata, $_result, $this->pub_key, $padding);
                if (!$_encrypt_flag) {
                    $this->_error('rsa public openssl_public_encrypt error');
                    $_return = '';
                    break;
                }
                $_return .= $_result;
            }
            $_ret = $this->_encode($_return, $code);
        }

        return $_ret;
    }

    /**
     * 私钥解密
     *
     * @param $data    string 密文
     * @param $code    string 密文编码（base64/hex/bin）
     * @param $padding int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param $rev     bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the
     *                 block）
     *
     * @return string 明文 错误返回空串 OR false
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false) {
        $_ret = false;
        $_check_pad_flag = $this->_checkPadding($padding, 'decode');
        if (!$_check_pad_flag) {
            $this->_error('encrypt padding error:_checkPadding');

            return $_ret;
        }
        $data = $this->_decode($data, $code);
        $_result = '';
        if (!empty($data) && $this->pri_key) {
            for ($_i = 0; $_i < strlen($data) / 128; $_i++) {
                $subdata = substr($data, $_i * 128, 128);
                $_decrypt_flag = openssl_private_decrypt($subdata, $decrypt, $this->pri_key);
                if (false == $_decrypt_flag) {
                    $_result = '';
                    break;
                }
                $_result .= $decrypt;
            }
            $_ret = $rev ? rtrim(strrev($_result), "\0") : ''.$_result;
        }

        return $_ret;
    }

    /**
     * 检测填充类型
     * 加密只支持PKCS1_PADDING
     * 解密支持PKCS1_PADDING和NO_PADDING
     *
     * @param $padding int 填充模式
     * @param $type    string 加密en/解密de
     *
     * @return bool
     */
    private function _checkPadding($padding, $type) {
        $_ret = false;
        if ('encode' == $type) {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING :
                    $_ret = true;
                    break;
                default :
                    $_ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING :
                case OPENSSL_NO_PADDING :
                    $_ret = true;
                    break;
                default :
                    $_ret = false;
            }
        }

        return $_ret;
    }

    /**
     * 转换十六进制字符串为二进制字符串
     *
     * @param $hex 十六进制字符串
     *
     * @return 二进制字符串
     */
    private function _hex2bin($hex = false) {
        $_ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;

        return $_ret;
    }

    public function __destruct() {
        if ($this->pri_key) {
            @openssl_free_key($this->pri_key);
        }
        if ($this->pub_key) {
            @openssl_free_key($this->pub_key);
        }
    }
}