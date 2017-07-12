<?php
/**
 * 游戏充值
 *
 * @ou
 * @2016-4-7
 */
namespace Web\Controller;

use Web\Controller\PayController;

class PayecoController extends PayController {
    private $MerchantNo, $privatKey, $MerchantName;

    function _initialize() {
        parent::_initialize();
        // 包含配置文件
        $conffile = SITE_PATH."conf/pay/payeco/config.php";
        if (file_exists($conffile)) {
            $payecoconf = include $conffile;
        } else {
            $payecoconf = array();
        }
        $this->MerchantNo = $payecoconf['MerchantNo']; // 商户号
        $this->privatKey = $payecoconf['privatKey']; // 密钥
        $this->MerchantName = $payecoconf['MerchantName']; //公司名
    }

    public function payeco() {
        $data = $this->_insertpay(); //插入订单记录到pay表中和CP支付信息表pay_cpinfo
        $Amount = $data['money'];   //金额
        $MerchantOrderNo = $data['order_id'];//商户系统订单号
        //发送请求
        $privatKey = $this->privatKey;//商户私钥
        //$url = 'http://58.248.38.252:9080/DnaOnline/servlet/DnaPayB2C';//请求提交地址
        $url = 'https://ebank.payeco.com/services/DnaPayB2C';//请求提交地址
        $Version = '2.0.0'; //接口版本
        //$OrderFrom = '12'; //订单来源
        $OrderFrom = '02'; //订单来源
        $Currency = 'CNY'; //币种
        $Language = '00'; //语言
        $SynAddress = WEBSITE.U('Web/Payeco/payReturn'); //同步返回报文地址
        $AsynAddress = WEBSITE.U('Web/Payeco/econotify'); //异步返回报文地址
        $OrderType = '00'; //订单类型 00=即时支付 01-非即时支付
        $Description = '商品描述'; //商品描述
        $remark = '备注'; //备注
        $MerchantName = $this->MerchantName; //姓名
        $ProcCode = '0200'; //消息类型
        $AccountNo = ''; //银行卡号
        $ProcessCode = '190011';  //处理码
        $TransDatetime = date("YmdHis", time()); //传输日期时间
        $AcqSsn = '123456'; //系统跟踪号
        $TransData = ''; //其他业务资料，用“|”分隔，例如：银行代码
        $TerminalNo = '02028828'; //终端号
        $MerchantNo = $this->MerchantNo; //商户号
        $Reference = ''; //系统参考号  原值返回
        //组建令牌
        $macClear = '';
        $macClear .= !empty($ProcCode) ? $ProcCode.' ' : '';
        $macClear .= !empty($AccountNo) ? $AccountNo.' ' : '';
        $macClear .= !empty($ProcessCode) ? $ProcessCode.' ' : '';
        $macClear .= !empty($Amount) ? $Amount.' ' : '';
        $macClear .= !empty($TransDatetime) ? $TransDatetime.' ' : '';
        $macClear .= !empty($AcqSsn) ? $AcqSsn.' ' : '';
        $macClear .= !empty($OrderNo) ? $OrderNo.' ' : '';
        $macClear .= !empty($TransData) ? $TransData.' ' : '';
        $macClear .= !empty($Reference) ? $Reference.' ' : '';
        $macClear .= !empty($TerminalNo) ? $TerminalNo.' ' : '';
        $macClear .= !empty($MerchantNo) ? $MerchantNo.' ' : '';
        $macClear .= !empty($MerchantOrderNo) ? $MerchantOrderNo.' ' : '';
        $macClear = trim($macClear);
        $mac = md5(strtoupper($macClear)." ".$privatKey);
        $mac = strtoupper($mac);
        //组建xml
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<x:NetworkRequest xmlns:x="http://www.payeco.com" xmlns:xsi="http://www.w3.org">';
        $xml .= '<Version>'.$Version.'</Version>';
        $xml .= '<ProcCode>'.$ProcCode.'</ProcCode>';
        $xml .= '<ProcessCode>'.$ProcessCode.'</ProcessCode>';
        $xml .= '<AccountNo>'.$AccountNo.'</AccountNo>';
        $xml .= '<AccountType></AccountType>';
        $xml .= '<MobileNo></MobileNo>';
        $xml .= '<Amount>'.$Amount.'</Amount>';
        $xml .= '<Currency>CNY</Currency>';
        $xml .= '<SynAddress>'.$SynAddress.'</SynAddress>';
        $xml .= '<AsynAddress>'.$AsynAddress.'</AsynAddress>';
        $xml .= '<Remark>'.$remark.'</Remark>';
        $xml .= '<TerminalNo>'.$TerminalNo.'</TerminalNo>';
        $xml .= '<MerchantNo>'.$MerchantNo.'</MerchantNo>';
        $xml .= '<MerchantOrderNo>'.$MerchantOrderNo.'</MerchantOrderNo>';
        $xml .= '<OrderNo></OrderNo>';
        $xml .= '<OrderFrom>'.$OrderFrom.'</OrderFrom>';
        $xml .= '<Language>'.$Language.'</Language>';
        $xml .= '<Description>'.$Description.'</Description>';
        $xml .= '<OrderType>'.$OrderType.'</OrderType>';
        $xml .= '<AcqSsn>'.$AcqSsn.'</AcqSsn>';
        $xml .= '<Reference>'.$Reference.'</Reference>';
        $xml .= '<TransDatetime>'.$TransDatetime.'</TransDatetime>';
        $xml .= '<MerchantName>'.$MerchantName.'</MerchantName>';
        $xml .= '<TransData></TransData>';
        $xml .= '<IDCardName></IDCardName>';
        $xml .= '<IDCardNo></IDCardNo>';
        $xml .= '<BankAddress></BankAddress>';
        $xml .= '<IDCardType></IDCardType>';
        $xml .= '<BeneficiaryName></BeneficiaryName>';
        $xml .= '<BeneficiaryMobileNo></BeneficiaryMobileNo>';
        $xml .= '<DeliveryAddress></DeliveryAddress>';
        $xml .= '<IpAddress></IpAddress>';
        $xml .= '<Location></Location>';
        $xml .= '<UserFlag></UserFlag>';
        $xml .= '<MAC>'.$mac.'</MAC>';
        $xml .= '</x:NetworkRequest>';
        $request_text = urlencode(base64_encode($xml));//最终请求格式
        //建立请求
        $para_temp = array('request_text' => $request_text);
        $sHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $sHtml .= "<form id='paysubmit' name='paysubmit' action='".$url."' method='post'>";
        foreach ($para_temp as $key => $value) {
            $sHtml .= "<input type='hidden' name='".$key."' value='".$value."'/>";
        }
        $sHtml = $sHtml."<input type='submit' value='' style='display:none;'></form>";
        $sHtml = $sHtml."loading...";
        $sHtml = $sHtml."<script>document.forms['paysubmit'].submit();</script>";
        echo $sHtml;
    }

