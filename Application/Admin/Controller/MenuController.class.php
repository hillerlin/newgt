<?php

namespace Admin\Controller;


class MenuController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $model = D('Menu');
        $pid = I('pid') ? I('pid') : 0;
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $list = $model->page($page, $pageSize)->select();
        $total = $model->count();
        $subject = $pid ? '后台菜单-"' . I('pname') . '"的下级菜单' : '后台菜单';
        $ftitle = $pid ? '"' . I('pname') . '"的下级菜单' : '后台菜单';
        $this->assign('subject', $subject);
        $this->assign('ftitle', $ftitle);
        $this->assign('list', $list);
        $this->assign('back', $pid);
        $this->assign('total', $total);
        $this->display();
    }

    public function add() {
        $model = D('Menu');
        $menu_list = $model->where(array('type' => 1))->select();
        foreach ($menu_list as $v) {

            $array[$v['menu_id']] = $v;
        }
        $str = "<option value='\$menu_id' \$selected>\$spacer \$menu_name</option>";
        $tree = new \Admin\Lib\Tree;
        $tree->init($array, 'menu_id', 'pid');
        $select_menu = $tree->get_tree(0, $str);
        $this->assign('select_menu', $select_menu);
        $this->display();
    }

    public function edit() {
        $model = D('Menu');
        $menu_id = I('menu_id');
        $data = $model->find(I('menu_id'));
        $menu_list = $model->where(array('type' => 1))->select();
        foreach ($menu_list as $v) {
            $v['selected'] = $v['menu_id'] == $data['pid'] ? 'selected' : '';
            $array[$v['menu_id']] = $v;
        }
        $str = "<option value='\$menu_id' \$selected>\$spacer \$menu_name</option>";
        $tree = new \Admin\Lib\Tree;
        $tree->init($array, 'menu_id', 'pid');
        $select_menu = $tree->get_tree(0, $str, $menu_id);
        $this->assign($data);
        $this->assign('select_menu', $select_menu);
        $this->display();
    }

    public function del() {
        $del_ids = explode(',', I('get.menu_id'));
        $model = D('Menu');
        $menu_list = $model->where(array('type' => 1))->select();
        $tree = new \Admin\Lib\Tree;
        foreach ($menu_list as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree->init($array, 'menu_id', 'pid');
        foreach ($del_ids as $id) {
            $del_menu = $tree->get_all_child($id);
        }
        foreach ($del_menu as $v) {
            $del_ids[] = $v['menu_id'];
        }

        $result = $model->where(array('menu_id' => array('in', $del_ids)))->delete();
        if ($result === false) {
            $this->json_error('删除失败');
        } else {
            $this->json_success('删除成功', U('menu/index'));
        }
    }

    /* 保存菜单 */

    public function save_menu() {
        $model = D('Menu');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->error($e);
        }
        $model->module_name = $data['module_name'];
        $model->action_name = $data['action_name'];
        if ($data[$model->getPk()]) {
            $result = $model->save();
        } else {
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', U('menu/index'));
        }
    }

    /* ajax保存菜单 */

    public function ajax_save_menu() {
        $model = D('Menu');
        $data[I('branch')] = I('value');
        $data[$model->getPk()] = I('id');
        if (false === $data = $model->create($data)) {
            $e = $model->getError();
            $this->error($e);
        }
        $result = $model->save();
        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }

}
