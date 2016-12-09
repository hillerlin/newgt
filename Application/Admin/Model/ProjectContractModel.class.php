<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectContractModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_id', 'require', '请先选择项目'),
//        array('pro_account', 'require', '请输入项目标题'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('gt_uid', 'require', '请输入项目标题'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
//        array('pro_no', 'getProNo', 1, 'callback'),
    );
    
    /**
     * 现在只有两种违约条款，故只需两位来标示；先向左移动$type-1位，再与10按位与如果为真，说明是$type指定的类型
     * @param type $demurrageType 数据库存的十进制数
     * @param type $type 第几种方法 1,2
     * @return boolean
     */
    public static function demurrageRateType($demurrageType, $type) {
        $xor = '10';
        return ($demurrageType << ($type - 1) & $xor) == true;
    }
    
    public function getContract($pro_id, $company_id) {
        $map['pro_id'] = $pro_id;
        $map['company_id'] = $company_id;
        return $this->where($map)->find();
    }
    
    public function isPreContract($pro_id, $company_id) {
        $map['pro_id'] = $pro_id;
        $map['company_id'] = $company_id;
        return $this->where($map)->count();
    }
    
    public function projectContract($page = 1, $pageSize = 30, $map = '', $order = '') {
        $map['p.step_pid'] = array('EGT', 4);
       
        $total = $this
                ->table('gt_prepare_contract AS prec')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS proc ON prec.pro_id=proc.`pro_id` AND prec.company_id=proc.`company_id`')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=prec.pro_id')
                ->where($map)
//                ->group('contract_id')
                ->count();
        $list = $this
                ->table('gt_prepare_contract AS prec')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS proc ON prec.pro_id=proc.`pro_id` AND prec.company_id=proc.`company_id`')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=prec.pro_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=prec.`company_id`')
                ->field('proc.*,prec.pro_id,prec.company_id,pro_title,pro_no,company_name,prec.pre_contract_id')
                ->where($map)
//                ->group('contract_id')
                ->page($page, $pageSize)
                ->select();
//        var_dump($this->_sql());exit;
        return array('total' => $total, 'list' => $list);
    }
    
    public function selectContract($page = 1, $pageSize = 30, $map = '', $order = '') {
        $map['p.step_pid'] = array('EGT', 4);
       
        $total = $this
                ->table('gt_prepare_contract AS prec')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS proc ON prec.pro_id=proc.`pro_id` AND prec.company_id=proc.`company_id`')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=prec.pro_id')
                ->where($map)
                ->count();
//        var_dump($this->_sql());exit;
        $list = $this
                ->table('gt_prepare_contract AS prec')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS proc ON prec.pro_id=proc.`pro_id` AND prec.company_id=proc.`company_id`')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=prec.pro_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=prec.`company_id`')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
                ->field('proc.*,prec.pro_id,prec.company_id,pro_title,pro_no,company_name,real_name')
                ->where($map)
                ->group('prec.pre_contract_id')
                ->page($page, $pageSize)
                ->select();
//        var_dump($this->_sql());exit;
        if (!empty($list)) {
            foreach ($list as & $val) {
                $val['loan_nums'] = D('LoanForm')->loanNums($val['pro_id']);
            }
        }
        return array('total' => $total, 'list' => $list);
    }
    
    public function formatData($data) {
        foreach ($data as & $val) {
            $val['term_type'] = self::termType($val['term_type']);
            $val['interest_type'] = self::interestType($val['interest_type']);
            $val['demurrage_rate_type1'] = self::demurrageRateType($val['demurrage_rate_type'], 1);
            $val['demurrage_rate_type2'] = D('PrepareContract')->demurrageRateType($val['demurrage_rate_type'], 2);
        }
        return $data;
    }
    
    public static function termType($term_type) {
        $type = array(
            'd' => '天',
            'm' => '月',
        );
        return isset($type[$term_type]) ? $type[$term_type] : '';
    }
    
    public static function interestType($interest_type) {
        $type = array(
            'day' => '天',
            'month' => '月',
            'quarter' => '季',
            'half_year' => '半年',
//            'year' => '年',
            'once' => '一次性',
        );
        return isset($type[$interest_type]) ? $type[$interest_type] : '';
    }
    
    public function interestTypeDesc() {
        return $type = array(
            'day' => '天',
            'month' => '月',
            'quarter' => '季',
            'half_year' => '半年',
//            'year' => '年',
            'once' => '一次性',
        );
    }
}

