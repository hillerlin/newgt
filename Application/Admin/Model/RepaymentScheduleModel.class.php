<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class RepaymentScheduleModel extends BaseModel {
    
    const DONE = 1;     //还完
    
//    protected $_validate = array(
//        array('pro_id', 'require', '请输入项目ID'),
//        array('start_time', 'require', '请输入债权起始时间'),
//        array('end_time', 'require', '请输入债权结束时间'),
//        array('debt_account', 'require', '请输入债权金额'),
//    );
    
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order = 'repay_time, status';
        $total = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__FINANCE_PROJECT__ AS fp ON fp.fp_id=t.fp_id')
                ->field('t.*,company_name,fp_title')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    
    public function addRecords($pay_id, $mpay_info, $rate) {
        $mid = $mpay_info['mid'];
        $total_money = $mpay_info['money'];
        $term = $mpay_info['term'];
        $pay_time = $mpay_info['pay_time'];
        $fp_id = $mpay_info['fp_id'];
        $oid = $mpay_info['oid'];
        $repaymentSchedule = new \Admin\Lib\CalcTool($total_money, $rate, $term);
        $insert_sql = 'INSERT INTO `' . $this->trueTableName . '`(`oid`,`fp_id`,`mpay_id`,`mid`,`principal`,`interest`,`term_rate`,`n_term`,`repay_time`) ';
        $term_rate = bcdiv($rate, 12, 2);
        for ($n_term = 1; $n_term <= $term; $n_term++) {
            $repay_time = \Admin\Lib\RepaymentSchedule::calc_end_time($pay_time, $n_term);
            $repaymentSchedule->firstInterest($n_term);
            $principal = $repaymentSchedule->monthPrincipal();
            $interest = $repaymentSchedule->monthInterest();
            $valuse .= "($oid, $fp_id, $pay_id, $mid, $principal, $interest, $term_rate, $n_term, $repay_time),";
        }
        $valuse = substr($valuse, 0, -1);
        $insert_sql .= 'VALUES ' . $valuse ;
        
        return $this->execute($insert_sql);
    }
   
}

