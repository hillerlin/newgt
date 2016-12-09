<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectBackModel extends BaseModel {
    
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
    
    protected $_link = array(
        'linker' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'linker',
            'foreign_key' => 'pro_linker',
//            'as_fields' => 'user_name',
        ),
        'executor' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'executor',
            'foreign_key' => 'executor_id',
//            'as_fields' => 'user_name',
        ),
        'sign_man' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'sign_man',
            'foreign_key' => 'sign_man_id',
//            'as_fields' => 'user_name',
        ),
    );
    
    public function waitDone($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order .= 't.addtime DESC';
        $map['t.step_pid'] = 4;
        $map['t.pro_step'] = 2;
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('LEFT JOIN __WORKFLOW_ROLE__ AS wr ON wr.step_id=w.step_id and wr.step_pid=w.step_pid')
                ->where($map)
                ->group('pro_id')
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=t.pro_linker')
                ->join('LEFT JOIN __COMPANY__ AS cp ON t.company_id=cp.company_id')
//                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
                ->field('t.*,pro_title,pro_no,a1.real_name as pmd_name,a2.real_name as pro_linker_name,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('pro_id')
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
  
}

