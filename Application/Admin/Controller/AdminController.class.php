<?php

namespace Admin\Controller;

class AdminController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    /* 管理员列表 */

    public function index() {
        $dp_id = I('get.dp_id');
        if (IS_POST) {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
            $model = D('Admin');
            if (!empty($dp_id)) {
                $map['t.dp_id'] = $dp_id;
            }

            $result = $model->getLists($page, $pageSize, $map,'t.add_time ASC',$dp_id);
            $list = $result['list'];
            $total = $result['total'];
            foreach ($list as & $val) {
                $val['last_login_time'] = date('Y-m-d H:s:i', $val['last_login_time']);
            }
            $this->ajaxReturn(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        }
        $this->assign('dp_id', $dp_id);
        $this->display();
    }
    
    public function listByDepartment() {

        $model = D('Department');
        $list = $model->select();
        foreach ($list as $v) {
            $array[$v['dept_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $list = $tree->get_array(0);
        $this->assign('list', $list);
        $this->display('list_by_department');
    }

    /* 添加管理员 */

    public function add() {
        $role_model = D('Role');
        $group = D('Role')->order('sort')->select();
        $department = D('Department')->select();
        
        $this->assign('department', $department);
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
        $department = D('Department')->select();
        
        $this->assign('department', $department);
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
//        if (!empty($admin_role)) {
//            $model->admin_role = $admin_role;//implode(',', $admin_role);
//        }
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
        $admin_id = I('get.admin_id');
        $model = D('Admin');
        $state = $model->delete($admin_id);
        if ($state !== false) {
            $this->json_success('删除成功', U('admin/index'));
        } else {
            $this->json_error('操作失败');
        }
    }

    public function group() {
        $model = D('Role');
        $list = $model->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function add_group() {
        $menu_model = D('Menu');
        $menu_list = $menu_model->where(array('type' => 1))->select();
        foreach ($menu_list as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $menu = $tree->get_array(0);
        $subject = '权限管理-编辑权限组';
        $ftitle = '权限操作设置详情';
        $this->assign('menu', $menu);
        $this->assign('subject', $subject);
        $this->assign('ftitle', $ftitle);
        $this->assign('back', 1);
        $this->display();
    }

    public function edit_group() {
        $role_id = I('role_id');
        $model = D('Role');
        $menu_model = D('Menu');
        if (!$role_id || !$model->check_exist(array('role_id' => $role_id))) {
            $this->error('参数错误！');
        }
        $data = $model->where(array('role_id' => $role_id))->find();

        $menu_list = $menu_model->where(array('type' => 1))->select();
        foreach ($menu_list as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $menu = $tree->get_array(0);

        $subject = '权限管理-编辑权限组';
        $ftitle = '权限操作设置详情';

        $this->assign('menu', $menu);
        $this->assign($data);
        $this->assign('subject', $subject);
        $this->assign('ftitle', $ftitle);
        $this->assign('back', 1);
        $this->display();
    }

    /* 检测管理员是否存在 */

    public function check_admin_exist() {
        $admin_name = I('admin_name');
        $admin_id = I('admin_id');
        $where['admin_name'] = I('admin_name');
        if (I('admin_id')) {
            $where['admin_id'] = array('neq', I('admin_id'));
        }
        $state = D('Admin')->check_exist($where) ? false : true;
        if (IS_AJAX) {
            $this->ajaxReturn($state);
        } else {
            return $state;
        }
    }

    /* 检测权限组是否存在 */

    public function check_role_exist() {
        $role_name = I('role_name');
        $role_id = I('role_id');
        $where['role_name'] = $role_name;
        if ($role_id) {
            $where['role_id'] = array('neq', I('role_id'));
        }
        $state = D('Role')->check_exist($where) ? false : true;
        if (IS_AJAX) {
            $this->ajaxReturn($state);
        } else {
            return $state;
        }
    }

    /* 删除权限组 */

    public function del_role() {
        $model = D('Role');
        $role_id = I('role_id');
        if (!$role_id)
            $this->error('参数错误');
        $status = $model->relation(true)->delete($role_id);
        if ($status !== false) {
            $this->json_success('删除成功', U('admin/group'));
        } else {
            $this->json_error('操作失败');
        }
    }

    /* 保存权限组 */

    public function save_role() {
        $model = D('Role');
        $auth = I('auth');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->error($e);
        }

        if ($auth) {
            foreach ($auth as $v) {
                $auth_d[] = array('menu_id' => $v);
            }
            $model->auth = $auth_d;
        }

        if ($data['role_id']) {
            $result = $model->relation('auth')->save();
        } else {
            $result = $model->relation('auth')->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', U('admin/group'));
        }
    }
    
    public function bindUserList() {
        $admin_id = I('get.admin_id');
        if (IS_POST) {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
            $user = D('User');
            $total = $user->where('status=2')->count();
            $list = $user->where('status=2')->relation('dept')->page($page, $pageSize)->select();
            $this->ajaxReturn(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        }
        $this->assign('admin_id', $admin_id);
        $this->display('bind_user_list');
    }
    
    public function bindUser() {
        $admin_id = I('get.admin_id');
        $uid = I('get.uid');
        if (!D('User')->checkUserExist($uid)) {
            $this->json_error('此用户不存在');
        }
        $admin = D('Admin');
        $map['admin_id'] = $admin_id;
        if ($admin->where($map)->getField('uid')) {
            $this->json_error('此管理员已经关联用户');
        }
        $save_data = array('uid'=>$uid);
        if ($admin->where('admin_id='.$admin_id)->save($save_data)) {
            $this->json_success('绑定成功');  
        }
        $this->json_error('绑定失败'.$admin->getDbError());
    }
    
    //查找项目跟进人
    public function projectFollow() {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($isSearch)) {
                $map['real_name'] = $real_name;
            }
        }
        $map['status'] = 1;
        $map['role_id'] = 2;
        $model = D('Admin');
        $list = $model->where($map)->select();
        $total = $model->where($map)->count();
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('post', $post);
        $this->display('project_follow');
    }
    
    //查找风控跟进人
    public function projectRiskFollow() {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($isSearch)) {
                $map['real_name'] = $real_name;
            }
        }
        $map['t.status'] = 1;
//        $map['dp_id'] = array(array('eq',2),array('eq',4), 'or');
        $map['dp_id|dp_id|admin_id'] = array(2, 4, 33, '_multi'=>true);
        $model = D('Admin');
//        $list = $model->where($map)->select();
//        $total = $model->where($map)->count();
        $result = $model->getLists(1, 30, $map);
        $this->assign('total', $result['total']);
        $this->assign('list', $result['list']);
        $this->assign('post', $post);
        $this->display('project_risk_follow');
    }
    
    public function lookUp() {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($isSearch)) {
                $map['real_name'] = $real_name;
            }
        }
        $map['t.status'] = 1;
        $model = D('Admin');
        
        $total = $model->count();
        $list = $model->select();
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->display('lookup');
    }

}
