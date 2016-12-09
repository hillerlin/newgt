<?php

namespace Admin\Model;

class CapitalFlowModel extends BaseModel {

    const FINANCING = 'financing';   //放款
    const CASH_DEPOSIT = 'cash_deposit';   //保证金
    const HANDLING_CHARGE = 'handling_charge';   //手续费
    const COUNSELING_FEE = 'counseling_fee';   //咨询费
    const INTERSETS = 'intersets';   //利息
    const PRINCIPAL = 'principal';   //本金
    const BACK_CASH_DEPOSIT = 'back_cash_deposit';   //退回保证金

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

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__BANK__ AS b ON b.bank_id=t.bank_id')
                ->field('t.*,pro_title,bank_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
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

    public function monthReport($month) {
        $time = strtotime('first day of this month');
//        var_dump(date('Y-m-d', $time));
        $end_time = strtotime("y-$month last day");
        $this->where($map)->getField($field);
        $sql = "SELECT b.pro_title,a.pro_id,FROM_UNIXTIME(a.`addtime`,'%Y%m%d') days,a.addtime,
                SUM(
                CASE TYPE 
                WHEN 'financing' THEN money 
                WHEN 'back_cash_deposit' THEN money 
                ELSE 0 END) AS out_money,
                SUM(
                CASE TYPE 
                WHEN 'cash_deposit' THEN money 
                WHEN 'cash_deposit' THEN money 
                WHEN 'counseling_fee' THEN money 
                WHEN 'intersets' THEN money 
                WHEN 'principal' THEN money 
                ELSE 0 END) AS in_money
                FROM gt_capital_flow a,gt_project b
                WHERE a.ADDTIME>$time AND b.pro_id=a.`pro_id`
                GROUP BY a.pro_id,days ";
        $list = $this->query($sql);
        return $list;
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

}
