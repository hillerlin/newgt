<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FinanceProjectModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_id', 'require', '请输入项目ID'),
        array('start_time', 'require', '请输入债权起始时间'),
        array('end_time', 'require', '请输入债权结束时间'),
        array('debt_account', 'require', '请输入债权金额'),
    );
    
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
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
        'white' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'class_name' => 'member',
            'foreign_key' => 'fp_id',
            'relation_foreign_key' => 'mid',
            'relation_table' => 'gt_financepro_white',
//            'as_fields' => 'real_name',
        ),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->field('t.*,pro_title,pro_no,pro_real_money,pro_step')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
   
}

