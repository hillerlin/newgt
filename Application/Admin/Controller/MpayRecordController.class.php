<?php

namespace Admin\Controller;
use  \Admin\Model\MpayRecordModel;

class MpayRecordController extends CommonController {

    public function __construct() {
        $this->mainModel = D('MpayRecord');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $oid = I('get.oid');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('MpayRecord');
        
        $map['t.status'] = array('GT', 0);
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['add_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($oid)) {
            $map['oid'] = $oid;
            $this->assign('oid', $oid);
        }
        $result = $model->getList($page, $pageSize ,$map);

        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
       
    }
    
    public function add() {
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $model = D('MpayRecord');
        $pay_id = I('get.pay_id');
        $data = $model->where(array('pay_id' => $pay_id))->find();
        $this->assign($data);
        $this->display();
    }
    
    public function save() {
        $model = D('MpayRecord');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $model->pay_time = strtotime($data['pay_time']);
        if ($data['pay_id']) {
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
    
    //确认还款，生成还款列表
    public function confirm() {
        $pay_id = I('get.pay_id');
        if (empty($pay_id)) {
            $this->json_error('参数不完整');
        }
        $mpay_info = $this->mainModel->where('pay_id=' . $pay_id)->find();
        if ($mpay_info['status'] == MpayRecordModel::CONFIRM) {
            $this->json_error('本条打款记录已经生成还款记录');
        }
        $finance_order_rate = D('FinanceOrder')->where('oid=' . $mpay_info['oid'])->getField('rate');
        $this->mainModel->startTrans();
        if (!$this->mainModel->updateStatus('pay_id=' . $pay_id, MpayRecordModel::CONFIRM)) {
            $e = $this->mainModel->getError();
            $this->mainModel->rollback();
            $this->json_error($e);
        }
        //生成还款列表
        if (!D('RepaymentSchedule')->addRecords($pay_id, $mpay_info, $finance_order_rate)) {
            $this->mainModel->rollback();
            $this->json_error('还款列表生成失败');
        }
        $this->mainModel->commit();
        $this->json_success('成功');
    }
  
}
