<?php
namespace Tui\Controller;

use Common\Controller\AdminbaseController;

class GameBenefitController extends AdminbaseController {
    function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $hs_Filter_obj = new \Huosdk\UI\Filter();
        $this->assign("app_select", $hs_Filter_obj->app_select());
        $this->assign("benefit_type_select", $hs_Filter_obj->benefit_type_select());
        $this->assign("promote_status_select", $hs_Filter_obj->promote_status_select());
        $where = array();
//        $where['g.status'] = 2;
        $where['g.is_delete'] = 2;
        $hs_where_obj = new \Huosdk\Where();
        $hs_where_obj->get_simple($where, "app_id", "g.id");
        $hs_where_obj->get_simple($where, "benefit_type", "gr.benefit_type");
        if (2 == $_GET['promote_status']) {
            $hs_where_obj->get_simple($where, "promote_status", "g.status");
        }
        $hs_where_obj->get_simple($where, "promote_status", "gr.promote_switch");
        $count = $this->getGameCnt($where);
        $page = $this->page($count, 10);
        $items = $this->GameList($where, $page->firstRow, $page->listRows);
        $this->assign("items", $items);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getGameCnt($where = array()) {
        $_cnt = M('game')
            ->alias('g')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_info gi ON gi.app_id=g.game_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_rate gr ON gr.app_id=g.id")
            ->where($where)
            ->count();

        return $_cnt;
    }

    public function GameList($where = array(), $start = 0, $limit = 0) {
        $items = M('game')
            ->field(
                "g.*,gv.size,gv.version,gi.mobile_icon m_icon,"
                ."gr.agent_rate,gr.benefit_type,gr.mem_rate,gr.first_mem_rate,gr.mem_rebate,gr.first_mem_rebate"
            )
            ->alias('g')
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_version gv ON gv.app_id=g.id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_info gi ON gi.app_id=g.game_id")
            ->join("LEFT JOIN ".C("DB_PREFIX")."game_rate gr ON gr.app_id=g.id")
            ->where($where)
            ->limit($start, $limit)
            ->select();
        $promote_data = array(
            "2" => "<span class='label label-success'>已上架</span>",
            "1" => "<span class='label label-danger'>未上架</span>"
        );
        foreach ($items as $key => $value) {
            if (2 == $value['promote_switch'] && 2 == $value['status']) {
                $value['promote_switch'] = 2;
            }else{
                $value['promote_switch'] = 1; //上线并且设置推广的游戏才上线
            }
            $items[$key]['promote_switch'] = $value['promote_switch'];
            $items[$key]['promote_status'] = $promote_data[$value['promote_switch']];
            if (!$value['agent_rate']) {
                $items[$key]['agent_rate'] = "未设置";
            }
            if ($value['benefit_type'] == 1) {
                $items[$key]['benefit_type_txt'] = "折扣";
                $items[$key]['mem_first'] = floatval($value['first_mem_rate']).'  ('.floatval(
                        $value['first_mem_rate'] * 10
                    ).'折)';
                $items[$key]['mem_refill'] = floatval($value['mem_rate']).'  ('.floatval($value['mem_rate'] * 10).'折)';
            } else if ($value['benefit_type'] == 2) {
                $items[$key]['benefit_type_txt'] = "返利";
                $items[$key]['mem_first'] = 100 * $value['first_mem_rebate'].'%';
                $items[$key]['mem_refill'] = 100 * $value['mem_rebate'].'%';
            } else {
                $items[$key]['benefit_type_txt'] = "无优惠";
                $items[$key]['mem_first'] = "";
                $items[$key]['mem_refill'] = "";
            }
            if (!empty($value['m_icon'])) {
                if (!strpos($value['m_icon'], 'upload')) {
                    $items[$key]['icon'] = '/upload/image/'.$value['m_icon'];
                } else {
                    $items[$key]['icon'] = $value['m_icon'];
                }
            }
            $hs_br = new \Huosdk\Benefit\RulesGame($value['agent_rate']);
            $items[$key]['hint'] = json_encode($hs_br->hint);
        }

        return $items;
    }

