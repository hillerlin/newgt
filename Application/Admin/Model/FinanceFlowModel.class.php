<?php

namespace Admin\Model;

class FinanceFlowModel extends BaseModel {

    const OUT = 'out';   //放款
    const IN = 'in';   //收款

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );

    public function addFlow($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $real_time, $remark = '') {
        $data = array(
            'pro_id' => $pro_id,
            'money' => $money,
            'company_id' => $company_id,
            'debt_all_id' => $debt_all_id,
            'type' => $type,
            'bank_id' => $bank_id,
            'remark' => $remark,
            'pay_time' => $real_time
        );
        if ($this->create($data) && $this->add()) {
            return true;
        }
        return false;
    }
    
    /**
     * 插入凭证，与fid关联
     * @param type $fid
     * @param type $list
     * @return boolean 成功or失败
     */
    public function addVoucher($fid, $list, $admin_id) {
        $time = time();
        $dataList = array();
        foreach ($list as & $v) {
            $v['fid'] = $fid;
            $v['addtime'] = $time;
            $v['admin_id'] = $admin_id;
            $dataList[] = $v;
        }
//        var_dump($dataList);exit;
        return D('FinanceVoucher')->addAll($dataList);
    }

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.pay_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__BANK__ AS b ON b.bank_id=t.bank_id')
                ->field("t.*,CASE TYPE WHEN 'out' THEN money ELSE 0 END AS out_money,CASE TYPE WHEN 'in' THEN money ELSE 0 END AS in_money,bank_name,account_name")
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function getSurplusList($page = 1, $pageSize = 30, $map = '', $order = 't.pay_time DESC') {
        $map['has_distribute'] = array('exp', '<`money`');
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__BANK__ AS b ON b.bank_id=t.bank_id')
                ->field("t.*,CASE TYPE WHEN 'out' THEN money ELSE 0 END AS out_money,CASE TYPE WHEN 'in' THEN money ELSE 0 END AS in_money,bank_name,account_name")
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return $list;
    }
    
    public function getDetailList($page = 1, $pageSize = 30, $map = '', $order = 't.pay_time ASC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=t.company_id')
                ->join('__BANK__ AS b ON b.bank_id=t.bank_id')
                ->field('t.*,pro_title,b.bank_name,b.bank_no,b.bank_id,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }

    //流水类型
    public static function getTypeDescribe() {
        $type_describe = array(
            self::OUT => '出款',
            self::IN => '进款',
        );
        return $type_describe;
    }

    public function projectMonth($pro_id) {
        $time = strtotime('first day of this month');
        $in = "'cash_deposit','handling_charge','counseling_fee','intersets','principal')";
        $out = "'financing','back_cash_deposit')";
        $sql = "SELECT b.pro_title,a.pro_id,a.addtime,money,TYPE
            FROM gt_capital_flow a,gt_project b
            WHERE a.ADDTIME>$time AND b.pro_id=a.`pro_id` AND a.`pro_id`=$pro_id AND TYPE IN(";
        $out_list = $this->query($sql . $out);
        $in_list = $this->query($sql . $in);
        return array('out_list' => $out_list, 'in_list' => $in_list);
    }
    
    public function isIncome($type) {
        $type_describe = array(
            self::FINANCING => 0,
            self::CASH_DEPOSIT => 1,
            self::HANDLING_CHARGE => 1,
            self::COUNSELING_FEE => 1,
            self::INTEREST => 1,
            self::PRINCIPAL => 1,
            self::BACK_CASH_DEPOSIT => 0,
            self::OVERDUE_PAY => 1,
        );
        return $type_describe[$type];
    }
    
    public function getProfit($debt_all_id) {
        $sql = "SELECT a.pro_id,
                SUM(CASE TYPE WHEN 'financing' THEN money ELSE 0 END) AS financing,
                SUM(CASE TYPE WHEN 'principal' THEN money ELSE 0 END) AS principal,
                SUM(CASE TYPE WHEN 'interest' THEN money ELSE 0 END) AS interest,
                SUM(CASE TYPE WHEN 'handling_charge' THEN money ELSE 0 END) AS handling_charge,
                SUM(CASE TYPE WHEN 'counseling_fee' THEN money ELSE 0 END) AS counseling_fee,
                SUM(CASE TYPE WHEN 'cash_deposit' THEN money ELSE 0 END) AS cash_deposit,
                SUM(CASE TYPE WHEN 'overdue_pay' THEN money ELSE 0 END) AS overdue_pay
                FROM gt_capital_flow AS a
                WHERE a.debt_all_id = $debt_all_id ";
        $data = $this->query($sql);
        return $data;
    }

}
