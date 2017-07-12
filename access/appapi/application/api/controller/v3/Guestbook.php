<?php

/**
 * Startup.php UTF-8
 * 启动控制
 * @date: 2016年8月18日下午9:47:10
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author : wuyonghong <wyh@huosdk.com>
 * @version : api 2.0
 */
namespace app\api\controller\v1;

use app\common\controller\Base;
use think\Db;
use think\Controller;

class Guestbook extends Base
{
    function _initialize() {
        parent::_initialize();
    }

    function save(){
        $content = $this->request->post('content');
        $linkman = $this->request->post('linkman');

        if (empty($linkman)){
            return hs_api_responce('400', '请填写联系方式', array());
        }
        $data['full_name'] = $linkman;

        if (empty($content)){
            return hs_api_responce('400', '请填写反馈意见', array());
        }
        $data['msg'] = $content;

        $data['create_time'] = time();
        $data['status'] = 1;

        $data['mem_id'] = $this->mem_id;

        $rs = Db::name('guest_book')->insert($data);
        if (false != $rs){
            return hs_api_responce(201,'反馈成功,我们会及时处理',array());
        }else{
            return hs_api_responce(400, '服务器内部错误');
        }
    }
}