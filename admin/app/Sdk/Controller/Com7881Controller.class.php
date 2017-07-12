<?php
/**
 * Com7881Controller.class.php UTF-8
 * 7881游戏交易市场记录与管理
 *
 * @date    : 2016年10月24日下午6:07:47
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : H5 2.0
 */
/*
**游戏管理
**/
namespace Sdk\Controller;

use Common\Controller\AdminbaseController;

class Com7881Controller extends AdminbaseController {
    protected $_7g_model, $_7o_model, $_7ol_model;

    function _initialize() {
        parent::_initialize();
        $this->_7g_model = M("7881_goods");
        $this->_7o_model = M('7881_order');
//         $this->_7ol_model = M('7881_order_log');
    }

    /*
     * 获取商品记录
     */
    function index() {
        $this->_gList();
        $this->_goods_status();
        $this->_goods();
        $this->_game();
        $this->display();
    }

    /*
     * 添加商品
     */
    function add() {
        $this->_game(true, 2, 2);
        $this->_goods_status(1);
        $this->_gList();
        $this->display();
    }

    /*
     * 添加商品操作函数
     */
    function add_post() {
        $data['goods_id'] = I('goods_id/s', '', trim);
        $data['type_id'] = I('type_id/d', 0);
        $data['app_id'] = I('app_id/d', 0);
        $data['goods_name'] = I('goods_name/s', '', trim);
        $data['price'] = I('price/f', 0);
        $data['real_price'] = I('real_price/f', 0);
        $data['gm_cnt'] = I('gm_cnt/f', 0);
        $data['status'] = I('status/d', 1);
        $data['is_delete'] = 2;
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        if (empty($data['goods_id']) || empty($data['type_id'])
            || empty($data['app_id'])
            || empty($data['goods_name'])
            || empty($data['price'])
            || empty($data['real_price'])
            || empty($data['gm_cnt'])
        ) {
            $this->error("参数错误");
        }
        $data['discount'] = round($data['real_price'] / $data['price']);
        $rs = $this->_7g_model->add($data);
        if ($rs) {
            $this->success("添加成功", U('Com7881/index'));
        } else {
            $this->error("添加失败");
        }
    }

    /*
     * 编辑商品
     */
    function edit() {
        $map['id'] = I('get.id/d', 0);
        if (empty($map['id'])) {
            $this->error("参数错误");
        }
        $info = $this->_7g_model->where($map)->find();
        $this->assign($info);
        $this->_game(true, 2, 2);
        $this->_goods_status(2);
        $this->display();
    }

    /*
     * 编辑商品操作函数
     */
    function edit_post() {
        $data['id'] = I('id/d', 0);
        $data['goods_id'] = I('goods_id/s', '', trim);
        $data['type_id'] = I('type_id/d', 0);
        $data['app_id'] = I('app_id/d', 0);
        $data['goods_name'] = I('goods_name/s', '', trim);
        $data['price'] = I('price/f', 0);
        $data['real_price'] = I('real_price/f', 0);
        $data['gm_cnt'] = I('gm_cnt/f', 0);
        $data['status'] = I('status/d', 1);
        $data['is_delete'] = 2;
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        if (empty($data['goods_id']) || empty($data['type_id'])
            || empty($data['app_id'])
            || empty($data['goods_name'])
            || empty($data['price'])
            || empty($data['real_price'])
            || empty($data['gm_cnt'])
            || empty($data['id'])
        ) {
            $this->error("参数错误");
        }
        $data['discount'] = round($data['real_price'] / $data['price'], 2);
        $rs = $this->_7g_model->save($data);
        if ($rs) {
            $this->success("编辑成功", U('Com7881/index'));
        } else {
            $this->error("编辑失败");
        }
    }

    /*
     * 获取7881订单记录
     */
    function orderindex() {
        $this->_game();
        $this->_goods(); //商品类型名称
        $this->_oList();
        $this->display();
    }

    /*
     * 重新发货
     */
    function repairorder() {
        $order_id = I('get.orderid/s', '');
        if (empty($order_id)) {
            $this->error("参数错误");
        }
        $url = SDKSITE."/api/public/v2/7881/send";
        $postdata['order_id'] = $order_id;
        $rdata = get_http_content($url, $postdata, 'POST');
        if ('SUCCESS' == $rdata['code']) {
            $this->success("发货成功");
        } else {
            $this->error("发货失败");
        }
    }

