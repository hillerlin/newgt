<?php

namespace Admin\Controller;

class UserController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    /* 管理员列表 */

    public function index() {
        if (IS_POST) {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
            $model = D('Admin');
//            $total = $model->count();
//            $list = $model->order('add_time desc')->relation('role')->page($page, $pageSize)->select();
            $result = $model->getList($page, $pageSize);
            $list = $result['list'];
            $total = $result['total'];
            foreach ($list as & $val) {
                $val['last_login_time'] = date('Y-m-d H:s:i', $val['last_login_time']);
            }
            $this->ajaxReturn(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        }
        $this->display();
    }

    /* 添加管理员 */

    public function add() {
        $role_model = D('Role');
        $group = D('Role')->order('sort')->select();
        $this->assign('group', $group);
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $model = D('Admin');
        $role_model = D('Role');
        $admin_id = I('get.admin_id');
        $data = $model->where(array('admin_id' => $admin_id))->find();
        $role_id = explode(',', $data['role_id']);
        $data['role_id'] = $role_id;
        $group = $role_model->order('sort')->select();
        $this->assign('group', $group);
        $this->assign($data);
        $this->display();
    }
    
    /* 编辑管理员 */
    public function editPaswd() {
        $model = D('Admin');
        $admin_id = I('get.admin_id');
        $data = $model->where(array('admin_id' => $admin_id))->find();
        $this->assign($data);
        $this->display('edit_paswd');
    }
    
    /* 保存管理员 */
    public function save_admin() {
        $model = D('Admin');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $admin_role = I('post.role_id');
        $model->role_id = implode(',', $admin_role);
//        var_dump($model->role_id);exit;
        if ($data['admin_id']) {
            $result = $model->save();
        } else {
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }

    /* 删除管理员 */

    public function del_admin() {
        $admin_id = I('admin_id');
        $model = D('Admin');
        $state = $model->delete($admin_id);
        if ($state !== false) {
            $this->json_success('删除成功', U('admin/index'));
        } else {
            $this->json_error('操作失败');
        }
    }
    
    public function followUpUser() {
        $pageSize = I('post.pageSize', 10);
        $page = I('post.pageCurrent', 1);
        $user = D('User');
        $total = $user->where('status=2')->count();
        $list = $user->where('status=2')->relation('dept')->page($page, $pageSize)->select();
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->display('follow-lookup-user');
    }
    
    public function lookUp() {
        if (IS_POST) {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
            $user_id = I('post.user_id');
            $user_name = I('post.user_name');
            $user_mobile = I('post.user_mobile');

            if (!empty($user_id)) {
                $map['uid'] = $user_id;
            }
            if (!empty($user_name)) {
                $map['user_name'] = $user_name;
            }
            if (!empty($user_mobile)) {
                $map['user_mobile'] = $user_mobile;
            }
            $map['stauts'] = 2;
            $user = D('user');
            $total = $user->where($map)->count();
            $list = $user->where($map)->relation('dept')->page($page, $pageSize)->select();
            $this->assign('total', $total);
            $this->assign('list', $list);
            $this->display('follow-lookup-user');
//            $this->ajaxReturn(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        }
    }

}
