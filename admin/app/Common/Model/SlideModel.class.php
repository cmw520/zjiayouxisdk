<?php
namespace Common\Model;
class SlideModel extends CommonModel {
    //自动验证
    protected $_validate
        = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('slide_cid', 'checkCid', '请选择分类！', 1, 'callback', 3),
        );

    protected function checkCid($data) {
        if (empty($data)) {
            return false;
        }

        return true;
    }

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}