    public function edit_post() {
        $data = $_POST;
        $data['update_time'] = time();
        $data['first_mem_rebate'] = number_format($data['first_mem_rebate'] / 100, 4, '.', '');
        $data['mem_rebate'] = number_format($data['mem_rebate'] / 100, 4, '.', '');
        $data['first_mem_rate'] = number_format($data['first_mem_rate'], 4, '.', '');
        $data['mem_rate'] = number_format($data['mem_rate'], 4, '.', '');
        $agent_rate = $data['agent_rate'];
        if (!($agent_rate > 0 && $agent_rate <= 1)) {
            $this->ajaxReturn(
                array(
                    "error" => "1",
                    "msg"   => "渠道折扣必须在(0,1]之间"
                )
            );
            exit();
        }
        // $hs_br_obj=new \Huosdk\Benefit\RulesGame($data['prev_agent_rate']);
        $hs_br_obj = new \Huosdk\Benefit\RulesGame($agent_rate);
        if (C("G_DISCONT_TYPE")) {
            if ($data['benefit_type'] == 1) {
                $result = $hs_br_obj->check_mem_rate($data['mem_rate'], $data['first_mem_rate']);
                if ($result != "ok") {
                    $this->ajaxReturn(
                        array(
                            "error" => "1",
                            "msg"   => $result
                        )
                    );
                    exit();
                }
            }
            if ($data['benefit_type'] == 2) {
                $result = $hs_br_obj->check_mem_rebate($data['mem_rebate'], $data['first_mem_rebate']);
                if ($result != "ok") {
                    $this->ajaxReturn(
                        array(
                            "error" => "1",
                            "msg"   => $result
                        )
                    );
                    exit();
                }
            }
        } else {
            $data['mem_rate'] = 1;
            $data['first_mem_rate'] = 1;
            $data['mem_rebate'] = 0;
            $data['first_mem_rebate'] = 0;
        }
        $exist = array();
        $_diff = array();
        $app_id = I('app_id');
        $exist = M('game_rate')->where(
            array(
                "app_id" => $app_id
            )
        )->find();
        if ($exist) {
            $_diff['agent_rate'] = $data['agent_rate'] - $exist['agent_rate'];
            if (1 == $data['benefit_type'] && (1 == $exist['benefit_type'] || 0 == $exist['benefit_type'])) {
                /* 折扣游戏只能修改为折扣游戏，或者无折扣模式 */
                $_diff['first_mem_rate'] = $data['first_mem_rate'] - $exist['first_mem_rate'];
                $_diff['mem_rate'] = $data['mem_rate'] - $exist['mem_rate'];
                $_diff['benefit_type'] = 1;
                $exist['agent_rate'] = $data['agent_rate'];
                $exist['first_mem_rate'] = $data['first_mem_rate'];
                $exist['mem_rate'] = $data['mem_rate'];
                $exist['first_mem_rebate'] = 0;
                $exist['mem_rebate'] = 0;
                $exist['update_time'] = time();
            } elseif (2 == $data['benefit_type'] && (2 == $exist['benefit_type'] || 0 == $exist['benefit_type'])) {
                $_diff['first_mem_rebate'] = $data['first_mem_rebate'] - $exist['first_mem_rebate'];
                $_diff['mem_rebate'] = $data['mem_rebate'] - $exist['mem_rebate'];
                $_diff['benefit_type'] = 2;
                $exist['agent_rate'] = $data['agent_rate'];
                $exist['first_mem_rate'] = 1;
                $exist['mem_rate'] = 1;
                $exist['first_mem_rebate'] = $data['first_mem_rebate'];
                $exist['mem_rebate'] = $data['mem_rebate'];
                $exist['update_time'] = time();
            } else if (0 == $data['benefit_type']) {
                $_diff['benefit_type'] = 0;
                $exist['agent_rate'] = $data['agent_rate'];
                $exist['first_mem_rate'] = 1;
                $exist['mem_rate'] = 1;
                $exist['first_mem_rebate'] = 0;
                $exist['mem_rebate'] = 0;
                $exist['update_time'] = time();
            } else {
                if (1 == $data['benefit_type']) {
                    $this->ajaxReturn(
                        array(
                            "error" => "1",
                            "msg"   => '玩家优惠类型不能从【返利】变为【折扣】'
                        )
                    );
                } else {
                    $this->ajaxReturn(
                        array(
                            "error" => "1",
                            "msg"   => '玩家优惠类型不能从【折扣】变为【返利】'
                        )
                    );
                }
                exit();
            }
            $this->grUpagr($exist, $_diff);
            // $data['mem_rebate']=$data['mem_rebate']/100;
            // $data['first_mem_rebate']=$data['first_mem_rebate']/100;
            // M('game_rate')->save($data);
        } else {
            $data['create_time'] = time();
            M('game_rate')->add($data);
        }
        $this->ajaxReturn(
            array(
                "error" => "0",
                "msg"   => "保存成功"
            )
        );
    }

