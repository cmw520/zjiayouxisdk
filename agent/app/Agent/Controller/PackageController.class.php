<?php
namespace Agent\Controller;

use Common\Controller\AgentbaseController;

class PackageController extends AgentbaseController {
    function _initialize() {
        parent::_initialize();
        if (!is_logged_in()) {
            redirect(U('user/account/login'));
            exit;
        }
        $user_info = get_agent_info_by_id($_SESSION['agent_id']);
        $this->assign("user", $user_info);
    }

    public function dopack() {
        $agent_id = $_SESSION['agent_id'];
        $down_root = SITE_PATH."access/download/";
        $m_pack = "default.apk";
        $sourcefile = $down_root.$m_pack;
        $newfile = $down_root."axin_game_platform_".$agent_id.".apk";
        $newfile_download_url = "/access/download/"."axin_game_platform_".$agent_id.".apk";
        if (file_exists($newfile)) {
            unlink($newfile);
        }
        if (!copy($sourcefile, $newfile)) {
            $this->ajaxReturn(array("error" => "1", "msg" => '创建文件失败'));
            exit;
        }
        $channelname = "META-INF/gamechannel";
        $zip = new \ZipArchive;
        if ($zip->open($newfile) === true) {
            $zip->addFromString($channelname, json_encode(array('agent_id' => $agent_id)));
            $zip->close();
            $this->ajaxReturn(array("error" => "0", "msg" => $newfile_download_url));
            exit;
        } else {
            $this->ajaxReturn(array("error" => "1", "msg" => '打包失败'));
            exit;
        }
    }
}
