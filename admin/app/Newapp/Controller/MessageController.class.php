<?php
/* 
 *  @time 2017-1-20 12:09:02
 *  @author 严旭
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class MessageController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $ui_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $ui_obj->app_select());
        $where = array();
        $h_obj = new \Huosdk\Where();
        $h_obj->get_simple($where, "app_id", "m.app_id");
        $h_obj->get_simple_like($where, "title", "m.title");
        $allitems = $this->getList($where);
        $count = count($allitems);
        $page = $this->page($count, 20);
        $items = $this->getList($where, $page->firstRow, $page->listRows);
        foreach ($items as $key => $value) {
            $items[$key]['message'] = mb_substr(strip_tags($value['message']), 0, 50);
        }
        $this->assign("items", $items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getList($where = array(), $start = 0, $limit = 0) {
        $items = M('message')
            ->alias("m")
            ->field("m.*,mb.username as receiver,g.name as game_name")
//            ->join("LEFT JOIN ".C("DB_PREFIX")."mem_message mm ON mm.message_id=m.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."members mb ON mb.id=m.mem_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game g ON g.id=m.app_id")
            ->where($where)
            ->order("m.id desc")
            ->limit($start, $limit)
            ->select();

        $category_data = array("1" => "活动消息", "2" => "系统消息", "3" => "卡卷消息", "4" => "优惠活动");
        foreach ($items as $key => $value) {
            $items[$key]['category_txt'] = $category_data[$value['type']];
            if (!$value['receiver']) {
                $items[$key]['receiver'] = "全体";
            } else {
                $items[$key]['receiver'] = "玩家：".$value['receiver'];
            }
            $items[$key]['send_time'] = date("Y-m-d H:i:s", $value['send_time']);
        }

        return $items;
    }

    public function add() {
        $ui_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $ui_obj->app_select());
        $this->display();
    }

    public function addPost() {
        $this->commonFilter();
        $memtype = I('memtype');
        if ($memtype != '1' && $memtype != '2') {
            $this->error("消息类型有误");
        }
        /**
         * 创建消息记录
         */
        /**
         * 如果是发给玩家的，还要在玩家消息表中创建记录
         */
        $mem_id = 0;
        if (2 == $memtype) {
            $mem_name = I('mem_name/s', '');
            $mem_id = $this->memExist($mem_name);
            if (!$mem_id) {
                $this->error("玩家帐号不存在");
            }
        }
        $data = array();
        $data['title'] = I('title/s', '');
        $data['message'] = html_entity_decode(I('message'));
        $data['send_time'] = time();
        $data['type'] = I('type/d', 2);
        $data['app_id'] = I('app_id/d', 0);
        $data['mem_id'] = $mem_id;
        $data['admin_id'] = sp_get_current_admin_id();
        $message_id = M('message')->add($data);
        if (!$message_id) {
            $this->error("消息创建失败");
        }
        if ($memtype == '1') {
            $this->success('添加成功', U('Newapp/Message/index'));
        } else if ($memtype == '2') {
            $data_mem = array();
            $data_mem['mem_id'] = $mem_id;
            $data_mem['message_id'] = $message_id;
            $data_mem['status'] = 1;
            $data_mem['create_time'] = time();
            $data_mem['type'] = $data['type'];
            $add_result = M('mem_message')->add($data_mem);
            if (!$add_result) {
                $this->error("个体消息关联错误");
            }
            $this->success('添加成功', U('Newapp/Message/index'));
        }
    }

    public function commonFilter() {
        if (!I('title')) {
            $this->error("标题不能为空");
        }
        if (!I('message')) {
            $this->error("内容不能为空");
        }
    }

    public function memExist($mem_name) {
        return M('members')->where(array("username" => $mem_name))->getField("id");
    }

    public function delete() {
        $id = I('id');
        $result = M('message')->where(array("id" => $id))->delete();
        if (!$result) {
            $this->error("删除失败");
        }
        $this->success("删除成功");
    }

    public function edit() {
        $id = I('id');
        $info = $this->getList(array("m.id" => $id), 0, 1);
        $this->assign("data", $info[0]);
        $_GET['app_id'] = $info[0]['app_id'];
        $ui_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $ui_obj->app_select());
        $this->display();
    }

    public function editPost() {
        $this->commonFilter();
        $id = I('id');
        $data = array();
        $data['title'] = I('title');
        $data['message'] = html_entity_decode(I('message'));
        $data['category'] = I('category');
        $data['app_id'] = I('app_id');
        $result = M('message')->where(array("id" => $id))->save($data);
        if (!$result) {
            $this->error("消息更新失败或无变化");
        }
        $this->success("保存成功");
    }
}
