<?php
namespace Admin\Lib;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RepaymentSchedule {
    //根据项目期数 计算要还款的每期的时间
    public static function calc_end_time($start_time, $term, $pay_interest_day, $begin_interest_time) {
        if (empty($begin_interest_time) === false && $term == 0) {
            return $begin_interest_time;
        }
        
        $start_time = self::startInterestTime($start_time, $pay_interest_day);
        $next = $start_time;
        $cd = $start_time;
        $H = date('H',$cd);
        $i = date('i',$cd);
        $s = date('s',$cd);
        $m = date('m',$cd);
        $d = date('d',$cd);
        $Y = date('Y',$cd);

        if($d <=28) {
            $next = mktime( $H, $i, $s, $m+$term, $d, $Y);
        } else {
            $dt = new \DateTime();
            $dt->setTimestamp($start_time);
            $current_mon_days = $dt->format('j');
            for($i=0; $i<$term; $i++) {
                $days = self::calc_days_of_next_month($dt->getTimestamp());
                if($current_mon_days > $days) {
                    $spec_day = $days;
                } else {
                    $spec_day = $current_mon_days;
                }
                $dt->setTimestamp(self::calc_spec_day_of_next_month($dt->getTimestamp(), $spec_day));
            }
            $next = $dt->getTimestamp();
        }

        if(date('H', $start_time) > 12) {
            $next += 86400;
        }
        return $next;
    }

    /**
     * 计算下个月（当前月份数字简单+1,不是strtotime的 +1 months)的天数
     *
     * @param mixed $cd
     * @return int
     */
    protected static function calc_days_of_next_month($cd) {
        $next_month             = sprintf("%02d", date('m', $cd)+1);
        $year = date('Y', $cd);
        if($next_month > 12) {
            $year += (int) ($next_month/12);
            $next_month = $next_month % 12;
        }
        $firt_day_of_next_month = strtotime($year . '-' .$next_month . '-01');
        $days_of_next_month     = date('t', $firt_day_of_next_month);
        return $days_of_next_month;
    }

    /**
     * 计算下个月指定日的时间戳
     *
     * @param mixed $cd 当前月的时间
     * @param mixed $spec_day_of_next_mon 下个月中的指定日
     * @return int
     */
    protected static function calc_spec_day_of_next_month($cd, $spec_day_of_next_mon) {
        $next_month = sprintf("%02u", date('m', $cd)+1);
        $year = date('Y', $cd);
        if($next_month > 12) {
            $year += (int) ($next_month/12);
            $next_month = $next_month % 12;
        }
        $last_day = strtotime($year. '-' .$next_month . '-' . $spec_day_of_next_mon. ' '. date('H:i:s', $cd));
        return $last_day;
    }
    
    public static function startInterestTime($loan_time, $pay_interest_day) {
//        $current_day = date('d', $loan_time);
//        if ($current_day < 16) {
//            $pay_time = strtotime(date('Y-m-10', $loan_time));
//        } else {
//            $pay_time = strtotime(date('Y-m-20', $loan_time));
//        }
        $pay_time = strtotime(date("Y-m-$pay_interest_day", $loan_time));
        return $pay_time;
    }
}