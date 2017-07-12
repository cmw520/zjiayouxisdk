<?php
/**
 * 初始化接口，检测版本
 */
 
include ('../include/common.inc.php');
$urldata = Response::verify('getptb');
$rdata = array();
if(!empty($urldata['code']) && $urldata['code']<0){
    $db->CloseConnection();
    if (empty($urldata['w'])){
        $urldata['w'] = 0;
    }
    return Response::show($urldata['code'], $rdata, $urldata['msg'], $urldata['w']);
}

//检查余额是否正确
$ptbdata = $db->getPtb($urldata['v']);

if (empty($ptbdata['remain'])){
    $ptbdata['remain'] = 0;
}

$rdata = array(
        'a' => $ptbdata['remain'],  //平台币余额
);
$db->CloseConnection();
return Response::show("1", $rdata, "查询成功", $urldata['w']);

$db->CloseConnection();
return Response::show("-1000", $rdata, "查询失败", $urldata['w']);
 
