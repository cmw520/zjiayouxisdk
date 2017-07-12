<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CompanyInfoController extends AdminbaseController {
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
        $v_str = M('options')->where("`option_name` = 'company_info'")->getField("option_value");
        $data = json_decode($v_str, true);

        return $data;
    }

    public function setInfo($data) {
        $data_str = json_encode($data);
        if (!$this->info_exist()) {
            $this->addInfo($data);
        }
        M('options')->where("`option_name` = 'company_info'")->setField("option_value", $data_str);
    }

    public function info_exist() {
        return M('options')->where("`option_name` = 'company_info'")->find();
    }

    public function addInfo($data) {
        $data_str = json_encode($data);
        $row = array();
        $row['option_name'] = "company_info";
        $row['option_value'] = $data_str;
        M('options')->add($row);
    }
}

