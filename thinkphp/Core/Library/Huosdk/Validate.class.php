<?php
namespace Huosdk;
class Validate {
    public function email() {
    }

    public function password($value) {
        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{6,20}$/';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }

    public function password2($value) {
        $match = '/^[0-9a-zA-Z]{6,20}$/';
        $v = trim($value);
        if (empty($v)) {
            return false;
        }
        return preg_match($match, $v);
    }

    public function phone($phone) {
        return preg_match("/^1[34578]\d{9}$/", $phone);
    }

    public function username($value) {
        return preg_match("/^[0-9a-zA-Z]{4,21}$/", $value);
    }

    public function userLogin($value) {
        return preg_match("/^[0-9a-zA-Z]{6,21}$/", $value);
    }

    public function userNicename($value) {
        return true;
    }
}