    /*
     * 获取商品详细函数
     */
    function _gList() {
        $where_ands = array();
        $fields = array(
            'start_time' => array(
                "field"    => "gd.create_time",
                "operator" => ">"
            ),
            'end_time'   => array(
                "field"    => "gd.create_time",
                "operator" => "<"
            ),
            'typeid'     => array(
                "field"    => "gd.type_id",
                "operator" => "="
            ),
            'gid'        => array(
                "field"    => "gd.app_id",
                "operator" => "="
            ),
            'goodsid'    => array(
                "field"    => "gd.goods_id",
                "operator" => "="
            ),
            'status'     => array(
                "field"    => "gd.status",
                "operator" => "="
            ),
        );
        if (IS_POST) {
            foreach ($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_POST[$param]);
                    $_GET[$param] = $get;
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach ($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_GET[$param]);
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $where = join(" and ", $where_ands);
        $count = $this->_7g_model
            ->alias("gd")
            ->join("left join ".C('DB_PREFIX')."game g ON gd.app_id = g.id")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "gd.*,g.name gamename";
        $items = $this->_7g_model
            ->alias("gd")
            ->field($field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON gd.app_id = g.id")
            ->order("gd.id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    /*
     * 获取订单详细函数
     */
    function _oList() {
        $where_ands = array();
        $fields = array(
            'start_time' => array(
                "field"    => "o.create_time",
                "operator" => ">"
            ),
            'end_time'   => array(
                "field"    => "o.create_time",
                "operator" => "<"
            ),
            'orderid'    => array(
                "field"    => "o.order_id",
                "operator" => "="
            ),
            'typeid'     => array(
                "field"    => "o.type_id",
                "operator" => "="
            ),
            'gid'        => array(
                "field"    => "o.app_id",
                "operator" => "="
            ),
            'username'   => array(
                "field"    => "m.username",
                "operator" => "="
            ),
            'goodsid'    => array(
                "field"    => "o.goods_id",
                "operator" => "="
            ),
            'billid'     => array(
                "field"    => "o.bill_id",
                "operator" => "="
            ),
            'status'     => array(
                "field"    => "o.status",
                "operator" => "="
            ),
            'cpstatus'   => array(
                "field"    => "o.status_7881",
                "operator" => "="
            )
        );
        if ('七日' == $_POST['submit']) {
            $_POST['start_time'] = date("Y-m-d", strtotime("-6 day"));
            $_POST['end_time'] = date("Y-m-d", time());
        } elseif ('本月' == $_POST['submit']) {
            $_POST['start_time'] = date("Y-m-01");
            $_POST['end_time'] = date("Y-m-d", time());
        }
        if (IS_POST) {
            foreach ($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_POST[$param]);
                    $_GET[$param] = $get;
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    } else if ('cpstatus' == $param) {
                        $get = intval($get);
                        if (2 == $get) {
                            array_push($where_ands, "$field $operator '$get'");
                        } else if (1 == $get) {
                            array_push($where_ands, "$field $operator '$get'");
                        }
                        continue;
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach ($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = trim($_GET[$param]);
                    if ('start_time' == $param) {
                        $get = strtotime($get);
                    } else if ('end_time' == $param) {
                        $get .= " 23:59:59";
                        $get = strtotime($get);
                    } else if ('cpstatus' == $param) {
                        $get = intval($get);
                        if (2 == $get) {
                            array_push($where_ands, "$field $operator '$get'");
                        } else if (1 == $get) {
                            array_push($where_ands, "$field $operator '$get'");
                        }
                        continue;
                    }
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }
        $where = join(" and ", $where_ands);
        $count = $this->_7o_model
            ->alias("o")
            ->join("left join ".C('DB_PREFIX')."members m ON o.mem_id = m.id")
            ->where($where)
            ->count();
        $rows = isset($_POST['rows']) ? intval($_POST['rows']) : $this->row;
        $page = $this->page($count, $rows);
        $field = "o.*,m.username,g.name gamename";
        $items = $this->_7o_model
            ->alias("o")
            ->field($field)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON o.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."members m ON o.mem_id = m.id")
            ->order("o.order_id DESC")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        $sumfield = "SUM(amount) sum_amount, SUM(real_amount) sum_real, SUM(gm_cnt) sum_gm_cnt";
        $sums = $this->_7o_model
            ->alias("o")
            ->field($sumfield)
            ->where($where)
            ->join("left join ".C('DB_PREFIX')."game g ON o.app_id = g.id")
            ->join("left join ".C('DB_PREFIX')."members m ON o.mem_id = m.id")
            ->find();
        $this->assign("sums", $sums);
        $this->assign("orders", $items);
        $this->assign("formget", $_GET);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
    }

    public function _goods($option = true, $status = null, $is_delete = null) {
        $cates = array(
            "0" => "选择商品",
        );
        if ($status) {
            $where['status'] = 2;
        }
        if ($is_delete) {
            $where['is_delete'] = 2;
        }
        $goods = M('7881_goods')->where($where)->getField("goods_id id,goods_name goodsname", true);
        if ($option && $goods) {
            $goods = $cates + $goods;
        }
        $this->assign("goods", $goods);
    }

    /**
     **游戏下拉列表
     **/
    public function _goods_status($option = null) {
        if (empty($option)) {
            $cates = array(
                "0" => "选择状态",
                "1" => "待上架",
                "2" => "已上架",
                "3" => "下架",
            );
        } elseif (1 == $option) {
            $cates = array(
                "1" => "待上架",
            );
        } else {
            $cates = array(
                "1" => "待上架",
                "2" => "已上架",
                "3" => "下架",
            );
        }
        $this->assign("goodsstatues", $cates);
    }
}
