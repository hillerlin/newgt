<?php

namespace Admin\Logic;

class ReportFormsLogic {

    /**
     * 按照放款额度形成报表
     * @return array
     */
    public function loanIndex() {
        $condition = array(array(0, 5000000), array(5000000, 10000000), array(10000000, 20000000), array(20000000, 100000000000));
        $result = array();
        $sum_money = 0;
        $total = 0;
        foreach ($condition as $val) {
            $sql = "SELECT SUM(pro_real_money) AS money,COUNT(*) AS num FROM gt_project WHERE pro_real_money<={$val[1]} AND pro_real_money>{$val[0]}";
            $data = D('Project')->query($sql);
            $res['money'] = empty($data[0]['money']) ? 0 : $data[0]['money'];
            $res['num'] = empty($data[0]['num']) ? 0 : $data[0]['num'];
            $sum_money += $res['money'];
            $total += $res['num'];
            $result[] = $res;
        }
        return array('list' => $result, 'sum_money' => $sum_money, 'total' => $total);
    }

    //按天计算放款
    public function dayLoan() {
        $end = strtotime('midnight');
//        var_dump(date('Y-m-d H:i', $end));
        $start = strtotime("-30 days", $end);
//        var_dump(date('Y-m-d H:i', $start));
        $sql = "SELECT FROM_UNIXTIME(`addtime`,'%Y%m%d') days, SUM(debt_account) AS money, COUNT(*) AS num FROM gt_project_debt WHERE `addtime`>=$start AND `addtime`<$end GROUP BY days ORDER BY `addtime` DESC ";
        $data = D('ProjectDebt')->query($sql);
        if (!empty($data)) {
            $data = array_switch_key($data, 'days');
        }
        for ($day = $start; $day < $end; $day = strtotime('+1 days', $day)) {
            $format_day = date('Ymd', $day);
            $days[] = $format_day;
            $list[] = isset($data[$format_day]) ? sprintf('%.2f', $data[$format_day]['money']) : 0;
        }
        return array('days' => $days, 'list' => $list);
    }

    //按月计算放款
    public function monthLoan() {
        $end = strtotime(date("Y-m-01"));
        $start = strtotime("-12 months", $end);
        $sql = "SELECT FROM_UNIXTIME(`addtime`,'%Y%m') months, SUM(debt_account) AS money, COUNT(*) AS num FROM gt_project_debt WHERE `addtime`<$end GROUP BY months ORDER BY `addtime` DESC ";
        $data = D('ProjectDebt')->query($sql);
        if (!empty($data)) {
            $data = array_switch_key($data, 'months');
        }
        for ($month = $start; $month < $end; $month = strtotime('+1 month', $month)) {
            $format_month = date('Ym', $month);
            $months[] = $format_month;
            $list[] = isset($data[$format_month]) ? sprintf('%.02f', $data[$format_month]['money'] / 10000) : 0;
        }
        return array('months' => $months, 'list' => $list);
    }
    
    public function loanMonthyDetail($month, $year = '2016') {
        $start = strtotime("$year-$month-01");
        $end = strtotime("+1 months", $start);
//        var_dump(date('Y-m-d',$start),date('Y-m-d',$end));exit;
        $sql = "SELECT a.pro_id,pro_title,c.real_time,
SUM(CASE TYPE WHEN 'financing' THEN money ELSE 0 END) AS financing,
SUM(CASE TYPE WHEN 'principal' THEN money ELSE 0 END) AS principal,
SUM(CASE TYPE WHEN 'interest' THEN money ELSE 0 END) AS interest,
SUM(CASE TYPE WHEN 'handling_charge' THEN money ELSE 0 END) AS handling_charge,
SUM(CASE TYPE WHEN 'counseling_fee' THEN money ELSE 0 END) AS counseling_fee,
SUM(CASE TYPE WHEN 'cash_deposit' THEN money ELSE 0 END) AS cash_deposit,
SUM(CASE TYPE WHEN 'overdue_pay' THEN money ELSE 0 END) AS overdue_pay,
SUM(CASE TYPE WHEN 'back_interest' THEN money ELSE 0 END) AS back_interest,
SUM(CASE TYPE WHEN 'back_cash_deposit' THEN money ELSE 0 END) AS back_cash_deposit,
SUM(CASE TYPE WHEN 'back_handling_charge' THEN money ELSE 0 END) AS back_handling_charge,
FROM_UNIXTIME(`pay_time`,'%Y%m%d') days,
pay_time
FROM gt_capital_flow AS a,gt_project AS b,gt_project_debt AS c
                WHERE a.pro_id = b.pro_id AND a.debt_all_id = c.debt_all_id and pay_time > $start and  pay_time < $end
                GROUP BY days,pro_id
                ORDER BY pay_time,a.pro_id";
        $data = D('Capital_flow')->query($sql);
        return array('total' => count($data), 'list' => $data);
    }
    