    //易联支付回调
    public function econotify() {
        $privatKey = $this->privatKey;//商户私钥
        $paynotify = $_REQUEST['response_text'];
        $xml = base64_decode($paynotify);
        $ret = simplexml_load_string($xml);
        //组建令牌
        $macClear = '';
        $macClear .= !empty($ret->ProcCode) ? $ret->ProcCode.' ' : '';
        $macClear .= !empty($ret->AccountNo) ? $ret->AccountNo.' ' : '';
        $macClear .= !empty($ret->ProcessCode) ? $ret->ProcessCode.' ' : '';
        $macClear .= !empty($ret->Amount) ? $ret->Amount.' ' : '';
        $macClear .= !empty($ret->TransDatetime) ? $ret->TransDatetime.' ' : '';
        $macClear .= !empty($ret->AcqSsn) ? $ret->AcqSsn.' ' : '';
        $macClear .= !empty($ret->OrderNo) ? $ret->OrderNo.' ' : '';
        $macClear .= !empty($ret->TransData) ? $ret->TransData.' ' : '';
        $macClear .= !empty($ret->Reference) ? $ret->Reference.' ' : '';
        $macClear .= !empty($ret->RespCode) ? $ret->RespCode.' ' : '';
        $macClear .= !empty($ret->TerminalNo) ? $ret->TerminalNo.' ' : '';
        $macClear .= !empty($ret->MerchantNo) ? $ret->MerchantNo.' ' : '';
        $macClear .= !empty($ret->MerchantOrderNo) ? $ret->MerchantOrderNo.' ' : '';
        $macClear .= !empty($ret->OrderState) ? $ret->OrderState.' ' : '';
        $macClear = trim($macClear);
        $mac = md5(strtoupper($macClear)." ".$privatKey);  //mac
        $mac = strtoupper($mac);  //mac
        //验证令牌
        if ($ret->MAC != $mac) {//验证失败
            $str = "验证失败";
            echo "<script type='text/javascript' >";
            echo "alert('".$str."');";
            echo "window.close();";
            echo "</script>";
            exit;
        }
        //验证交易
        if ($ret->OrderState != '02' || $ret->RespCode != '0000') {//交易失败
            $str = "交易失败";
            echo "<script type='text/javascript' >";
            echo "alert('".$str."');";
            echo "window.close();";
            echo "</script>";
            exit;
        }
        //调用后续逻辑
        //如果要使用返回的对象作为其他方法的参数 需将其转换为字符串类型 如：$Amount = (string) $ret->Amount;
        $amount = $ret->Amount;
        $MerchantOrderNo = $ret->MerchantOrderNo;
        //将订单状态写入数据表中
        $this->paypost($MerchantOrderNo, $amount);
        echo '0000'; //如果交易完成 则返回'0000'通知系统
        exit;
    }

