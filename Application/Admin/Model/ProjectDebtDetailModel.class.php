<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectDebtDetailModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_id', 'require', '请输入项目ID'),
        array('start_time', 'require', '请输入债权起始时间'),
        array('end_time', 'require', '请输入债权结束时间'),
        array('debt_account', 'require', '请输入债权金额'),
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
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
//                ->join('__PROJECT_DEBT__ AS pd ON pd.debt_all_id=t.debt_all_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
//                ->join('__PROJECT_DEBT__ AS pd ON pd.debt_all_id=t.debt_all_id')
                ->field('t.*,pro_title,pro_no,real_name,pro_real_money')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public static function debtStatusStr($status) {
        $statusStr = array(
            0 => '作废',
            1 => '正常',
            2 => '已还款'
        );
        if (is_numeric($status) && isset($statusStr[$status])) {
            return $statusStr[$status];
        } else {
            return '--';
        }
    }
    
    //获取指定期限的项目信息
    public function getDueList($map) {
        $map['p.finish_status'] = 0;
        $map['p.step_id'] = array('lt', 8);
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
//                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->field('t.*,p.*')
                ->where($map)
                ->select();
        return $list;
    }
   
}

