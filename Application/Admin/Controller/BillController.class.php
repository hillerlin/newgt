<?php

namespace Admin\Controller;

class BillController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $type = I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ProjectBill');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.addtime'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.addtime'][] = array('ELT', $end_time);
            }
        }
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $result = $model->getList($page, $pageSize, $map);
        $status_describe = $model->getStatusDescribe();

        $this->assign('status_describe', $status_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }

    public function add() {
        $type_describe = D('CapitalFlow')->getTypeDescribe();
        $banks = D('Bank')->select();
        $this->assign('banks', $banks);
        $this->assign('type_describe', $type_describe);
        $this->display();
    }
    
    public function edit() {
        $bill_id = I('get.bill_id');
        $model = D('ProjectBill');
        $map['bill_id'] = $bill_id;
        
        $result = $model->getList(1, 1, $map);
//        var_dump($result);exit;
        $status_describe = $model->getStatusDescribe();

        $this->assign('status_describe', $status_describe);
        $this->assign($result['list'][0]);
        $this->display();
    }

    public function save() {
        $model = D('ProjectBill');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $admin = session('admin');
        $model->admin_id = $admin['admin_id'];
        if ($data['bill_id']) {
            $result = $model->save();
        } else {
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('tabid' => 'bill-index'));
        }
    }
    
    public function financeManage() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $type = I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ProjectBill');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.addtime'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.addtime'][] = array('ELT', $end_time);
            }
        }
        
        $result = $model->getList($page, $pageSize, $map);
        $status_describe = $model->getStatusDescribe();

        $this->assign('status_describe', $status_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display('finance_manage');
    }
    
    public function bill() {
        $pro_id = I('post.pro_id');
        $model = D('ProjectBill');
        $map['t.pro_id'] = $pro_id;
        $result = $model->getList(1, 30, $map);
        $status_describe = $model->getStatusDescribe();

        $this->assign('status_describe', $status_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => 1, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }

}
