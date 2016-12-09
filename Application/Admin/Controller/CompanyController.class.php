<?php

namespace Admin\Controller;

class CompanyController extends CommonController {

    public function __construct() {
        $this->mainModel = D('Company');
        parent::__construct();
    }

    /* 客户列表 */
    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        $this->mainModel = D('Company');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', '%'.$company_name.'%');
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', '%'.$company_linker.'%');
            }
        }
        $map['status'] = 1;
        $map['type'] = 0;
        $admin = session('admin');
        if ($admin['role_id'] == 16) {
            $map['admin_id'] = $admin['admin_id'];
        }
        $total = $this->mainModel->where($map)->relation('admin')->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->relation('admin')->page($page, $pageSize)->select();
        
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }

    /* 添加管理员 */
    public function add() {
        $industries = C('industries');
        $type = I('get.type', 0);
        $this->assign('type', $type);
        $this->assign('industries', $industries);
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $company_id = I('get.company_id');
        $data = $this->mainModel->where(array('company_id' => $company_id))->find();
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign($data);
        $this->display();
    }
    
    /* 保存 */
    public function save() {
        if (false === $data = $this->mainModel->create()) {
            $e = $this->mainModel->getError();
            $this->json_error($e);
        }
//        var_dump($model->role_id);exit;
        if ($data['company_id']) {
            $this->mainModel->addtime = $_SERVER['REQUEST_TIME'];
            $result = $this->mainModel->save();
        } else {
            if (empty($this->mainModel->admin_id)) {
            $admin = session('admin');
            $this->mainModel->admin_id = $admin['admin_id'];
        }
            $result = $this->mainModel->add();
        }
        if ($data['type']) {
            $refresh_type = 'dialogid';
            $refresh_id = 'supplier-add';
        } else {
            $refresh_type = 'tabid';
            $refresh_id = 'company-index';
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array($refresh_type => $refresh_id));
        }
    }

    /* 删除管理员 */

    public function del() {
        $company_id = I('get.company_id');
        $state = $this->mainModel->where('company_id=' . $company_id)->save(array('status' => 0));
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    //公司查找
    public function lookup() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', "%$company_name%");
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $admin = session('admin');
        if ($admin['role_id'] == 16) {
            $map['admin_id'] = $admin['admin_id'];
        }
        $map['status'] = 1;
        $map['type'] = 0;   //0客户，1供应链
        $total = $this->mainModel->where($map)->relation('admin')->count();
        $list = $this->mainModel->where($map)->relation('admin')->order('addtime desc')->page($page, $pageSize)->select();

        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }
    
    //供应商查找
    public function lookupSupplier() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $map['status'] = 1;
        $map['type'] = 1;   //0客户，1供应链
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();

        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display('lookup_supplier');
    }
    
    /* 供应商 */
    public function supplier() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        $this->mainModel = D('Company');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $map['status'] = 1;
        $map['type'] = 1;
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();
        
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }
    
    //查找指定项目的供应商
    public function lookupByProId() {
        $pro_id = I('get.pro_id');
        
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        if (D('Project')->isReverseFactoring($pro_id)) {
            $list = $this->mainModel->getProSupplier($pro_id);
        } else {
            $list = $this->mainModel->getProCompany($pro_id);
        }
        $total = count($list);
        $this->assign(array('total'=>$total, 'pageCurrent'=>1, 'list'=>$list));
        $this->display('lookup_prosupplier');
    }
    
    public function distribute() {
        $company_id = I('get.company_id');
        $company_info = D('Company')->where('company_id=' . $company_id)->relation('admin')->find();
        $this->assign($company_info);
        $this->display();
    }
    
    public function findProjectManager() {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($isSearch)) {
                $map['real_name'] = $real_name;
            }
        }
        $map['t.status'] = 1;
//        $map['dp_id'] = array(array('eq',2),array('eq',4), 'or');
        $map['r.role_id'] = 16;
        $model = D('Admin');
//        $list = $model->where($map)->select();
//        $total = $model->where($map)->count();
        $result = $model->getLists(1, 30, $map);
        $this->assign('total', $result['total']);
        $this->assign('list', $result['list']);
        $this->assign('post', $post);
        $this->display('lookup_pm');
    }
}
