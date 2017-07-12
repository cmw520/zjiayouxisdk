<?php
/**
 * GametypeController.class.php UTF-8
 * 游戏管理页面
 *
 * @date    : 2016年4月8日下午3:05:03
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@1tsdk.com>
 * @version : 1.0
 */
namespace Newapp\Controller;

use Common\Controller\AdminbaseController;

class GametypeController extends AdminbaseController {
    protected $gt_model;

    function _initialize() {
        parent::_initialize();
        $this->gt_model = M('game_type');
    }

    /**
     * 图片上传类
     *
     * @date  : 2016年4月9日上午11:26:50
     *
     * @param NULL
     *
     * @return NULL
     * @since 1.0
     */
    public function upload($up_info, $savePath, $name) {
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array(
            'jpg',
            'png',
            'jpeg'
        ); // 设置附件上传类型
        $upload->rootPath = C('UPLOADPATH'); // 设置附件上传根目录
        $upload->savePath = $savePath.'/'; // 设置附件上传（子）目录
        $upload->saveName = $name;
        $upload->autoSub = false;
        $upload->replace = true;
        $info = $upload->uploadOne($up_info);
        /* 上传错误提示错误信息 */
        if (!$info) {
            $return['status'] = 0;
            $return['msg'] = $upload->getError();
        } else {
            /* 上传成功 */
            $return['status'] = 1;
            $return['msg'] = C("TMPL_PARSE_STRING.__UPLOAD__").$info['savepath'].$info['savename'];
        }

        return $return;
    }

    /**
     * 游戏类型列表
     */
    public function index() {
        $this->typecate();
        exit;
        $this->gtList();
        $this->gtStatus();
        $this->display();
    }

