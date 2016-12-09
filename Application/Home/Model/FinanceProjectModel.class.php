<?php

namespace Home\Model;

use Think\Model;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FinanceProjectModel extends Model {
    
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__FINANCEPRO_WHITE__ AS fw ON fw.fp_id=t.fp_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__FINANCEPRO_WHITE__ AS fw ON fw.fp_id=t.fp_id')
                ->field('t.*,pro_title,pro_no,pro_real_money,pro_step')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
   
}