    //项目管理表
    public function projectStatistics() {
        $sql = "SELECT a.pro_id,pro_title,industry,pro_real_money,pro_type,c.repurchase_rate,c.handling_charge,c.counseling_fee,c.cash_deposit,e.`real_name` AS pro_linker_name,d.`real_name` AS pmd_name,c.`term`,f.`deadline`,`pro_kind`
                ,c.real_money as contract_money,c.interest_type,f.real_time as loan_time,c.term
                FROM gt_project AS a
                LEFT JOIN gt_company AS b ON a.`company_id`=b.`company_id`
                LEFT JOIN gt_project_contract AS c ON c.`pro_id`=a.`pro_id`
                LEFT JOIN gt_admin AS d ON d.`admin_id`=a.`admin_id`
                LEFT JOIN gt_admin AS e ON e.`admin_id`=a.`pro_linker`
                LEFT JOIN gt_project_debt AS f ON f.`pro_id`=a.`pro_id`
                LEFT JOIN gt_prepare_contract AS g ON g.`pro_id`=a.`pro_id`
                WHERE a.`step_pid`=4 AND a.`pro_step` > 0
                GROUP BY a.pro_id";
        $data = D('Project')->query($sql);
        foreach ($data as & $val) {
            $map['pro_id'] = $val['pro_id'];
            $val['debt'] = D('ProjectDebtDetail')->where($map)->sum('debt_value');  //项目债权（发票金额）
            $val['profit'] =  D('CapitalFlow')->getProfitByProId($val['pro_id']);//项目收入
        }
        return array('total' => count($data), 'list' => $data);
    }
    
    //每月预计收息报表
    public function predictInterest($month, $year = '2016') {
        $start = strtotime("$year-$month-01");
        $end = strtotime("+1 months", $start);
        $sql = "SELECT a.pro_id,pro_title,interest,repay_time,has_repay_money,last_repay_time FROM
                gt_project_repayment_schedule AS a
                LEFT JOIN gt_project AS b ON a.`pro_id`=b.pro_id
                LEFT JOIN gt_project_debt AS pd ON pd.`debt_all_id`=a.debt_all_id
                WHERE repay_time > $start AND repay_time <$end AND `type`='interest' AND pd.status = 1
                ORDER BY pro_id";
        $data = D('Project')->query($sql);
        return $data;
    }
    
    //指定年限的项目收放款统计
    public function loanStatisticsByPro($start, $end) {
        $sql = "SELECT a.`pro_id`,c.`debt_all_id`,a.`pro_title`,c.real_time AS loan_time,c.`deadline`,c.`debt_account`,b.`repurchase_rate`,b.`interest_type`,b.`term`,b.`handling_charge`,b.`counseling_fee`,b.`cash_deposit` FROM gt_project AS a
                LEFT JOIN gt_project_contract AS b ON a.pro_id=b.`pro_id`
                LEFT JOIN gt_project_debt AS c ON c.`pro_id`=a.`pro_id`
                LEFT JOIN gt_capital_flow AS d ON d.`pro_id`=a.`pro_id`
                WHERE pay_time > $start AND pay_time <$end AND c.debt_all_id>0
                GROUP BY c.`debt_all_id`
                ORDER BY c.real_time,a.pro_id";
//        var_dump($sql);
        $data = D('Project')->query($sql);
        return $data;
    }
    
