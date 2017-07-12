<?php
namespace Common\Model;

class MessageModel extends CommonModel {
    //自动验证
    protected $_validate
        = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('app_id', 'require', '游戏名称不能为空！', 1, 'regex', 3),
            array('url', 'require', 'url不能为空！', 1, 'regex', 3),
        );
    //自动完成
    protected $_auto
        = array(
            array('create_time', 'time', 1, 'function'),
            array('update_time', 'time', 3, 'function'),
        );

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}