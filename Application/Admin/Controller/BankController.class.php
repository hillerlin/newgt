<?php

namespace Admin\Controller;

class BankController extends CommonController {

    public function __construct() {
        $this->mainModel = D('Bank');
        parent::__construct();
    }

    /* 客户列表 */
    public function index() {
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
        $map['type'] = 0;
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();
        
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }

    /* 添加银行卡 */
    public function add() {
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $bank_id = I('get.bank_id');
        $data = $this->mainModel->where(array('bank_id' => $bank_id))->find();
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
        if ($data['bank_id']) {
            $result = $this->mainModel->save();
        } else {
            $this->mainModel->addtime = $_SERVER['REQUEST_TIME'];
            $result = $this->mainModel->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('dialogid' => 'bank-lookup'));
        }
    }

    /* 删除管理员 */

    public function del() {
        $bank_id = I('get.bank_id');
        $state = $this->mainModel->where('bank_id=' . $bank_id)->save(array('status' => 0));
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    //公司查找
    public function lookup() {
        $type = I('get.type');
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
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();
        
        $this->assign('type', $type);
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
//        $map['status'] = 1;
//        $map['type'] = 1;   //0客户，1供应链
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
}
