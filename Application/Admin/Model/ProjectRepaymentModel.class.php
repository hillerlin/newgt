<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectRepaymentModel extends BaseModel {
    
//    protected $_validate = array(
//        array('pro_id', 'require', '请输入项目ID'),
//        array('start_time', 'require', '请输入债权起始时间'),
//        array('end_time', 'require', '请输入债权结束时间'),
//        array('debt_account', 'require', '请输入债权金额'),
//    );
    
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 'real_repay_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__MEMBER__ AS m ON m.mid=t.mid')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->field('t.*,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function addRecords($pay_id, $mid, $total_money, $term, $rate, $pay_time) {
        $repaymentSchedule = new \Admin\Lib\CalcTool($total_money, $rate, $term);
        $insert_sql = 'INSERT INTO `' . $this->trueTableName . '`(`mpay_id`,`mid`,`principal`,`interest`,`term_rate`,`n_term`,`repay_time`) ';
        $term_rate = bcdiv($rate, 12, 2);
        for ($n_term = 1; $n_term <= $term; $n_term++) {
            $repay_time = \Admin\Lib\RepaymentSchedule::calc_end_time($pay_time, $n_term);
            $repaymentSchedule->firstInterest($n_term);
            $principal = $repaymentSchedule->monthPrincipal();
            $interest = $repaymentSchedule->monthInterest();
            $valuse .= "($pay_id, $mid, $principal, $interest, $term_rate, $n_term, $repay_time),";
        }
        $valuse = substr($valuse, 0, -1);
        $insert_sql .= 'VALUES ' . $valuse ;
        
        return $this->execute($insert_sql);
    }
   
    /**
     * 统计指定类型的还款额度
     * @param type $debt_all_id
     * @param type $type
     * @return int
     */
    public function sumType($debt_all_id, $type) {
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = $type;
        $sum = $this->where($map)->sum('repay_money');
        if (empty($sum)) {
            $sum = 0;
        }
        return $sum;
    }
    
    public function lastRepay($debt_all_id, $type) {
        $map['debt_all_id'] = $debt_all_id;
        $map['type'] = $type;
        $real_repay_time = $this->where($map)->max('real_repay_time');
        if (empty($real_repay_time)) {
            $real_repay_time = 0;
        }
        return $real_repay_time;
    }
}