    public function typecate() {
        $result = M('game_type')->order(array("listorder" => "DESC"))->select();
        import("Tree");
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $newmenus = array();
        foreach ($result as $m) {
            $newmenus[$m['id']] = $m;
        }
        foreach ($result as $n => $r) {
            $result[$n]['level'] = $this->_get_level($r['id'], $newmenus);
            $result[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-'.$r['parentid'].'"' : '';
            $result[$n]['str_manage'] = '<a href="'.U(
                    "Gametype/edit", array("id" => $r['id'])
                ).'">修改</a> | <a class="js-ajax-delete" href="'.U(
                                            "Gametype/delete", array("id" => $r['id'], "menuid" => I("get.menuid"))
                                        ).'">删除</a> ';
            if (0 == $r['parentid']) {
                $result[$n]['str_manage'] = '<a href="'.U(
                        "Gametype/add", array("parentid" => $r['id'])
                    ).'">添加子类型</a> | '.$result[$n]['str_manage'];
            }
            $result[$n]['status'] = 2 == $r['status'] ? "显示" : "隐藏";
            $result[$n]['image'] = '<a class="img_a" href="javascript:onClick=image_priview(\''
                                   .sp_get_asset_upload_path($r['image']).'\')" >
                            <img class="img_prew" src="'.sp_get_asset_upload_path($r['image']).'"
                                 style="height: 50px;" ></img ></a >';
        }
        $tree->init($result);
        $str
            = "<tr id='node-\$id' \$parentid_node>
					<td style='padding-left:20px;'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$id</td>
        			<td>\$name</td>    
					<td>\$image</td>
				    <td>\$status</td>
					<td>\$str_manage</td>
				</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display();
    }

    /**
     * 获取菜单深度
     *
     * @param $id
     * @param $array
     * @param $i
     *
     * @return int
     */
    protected function _get_level($id, $array = array(), $i = 0) {
        if ($array[$id]['parentid'] == 0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid'] == $id) {
            return $i;
        } else {
            $i++;

            return $this->_get_level($array[$id]['parentid'], $array, $i);
        }
    }

    /**
     * 游戏类型列表
     */
    public function gtList() {
        $field = "id, name, status, image";
        $items = $this->gt_model->field($field)->select();
        $this->assign('gtypes', $items);
    }

    /**
     * 游戏类型所有状态
     *
     * @date     : 2016年4月8日下午6:07:08
     *
     * @param 参数选项|null $option
     *
     * @return NULL
     * @internal param 参数选项 $option
     *
     * @since    1.0
     */
    public function gtStatus($option = null) {
        $gtstatus = array(
            '0' => "全部状态",
            '2' => "显示",
            '1' => "隐藏"
        );
        if (1 == $option) {
            $gtstatus = array(
                '2' => "显示",
                '1' => "隐藏"
            );
        }
        $this->assign("gtstatus", $gtstatus);

        return;
    }

    /**
     * 添加游戏类型
     */
    public function add() {
        $this->addtree();
//        $this->gtStatus(1);
//        $this->display();
    }

    public function addtree() {
        import("Tree");
        $tree = new \Tree();
        $parentid = I("get.parentid/d", 0);
        $result = M('game_type')->order(array("listorder" => "DESC"))->select();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }

    /**
     * 编辑游戏类型
     */
    public function edit() {
        $this->edittree();
        exit;
        $type_id = I('get.id/d', 0);
        if ($type_id > 0) {
            $_map['id'] = $type_id;
            $typedata = $this->gt_model->where($_map)->find();
            $this->assign($typedata);
            $this->display();
        } else {
            $this->error("参数错误");
        }
    }

    public function edittree() {
        import("Tree");
        $tree = new \Tree();
        $id = intval(I("get.id"));
        $rs = $this->gt_model->where(array("id" => $id))->find();
        $result = $this->gt_model->order(array("listorder" => "DESC"))->select();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign($rs);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }

    //排序
    public function listorders() {
        $status = parent::_listorders($this->gt_model);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    /**
     * 渠道添加游戏
     */
    public function add_post() {
        if (IS_POST) {
            /* 获取POST数据 */
            $gt_data['name'] = trim(I('post.gt_name'));
            $gt_data['parentid'] = trim(I('post.parentid/d', 0));
            $gt_data['status'] = I('post.gt_status');
            $gt_data['image'] = I('post.gt_image');
            /* 检测输入参数合法性, 游戏名 */
            if (empty($gt_data['name'])) {
                $this->error("游戏类型为空，请填写游戏类型");
                exit();
            }
            $_check_map['name'] = $gt_data['name'];
            $_cnt = $this->gt_model->where($_check_map)->count();
            if ($_cnt > 0) {
                $this->error("游戏类型名称已存在,请重新输入");
                exit();
            }
            $lastInsId = $this->gt_model->add($gt_data);
            if ($lastInsId) {
                $this->success("添加成功", U("Gametype/index"));
            } else {
                $this->error("添加失败");
            }
            exit();
        } else {
            $this->error("参数错误");
        }
    }

    public function edit_post() {
        if (IS_POST) {
            /* 获取POST数据 */
            $gt_data['id'] = I('post.gt_id', 0, intval);
            if ($gt_data['id'] > 0) {
                $gt_data['parentid'] = I('post.parentid/d', 0);
                $gt_data['name'] = trim(I('post.gt_name'));
                $gt_data['status'] = I('post.gt_status');
                $gt_data['image'] = I('post.gt_image');
                /* 检测输入参数合法性, 游戏名 */
                if (empty($gt_data['name'])) {
                    $this->error("游戏类型为空，请填写游戏类型");
                    exit();
                }
                $_check_map['name'] = $gt_data['name'];
                $_cnt = $this->gt_model->where($_check_map)->count();
                if ($_cnt > 0) {
                    $this->error("游戏类型名称已存在,请重新输入");
                    exit();
                }
                $rs = $this->gt_model->save($gt_data);
                if (false !== $rs) {
                    $this->success("修改成功", U("Gametype/index"));
                    exit();
                } else {
                    $this->error("修改失败".$this->gt_model->error());
                    exit();
                }
            }
        }
        $this->error("参数错误");
    }

    /**
     * 删除
     */
    function delete() {
        $id = I("get.id", 0, "intval");
        $count = $this->gt_model->where(array("parentid" => $id))->count();
        if ($count > 0) {
            $this->error("该游戏类型下还有子类型，无法删除！");
        }
        if ($this->gt_model->delete($id) !== false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}

?>