<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectDebtModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_id', 'require', '请输入项目ID'),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
    
    protected $_link = array(
        'admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'admin',
            'foreign_key' => 'admin_id',
            'as_fields' => 'real_name',
        ),
        'project' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'project',
            'mapping_name' => 'project',
            'foreign_key' => 'pro_id',
//            'as_fields' => 'user_name',
        ),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order = 'status asc,t.addtime DESC';
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->join('__PROJECT_CONTRACT__ AS pc ON t.contract_id=pc.contract_id')
                ->field('t.*,pro_title,pro_no,real_name,company_name,deadline')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

        return array('total' => $total, 'list' => $list);
    }
    
    public function addDebt($data) {
        $this->create($data);
        return $this->add();
    }
   
    //获取逾期列表
    public function overdueList() {
        $curent_time = 1497110401;//strtotime('midnight');
//        var_dump(date('Y-m-d', $time));
        $map['t.status'] = 1;
        $map['deadline'] = array(array('lt', $curent_time));
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->field('t.*,pc.*')
                ->where($map)
//                ->group('t.debt_all_id')
                ->select();
        return $list;
    }
    
    //
    public function upOverdueFee($rp_id, $data) {
        return $this->updateByPk($rp_id, $data);
    }
    
    public function getDebtAllInfo($debt_all_id) {
        $map['debt_all_id'] = $debt_all_id;
        $debt_info = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->field('*')
                ->where($map)
                ->find();
        return $debt_info;
    }
    
    /**
     * 还款完成
     * @param type $debt_all_id
     */
    public function repaymentDone($debt_all_id, $real_pay_time) {
        $map['debt_all_id'] = $debt_all_id;
        $data['real_pay_time'] = strtotime($real_pay_time);
        $data['status'] = 2;
        return $this->where($map)->save($data);
    }
    
    public function findContractByDebtId($debt_all_id) {
        $map['debt_all_id'] = $debt_all_id;
        $contrac_info = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->field('pc.*')
                ->where($map)
                ->find();
        return $contrac_info;
    }
    
    //获取指定期限的项目信息
    public function getDueList($map) {
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->field('t.*,p.*')
                ->where($map)
                ->select();
        return $list;
    }
    
    //获取项目收益
    public function getProfit($pro_id) {
        $map['t.pro_id'] = $pro_id;
        $list = $this->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
//                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->field('t.*,pro_title,pc.cash_deposit')
                ->where($map)
                ->select();
        $this->format($list);
        return $list;
    }
    
    public function format(& $list) {
        $format_unit = 10000;
        bcscale(10);
        foreach ($list as & $v) {
            $result = D('CapitalFlow')->getProfit($v['debt_all_id']);
            $profit = $result[0];
            $v['handling_charge'] = bcdiv($profit['handling_charge'] - $profit['back_handling_charge'], $format_unit);
            $v['counseling_fee'] = bcdiv($profit['counseling_fee'], $format_unit);
            $v['debt_account'] = bcdiv($v['debt_account'], $format_unit);
            $v['cash_deposit'] = bcdiv($profit['cash_deposit'], $format_unit);
            $v['real_loan'] = bcsub($v['debt_account'], $v['cash_deposit']);//除开保证金的本金
            $v['interest'] = bcdiv(($profit['interest'] + $profit['overdue_pay'] - $profit['back_interest']), $format_unit);
            $profit_fee = $v['interest'] + $v['counseling_fee'] + $v['handling_charge'] ;
            
            $v['sum'] = $profit_fee ;
            $loan_time = new \DateTime(date('Y-m-d', $v['real_time']));
            $pay_time = new \DateTime(date('Y-m-d', $v['real_pay_time']));
            $diff = $pay_time->diff($loan_time);
            $v['days'] = $diff->format('%a');
            $v['profit_rate'] = bcmul(bcdiv(bcdiv($profit_fee, $v['real_loan']), $v['days']), 365);
            $principal += $v['debt_account'];
            $v['real_time'] = date('Y-m-d', $v['real_time']);
            $v['real_pay_time'] = date('Y-m-d', $v['real_pay_time']);
        }
        foreach($list as & $v) {
            $v['part_sum_profit'] = bcmul(bcdiv($v['debt_account'], $principal), $v['profit_rate']);
        }
    }
    
    public function getDebtProfit($debt_all_id) {
        $format_unit = 10000;
        $map['debt_all_id'] = $debt_all_id;
        $detb_info = $this->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->field('t.*,pro_title')
                ->find();
        $result = D('CapitalFlow')->getProfit($debt_all_id);
//        var_dump($result);
        $profit = $result[0];
        $real_pay_time = $detb_info['real_pay_time'] > 0 ? $detb_info['real_pay_time'] : time();
        $v['handling_charge'] = bcdiv($profit['handling_charge'] - $profit['back_handling_charge'], $format_unit, 6);
        $v['counseling_fee'] = bcdiv($profit['counseling_fee'], $format_unit, 6);
        $v['debt_account'] = bcdiv($detb_info['debt_account'], $format_unit, 6);
        $cash_deposit = bcdiv($profit['cash_deposit'], $format_unit, 6);
        $v['debt_account'] = bcsub($v['debt_account'], $cash_deposit, 6);
        $v['interest'] = bcdiv(($profit['interest'] + $profit['overdue_pay'] - $profit['back_interest']), $format_unit, 6);
        $profit_fee = $v['interest'] + $v['counseling_fee'] + $v['handling_charge'] ;
//        var_dump($v);exit;
        $v['sum'] = $profit_fee ;
        $loan_time = new \DateTime(date('Y-m-d', $detb_info['real_time']));
        $pay_time = new \DateTime(date('Y-m-d', $real_pay_time));
        $diff = $pay_time->diff($loan_time);
        $v['days'] = $diff->format('%a');
        $v['profit_rate'] = bcdiv(bcdiv(bcmul($profit_fee, 365, 10), $v['debt_account'], 10), $v['days'], 10);
        $v['real_time'] = date('Y-m-d', $detb_info['real_time']);
        $v['real_pay_time'] = date('Y-m-d', $real_pay_time);
        $v['pro_title'] = $detb_info['pro_title'];
        return $v;
    }
}

