<?php
namespace Common\Model;

class ClientadminModel extends CommonModel {
    protected $trueTableName = 'db_sdk_mn.l_clientadmin';

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}