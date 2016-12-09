<?php

namespace Home\Model;
use Think\Model;
class FinanceOrderModel extends Model{
    
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );

    public function sumBuyMoney($fp_id, $mid) {
        $map['fp_id'] = $fp_id;
        $map['mid'] = $mid;
        $sum_money = $this->where($map)->sum('money');
        return empty($sum_money) ? 0 : $sum_money;
    }

}