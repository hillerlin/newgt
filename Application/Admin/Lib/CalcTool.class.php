<?php
namespace Admin\Lib;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalcTool
 *
 * 
 */
class CalcTool {

    public $total_money; //还款总额
    public $annual_rate; //年化利率,百分比
    public $repay_month; //还款期限,单位月
    public $month_rate; //月利率
    public $principal_interest_rate; //月本息率
    public $total_principal_interest; //本息总额
    public $month_principal_interest; //月本息
    public $month_principal; //月本金
    public $month_interest; //月利息
    public $month_principal_balance; //月本金余额

    /**
     * 
     * @param type $total_money     //还款总额
     * @param type $annual_rate     //年化利率,百分比
     * @param type $repay_month     //还款期限,单位月
     */
    public function __construct($total_money, $annual_rate, $repay_month) {
        bcscale(10);
        $this->total_money = $total_money;
        $this->annual_rate = bcdiv($annual_rate, 100);
        $this->repay_month = $repay_month;

        $this->month_rate = bcdiv($this->annual_rate, 12);
        $this->principal_interest_rate = bcadd($this->month_rate, 1);
        $this->month_principal_interest = 
                bcdiv(bcmul($this->total_money, bcmul($this->month_rate, bcpow($this->principal_interest_rate, $this->repay_month))),
                bcsub(bcpow($this->principal_interest_rate, $this->repay_month), 1));
        $this->total_principal_interest = bcmul($this->month_principal_interest, $this->repay_month);
    }

    //每月等额还款
    public function equalRepayments($period) {
        $this->month_principal = bcmul(bcsub($this->month_principal_interest, bcmul($this->total_money, $this->month_rate)),
             bcpow($this->principal_interest_rate, bcsub($period, 1, 0)));
       //$this->month_principal = $this->month_principal_interest - $this->total_money * $this->month_rate;
        $this->month_interest = bcsub($this->month_principal_interest, $this->month_principal);
    }
    //先息后本
    public function firstInterest($period) {
        $this->month_principal = $period == $this->repay_month ? $this->total_money : 0;
       //$this->month_principal = $this->month_principal_interest - $this->total_money * $this->month_rate;
        $this->month_interest = bcmul($this->total_money, $this->month_rate);
    }
    
    //所有期数总本息
    public function totalPrincipalInterest() {
        return round($this->total_principal_interest, 2);
    }
    
    //本息和
    public function monthPrincipalInterest() {
        return round($this->month_principal_interest, 2);
    }
    
    //本金
    public function monthPrincipal() {
        return round($this->month_principal, 2);
    }
    
    //利息
    public function monthInterest() {
        return round($this->month_interest, 2);
    }
    
    public function onceInterests() {
        $total_interest = bcmul($this->total_money, bcdiv($this->repay_month * $this->annual_rate, 12));
        return round($total_interest, 2);
    }
    
    public function getInterest($interestType) {
        switch ($interestType) {
            case 'month':
                $interest = $this->monthInterest();
                break;
            case 'quarter':
                $interest = $this->quarterInterest();
                break;
            case 'half_year':
                $interest = $this->halfYearInterest();
                break;
            case 'once':
                $interest = $this->onceInterests();
                break;
        }
        return $interest;
    }
    
    //按天付息
    public function dayInterest($start, $end) {
        $startObj = new \DateTime(date('Y-m-d',$start));
        $endObj = new \DateTime(date('Y-m-d',$end));
        $diff = $endObj->diff($startObj);
        $days = $diff->format('%a');
//        var_dump($days);
        return bcdiv(bcmul($this->annual_rate * $days, $this->total_money ), 365);
    }
    
    //季度利息
    public function quarterInterest() {
        $total_interest = bcmul($this->total_money, $this->annual_rate);
        return round(bcdiv($total_interest, 4), 2);
    }
    
    //半年利息
    public function halfYearInterest() {
        $total_interest = bcmul($this->total_money, $this->annual_rate);
        return round(bcdiv($total_interest, 2), 2);
    }

    public function MonthPrincipalBalance() {
        return round($this->month_principal_balance, 2);
    }
    
    /**
     * 
     * @param type $loan_time
     * @param type $term
     * @param type $termType
     * @return int 截止日期
     */
    public static function deadline($loan_time, $term, $termType) {
        $date = date('Y-m-d', $loan_time);
        switch ($termType) {
            case 'm':   //按月
                $deadline = strtotime("$date +$term month") - 86400;
                break;
            case 'd':   //按天
                $deadline = strtotime("$date +$term months") - 86400;
                break;
            case 'q':   //按季度
//                $deadline = strtotime("+$term months") - 86400;
                break;
        }
        return $deadline;
    }
    
    //先息后本，计算每期利息
    public static function monthlyInterest($principal, $rate) {
        bcscale(10);
        return round(bcdiv(bcmul($principal, $rate / 100), 12), 2);
    }
    
    public static function onceInterest($principal, $rate, $term) {
        bcscale(10);
        return bcmul($principal, bcdiv($term * $rate, 12 * 100));
    }
    
    public static function mixInterest($principal, $rate, $interest_type, $term, $n_term) {
        $monthly_interest = self::monthlyInterest($principal, $rate);
        switch ($interest_type) {
            case 'month':
                $interest = $monthly_interest;
                break;
            case 'quarter':
                $interest = self::specifiedInterest($principal, $rate, $monthly_interest, $term, $n_term, 3);
                break;
            case 'half_year':
                $interest = self::specifiedInterest($principal, $rate, $monthly_interest, $term, $n_term, 6);
                break;
            case 'once':
                $interest = self::onceInterest($principal, $rate, $term) ;
//                $interest = $monthly_interest;
                break;
            default:
                $interest = 0;
                break;
        }
        return $interest;
    }
    
    private static function specifiedInterest($principal, $rate, $monthly_interest, $term, $n_term, $every_term) {
        $vir_term = $n_term * $every_term ;
            if ($vir_term < $term) {
                $mod = 0;
            } else {
                $mod = $every_term + $term - $vir_term ;
            }
            return $interest = ($mod == 0 ? round(bcdiv(bcmul($principal, $rate / 100), 12/$every_term), 2) : $monthly_interest * $mod);
    }

    /**
     * 根据年化利率计算应付的利息
     * @param type $total 金额
     * @param type $rate  年化利率，需要除以100
     * @param type $term  期限
     * @return type
     */
    public static function calc($total, $rate, $term) {
        bcscale(10);
        $month_rate = bcdiv($rate * $term, 1200);
        return round(bcmul($total, $month_rate), 2);
    }
    
    //计算保证金
    public static function cashDeposit($money, $rate) {
        bcscale(10);
        return round(bcmul($money, $rate / 100), 2);
    }
}
