<?php

namespace Admin\Model;

class FinanceOrderModel extends BaseModel{
    
    protected $_validate = array(
        array('rate', 'check_rate', '请输入正确的利率', 0, 'callback'),
    );
    
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    
    public function setConfirmTime() {
        if ($this->status == 1) {
            $this->confirm_time = time();
        } else {
            $this->confirm_time = 0;
        }
    }
    
    public function check_rate($rate) {
        if (empty($rate)) {
            return false;
        }
        if (!is_numeric($rate)) {
            return false;
        }
        return true;
    }

    public function sumBuyMoney($fp_id, $mid) {
        $map['fp_id'] = $fp_id;
        $map['mid'] = $mid;
        return $this->where($map)->sum('money');
    }
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__MEMBER__ AS m ON m.mid=t.mid')
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
    
    //机构汇总报表
    public function getChartList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        
        $pay_list = D('MpayRecord')->field('oid,MIN(pay_time)')->where('status=2')->group('oid'); //这样查有个问题，如果打款日期都为同一天，只会拿出一条记录
//        $oids = array_column('oid', $pay_list);
//        $map = array('in', $oids);
        $map['mr.status'] = 2;
        $order = ' first_pay_time';
        $total = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__MPAY_RECORD__ AS mr ON mr.oid=t.oid')
                ->group('t.oid')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__FINANCE_PROJECT__ AS fp ON fp.fp_id=t.fp_id')
                ->join('__MPAY_RECORD__ AS mr ON mr.oid=t.oid')
                ->field('t.*,company_name,fp_title,MIN(mr.pay_time) as first_pay_time')
                ->group('t.oid')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        foreach ($list as & $val) {
            $repay_time = D('RepaymentSchedule')->where('principal > 0 AND oid ='.$val['oid'])->getField('repay_time');
            $val['first_principal_time'] = $repay_time;
        }
        $principal_time = array();
        foreach ($list as $v) {
            $principal_time[] = $v['first_principal_time'];
        }
        
        array_multisort($principal_time, SORT_ASC, $list);
//        var_dump($list);
//        var_dump($principal_time);exit;
        return array('total' => $total, 'list' => $list);
    }

}