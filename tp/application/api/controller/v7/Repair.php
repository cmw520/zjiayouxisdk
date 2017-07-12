<?php
/**
 * Repair.php UTF-8
 * 修复工具
 *
 * @date    : 2017/3/16 14:30
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace app\api\controller\v7;

use think\Controller;

class Repair extends Controller {
    function _initialize() {
        parent::_initialize();
    }

    /**
     * 关于我们(system/aboutus)
     * http://doc.1tsdk.com/43?page_id=825
     */
    public function geturl() {


//
//        $_app_ids = \think\Config::get('config.HUOAPP');
//        $_ios_app_id = $_app_ids['IOS_APP_APPID'];
//        $game_info = \think\Db::name('game')->where(array('id' =>$_ios_app_id))->find();
//        /* 查询最新版本 */
//
//        $init = $game_info['initial'].'/'.$ver_id;
////        $opt = md5(md5($init.$ag_info['agentgame']).'resub');
//        $initial = base64_encode($init);
//        $agentgame = base64_encode($game_info['initial']);
//        $opt = base64_encode($opt);
//        $data_string = array('p' => $initial, 'a' => $agentgame, 'o' => $opt,'image'=>$m_icon,'rurl'=);
//        $data_string = json_encode($data_string);
//        $url = DOWNIP."/sub.php";
//        $cnt = 0;
//        while (1) {
//            $return_content = base64_decode(self::http_post_data($url, $data_string));
//            if (0 < $return_content || 3 == $cnt) {
//                break;
//            }
//            $cnt++;
//        }
//        if (0 < $return_content) {
//            $updatedata['url'] = '/sdkgame/'.$init.'/'.$ag_info['agentgame'].".apk";
//            $updatedata['status'] = 2;
//            $updatedata['update_time'] = time();
//            $rs = $this->ag_model->where("id=%d", $ag_id)->save($updatedata);
//            if ($return_content == 1) {
//                $this->ajaxReturn(array('success' => true, 'msg' => '分包成功'), 'JSON');
//            } else {
//                //$this->success("分包成功");
//                $this->_ajax_return("更新成功", $option);
//            }
//            exit;
//        } else if (-6 == $return_content) {
//            $this->_ajax_return("拒绝访问", $option);
//            exit;
//        } else if (-4 == $return_content) {
//            $this->_ajax_return("验证错误", $option);
//            exit;
//        } else if (-3 == $return_content) {
//            $this->_ajax_return("请求数据为空", $option);
//            exit;
//        } else if (-2 == $return_content) {
//            $this->_ajax_return("分包失败", $option);
//            exit;
//        } else if (-1 == $return_content) {
//            $this->_ajax_return("无法创建文件,打包失败.", $option);
//            exit;
//        } else if (-5 == $return_content) {
//            $this->_ajax_return("游戏原包不存在.", $option);
//            exit;
//        } else {
//            $this->_ajax_return("请求数据失败.", $option);
//            exit;
//        }
//        $this->_ajax_return("分包记录添加失败！", $option);
//        exit;


        return hs_api_responce('200', '请求成功', json_decode($_rdata, true));
    }
}