    //项目
    public function proCapitalFlow($pro_id) {
        $sql = "SELECT a.pro_id,pro_title,c.real_time,a.type,
                SUM(CASE TYPE WHEN 'financing' THEN money ELSE 0 END) AS financing,
                SUM(CASE TYPE WHEN 'principal' THEN money ELSE 0 END) AS principal,
                SUM(CASE TYPE WHEN 'interest' THEN money ELSE 0 END) AS interest,
                SUM(CASE TYPE WHEN 'handling_charge' THEN money ELSE 0 END) AS handling_charge,
                SUM(CASE TYPE WHEN 'counseling_fee' THEN money ELSE 0 END) AS counseling_fee,
                SUM(CASE TYPE WHEN 'cash_deposit' THEN money ELSE 0 END) AS cash_deposit,
                SUM(CASE TYPE WHEN 'overdue_pay' THEN money ELSE 0 END) AS overdue_pay,
                SUM(CASE TYPE WHEN 'back_interest' THEN money ELSE 0 END) AS back_interest,
                SUM(CASE TYPE WHEN 'back_cash_deposit' THEN money ELSE 0 END) AS back_cash_deposit,
                SUM(CASE TYPE WHEN 'back_handing' THEN money ELSE 0 END) AS back_handing,
                FROM_UNIXTIME(`pay_time`,'%Y%m%d') days,
                pay_time
                FROM gt_capital_flow AS a,gt_project AS b,gt_project_debt AS c
                WHERE a.pro_id = $pro_id AND a.pro_id = b.pro_id AND a.debt_all_id = c.debt_all_id ##AND pay_time > 1459440000 AND  pay_time < 1462032000
                GROUP BY pro_id,days,a.debt_all_id
                ORDER BY pay_time";
        $data = D('Project')->query($sql);
        return $data;
    }
    
    //项目到期表
    public function proFinish($month, $year = '2016') {
        $start = strtotime("$year-$month-01");
        $end = strtotime("+1 months", $start);
        $sql = "SELECT a.`pro_id`,b.`pro_title`,a.`real_time`,a.deadline,a.debt_account,a.real_pay_time,department FROM gt_project_debt AS a
                LEFT JOIN gt_project AS b ON a.`pro_id`=b.`pro_id`
                LEFT JOIN gt_admin AS c ON c.`admin_id`=b.`pro_linker`
                LEFT JOIN gt_department AS d ON d.`dept_id`=c.`dp_id`
                WHERE deadline >= $start AND deadline < $end AND b.finish_status!=1 AND b.step_pid!=8";
        $data = D('Project')->query($sql);
        return $data;
    }

    //项目收益表
    public function profit() {
        $pro_finish = D('Project')->done(); //首先查出已完结的项目
        $result = array();
        foreach ($pro_finish as $pro) {
            $pro_profit = D('ProjectDebt')->getProfit($pro['pro_id']);
//            var_dump($pro_profit);
            $sum_loan_money = 0;
            $sum_handling_charge = 0;
            $sum_counseling_fee = 0;
            $sum_interest = 0;
            $sum_profit = 0;
            $sum_profit_rate = 0;
            foreach($pro_profit as $debt_profit) {
                $sum_loan_money += $debt_profit['real_loan'];
                $sum_handling_charge += $debt_profit['handling_charge'];
                $sum_counseling_fee += $debt_profit['counseling_fee'];
                $sum_interest += $debt_profit['interest'];
                $sum_profit += $debt_profit['sum'];
                $sum_profit_rate += $debt_profit['part_sum_profit'];
            }
            $list['pro_id'] = $pro['pro_id'];
            $list['pro_title'] = $pro['pro_title'];
            $list['sum_loan_money'] = $sum_loan_money;
            $list['sum_handling_charge'] = $sum_handling_charge;
            $list['sum_counseling_fee'] = $sum_counseling_fee;
            $list['sum_interest'] = $sum_interest;
            $list['sum_profit'] = $sum_profit;
            $list['sum_profit_rate'] = $sum_profit_rate;
            $result[] = $list;
        }
        return $result;
    }
}
