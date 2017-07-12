<?php
/* *
 * 游戏
 */
namespace Common\Model;

use Common\Model\CommonModel;

class GameModel extends CommonModel {
    //自动验证
    protected $_validate
        = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('name', 'require', '游戏名称不能为空！', 1, 'regex', 1),
            array('name', '', '游戏名已经存在！', 0, 'unique', 1), // 验证name字段是否唯一
        );
    //自动完成
    protected $_auto
        = array(
            array('create_time', 'time', 1, 'function'),
        );
}