<?php

namespace Admin\Controller;

class ProjectDebtController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $pro_title = I('post.pro_title');
        $debt_no = I('post.debt_no');
        $model = D('ProjectDebt');

        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($pro_title)) {
                $map['p.pro_title'] = array('like', "%$pro_title%");
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $result = $model->getList($page, $pageSize, $map);
//        var_dump($status);eixt;
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }

    public function specified() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $pro_id = I('get.pro_id');
        $model = D('ProjectDebt');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($pro_no)) {
                $map['p.pro_no'] = $pro_no;
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $map['t.pro_id'] = $pro_id;
        $result = $model->getList($page, $pageSize, $map);
//        var_dump($status);eixt;
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('pro_id', $pro_id);
        $this->assign('post', $_POST);
        $this->display();
    }

    public function add() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('项目id不能为空');
        }
        $this->assign('pro_id', $pro_id);
        $this->display();
    }

    public function edit() {
        
    }

    public function del() {
        
    }

    public function save_debt() {
        $model = D('ProjectDebt');
        $admin = session('admin');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if ($data['debt_id']) {
            $result = $model->save();
        } else {
            $model->admin_id = $admin['admin_id'];
            $model->start_time = strtotime($model->start_time);
            $model->end_time = strtotime($model->end_time);
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('tabid' => 'project-fangkuan'));
        }
    }

    public function exchange() {
        if (IS_POST) {
            $model = D('ProjectDebt');
            $admin = session('admin');
            if (false === $data = $model->create($request_data)) {
                $e = $model->getError();
                $this->json_error($e);
            }
            $model->startTrans();

            $model->admin_id = $admin['admin_id'];
            $model->start_time = strtotime($data['start_time']);
            $model->end_time = strtotime($data['end_time']);
            if (!$model->add()) {
                $model->rollback();
                $this->json_error('内部错误');
            }
            if (!$model->where('debt_id=' . $data['parent_id'])->save(array('status' => 0))) {
                $model->rollback();
                $this->json_error('内部错误');
            }
            $model->commit();
            $this->json_success('换质成功');
        }
        $pro_id = I('get.pro_id');
        $debt_id = I('get.debt_id');
        if (empty($pro_id)) {
            $this->json_error('项目id不能为空');
        }
        $this->assign('pro_id', $pro_id);
        $this->assign('debt_id', $debt_id);
        $this->display();
    }

    public function repayFrontendFee() {
        $type = I('get.type');
        $debt_all_id = I('get.debt_all_id');
        $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
        
        if (IS_POST) {
            $repay_money = I('post.repay_money');
            $real_repay_time = strtotime(I('post.real_repay_time'));
            $status = (int) I('post.status');
            $fid = I('post.fid', 0);
            
            $need_pay = $debt_info['debt_account'] * $debt_info[$type] / 100;
//            $generate_flow = I('post.generate_flow');
//            $this->repay($debt_all_id, $type, $need_pay, $repay_money, $real_repay_time, $status, $generate_flow);
            $this->repayWithFinance($debt_all_id, $type, $need_pay, $repay_money, $real_repay_time, $status, $fid);
        }
        $has_repay = D('ProjectRepayment')->sumType($debt_all_id, $type);
        $fee = round($debt_info['debt_account'] * $debt_info[$type] / 100, 2) - $has_repay;
        $fee = $fee > 0 ? $fee : 0;
        $banks = D('Bank')->select();
        
        $this->assign('banks', $banks);
        $this->assign('fee', $fee);
        $this->assign('type', $type);
        $this->assign('debt_all_id', $debt_all_id);
        $this->display('repay_frontend_fee');
    }

    protected function repay($debt_all_id, $type, $need_pay, $repay_money, $real_repay_time, $status, $generate_flow) {
        $projectFeeModel = D('ProjectFee');
        $projectFeeModel->startTrans();
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = $type;
        $pro_fee = $projectFeeModel->where($map)->find();
        if (!$pro_fee) {
            $save_fee = array('debt_all_id' => $debt_all_id, 'type' => $type, 'money' => $need_pay, 'addtime' => time(), 'status' => $status, 'has_repay_money' => $repay_money);
            if (!$projectFeeModel->add($save_fee)) {
                $projectFeeModel->rollback();
                $this->json_error('请稍后再试');
            }
        } else {
            $has_repay_money = $repay_money + $pro_fee['has_repay_money'];
            $save_fee = array('status' => $status, 'has_repay_money' => $has_repay_money);
            if (!$projectFeeModel->where($map)->save($save_fee)) {
                $projectFeeModel->rollback();
                $this->json_error('请稍后再试');
            }
        }
        $repay_info = array('debt_all_id' => $debt_all_id, 'type' => $type, 'repay_money' => $repay_money, 'addtime' => time(), 'real_repay_time' => $real_repay_time);
        if (!D('ProjectRepayment')->add($repay_info)) {
            $projectFeeModel->rollback();
            $this->json_error('内部错误');
        }
        //是否生成资金流水记录
        if ($generate_flow) {
            $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
            if ($this->generateFlow($debt_info['pro_id'], $debt_info['company_id'], $debt_all_id, $repay_money, $type, $real_repay_time) === false) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误001');
            }
        }
        $projectFeeModel->commit();
        $this->json_success('还款成功', '', '', true, array('dialogid' => 'pro-repayment-schedule-specified'));
    }
    
    /**
     * 处理还款操作
     * @param type $rp_id
     * @param type $repay_money
     * @param type $real_repay_time
     * @param type $repay_pic
     */
    protected function repayWithFinance($debt_all_id, $type, $need_pay, $repay_money, $real_repay_time, $status, $fid) {
        $projectFeeModel = D('ProjectFee');
        $projectFeeModel->startTrans();
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = $type;
        $finance_info = D('FinanceFlow')->findByPk($fid);   //获取财务流水信息
        $finance_surplu_money = $finance_info['money'] - $finance_info['has_distribute'];
        if ($finance_surplu_money < $repay_money) {
            $this->json_error('本条财务流水剩余金额为'.$finance_info['has_distribute'].',请输入正确的金额');
        }
        $pro_fee = $projectFeeModel->where($map)->find();
        if (!$pro_fee) {
            $save_fee = array('debt_all_id' => $debt_all_id, 'type' => $type, 'money' => $need_pay, 'addtime' => time(), 'status' => $status, 'has_repay_money' => $repay_money);
            if (!$projectFeeModel->add($save_fee)) {
                $projectFeeModel->rollback();
                $this->json_error('请稍后再试');
            }
        } else {
            $has_repay_money = $repay_money + $pro_fee['has_repay_money'];
            $save_fee = array('status' => $status, 'has_repay_money' => $has_repay_money);
            if (!$projectFeeModel->where($map)->save($save_fee)) {
                $projectFeeModel->rollback();
                $this->json_error('请稍后再试');
            }
        }
        $repay_info = array('debt_all_id' => $debt_all_id, 'type' => $type, 'repay_money' => $repay_money, 'addtime' => time(), 'real_repay_time' => $real_repay_time);
        if (!D('ProjectRepayment')->add($repay_info)) {
            $projectFeeModel->rollback();
            $this->json_error('内部错误');
        }
        //是否生成资金流水记录
        $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
        if ($this->generateFlow($debt_info['pro_id'], $debt_info['company_id'], $debt_all_id, $repay_money, $type, $real_repay_time, $fid) === false) {
            $projectFeeModel->rollback();
            $this->json_error('还款失败。错误原因：内部错误001');
        }
        //更新财务流水表，已经分配的金额
        $has_distribute = bcadd($finance_info['has_distribute'], $repay_money, 2);
        if (D('FinanceFlow')->where('fid='.$fid)->save(array('has_distribute' => $has_distribute)) === false) {
            $projectFeeModel->rollback();
            $this->json_error('还款失败。错误原因：内部错误003');
        }
        $projectFeeModel->commit();
        $this->json_success('还款成功', '', '', true, array('dialogid' => 'pro-repayment-schedule-specified'));
    }
    
    //生成流水记录
    protected function generateFlow($pro_id, $company_id, $debt_all_id, $money, $type, $real_time, $fid = 0) {
        $remark = I('post.remark');
        $bank_id = I('post.bank_id');
        return D('CapitalFlow')->addFlowWithFinance($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $real_time, $fid, $remark);
    }
    
    public function backCashDeposit() {
        $debt_all_id = I('get.debt_all_id');
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = 'cash_deposit';
        $projectFeeModel = D('ProjectFee');
        $pro_fee = $projectFeeModel->where($map)->find();
        if (IS_POST) {
            $generate_flow = I('post.generate_flow', 0);
            $real_repay_time = strtotime(I('post.real_repay_time'));
            $fid = I('post.fid');
            $repay_money = $pro_fee['money'];
            $finance_info = D('FinanceFlow')->findByPk($fid);   //获取财务流水信息
            $finance_surplu_money = $finance_info['money'] - $finance_info['has_distribute'];
            if ($finance_surplu_money < $repay_money) {
                $this->json_error('本条财务流水剩余金额为'.$finance_info['has_distribute'].',请输入正确的金额');
            }
            $projectFeeModel->startTrans();
            if (D('ProjectFee')->where($map)->save(array('status' => 2)) === false) {
                $projectFeeModel->rollback();
                $this->json_error('操作失败');
            }
            $type = 'back_cash_deposit';
            //插入还款记录
            $record = array('debt_all_id' => $debt_all_id, 'repay_money' => $pro_fee['money'], 'real_repay_time' => $real_repay_time, 'repay_pic' => '', 'type' => $type, 'addtime' => time());
            if (!D('ProjectRepayment')->add($record)) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误001');
            }
            $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
            if ($this->generateFlow($debt_info['pro_id'], $debt_info['company_id'], $debt_all_id, $pro_fee['money'], 'back_cash_deposit', $real_repay_time, $fid) === false) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误002');
            } 
            //更新财务流水表，已经分配的金额
            $has_distribute = bcadd($finance_info['has_distribute'], $repay_money, 2);
            if (D('FinanceFlow')->where('fid='.$fid)->save(array('has_distribute' => $has_distribute)) === false) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误003');
            }
            $projectFeeModel->commit();
            $this->json_success('操作成功', '', '', 'true', array('dialogid' => 'pro-repayment-schedule-specified'));
        }
        $banks = D('Bank')->select();
        
        $this->assign('banks', $banks);
        $this->assign('fee', $pro_fee['money']);
        $this->assign('type', $map['type']);
        $this->assign('debt_all_id', $debt_all_id);
        $this->display('back_cash_deposit');
    }
    
    public function repaymentDoneV() {
        $debt_all_id = I('get.debt_all_id');
        $profit = D('ProjectDebt')->getDebtProfit($debt_all_id);
//        var_dump($profit);exit;
        $this->assign('profit', $profit);
        $this->assign('debt_all_id', $debt_all_id);
        $this->display('repayment_done');
    }
    
    //还款完成
    public function repaymentDone() {
        if (IS_POST) {
            $debt_all_id = I('post.debt_all_id');
            $real_pay_time = I('post.real_pay_time');
            if (D('ProjectDebt')->repaymentDone($debt_all_id, $real_pay_time) === false) {
                $this->json_error('操作失败');
            }
            $this->json_success('操作成功', '', '', true, array('tabid' => 'projectdebt-index'));
        }
    }
    
    //退还资金
    public function backMoney() {
        $debt_all_id = I('get.debt_all_id');
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = I('get.type');
        
        $banks = D('Bank')->select();
        
        $this->assign('banks', $banks);
        $this->assign('type', $map['type']);
        $this->assign('debt_all_id', $debt_all_id);
        $this->display('back_money');
    }
    
    public function back() {
        if (IS_POST) {
            $type = I('get.type', 0);
            $real_repay_time = strtotime(I('post.real_repay_time'));
            $back_money = I('post.back_money');
            $fid = I('post.fid');
            $debt_all_id = I('get.debt_all_id');
            
            $type = 'back_' . $type;
            $projectFeeModel = D('ProjectFee');
            $finance_info = D('FinanceFlow')->findByPk($fid);   //获取财务流水信息
            $finance_surplu_money = $finance_info['money'] - $finance_info['has_distribute'];
            if ($finance_surplu_money < $back_money) {
                $this->json_error('本条财务流水剩余金额为'.$finance_info['has_distribute'].',请输入正确的金额');
            }
            $projectFeeModel->startTrans();
            //插入还款记录
            $record = array('debt_all_id' => $debt_all_id, 'repay_money' => $back_money, 'real_repay_time' => $real_repay_time, 'repay_pic' => '', 'type' => $type, 'addtime' => time());
            if (!D('ProjectRepayment')->add($record)) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误001');
            }
            $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
            if ($this->generateFlow($debt_info['pro_id'], $debt_info['company_id'], $debt_all_id, $back_money, $type, $real_repay_time, $fid) === false) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误002');
            } 
            //更新财务流水表，已经分配的金额
            $has_distribute = bcadd($finance_info['has_distribute'], $back_money, 2);
            if (D('FinanceFlow')->where('fid='.$fid)->save(array('has_distribute' => $has_distribute)) === false) {
                $projectFeeModel->rollback();
                $this->json_error('还款失败。错误原因：内部错误003');
            }
            $projectFeeModel->commit();
            $this->json_success('操作成功', '', '', 'true', array('dialogid' => 'pro-repayment-schedule-specified'));
        }
    }
    
    //放款日志
    public function loanLog() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $model = D('ProjectDebt');
        $map['t.pro_id'] = $pro_id;
        $result = $model->getList($page, $pageSize, $map);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('loan_log');
    }
    
    //跟进项目返回债权列表
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
}
