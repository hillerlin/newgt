<?php
namespace Common\Lib;
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
class CalcOverdueFee {

    public $principal; //本金
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
//    public function __construct($principal, $annual_rate, $repay_month) {
//        bcscale(10);
//        $this->principal = $total_money;
//        $this->annual_rate = bcdiv($annual_rate, 100);
//        $this->repay_month = $repay_month;
//
//        $this->month_rate = bcdiv($this->annual_rate, 12);
//        $this->principal_interest_rate = bcadd($this->month_rate, 1);
//        $this->month_principal_interest = 
//                bcdiv(bcmul($this->total_money, bcmul($this->month_rate, bcpow($this->principal_interest_rate, $this->repay_month))),
//                bcsub(bcpow($this->principal_interest_rate, $this->repay_month), 1));
//        $this->total_principal_interest = bcmul($this->month_principal_interest, $this->repay_month);
//    }
    
    public function iniParams($param) {
        $this->principal = $param;
    }

    //违约金
    public function calcPenalty() {
        $this->month_principal = bcmul(bcsub($this->month_principal_interest, bcmul($this->total_money, $this->month_rate)),
             bcpow($this->principal_interest_rate, bcsub($period, 1, 0)));
        $demurrage_days * $overdue['debt_account'] * $overdue['penalty_rate'] / 100;
        return;
    }
    //先息后本
    public function firstInterest($period) {
        $this->month_principal = $period == $this->repay_month ? $this->total_money : 0;
       //$this->month_principal = $this->month_principal_interest - $this->total_money * $this->month_rate;
        $this->month_interest = bcmul($this->total_money, $this->month_rate, 2);
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

    public function MonthPrincipalBalance() {
        return round($this->month_principal_balance, 2);
    }
    
    
}
