<?php

namespace Admin\Controller;

class ReportFormsController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    //总放款图表
    public function loanChart() {
        $result = D('ReportForms', 'Logic')->loanIndex();
//        var_dump($list);
        $describe = array('500万(含)以内', '500万~1000万(含)', '1000万~2000万(含)', '2000万以上');
        $charts = array();
        foreach ($result['list'] as $key => & $val) {
            $chart['name'] = $describe[$key];
            $chart['value'] = $val['num'];
            $charts[] = $chart;
        }
        $this->assign('charts' ,  json_encode($charts));
        $this->assign('list', $result['list']);
        $this->assign('sum_money', $result['sum_money']);
        $this->assign('total', $result['total']);
        $this->assign('describe', $describe);
        $this->display('echarts');
    }
    
    //按天放款报表
    public function dayLoan() {
        $result = D('ReportForms', 'Logic')->dayLoan();
//        var_dump($result);
        
        $this->assign('xaxis' ,  json_encode($result['days']));
        $this->assign('yaxis' ,  json_encode($result['list']));
//        $this->display('loan_chart');
        $this->display('day_loan');
    }
    
    //按天放款报表
    public function monthLoan() {
        $result = D('ReportForms', 'Logic')->monthLoan();
//        var_dump($result);
        
        $this->assign('xaxis' ,  json_encode($result['months']));
        $this->assign('yaxis' ,  json_encode($result['list']));
//        $this->display('loan_chart');
        $this->display('month_loan');
    }
    
    
    //按天放款报表
    public function generalLoan() {
        $result = D('ReportForms', 'Logic')->loanIndex();
//        var_dump($list);
        $describe = array('500万(含)以内', '500万~1000万(含)', '1000万~2000万(含)', '2000万以上');
        $charts = array();
        foreach ($result['list'] as $key => & $val) {
            $chart['name'] = $describe[$key];
            $chart['value'] = $val['num'];
            $charts[] = $chart;
        }
        $this->assign('charts' ,  json_encode($charts));
        $this->assign('list', $result['list']);
        $this->assign('sum_money', $result['sum_money']);
        $this->assign('total', $result['total']);
        $this->assign('describe', $describe);
//        $this->display('loan_chart');
        $this->display('general_loan');
    }
    
    //总放款图表
    public function month() {
        $list = D('CapitalFlow')->monthReport(6);
//        var_dump($list);
        $pro_ids = array_column($list, 'pro_id');
        $pro_id_counts = array_count_values($pro_ids);
//        $n_counts = $pro_id_counts;
//        foreach ($list as $v) {
//            if($n_counts[$v['pro_id']] == $pro_id_counts[$v['pro_id']]){
//                echo 11;
//            }
//                $n_counts[$v['pro_id']]--;
//                echo $v['pro_id'];
//            
//        }
//        var_dump($pro_id_counts);
        $this->assign('pro_id_counts', $pro_id_counts);
        $this->assign('n_counts', $pro_id_counts);
        $this->assign('list', $list);
        $this->display();
    }
    
    public function projectFlow() {
        $pro_id = I('get.pro_id');
        $list = D('CapitalFlow')->projectMonth($pro_id);
        $pro_info = D('Project')->getByPk($pro_id);
//            foreach ($v as $_v) {
//                echo 'a<br/>';
//                var_dump($_v['type']);
//            }
//        }
        $type = D('CapitalFlow')->getTypeDescribe();
        
        $this->assign('type', $type);
        $this->assign($pro_info);
        $this->assign('list', $list);
        $this->display('project_flow');
    }
    
    //月度项目放款统计表
    public function loanStatistics() {
        $month = I('post.month', date('n'));
        $result = D('ReportForms', 'Logic')->loanMonthyDetail($month);
        
        $new_arr = array();
        foreach ($result['list'] as $val) {
            if (isset($new_arr[$val['pro_id']])){
                $new_arr[$val['pro_id']][] = $val;
            } else {
                $new_arr[$val['pro_id']][] = $val;
            }
        }
        foreach ($new_arr as & $pro_loan) {
            $tmp_arr = array(
                'real_time' =>  0 ,
                'financing' =>  '0.00' ,
                'principal' =>  '0.00' ,
                'interest' =>  '0.00' ,
                'handling_charge' =>  '0.00' ,
                'counseling_fee' =>  '0.00' ,
                'cash_deposit' =>  '0.00' ,
                'overdue_pay' =>  '0.00' ,
                'back_interest' =>  '0.00' ,
                'back_cash_deposit' =>  '0.00' ,
                'back_handing' =>  '0.00',
                'days' =>  '0' ,
                'pay_time' =>  '0' 
            );
            $flag = false;
            //退款项单独放一列
            foreach ($pro_loan as $val) {
                if ($val['back_interest'] > 0) {
                    $tmp_arr['interest'] += -$val['back_interest'];
                    $flag = true;
                }
                if ($val['back_cash_deposit'] > 0) {
                    $tmp_arr['cash_deposit'] += -$val['back_cash_deposit'];
                    $flag = true;
                }
                if ($val['back_handling_charge'] > 0) {
                    $tmp_arr['handling_charge'] += -$val['back_handling_charge'];
                    $flag = true;
                }
                $tmp_arr['pro_id'] =  $val['pro_id'];
                $tmp_arr['pro_title'] =  $val['pro_title'];
                $tmp_arr['pay_time'] = $val['pay_time'];
            }
            if ($flag) {
                $pro_loan[] = $tmp_arr;
            }
            if (count($pro_loan) > 1) {
                $sum = $pro_loan[0]['financing']+$pro_loan[0]['interest']+$pro_loan[0]['handling_charge']+$pro_loan[0]['counseling_fee']+$pro_loan[0]['cash_deposit']+$pro_loan[0]['overdue_pay']+$pro_loan[0]['principal'];
                if ($sum == 0) {
                    unset($pro_loan[0]);
                }
            }
        }
//        var_dump($new_arr);
        $pro_ids = array_column($result['list'], 'pro_id');
        $rowspan = array_count_values($pro_ids);
        $this->assign('rowspan',$rowspan);
        $this->assign('list', $new_arr);
        $this->assign('month', $month);
        $this->display('loan_monthy');
    }
    
    //电子商业承兑汇票明细
    public function electronicBill() {
        $sumByStatus = D('ElectronicBill')->sumByStatus();
        $sumByBeforeCompany = D('ElectronicBill')->sumByBeforeCompany();
        $typeDescribe = D('ElectronicBill')->getTypeDescribe();
        
        $this->assign('typeDescribe', $typeDescribe);
        $this->assign('sumByStatus', $sumByStatus);
        $this->assign('sumByBeforeCompany', $sumByBeforeCompany);
        $this->display('electronic_bill');
    }
    
    //项目管理统计表
    public function projectStatistics() {
        
        if ( IS_POST) {
//        var_dump($order);exit;
        $map['submit_status'] = 1;
        $map['step_pid'] = array('GT', 0);
//        $map['_string'] = '(submit_status=1 AND pro_step>0) OR (submit_status=0 AND step_pid>1)';
        $result = D('ReportForms', 'Logic')->projectStatistics();
        $workflow = D('Workflow')->getWorkFlow();
//        $this->assign('workflow', $workflow);
        $industry = C('industries');
            foreach ($result['list'] as & $value) {
                $value['industry'] = $industry[$value['industry']];
                $value['real_loan_money'] = number_format($value['pro_real_money'] / 10000);
                $value['profit_rate'] = number_format($value['repurchase_rate'] + $value['handling_charge'] + $value['counseling_fee'], 2);
                $value['cash_deposit_money'] = number_format($value['pro_real_money'] * $value['cash_deposit'] / 1000000);
                $value['deadline'] = date('Y-m-d', $value['deadline']);
                $value['loan_time'] = date('Y-m-d', $value['loan_time']);
                $value['interest_type'] = $this->interestTypeDesc($value['interest_type']);
                $value['debt'] = number_format($value['debt'] / 10000);
                $value['contract_money'] = number_format($value['contract_money'] / 10000);
                $value['profit'] = number_format($value['profit'] / 10000, 4);
            }
        $this->ajaxRe(array('total' => $result['total'], 'pageCurrent' => 1, 'list' => $result['list']));
        }
        $this->display('project_statistics');
    }
    
    protected function interestTypeDesc($interest_type) {
        switch ($interest_type) {
            case 'day':
                $desc = '每月付息,按天计息';
                break;
            case 'month':
                $desc = '每月付息';
                break;
            case 'quarter':
                $desc = '每三月付息';
                break;
            case 'once':
                $desc = '一次性付息';
                break;
            case 'half_year':
                $desc = '每六个月付息';
                break;
        }
        return $desc;
    }
    
    //项目预期付息统计表
    public function predictInterest() {
        $month = I('post.month', date('n'));
        $result = D('ReportForms', 'Logic')->predictInterest($month);
        $pro_ids = array_column($result, 'pro_id');
        $rowspan = array_count_values($pro_ids);
//        var_dump($result);exit;
//        var_dump($rowspan);exit;
        
        $this->assign('rowspan',$rowspan);
        $this->assign('list', $result);
        $this->assign('month', $month);
        $this->display('predict_interest');
    }
    
    //收放款统计表（按项目）
    public function loanStatisticsByPro() {
        $start = strtotime(I('post.start', '2015-12-31'));
        $end = strtotime(I('post.end', '2016-12-31'));
        $result = D('ReportForms', 'Logic')->loanStatisticsByPro($start, $end);
//        var_dump($result);
        foreach ($result as & $value) {
            $value['due_interest'] = \Admin\Lib\CalcTool::calc($value['debt_account'], $value['repurchase_rate'], $value['term']);
            $value['due_handling_charge'] = \Admin\Lib\CalcTool::calc($value['debt_account'], $value['handling_charge'], $value['term']);
            $value['due_counseling_fee'] = \Admin\Lib\CalcTool::calc($value['debt_account'], $value['counseling_fee'], $value['term']);
            $value['due_cash_deposit'] = \Admin\Lib\CalcTool::cashDeposit($value['debt_account'], $value['cash_deposit']);
            $profit = D('CapitalFlow')->getProfit($value['debt_all_id']);
            $value['real_interest'] = $profit[0]['interest'] - $profit[0]['back_interest'];   //实收利息
            $value['real_handling_charge'] = $profit[0]['handling_charge'] - $profit[0]['back_handling_charge'];   //实收手续费
            $value['real_counseling_fee'] = $profit[0]['counseling_fee'];   //实收咨询费
            $value['real_cash_deposit'] = $profit[0]['cash_deposit'];   //实收保证金
            $sum_due = $value['due_interest'] + $value['due_handling_charge'] + $value['due_counseling_fee'] + $value['due_cash_deposit'] ;
            $sum_real_pay = $value['real_interest'] + $value['real_handling_charge'] + $value['real_counseling_fee'] + $value['real_cash_deposit'];
            $value['unpay_money'] = $sum_due - $sum_real_pay;   //待收
        }
        $new_arr = array();
        foreach ($result as $val) {
            if (isset($new_arr[$val['pro_id']])){
                $new_arr[$val['pro_id']][] = $val;
            } else {
                $new_arr[$val['pro_id']][] = $val;
            }
        }
//        var_dump($result);
//        var_dump($new_arr);exit;
        $pro_ids = array_column($result, 'pro_id');
        $rowspan = array_count_values($pro_ids);
//        var_dump($result);exit;
//        var_dump($rowspan);exit;
        
        $this->assign('rowspan',$rowspan);
        $this->assign('list', $new_arr);
        $this->assign('month', $month);
        $this->display('laon_statistics_pro');
    }
    
    public function proCapitalFlow() {
        $pro_id = I('get.pro_id');
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
                $map['add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['add_time'][] = array('ELT', $end_time);
            }
        }
        if ($pro_id !== '') {
            $map['t.pro_id'] = $pro_id;
        }
        $result = D('ReportForms', 'Logic')->proCapitalFlow($pro_id);
        foreach ($result as & $value) {
            $value['is_income'] = $model->isIncome($value['type']);
        }
        $sum_money_in = 0;
        $sum_money_out = 0;
