<?php
/**
 * 7881配置文件
 */
$path = __DIR__;
return array(
    'partner_id'       => '###',
    'key'              => '',
    'private_key_path' => $path.'/key/rsa_private_key.pem',
    'public_key_path'  => $path.'/key/liebao_public_key.pem',
    'sign_type'        => 'RSA',
    'url'              => 'http://test.api.7881.com/order/noticeCallBack.action',
    //     'url' => 'http://v1.api.7881.com/order/noticeCallBack.action',
);