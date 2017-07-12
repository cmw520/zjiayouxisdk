<?php
namespace Huosdk\Money;
class Payway {
    public function Normal_payways() {
        $where = array();
        $where['status'] = 2;
        $where['_string'] = "`payname` != 'gamepay' AND `payname` != 'ptbpay'";
        $data = M('payway')->where($where)->getField("payname", true);
        return $data;
    }

    public function Normal_payways_txt() {
        $data = $this->Normal_payways();
        /**
         * 其实这样连接字符串会有隐患
         */
        $result = join("','", $data);
        $result = "'".$result."'";
        return $result;
    }
}

