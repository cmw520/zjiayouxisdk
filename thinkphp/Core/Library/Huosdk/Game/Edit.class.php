<?php
namespace Huosdk\Game;
class Edit {
    public function __construct() {
    }

    public function basicInfo($app_id, $game_name) {
    }

    public function icon($app_id, $fp) {
        M('game')->where(array("id" => $app_id))->setField("icon", $fp);
    }

    public function mobileIcon() {
    }
}
