<?php

namespace Admin\Controller;

class BillCompanyController extends CommonController {

    public function __construct() {
        $this->mainModel = D('BillCompany');
        parent::__construct();
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
        if (empty($this->mainModel->admin_id)) {
            $admin = session('admin');
            $this->mainModel->admin_id = $admin['admin_id'];
        }
        if ($data['company_id']) {
            $this->mainModel->add_time = $_SERVER['REQUEST_TIME'];
            $result = $this->mainModel->save();
        } else {
            $result = $this->mainModel->add();
        }
       

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('dialogid' => 'billcompany-lookup'));
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
        $isSearch = I('post.isSearch');
        $type = I('get.type');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
        }
        
//        $map['status'] = 1;
//        $map['type'] = 0;   //0客户，1供应链
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('add_time desc')->page($page, $pageSize)->select();

        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->assign('type', $type);
        $this->display();
    }
}
