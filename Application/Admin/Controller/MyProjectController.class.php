<?php

namespace Admin\Controller;
use Think\Controller;
use Admin\Lib\CalcTool;

class MyProjectController extends Controller {
    
    
    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $admin = session('admin');
//        $map['pro_step'] = array('GT', 4);
        $map['pro_linker'] = $admin['admin_id'];
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('index');
    }
    
    //合同信息显示
    public function contract() {
        $pro_id = I('get.pro_id');

        $admin = session('admin');
        $model = D('ProjectContract');
        $map['proc.pro_id'] = $pro_id;
        $map['pro_linker'] = $admin['admin_id'];
        $result = $model->projectContract(1, 30, $map);
//        var_dump($model->_sql());exit;
        $total = $result['total'];
        $list = $model->formatData($result['list']);
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display();
    }
    
    //放款日志
    public function loanLog() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $model = D('ProjectDebt');
        $map['t.pro_id'] = $pro_id;
        $admin = session('admin');
        $map['p.pro_linker'] = $admin['admin_id'];
        $result = $model->getList($page, $pageSize, $map);
//        var_dump($status);eixt;
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display('loan_log');
    }
    
    //贷后日志
    public function afterLoanLog() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');

        $model = D('AfterLoanLog');
        $map['t.pro_id'] = $pro_id;
        $this->assign('pro_id', $pro_id);
        $admin = session('admin');
        $map['p.pro_linker'] = $admin['admin_id'];
        $result = $model->getList($page, $pageSize, $map);
//        $total = $model->where($map)->count();
//        $list = $model->where($map)->page($page, $pageSize)->select();

        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('after_loan_log');
    }
    
    //
    public function detail() {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $map['t.context_id'] = $pro_id;
        $map['t.context_type'] = 'pro_id';
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $map1['lf.pro_id'] = $pro_id;
        $loan_log = D('ProcessLog')->getLoanList(1, 30, $map1);
        $process_list = array_merge($loan_log['list'], $process_list['list']);
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $exts = getFormerExts();
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list);
        $this->assign('review_file_autho', C('REVIEW_FILE_AUTHO'));
        $this->assign('signin_admin', $admin);
        $this->assign($data);
        $this->display();
    }
    
    //债权列表
    public function debt() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        
        $debt_all_id = I('get.debt_all_id');
        $model = D('ProjectDebtDetail');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        $admin = session('admin');
        $map['p.pro_linker'] = $admin['admin_id'];
        $map['t.debt_all_id'] = $debt_all_id;
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($status);eixt;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('debt_all_id', $debt_all_id);
        $this->assign('post', $_POST);
        $this->display();
    }
    
    public function schedule() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $debt_all_id = I('get.debt_all_id');
        
        $model = D('ProRepaymentSchedule');
        if (!empty($debt_all_id)) {
            $map['t.debt_all_id'] = $debt_all_id;
            $this->assign('debt_all_id', $debt_all_id);
        }
        $admin = session('admin');
        $map['p.pro_linker'] = $admin['admin_id'];
        $result = $model->getList($page, $pageSize, $map);
        $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
//        var_dump($debt_info);exit;
        $cash_deposit_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'cash_deposit');
        $cash_deposit_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'cash_deposit');
        $back_cash_deposit_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'back_cash_deposit');
        $counseling_fee_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'counseling_fee');
        $counseling_fee_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'counseling_fee');
        $handling_charge_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'handling_charge');
        $handling_charge_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'handling_charge');
        $interest_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'interest');
        $interest_need_repay = CalcTool::calc($debt_info['debt_account'], $debt_info['repurchase_rate'], $debt_info['term']);
        $cash_deposit_need_repay = CalcTool::calc($debt_info['debt_account'], $debt_info['cash_deposit'], 12);
        $counseling_fee_need_repay = CalcTool::calc($debt_info['debt_account'], $debt_info['counseling_fee'], $debt_info['term']);
        $handling_charge_need_repay = CalcTool::calc($debt_info['debt_account'], $debt_info['handling_charge'], $debt_info['term']);
        $pro_fee = D('ProjectFee')->where("debt_all_id=$debt_all_id")->select();
        $pro_fee = array_switch_key($pro_fee, 'type');
//        $interest['interest'] = $debt_info[0]['rate'];
//        $interest['interest_has_repay'] = $a;
        
        $this->assign('pro_fee', $pro_fee);
        $this->assign('cash_deposit_last_repay', $cash_deposit_last_repay);
        $this->assign('back_cash_deposit_last_repay', $back_cash_deposit_last_repay);
        $this->assign('counseling_fee_last_repay', $counseling_fee_last_repay);
        $this->assign('handling_charge_last_repay', $handling_charge_last_repay);
        $this->assign('cash_deposit_has_repay', $cash_deposit_has_repay);
        $this->assign('cash_deposit_need_repay', $cash_deposit_need_repay);
        $this->assign('counseling_fee_need_repay', $counseling_fee_need_repay);
        $this->assign('handling_charge_need_repay', $handling_charge_need_repay);
        $this->assign('cash_deposit_has_repay', $cash_deposit_has_repay);
        $this->assign('counseling_fee_has_repay', $counseling_fee_has_repay);
        $this->assign('handling_charge_has_repay', $handling_charge_has_repay);
        $this->assign('interest_has_repay', $interest_has_repay);
        $this->assign('interest_need_repay', $interest_need_repay);
        $this->assign('debt_info', $debt_info);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
}