    /*
     * 游戏折扣更新时，更新所有渠道折扣
     * @param $exist 存在的数据
     * @param $data 新数据
     */
    protected function grUpagr($grdata, $diff) {
        if (empty($grdata)) {
            return false;
        }
        $_diff_flag = false;
        if ($grdata['benefit_type'] != $diff['benefit_type']) {
            $grdata['benefit_type'] = $diff['benefit_type'];
            $_diff_flag = true;
        }
        /* 更新游戏优惠 */
        M('game_rate')->save($grdata);
        /* 获取agent_game_rate推广方信息 */
        $map['app_id'] = $grdata['app_id'];
        $_diff['agent_rate'] = 0;
        $_diff['mem_rate'] = 0;
        $_diff['first_mem_rate'] = 0;
        $_diff['mem_rebate'] = 0;
        $_diff['first_mem_rebate'] = 0;
        $_diff['promote_switch'] = $grdata['promote_switch'];
        if (!empty($diff['agent_rate'])) {
            $_diff['agent_rate'] = $diff['agent_rate'];
        }
        if (!empty($diff['mem_rate'])) {
            $_diff['mem_rate'] = $diff['mem_rate'];
        }
        if (!empty($diff['first_mem_rate'])) {
            $_diff['first_mem_rate'] = $diff['first_mem_rate'];
        }
        if (!empty($diff['mem_rebate'])) {
            $_diff['mem_rebate'] = $diff['mem_rebate'];
        }
        if (!empty($diff['first_mem_rebate'])) {
            $_diff['first_mem_rebate'] = $diff['first_mem_rebate'];
        }
        if (!$_diff_flag && 0.0001 >= abs($_diff['agent_rate']) && 0.0001 >= abs($_diff['mem_rate'])
            && 0.0001 >= abs(
                $_diff['first_mem_rate']
            )
            && 0.0001 >= abs($_diff['mem_rebate'])
            && 0.0001 >= abs(
                $_diff['first_mem_rebate']
            )
        ) {
            /* 无差异，不需要更新 */
            return true;
        }
        $_agr_data = M('agent_game_rate')->where($map)->select();
        foreach ($_agr_data as $_key => $_val) {
            $_val['benefit_type'] = $grdata['benefit_type'];
            $_val['agent_rate'] = $_val['agent_rate'] + $_diff['agent_rate'];
            $_val['mem_rate'] = $_val['mem_rate'] + $_diff['mem_rate'];
            $_val['first_mem_rate'] = $_val['first_mem_rate'] + $_diff['first_mem_rate'];
            $_val['mem_rebate'] = $_val['mem_rebate'] + $_diff['mem_rebate'];
            $_val['first_mem_rebate'] = $_val['first_mem_rebate'] + $_diff['first_mem_rebate'];
            $_val['promote_switch'] = $_diff['promote_switch'];
            $_val['update_time'] = time();
            if ($_val['agent_rate'] <= 0.0001) {
                $_val['agent_rate'] = 0.01; /* 默认最小折扣0.01 */
            }
            if ($_val['agent_rate'] > 1) {
                $_val['agent_rate'] = 1; /* 默认最大折扣1 */
            }
            if (1 == $_val['benefit_type']) {
                if ($_val['mem_rate'] <= 0.0001) {
                    $_val['mem_rate'] = 0.01; /* 默认最小折扣0.01 */
                }
                if ($_val['mem_rate'] > 1) {
                    $_val['mem_rate'] = 1; /* 默认最大折扣1 */
                }
                if ($_val['first_mem_rate'] <= 0.0001) {
                    $_val['first_mem_rate'] = 0.01; /* 默认最小折扣0.01 */
                }
                if ($_val['mem_rate'] > 1) {
                    $_val['mem_rate'] = 1; /* 默认最小折扣1 */
                }
                $_val['mem_rebate'] = 0;
                $_val['first_mem_rebate'] = 0;
            } elseif (2 == $_val['benefit_type']) {
                if ($_val['mem_rebate'] <= 0.0001) {
                    $_val['mem_rebate'] = 0; /* 默认最小折扣0.01 */
                }
                if ($_val['first_mem_rebate'] <= 0.0001) {
                    $_val['first_mem_rebate'] = 0; /* 默认最小折扣1 */
                }
                $_val['mem_rate'] = 1;
                $_val['first_mem_rate'] = 1;
            } else {
                $_val['mem_rate'] = 1;
                $_val['first_mem_rate'] = 1;
                $_val['mem_rebate'] = 0;
                $_val['first_mem_rebate'] = 0;
            }
            M('agent_game_rate')->save($_val);
        }

        return true;
    }

