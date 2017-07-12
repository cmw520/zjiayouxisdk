<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Huosdk;
class Where {
    public function time(&$where, $field) {
        $this->start_time($where, $field);
        $this->end_time($where, $field);
        $this->time_interval($where, $field);
    }

    public function time_interval(&$where, $field) {
        if (isset($_GET["start_time"]) && $_GET["start_time"]
            && isset($_GET["end_time"])
            && $_GET["end_time"]
        ) {
            $where[$field] = array(
                array("gt", strtotime($_GET["start_time"])),
                array("lt", strtotime($_GET["end_time"]) + 86400)
            );
        }
    }

    public function start_time(&$where, $field) {
        $name = "start_time";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $where[$field] = array("gt", strtotime($_GET[$name]));
        }
    }

    public function end_time(&$where, $field) {
        $name = "end_time";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $where[$field] = array("lt", strtotime($_GET[$name]));
        }
    }

    public function order_id(&$where, $field) {
        $name = "orderid";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $orderid = $_GET[$name];
            $where[$field] = array("eq", "$orderid");
        }
    }

    public function agent_name(&$where, $field) {
        $name = "agentname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $agentname = $_GET[$name];
            $where[$field] = array("like", "%$agentname%");
        }
    }

    public function admin_name(&$where, $field1, $field2) {
        $name = "adminname";
        if (isset($_GET[$name]) && $_GET[$name]) {
            $adminname = trim($_GET[$name]);
            if ($adminname == "平台官方") {
                $where[$field1] = array("eq", "0");
            } else {
                $where[$field2] = array("like", "%$adminname%");
            }
        }
    }

    public function get_simple(&$where, $name, $field) {
        if (isset($_GET[$name]) && $_GET[$name]) {
            $where[$field] = trim($_GET[$name]);
        }
    }

    public function get_simple_like(&$where, $name, $field) {
        if (isset($_GET[$name]) && $_GET[$name]) {
            $_GET[$name] = trim($_GET[$name]);
            $v = $_GET[$name];
            $where[$field] = array("like", "%$v%");
        }
    }

    public function agent_name_with_official(&$where, $name, $field1, $field2) {
        if (isset($_GET[$name]) && $_GET[$name]) {
            $v = $_GET[$name];
            if ($v == "官方渠道") {
                $where[$field1] = array("eq", 0);
            } else {
                $where[$field2] = array("like", "%$v%");
            }
        }
    }
}

