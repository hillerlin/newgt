<?php

namespace Admin\Controller;
use Admin\Lib\CalcTool;

class ProRepaymentScheduleController extends CommonController {

    public function __construct() {
        $this->mainModel = D('ProRepaymentSchedule');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $debt_all_id = I('get.debt_all_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ProRepaymentSchedule');

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
        if (!empty($debt_all_id)) {
            $map['t.debt_all_id'] = $debt_all_id;
            $this->assign('debt_all_id', $debt_all_id);
        }
        $result = $model->getList($page, $pageSize, $map);

        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
    public function specified() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $debt_all_id = I('get.debt_all_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ProRepaymentSchedule');

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
        if (!empty($debt_all_id)) {
            $map['t.debt_all_id'] = $debt_all_id;
            $this->assign('debt_all_id', $debt_all_id);
        }
        $result = $model->getList($page, $pageSize, $map);
        $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
//        var_dump($debt_info);exit;
        $cash_deposit_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'cash_deposit');
        $cash_deposit_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'cash_deposit');
        $back_cash_deposit_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'back_cash_deposit');
        $counseling_fee_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'counseling_fee');
        $counseling_fee_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'counseling_fee');
        $handling_charge_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'handling_charge');//已还手续费
        $back_handling_charge_has_repay = D('ProjectRepayment')->sumType($debt_all_id, 'back_handling_charge');//退回手续费
        $handling_charge_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'handling_charge');
        $interest_last_repay = D('ProjectRepayment')->lastRepay($debt_all_id, 'interest');
        $back_interest = D('ProjectRepayment')->sumType($debt_all_id, 'back_interest');
        $back_other = D('ProjectRepayment')->sumType($debt_all_id, 'back_other');    //其他退款
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
        $this->assign('back_handling_charge_has_repay', $back_handling_charge_has_repay);
        $this->assign('counseling_fee_last_repay', $counseling_fee_last_repay);
        $this->assign('handling_charge_last_repay', $handling_charge_last_repay);
        $this->assign('interest_last_repay', $interest_last_repay);
        $this->assign('cash_deposit_has_repay', $cash_deposit_has_repay);
        $this->assign('cash_deposit_need_repay', $cash_deposit_need_repay);
        $this->assign('counseling_fee_need_repay', $counseling_fee_need_repay);
        $this->assign('handling_charge_need_repay', $handling_charge_need_repay);
        $this->assign('cash_deposit_has_repay', $cash_deposit_has_repay);
        $this->assign('counseling_fee_has_repay', $counseling_fee_has_repay);
        $this->assign('handling_charge_has_repay', $handling_charge_has_repay);
        $this->assign('interest_has_repay', $interest_has_repay);
        $this->assign('interest_need_repay', $interest_need_repay);
        $this->assign('back_interest', $back_interest);
        $this->assign('back_other', $back_other);
        $this->assign('debt_info', $debt_info);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }

    public function add() {
        $this->display();
    }

    /* 还款 */

    public function repay() {
        if (IS_POST) {
            $rp_id = I('post.rp_id');
            $repay_money = I('post.repay_money');
            $real_repay_time = I('post.real_repay_time');
            $fid = I('post.fid');
            $status = (int) I('post.status');
            $generate_flow = I('post.generate_flow', 0);
            
            if (empty($repay_money) || !is_numeric($repay_money)) {
                $this->json_error('还款金额不能为0或非数字');
            }
            if (!is_int($status) || !in_array($status, array(0, 1))) {
                $this->json_error('参数不正确' . is_int($status));
            }
//            $this->process($rp_id, $repay_money, strtotime($real_repay_time), '', $status, $generate_flow);
            $this->repayWithFinance($rp_id, $repay_money, strtotime($real_repay_time), $fid, $status);
        }
        $model = D('ProRepaymentSchedule');
        $rp_id = I('get.rp_id');
        $data = $model->where(array('rp_id' => $rp_id))->find();
        $banks = D('Bank')->select();
        
        $this->assign('banks', $banks);
        $this->assign($data);
        $this->display('repay_with_finance');
//        $this->display();
    }

    /**
     * 处理还款操作
     * @param type $rp_id
     * @param type $repay_money
     * @param type $real_repay_time
     * @param type $repay_pic
     */
    protected function process($rp_id, $repay_money, $real_repay_time, $repay_pic, $status, $generate_flow) {
        $record_model = D('ProjectRepayment');
        $record_model->startTrans();
        //更新还款计划表的实际还款金额
        $map['rp_id'] = $rp_id;
        $schedule_info = $this->mainModel->getSpecified($rp_id);
        $contract_info = D('ProjectDebt')->findContractByDebtId($schedule_info['debt_all_id']);
        $max_term = $this->mainModel->maxTerm($schedule_info['term'], $contract_info['interest_type']); //获取最大期数
        //判断是否可以做还款操作
        if ($this->couldLoan($schedule_info['debt_all_id'], $schedule_info['n_term']) === false ) {
            $this->json_error('请先还清上一期利息');
        }
        $has_repay_money = bcadd($schedule_info['has_repay_money'], $repay_money, 2);
        if ($schedule_info['status'] == \Admin\Model\RepaymentScheduleModel::DONE) {
            $record_model->rollback();
            $this->json_error('本条还款已结清');
        }
        if (!$this->mainModel->where($map)->save(array('has_repay_money' => $has_repay_money, 'last_repay_time' => $real_repay_time, 'status' => $status))) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误002');
        }
        $real_interest = CalcTool::mixInterest($schedule_info['surplus_principal'], $schedule_info['term_rate'], $schedule_info['interest_type'], $schedule_info['term'], $schedule_info['n_term']);//$schedule_info['surplus_principal'] * $schedule_info['term_rate'] / 100;
