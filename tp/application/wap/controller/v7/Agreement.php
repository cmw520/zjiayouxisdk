<?php
/**
 * Agreement.php UTF-8
 * 协议
 *
 * @date    : 2017/1/22 19:49
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace app\wap\controller\v7;

use app\common\controller\Base;
use huosdk\slide\Slide;

class Agreement extends Base {
    function _initialize() {
        parent::_initialize();
    }

    /**
     *
     * 获取协议页面(agreement/:type)
     * http://doc.1tsdk.com/43?page_id=646
     *
     * @param $type
     *
     * @return $this
     */
    public function read($type) {
        if (empty($type)) {
            echo '未找到页面';
        }
        $_type = $type;
        $_slide_class = new Slide($_type);
        $_rdata = $_slide_class->getNews();
        if (empty($_rdata)) {
            echo "未找到页面";
        }
        $this->assign('data', $_rdata);

        return $this->fetch('agreement/index');
    }
}