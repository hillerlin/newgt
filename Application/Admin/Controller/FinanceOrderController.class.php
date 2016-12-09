<?php

namespace Admin\Controller;


class FinanceOrderController extends CommonController {

    public function __construct() {
        $this->mainModel = D('FinanceOrder');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $fp_id = I('get.fp_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $company_name = I('post.company_name');
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FinanceOrder');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($company_name)) {
                $map['m.company_name'] = $company_name;
            }
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.add_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($fp_id)) {
            $map['t.fp_id'] = $fp_id;
            $this->assign('fp_id', $fp_id);
        }
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($result);exit;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
       
    }

    public function add() {
        $this->display();
    }

    public function edit() {
        $oid = I('get.oid');
        if (empty($oid)) {
            $this->json_error('id不能为空');
        }
        $model = D('FinanceOrder');
        $map['oid'] = $oid;
        $result = $model->getList(1, 1 ,$map);
//        $data = D('FinanceOrder')->where('oid='.$oid)->find();
//        $project = D('Project')->where('pro_id=' . $data['pro_id'])->relation(true)->find();
        $this->assign($result['list'][0]);
//        $this->assign('project', $project);
        $this->display();
    }

    public function del() {
        $oid = I('get.oid');
        $model = D('FinanceOrder');
        $state = $model->delete($oid);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    public function save() {
        if (false === $data = $this->mainModel->create()) {
            $e = $this->mainModel->getError();
            $this->json_error($e);
        }
        if ($data['oid']) {
            $this->mainModel->setConfirmTime();
            $result = $this->mainModel->save();
        } else {
            $result = $this->mainModel->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('dialogid'=>'finance-order-list'));
        }
    }
    
    public function confirm() {
        $oid = I('get.oid');
        if (empty($oid)) {
            $this->json_error('id不能为空');
        }
        $map['oid'] = $oid;
        $order_info = $this->mainModel->where($map)->find();
        if ($order_info['status'] == 1) {
            $this->json_error('此订单已被确认过，请勿重复确认');
        }
        if (!$this->mainModel->where($map)->save(array('status' => 1, 'confirm_time' => $_SERVER['REQUEST_TIME']))) {
            $this->json_error('确认失败');
        }
        $this->json_success('确认成功');
    }
    
    public function service() {
        $oid = I('get.oid');
        if (empty($oid)) {
            $this->json_error('id不能为空');
        }
        $model = D('FinanceOrder');
        $map['oid'] = $oid;
        $result = $model->getList(1, 1 ,$map);
        if ($result['list'][0]['service_rate_status'] == 1) {
            $list = D('ServiceCharge')->where($map)->select();
            $this->assign('list', $list);
        }
        $this->assign($result['list'][0]);
        $this->display();
    }
    
    //上传附件
    public function upload_attachment() {
//        $mpay_id = I('request.mpay_id');
        $field = I('post.field');
        $upload_info = upload_file('/expenditure/attachment/', $field);
        if (isset($upload_info['file_path'])) {
            $content = array('file_path' => $upload_info['file_path'],'file_id' => date('YmdHis'), 'file_name'=>$upload_info['name']);
            $this->ajaxReturn(array('statusCode' => 200, 'content'=>$content, 'message'=>'上传成功'));
        }
        $this->json_error('上传失败');
    }
    
    public function save_service() {
        $oid = I('post.oid');
        $model = D('ServiceCharge');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $map['oid'] = $oid;
        $result = $this->mainModel->getList(1, 1 ,$map);
        $order_info = $result['list'][0];
        if ($order_info['service_rate_status'] == 1) {
            $this->json_error('服务费已打款');
        }
        $model->startTrans();
        if (!$this->mainModel->where($map)->save(array('service_rate_status' => 1))) {
            $model->rollback();
            $this->json_error('内部错误');
        }
        $model->pay_time = strtotime($data['pay_time']);
        if (!$model->add()) {
            $model->rollback();
            $this->json_error('内部错误');
        }
        $model->commit();
        $this->json_success('保存成功');
    }
    
    public function deposit() {
        $oid = I('get.oid');
        if (empty($oid)) {
            $this->json_error('id不能为空');
        }
        $model = D('FinanceOrder');
        $map['oid'] = $oid;
        $result = $model->getList(1, 1 ,$map);
        $list = D('DepositCharge')->where($map)->select();
        $this->assign('list', $list);
        $this->assign($result['list'][0]);
        $this->display();
    }
    
    public function depositAdd() {
        $oid = I('get.oid');
        if (empty($oid)) {
            $this->json_error('id不能为空');
        }
        $model = D('FinanceOrder');
        $map['oid'] = $oid;
        $result = $model->getList(1, 1 ,$map);
        $this->assign($result['list'][0]);
        $this->display('deposit_add');
    }
    
    public function save_deposit() {
        $oid = I('post.oid');
        $model = D('DepositCharge');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $map['oid'] = $oid;
        $result = $this->mainModel->getList(1, 1 ,$map);
        $order_info = $result['list'][0];
        if ($data['type'] == 'expend') {
            if ($order_info['cash_deposit_status'] == 1) {
                $this->json_error('保证金已打款');
            }
            $save_data['cash_deposit_status'] = 1;
        }
        if ($data['type'] == 'income') {
            if ($order_info['cash_deposit_status'] == 0) {
                $this->json_error('未支付保证金，不可收入保证金');
            }
            if ($order_info['cash_deposit_status'] == 2) {
                $this->json_error('保证金已收款');
            }
            $save_data['cash_deposit_status'] = 2;
        }
        
        $model->startTrans();
        if (!$this->mainModel->where($map)->save($save_data)) {
            $model->rollback();
            $this->json_error('内部错误');
        }
        $model->pay_time = strtotime($data['pay_time']);
        if (!$model->add()) {
            $model->rollback();
            $this->json_error('内部错误');
        }
        $model->commit();
        $this->json_success('保存成功', '', '', true, array('dialogid' => 'project-order-service'));
    }
    
    //机构兑付汇总表
    public function gatherChart() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $fp_id = I('get.fp_id');
        $isSearch = I('post.isSearch');
        $company_name = I('post.company_name');
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FinanceOrder');
        if ($isSearch) {
            
            if (!empty($company_name)) {
                $map['m.company_name'] = $company_name;
            }
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.add_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($fp_id)) {
            $map['t.fp_id'] = $fp_id;
            $this->assign('fp_id', $fp_id);
        }
        $map['t.status'] = 1;
        $result = $model->getChartList($page, $pageSize ,$map);
//        $list = $model->firstPay();
//        var_dump($result);exit;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display('gather_chart');
    }
}