//        var_dump($schedule_info,$real_interest);exit;
        if ($has_repay_money > $real_interest) {
            $surplus_principal = $schedule_info['surplus_principal'] - ($has_repay_money - $real_interest);
        } else {
            $surplus_principal = $schedule_info['surplus_principal'];
        }
//        var_dump($schedule_info['debt_all_id'], $schedule_info['n_term'], $surplus_principal);exit;
        //跟新下一条还款计划表的剩余本金
//        var_dump($schedule_info['n_term'],$schedule_info['term']);exit;
        if (!$this->mainModel->updateNextSurplurPrincipal($schedule_info['debt_all_id'], $schedule_info['n_term'], $surplus_principal)) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误002');
        } 
        //资金类型
        $type = $schedule_info['type'];
        //插入还款记录
        $record = array('debt_all_id' => $schedule_info['debt_all_id'], 'repay_money' => $repay_money, 'real_repay_time' => $real_repay_time, 'repay_pic' => $repay_pic, 'rp_id' => $schedule_info['rp_id'], 'type' => $type);
        if (!$record_model->add($record)) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误001');
        }
        //是否生成资金流水记录
        if ($generate_flow) {
            if ($this->generateFlow($schedule_info['pro_id'], $schedule_info['company_id'], $schedule_info['debt_all_id'], $repay_money, $type, $real_repay_time) === false) {
                $record_model->rollback();
                $this->json_error('还款失败。错误原因：内部错误001');
            }
        }
        $record_model->commit();
        $this->json_success('还款成功', '', '', true, array('dialogid' => 'pro-repayment-schedule-specified'));
    }
    
    /**
     * 处理还款操作
     * @param type $rp_id
     * @param type $repay_money
     * @param type $real_repay_time
     * @param type $repay_pic
     */
    protected function repayWithFinance($rp_id, $repay_money, $real_repay_time, $fid, $status) {
        $record_model = D('ProjectRepayment');
        $record_model->startTrans();
        //更新还款计划表的实际还款金额
        $map['rp_id'] = $rp_id;
        $schedule_info = $this->mainModel->getSpecified($rp_id);
        $finance_info = D('FinanceFlow')->findByPk($fid);   //获取财务流水信息
        $finance_surplu_money = $finance_info['money'] - $finance_info['has_distribute'];
        if ($finance_surplu_money < $repay_money) {
            $this->json_error('本条财务流水剩余金额为'.$finance_info['has_distribute'].',请输入正确的金额');
        }
        //判断是否可以做还款操作
        if ($this->couldLoan($schedule_info['debt_all_id'], $schedule_info['n_term']) === false ) {
            $this->json_error('请先还清上一期利息'.$schedule_info['n_term']);
        }
        $has_repay_money = bcadd($schedule_info['has_repay_money'], $repay_money, 2);
        if ($schedule_info['status'] == \Admin\Model\RepaymentScheduleModel::DONE) {
            $record_model->rollback();
            $this->json_error('本条还款已结清');
        }
        if (!$this->mainModel->where($map)->save(array('has_repay_money' => $has_repay_money, 'last_repay_time' => $real_repay_time, 'status' => $status))) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误002');
        }
        //计算多还款时应扣除的本金（先注释掉，因为如按天算息的，他们如果在提前还款的时候，实际利息应比计划利息要低，但是他们会按照计划利息金额还息，但是本金却不减少，他们算息灵活度太高！）
