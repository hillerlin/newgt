<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProRepaymentScheduleModel extends BaseModel {
    
    const DONE = 1;     //还完
    const MONTH = 'month';
    const QUARTER = 'quarter';
    const HALFYEAR = 'harf_year';
    const ONCE = 'once';
    
    protected $tableName = 'project_repayment_schedule';

    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order = 'repay_time, status DESC';
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__PROJECT_DEBT__ AS pd ON pd.debt_all_id=t.debt_all_id')
                ->join('__PROJECT_CONTRACT__ AS pc ON pc.contract_id=pd.contract_id')
                ->field('t.*,pro_title,interest_type')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        foreach ($list as & $val) {
            if ($val['surplus_principal'] > 0 && $val['n_term']) {
//                $calcTool = new \Admin\Lib\CalcTool($val['surplus_principal'], $val['term_rate'], $val['term']);
//                $calcTool->firstInterest($val['n_term']);
                $val['real_interest'] = \Admin\Lib\CalcTool::mixInterest($val['surplus_principal'], $val['term_rate'], $val['interest_type'], $val['term'], $val['n_term']);
            }
        }
        return array('total' => $total, 'list' => $list);
    }
    
    public function getSpecified($rp_id) {
        $map['rp_id'] = $rp_id;
        $result = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__PROJECT_DEBT__ AS pd ON pd.debt_all_id=t.debt_all_id')
                ->join('__PROJECT_CONTRACT__ AS pc ON pc.contract_id=pd.contract_id')
                ->field('t.*,pro_title,interest_type')
                ->where($map)
                ->find();
        return $result;
    }
    
    /**
     * 放款后生成还款计划表
     * @param type $debt_all_id
     * @param type $pro_id
     * @param type $total_money
     * @param type $rate
     * @param type $term
     * @param type $term_type
     * @return type
     */
    public function addRecords($debt_all_id, $pro_id, $company_id, $total_money, $rate, $term, $loan_time, $interestType = 'month', $calc_interest_info) {
//        var_dump($calc_interest_info);exit;
        $is_day_interest = $calc_interest_info['is_day_interest'];
        $begin_interest_time = $calc_interest_info['begin_interest_time'];
        $pay_interest_day = $calc_interest_info['pay_interest_day'];
        $repaymentSchedule = new \Admin\Lib\CalcTool($total_money, $rate, $term);
        $pay_time = $loan_time;
        switch ($interestType) {
            case 'day':
                return $this->dayRepay($debt_all_id, $pro_id, $company_id, $total_money, $rate, $term, $loan_time, $interestType, $calc_interest_info);
            case 'month':
                $every_term = 1;
                break;
            case 'quarter':
                $every_term = 3;
                break;
            case 'half_year':
                $every_term = 6;
                break;
            case 'once':
                $every_term = $term;
                break;
        }
        if (empty($begin_interest_time) === false) {
            $first_interest_month = date('m', $begin_interest_time);
            $loan_month = date('m', $loan_time);
            if ($first_interest_month == $loan_month) {
                $n_term = 0;
            } else {
                $n_term = $every_term;
            }
        } else {
            $n_term = $every_term;
        }
        $num_term = 1;
        $sum_term = ceil($term / $every_term);
        $mod = $term % $every_term;
        for ($n = 1; $n <= $sum_term; $n++) {
            $repaymentSchedule->firstInterest($n_term);
//                    if ($mod == 0) {
            $data['repay_time'] = ($n_term != $term ? \Admin\Lib\RepaymentSchedule::calc_end_time($pay_time, $n_term, $pay_interest_day, $begin_interest_time) : \Admin\Lib\CalcTool::deadline($loan_time, $term, 'm'));
            $data['principal'] = 0;
            $data['interest'] = $repaymentSchedule->getInterest($interestType);
            if ($n == $sum_term && $mod > 0) {
                $data['interest'] = $repaymentSchedule->monthInterest() * $mod;
            }
            $data['debt_all_id'] = $debt_all_id;
            $data['pro_id'] = $pro_id;
            $data['term_rate'] = $rate;
            $data['n_term'] = $num_term;
            $data['term'] = $term;
            if ($num_term == 1) {
                $data['surplus_principal'] = $total_money;
            } else {
                $data['surplus_principal'] = 0;
            }
            $data['type'] = 'interest';
            $num_term++;
            $data['company_id'] = $company_id;
            $dataList[] = $data;
            if ($n == $sum_term - 1 && $mod > 0) {
                $n_term += $mod;
            } else {
                $n_term += $every_term;
            }
        }
            $data['repay_time'] =  \Admin\Lib\CalcTool::deadline($loan_time, $term, 'm');
            $data['principal'] = $total_money;
            $data['interest'] = 0;

            $data['debt_all_id'] = $debt_all_id;
            $data['pro_id'] = $pro_id;
            $data['term_rate'] = $rate;
            $data['n_term'] = 0;
            $data['term'] = $term;
            $data['surplus_principal'] = 0;
            $data['company_id'] = $company_id;
            $data['type'] = 'principal';
            $dataList[] = $data;
//        var_dump($dataList);exit;;
        return $this->addAll($dataList);
    }
    
    public function dayRepay($debt_all_id, $pro_id, $company_id, $total_money, $rate, $term, $loan_time, $interestType = 'month', $calc_interest_info) {
        $is_day_interest = $calc_interest_info['is_day_interest'];
        $begin_interest_time = $calc_interest_info['begin_interest_time'];
        $pay_interest_day = $calc_interest_info['pay_interest_day'];
        $repaymentSchedule = new \Admin\Lib\CalcTool($total_money, $rate, $term);
        $pay_time = $loan_time;
        $every_term = 1;
        
        if (empty($begin_interest_time) === false) {
            $first_interest_month = date('m', $begin_interest_time);
            $loan_month = date('m', $loan_time);
            if ($first_interest_month == $loan_month) {
                $n_term = 0;
            } else {
                $n_term = $every_term;
            }
        } else {
            $n_term = $every_term;
        }
        $num_term = 1;
        $sum_term = ceil($term / $every_term);
        $mod = $term % $every_term;
        $deadline = \Admin\Lib\CalcTool::deadline($loan_time, $term, 'm');  //到期日
        $start = $loan_time;    //第一次还息起始时间是放款当天
        for ($n = 1; $n <= $sum_term; $n++) {
            $repaymentSchedule->firstInterest($n_term);
            $data['repay_time'] = \Admin\Lib\RepaymentSchedule::calc_end_time($pay_time, $n_term, $pay_interest_day, $begin_interest_time);
            if ($n_term == $term ) {    //判断如果最后一期还息日大于了到期日，那就在到期日还息
                $data['repay_time'] = $data['repay_time'] > $deadline ? $deadline : $data['repay_time'];
            }
            $data['principal'] = 0;
            $end = $data['repay_time'];
            $data['interest'] = $repaymentSchedule->dayInterest($start, $end);
            $start = $data['repay_time'];   //第二次以后的起息时间是前一次的还款时间
            
            $data['debt_all_id'] = $debt_all_id;
            $data['pro_id'] = $pro_id;
            $data['term_rate'] = $rate;
            $data['n_term'] = $num_term;
            $data['term'] = $term;
            if ($num_term == 1) {
                $data['surplus_principal'] = $total_money;
            } else {
                $data['surplus_principal'] = 0;
            }
            $data['type'] = 'interest';
            $num_term++;
            $data['company_id'] = $company_id;
            $dataList[] = $data;
            if ($n == $sum_term - 1 && $mod > 0) {
                $n_term += $mod;
            } else {
                $n_term += $every_term;
            }
        }
        $data['repay_time'] =  $deadline;
        if ($start < $data['repay_time']) { //如果最后一次还息日期比还本金时间早，剩下几天也需要还息
            $data['principal'] = 0;
            $data['interest'] = $repaymentSchedule->dayInterest($start, $data['repay_time']);

            $data['debt_all_id'] = $debt_all_id;
            $data['pro_id'] = $pro_id;
            $data['term_rate'] = $rate;
            $data['n_term'] = 13;
            $data['term'] = $term;
            $data['surplus_principal'] = 0;
            $data['company_id'] = $company_id;
            $data['type'] = 'interest';
            $dataList[] = $data;
        }
        $data['principal'] = $total_money;
        $data['interest'] = 0;

        $data['debt_all_id'] = $debt_all_id;
        $data['pro_id'] = $pro_id;
        $data['term_rate'] = $rate;
        $data['n_term'] = 0;
        $data['term'] = $term;
        $data['surplus_principal'] = 0;
        $data['company_id'] = $company_id;
        $data['type'] = 'principal';
        $dataList[] = $data;
//        var_dump($dataList);exit;
        return $this->addAll($dataList);
    }
    
    //
    public function upOverdueFee($rp_id, $data) {
        return $this->updateByPk($rp_id, $data);
    }
    
    //上期未还利息，更新下期剩余本金
    public function updateNextSurplurPrincipal($debt_all_id, $n_term, $surplus_principal) {
        $map['debt_all_id'] = $debt_all_id;
        $debt_info = D('ProjectDebt')->getDebtAllInfo($debt_all_id);
        $max_term = $this->maxTerm($debt_info['term'], $debt_info['interest_type']);
        if ($n_term < $max_term && $n_term > 0) {
            $map['n_term'] = $n_term + 1;
            if ($this->where($map)->save(array('surplus_principal' => $surplus_principal)) === false) {
                return false;
            }
            $map['n_term'] = 0;
            if ($this->where($map)->save(array('surplus_principal' => $surplus_principal)) === false ) {
                return fasle;
            }
        }
        return true;
    }
    
    /**
     * 根据当前期数获取到上一期的信息
     * @param type $debt_all_id
     * @param type $n_term
     * @return array
     */
    public function getTerm($debt_all_id, $n_term) {
        $map['debt_all_id'] = $debt_all_id;
        $map['n_term'] = $n_term ;
        return $this->where($map)->find();
    }
    
    /**
     * 返回还有几条利息未还
     * @param type $debt_all_id
     * @return type
     */
    public function interestRepayDone($debt_all_id) {
        $map['debt_all_id'] = $debt_all_id;
        $map['n_term'] = array('GT', 0);
        $map['status'] = 0;
        $count =$this->where($map)->count(); 
        return (int)$count;
    }
    
    /**
     * 根据付息类型，返回最大付息期数
     * @param type $term
     * @param type $interestType
     * @return type
     */
    public function maxTerm($term, $interestType) {
        switch ($interestType) {
            case self::MONTH:
                $every_term = 1;
                break;
            case self::QUARTER:
                $every_term = 3;
                break;
            case self::HALFYEAR:
                $every_term = 6;
                break;
            case self::ONCE:
                $every_term = $term;
                break;
            default:
                $every_term = 1;
                break;
        }
        return ceil($term / $every_term);
    }
}

