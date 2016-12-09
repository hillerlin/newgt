<?php

namespace Admin\Model;
use Admin\Lib\CalcTool;

class LoanFormModel extends BaseModel {

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );

    public function addFlow($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $remark = '') {
        $data = array(
            'pro_id' => $pro_id,
            'money' => $money,
            'company_id' => $company_id,
            'debt_all_id' => $debt_all_id,
            'type' => $type,
            'bank_id' => $bank_id,
            'remark' => $remark,
        );
        if ($this->create($data) && $this->add()) {
            return true;
        }
        return false;
    }

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime ASC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=t.company_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=p.admin_id')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,wp.*,contract_no,a.real_name as pmd_real_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
       // var_dump($this->_sql());
        return array('total' => $total, 'list' => $list);
    }
    
    public function waitAudit($page = 1, $pageSize = 30, $map = '', $order = 't.addtime ASC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->where($map)
                ->count();
//        var_dump($this->_sql());exit;
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=p.risk_admin_id')
                ->join('__COMPANY__ AS c ON c.company_id=t.company_id')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,wp.*,a1.real_name as pmd_name,a2.real_name as rcd_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    //获取审核信息
    public function auditInfo($loan_id) {
        $map['loan_id'] = $loan_id;
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=p.admin_id')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=t.company_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,pro_no,wp.*,pc.*,a.real_name as pmd_name')
                ->where($map)
                ->find();
        $list['handling_charge_bank'] = D('Bank')->getBank($list['handling_charge_bank_id']);
        $list['cash_deposit_bank'] = D('Bank')->getBank($list['cash_deposit_bank_id']);
        $list['repurchase_rate_bank'] = D('Bank')->getBank($list['repurchase_rate_bank_id']);
        $list['counseling_fee_bank'] = D('Bank')->getBank($list['counseling_fee_bank_id']);
        $list['calc_handling_charge'] = CalcTool::calc($list['money'], $list['handling_charge'], $list['term']);
        $list['calc_repurchase_rate'] = CalcTool::calc($list['money'], $list['repurchase_rate'], $list['term']);
        $list['calc_counseling_fee'] = CalcTool::calc($list['money'], $list['counseling_fee'], $list['term']);
        $list['calc_cash_deposit'] = CalcTool::calc($list['money'], $list['cash_deposit'], $list['term']);
//        $list['loan_nums'] = $this->loanNums($list['pro_id']);
        return $list;
    }
    
    //获取审核信息
    public function applyInfo($loan_id) {
        $map['loan_id'] = $loan_id;
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=p.admin_id')
                ->join('LEFT JOIN __PROJECT_CONTRACT__ AS pc ON pc.contract_id=t.contract_id')
                ->join('__COMPANY__ AS c ON c.company_id=t.company_id')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,pro_no,pc.*,a.real_name as pmd_name')
                ->where($map)
                ->find();
        $list['handling_charge_bank'] = D('Bank')->where('bank_id='.$list['handling_charge_bank_id'])->find();
        $list['cash_deposit_bank'] = D('Bank')->where('bank_id='.$list['cash_deposit_bank_id'])->find();
        $list['repurchase_rate_bank'] = D('Bank')->where('bank_id='.$list['repurchase_rate_bank_id'])->find();
        $list['counseling_fee_bank'] = D('Bank')->where('bank_id='.$list['counseling_fee_bank_id'])->find();
        $list['calc_handling_charge'] = CalcTool::calc($list['money'], $list['handling_charge'], $list['term']);
        $list['calc_repurchase_rate'] = CalcTool::calc($list['money'], $list['repurchase_rate'], $list['term']);
        $list['calc_counseling_fee'] = CalcTool::calc($list['money'], $list['counseling_fee'], $list['term']);
        $list['calc_cash_deposit'] = CalcTool::calc($list['money'], $list['cash_deposit'], $list['term']);
        $list['loan_nums'] = $this->loanNums($list['pro_id']);
        return $list;
    }
    
    //获取放款次数
    public function loanNums($pro_id) {
        $map['pro_id'] = $pro_id;
        $map['wp.current_node_index'] = array('LT', 9);
        return $this->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->where($map)
                ->count();
    }
    
    public function findProInfoByPk($loan_id) {
        $map['loan_id'] = $loan_id;
        $result = $this->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->find();
        return $result;
    }
    
    public function unloan($map) {
        $map['wp.current_node_index'] = 10;
//        $map['t.loan_type'] = 1;
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__COMPANY__ AS c ON c.company_id=t.company_id')
                ->join('__ADMIN__ AS a ON a.admin_id=p.admin_id')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.loan_id')
                ->field('t.*,pro_title,company_name,pro_no,pro_real_money,pro_account,wp.*,a.real_name as pmd_name')
                ->where($map)
                ->select();
        return array('total' => $total, 'list' => $list);
    }

    //流水类型
    public static function getTypeDescribe() {
        $type_describe = array(
            self::FINANCING => '融资款',
            self::CASH_DEPOSIT => '保证金',
            self::HANDLING_CHARGE => '手续费',
            self::COUNSELING_FEE => '咨询费',
            self::INTERSETS => '利息',
            self::PRINCIPAL => '本金',
            self::BACK_CASH_DEPOSIT => '保证金退回',
        );
        return $type_describe;
    }


}