    public function benefit_rule($param) {
        /**
         * agent_rate
         * benefit_type
         * mem_rate
         * first_mem_rate
         * mem_rebate
         * first_mem_rebate
         */
        if (!$this->benefit_rule_agent_rate($param['agent_rate'])) {
            return false;
        }
        if ($param['benefit_type'] == 1) {
            return $this->benefit_rule_discount($param['agent_rate'], $param['mem_rate'], $param['first_mem_rate']);
        } else if ($param['benefit_type'] == 2) {
            return $this->benefit_rule_rebate($param['agent_rate'], $param['mem_rebate'], $param['first_mem_rebate']);
        } else if ($param['benefit_type'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function benefit_rule_agent_rate($agent_rate) {
        if (!(($agent_rate > 0) && ($agent_rate < 1))) {
            return false;
        }

        return true;
    }

    public function benefit_rule_discount($agent_rate, $mem_rate, $first_mem_rate) {
        if (!($mem_rate > $agent_rate)) {
            return false;
        }
        if (!(($mem_rate > 0) && ($mem_rate < 1) && ($first_mem_rate > 0) && ($first_mem_rate < 1))) {
            return false;
        }
        if (!($mem_rate >= $first_mem_rate)) {
            return false;
        }

        return true;
    }

    public function benefit_rule_rebate($agent_rate, $mem_rebate, $first_mem_rebate) {
        if (!(($mem_rebate > 0) && ($mem_rebate < 9) && ($first_mem_rebate > 0) && ($first_mem_rebate < 9))) {
            return false;
        }
        if (!($mem_rebate <= $first_mem_rebate)) {
            return false;
        }

        return true;
    }

    public function generate_limit($benefit_type, $agent_rate) {
        /**
         * 如果agent rate为0，不能往下进行
         */
        if (!$agent_rate) {
            return false;
        }
        $result = array();
        if ($benefit_type == 1) {
            $result = array(
                $agent_rate,
                "1"
            );
        } else if ($benefit_type == 2) {
            $top = round((1 / $agent_rate - 1) * 100);
            $result = array(
                0,
                $top
            );
        }

        return $result;
    }
}
