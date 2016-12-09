<?php

namespace Admin\Controller;

class CapitalFlowController extends CommonController {

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
        $model = D('CapitalFlow');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
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
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $result = $model->getList($page, $pageSize, $map);
        $type_describe = $model->getTypeDescribe();
        
        $this->assign('type_describe', $type_describe);
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

    public function save() {
        $model = D('CapitalFlow');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $fid = (int)I('post.fid');
        if (!empty($fid)) {
            $finance_info = D('FinanceFlow')->findByPk($fid);   //获取财务流水信息
            $finance_surplu_money = $finance_info['money'] - $finance_info['has_distribute'];
            if ($finance_surplu_money < $data['money']) {
                $this->json_error('本条财务流水剩余金额为'.$finance_info['has_distribute'].',请输入正确的金额');
            }
        }
        $model->pay_time = strtotime($data['pay_time']);
        $model->startTrans();
        if ($data['id']) {
            $result = $model->save();
        } else {
            $debt_info = D('ProjectDebt')->getDebtAllInfo($data['debt_all_id']);
            $model->company_id = $debt_info['company_id'];
            $model->fid = $fid;
            $result = $model->add();
        }
        
        //更新财务流水表，已经分配的金额
        if (!empty($fid)) {
            $has_distribute = bcadd($finance_info['has_distribute'], $data['money'], 2);
            if (D('FinanceFlow')->where('fid='.$fid)->save(array('has_distribute' => $has_distribute)) === false) {
                $model->rollback();
                $this->json_error('还款失败。错误原因：内部错误003');
            }
        }
        if ($result === false) {
            $model->rollback();
            $this->json_error('保存失败');
        } else {
            $model->commit();
            $this->json_success('保存成功');
        }
    }

    public function specified() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $type = I('post.type');
        $debt_all_id = I('get.debt_all_id');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('CapitalFlow');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
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
        if ($debt_all_id !== '') {
            $map['t.debt_all_id'] = $debt_all_id;
        }
        $result = $model->getList($page, $pageSize, $map);
        $sum_money_in = 0;
        $sum_money_out = 0;
        foreach($result['list'] as & $v) {
            $v['is_income'] = $model->isIncome($v['type']);
            if ($v['is_income']) {
                $v['money_in'] = $v['money'];
                $v['money_out'] = 0;
            } else {
                $v['money_in'] = 0;
                $v['money_out'] = $v['money'];
            }
            $sum_money_in += $v['money_in'];
            $sum_money_out += $v['money_out'];
        }
        $type_describe = $model->getTypeDescribe();
        
        $this->assign(array('sum_money_in' => $sum_money_in, 'sum_money_out' => $sum_money_out));
        $this->assign('type_describe', $type_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->assign('debt_all_id', $debt_all_id);
        $this->display();
    }

    public function specifiedAdd() {
        $debt_all_id = I('get.debt_all_id');
        $debt_info = D('ProjectDebt')->findByPk($debt_all_id);
        $type_describe = D('CapitalFlow')->getTypeDescribe();
        $banks = D('Bank')->select();
        $this->assign('banks', $banks);
        $this->assign('type_describe', $type_describe);
        $this->assign('debt_info', $debt_info);
        $this->display('specified_add');
    }
    
    //流水编辑
    public function specifiedEdit() {
        $debt_all_id = I('get.debt_all_id');
        $debt_info = D('ProjectDebt')->findByPk($debt_all_id);
        $type_describe = D('CapitalFlow')->getTypeDescribe();
        $banks = D('Bank')->select();
        $this->assign('banks', $banks);
        $this->assign('type_describe', $type_describe);
        $this->assign('debt_info', $debt_info);
        $this->display('specified_add');
    }
    
    public function withBill() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $type = I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('CapitalFlow');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
        }
        
        $is_supper = isSupper();
        $is_boss = isBoss();
        if (!$is_supper && !$is_boss) {
            $admin = session('admin');
            $map['p.admin_id'] = $admin['admin_id'];
        }
        $result = $model->getNoBillList($page, $pageSize, $map);
        $type_describe = $model->getTypeDescribe();

        $this->assign('type_describe', $type_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
//        $this->assign('debt_all_id', $debt_all_id);
        $this->display('with_bill');
    }

}
