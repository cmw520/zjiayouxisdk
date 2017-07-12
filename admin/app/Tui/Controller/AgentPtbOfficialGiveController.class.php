<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class AgentPtbOfficialGiveController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_ui_filter_obj = new \Huosdk\UI\Filter();
        $this->assign("agent_select", $hs_ui_filter_obj->agent_select());
        $this->assign("agent_select_Level_one", $hs_ui_filter_obj->agent_select_Level_one());
        $this->assign("agent_select_Level_two", $hs_ui_filter_obj->agent_select_Level_two());
        $this->assign("agent_level_select", $hs_ui_filter_obj->agent_level_select());
        $this->assign("time_choose", $hs_ui_filter_obj->time_choose());
        $where = '';
//        $hs_where_obj=new \Huosdk\Where();
//        $hs_where_obj->time($where,"pg.create_time");
//        $hs_where_obj->get_simple($where,"agent_id","agent_id");
        if (isset($_GET['agent_id']) && ($_GET['agent_id'])) {
            $v = $_GET['agent_id'];
            $where .= " AND agent_id= $v ";
        }
        if (isset($_GET['start_time']) && ($_GET['start_time'])) {
            $v = strtotime($_GET['start_time']);
            $where .= " AND create_time >= $v ";
        }
        if (isset($_GET['end_time']) && ($_GET['end_time'])) {
            $v = strtotime($_GET['end_time']) + 86400;
            $where .= " AND create_time <= $v ";
        }
//        if(isset($_GET['agent_id'])&&$_GET['agent_id']){
//            $where['pg.agent_id']=$_GET['agent_id'];
//        }
        $hs_ptb_obj = new \Huosdk\Ptb();
//        $all_items=$hs_ptb_obj->giveList($where);
        $all_items = $this->getList($where);
        $count = count($all_items);
        $page = $this->page($count, 10);
//        $items=$hs_ptb_obj->getList($where,$page->firstRow,$page->listRows);       
        $new_items = $this->getList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $new_items);
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show("Admin"));
        $this->display();
    }

    public function getList($where = '', $start = 0, $limit = 0) {
        $sql = ""
               ."SELECT ptb_cnt,remark,agent_id,create_time FROM c_ptb_agentcharge "
               ."WHERE admin_id = 1 $where "
               .""
               .""
               ."UNION ALL "
               ."SELECT ptb_cnt,remark,agent_id,create_time FROM c_ptb_given "
               ."WHERE 1 $where "
               .""
               .""
               ."ORDER BY create_time DESC "
               ."LIMIT $start ,$limit "
               ."";
        $model = new \Think\Model();
        $items = $model->query($sql);
        foreach ($items as $key => $value) {
            $data = M('users')->where(array("id" => $value['agent_id']))->find();
            $items[$key]['agent_name'] = $data['user_nicename'];
            $items[$key]['user_type'] = $data['user_type'];
        }
        \Huosdk\Data\FormatRecords::agent_level($items);

        return $items;
    }
}