//        $real_interest = CalcTool::mixInterest($schedule_info['surplus_principal'], $schedule_info['term_rate'], $schedule_info['interest_type'], $schedule_info['term'], $schedule_info['n_term']);//$schedule_info['surplus_principal'] * $schedule_info['term_rate'] / 100;
//        if ($has_repay_money > $real_interest) {
//            $surplus_principal = $schedule_info['surplus_principal'] - ($has_repay_money - $real_interest);
//        } else {
//            $surplus_principal = $schedule_info['surplus_principal'];
//        }
        $surplus_principal = $schedule_info['surplus_principal'];
        if ($status == 1) {
            //跟新下一条还款计划表的剩余本金
            if (!$this->mainModel->updateNextSurplurPrincipal($schedule_info['debt_all_id'], $schedule_info['n_term'], $surplus_principal)) {
                $record_model->rollback();
                $this->json_error('还款失败。错误原因：内部错误002');
            } 
        }
        //资金类型
        $type = $schedule_info['type'];
        //插入还款记录
        $record = array('debt_all_id' => $schedule_info['debt_all_id'], 'repay_money' => $repay_money, 'real_repay_time' => $real_repay_time, 'rp_id' => $schedule_info['rp_id'], 'type' => $type);
        if (!$record_model->add($record)) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误001');
        }
        //生成资金流水记录
        if ($this->generateFlow($schedule_info['pro_id'], $schedule_info['company_id'], $schedule_info['debt_all_id'], $repay_money, $type, $real_repay_time, $fid) === false) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误001');
        }
        //更新财务流水表，已经分配的金额
        $has_distribute = bcadd($finance_info['has_distribute'], $repay_money, 2);
        if (D('FinanceFlow')->where('fid='.$fid)->save(array('has_distribute' => $has_distribute)) === false) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误003');
        }
        //消息通知
        D('Message')->checkFinanceFlow($schedule_info['pro_id'], $repay_money, $type);
        $record_model->commit();
        $this->json_success('还款成功', '', '', true, array('dialogid' => 'pro-repayment-schedule-specified'));
    }
    
    //生成流水记录
    protected function generateFlow($pro_id, $company_id, $debt_all_id, $money, $type, $real_time, $fid = 0) {
        $remark = I('post.remark');
        $bank_id = I('post.bank_id');
        return D('CapitalFlow')->addFlowWithFinance($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $real_time, $fid, $remark);
    }
    
    //判断前置期数是否已还
    protected function couldLoan($debt_all_id, $n_term) {
        if ($n_term == 0) {
//            if ($this->mainModel->interestRepayDone($debt_all_id) === 0) {
                return true;
//            }
        }
        if ($n_term == 1) {
            return true;
        }
        $pre_schedule_info = $this->mainModel->getTerm($debt_all_id, $n_term - 1);
        if ($pre_schedule_info['status'] == 1) {
            return true;
        }
        return false;
    }

    //上传附件
    public function upload_attachment() {
        $mpay_id = I('request.mpay_id');
        $mid = I('request.mid');
        $field = 'mpay-' . $mpay_id;
        $upload_info = upload_file('/repayment/attachment/', $field, $mid . '-');
//        $this->ajaxReturn(array('status' => 1, 'data' => array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'))));
        if (isset($upload_info['file_path'])) {
            $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name']);
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败');
    }

    //逾期列表
    public function overdue() {
        $datetime1 = new \DateTime('2014-6-10');
        $datetime2 = new \DateTime('2015-5-25');
        $interval = $datetime1->diff($datetime2);
        echo $interval->format('%R%a days');
//        $this->display();
    }
}
