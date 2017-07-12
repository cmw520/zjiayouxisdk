<?php
/**
 * AboutusController.class.php UTF-8
 * app中关于我们
 *
 * @date    : 2017/3/2 21:44
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class AboutusController extends AdminbaseController {
    public function _initialize() {
        parent::_initialize();
    }

    public function edit() {
        $info = $this->getInfo();
        $this->assign("info", $info);
        $this->display();
    }

    public function edit_post() {
        $this->setInfo($_POST);
        $this->success("设置成功");
    }

    public function getInfo() {
        $v_str = M('options')->where("`option_name` = 'appaboutus'")->getField("option_value");
        $data = json_decode($v_str, true);

        return $data;
    }

    public function setInfo($data) {
        if (!empty($data['qq'])) {
            $_qq = array(
                array(
                    'number' => $data['qq'],
                    'name'   => $data['qq'],
                )
            );
            $data['qq'] = $_qq;
        }
        $data_str = json_encode($data);
        if (!$this->info_exist()) {
            $this->addInfo($data);
        }
        M('options')->where("`option_name` = 'appaboutus'")->setField("option_value", $data_str);
    }

    public function info_exist() {
        return M('options')->where("`option_name` = 'appaboutus'")->find();
    }

    public function addInfo($data) {
        $data_str = json_encode($data);
        $row = array();
        $row['option_name'] = "appaboutus";
        $row['option_value'] = $data_str;
        M('options')->add($row);
    }
}

