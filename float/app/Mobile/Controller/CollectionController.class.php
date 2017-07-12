<?php
/**
 * GiftController.class.php UTF-8
 * ���������
 *
 * @date    : 2016��9��7������3:06:32
 *
 * @license �ⲻ��һ�����������δ����Ȩ�����κ�ʹ�úʹ�����
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
namespace Mobile\Controller;

use Common\Controller\MobilebaseController;

class CollectionController extends MobilebaseController {
    function _initialize() {
        parent::_initialize();
        $this->assign('title', '我的收藏');
    }

    /**
     * 我的收藏
     */
    function myCollection() {
        // 我的收藏
        $myCollection = $this->getCollection();
        $this->assign('myCollection', $myCollection);
        $this->display('myCollection');
    }

    // 我的收藏
    private function getCollection() {
        $field = "mf.app_id,g.name,g.type,CONCAT('/".C('UPLOADPATH').C('LOGOPATH')."/',g.icon) icon";
        $userid = get_current_userid();
        $where['mem_id'] = $userid;
        $myCollection = M('mem_game_like')->alias('mf')->join('left join '.C("DB_PREFIX").'game g on g.id=mf.app_id')
                                          ->field(
                                              $field
                                          )->where(
                array(
                    'mf.mem_id' => $userid
                )
            )->order('mf.create_time')->select();
        // 获取类型
        foreach ($myCollection as $key => $val) {
            $myCollection[$key]['type'] = getGametype($val['type']);
        }
        return $myCollection;
    }
}