    /**
     * 易联支付通知页面
     */
    function payReturn() {
        $privatKey = $this->privatKey;//商户私钥
        $paynotify = $_REQUEST['response_text'];
        $xml = base64_decode($paynotify);
        $ret = simplexml_load_string($xml);
        //组建令牌
        $macClear = '';
        $macClear .= !empty($ret->ProcCode) ? $ret->ProcCode.' ' : '';
        $macClear .= !empty($ret->AccountNo) ? $ret->AccountNo.' ' : '';
        $macClear .= !empty($ret->ProcessCode) ? $ret->ProcessCode.' ' : '';
        $macClear .= !empty($ret->Amount) ? $ret->Amount.' ' : '';
        $macClear .= !empty($ret->TransDatetime) ? $ret->TransDatetime.' ' : '';
        $macClear .= !empty($ret->AcqSsn) ? $ret->AcqSsn.' ' : '';
        $macClear .= !empty($ret->OrderNo) ? $ret->OrderNo.' ' : '';
        $macClear .= !empty($ret->TransData) ? $ret->TransData.' ' : '';
        $macClear .= !empty($ret->Reference) ? $ret->Reference.' ' : '';
        $macClear .= !empty($ret->RespCode) ? $ret->RespCode.' ' : '';
        $macClear .= !empty($ret->TerminalNo) ? $ret->TerminalNo.' ' : '';
        $macClear .= !empty($ret->MerchantNo) ? $ret->MerchantNo.' ' : '';
        $macClear .= !empty($ret->MerchantOrderNo) ? $ret->MerchantOrderNo.' ' : '';
        $macClear .= !empty($ret->OrderState) ? $ret->OrderState.' ' : '';
        $macClear = trim($macClear);
        $mac = md5(strtoupper($macClear)." ".$privatKey);  //mac
        $mac = strtoupper($mac);  //mac
        $html = "<!DOCTYPE HTML>";
        $html .= "<html>";
        $html .= "<head>";
        $html .= "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        $html .= "<link href='public/pay/css/toper.css' rel='stylesheet' type='text/css'>";
        $html .= "<style type='text/css'>";
        $html .= "	.cz_ba p{ line-height:22px;}";
        $html .= "	.cz_b{width:600px; margin:0 auto; padding-top:50px;}";
        $html .= "	.cz_ba{ background:url(../images/cg.jpg) no-repeat; padding-left:80px;}";
        $html .= "	.mna{padding-top:10px;}";
        $html .= "	.mna a{ color:#006699; padding:0 6px;}";
        $html .= "	.cz_ann{ height:30px; padding:0 10px;}";
        $html .= "</style>";
        //验证令牌
        if ($ret->MAC != $mac || $ret->OrderState != '02' || $ret->RespCode != '0000') {//验证失败
            $html .= "<div class='cz_b'>";
            $html .= "<div class='cz_ba'>";
            $html .= "<p style='font-size:16px; font-weight:bold;'>充值失败，请重试！</p>";
            $html .= "<p style='margin-top:20px;'><a href=".$paysite
                     ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
            $html .= "</div>";
            $html .= "</div>";
        } else {
            $html .= "<div class='cz_b'>";
            $html .= "<div class='cz_ba'>";
            $html .= "<p style='font-size:16px; font-weight:bold;'>恭喜您，充值成功！</p>";
            $html .= "<p style='border-bottom:1px solid #e0e0e0; padding-bottom:10px; line-height:20px;'>如果查询未到账可能是运营商网络问题而导致暂时充值不成功，请联系客服。</p>";
            $html .= "<p class='mna'>订单号：".$ret->MerchantOrderNo."</p>";
            $html .= "<p>充值金额：".$ret->Amount."</p>";
            $html .= "<p style='margin-top:20px;'><a href=".$paysite
                     ."><input type='button' value='返回充值中心' class='cz_ann'/></a></p>";
            $html .= "</div>";
            $html .= "</div>";
        }
        $html .= "<title>易联充值</title>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
    }
}