//        var_dump($result);exit;
        
        $this->assign(array('sum_money_in' => $sum_money_in, 'sum_money_out' => $sum_money_out));
        $this->assign('type_describe', $type_describe);
        $this->assign(array('total' => count($result), 'pageCurrent' => $page, 'list' => $result));
        $this->assign('post', $_POST);
        $this->assign('pro_id', $pro_id);
        $this->display('specified');
    }
    
    //项目到期统计表
    public function proFinish() {
        $month = I('post.month', date('n'));
        $result = D('ReportForms', 'Logic')->proFinish($month);
        $pro_ids = array_column($result, 'pro_id');
        $rowspan = array_count_values($pro_ids);
//        var_dump($result);exit;
//        var_dump($rowspan);exit;
        
        $this->assign('rowspan',$rowspan);
        $this->assign('list', $result);
        $this->assign('month', $month);
        $this->display('pro_finish');
    }
    
    public function proProfit() {
        $month = I('post.month', date('n'));
        $result = D('ReportForms', 'Logic')->profit();
        $pro_ids = array_column($result, 'pro_id');
        $rowspan = array_count_values($pro_ids);
//        var_dump($result);exit;
//        var_dump($rowspan);exit;
        
        $this->assign('rowspan',$rowspan);
        $this->assign('list', $result);
        $this->assign('month', $month);
        $this->display('pro_profit');
    }
}
