<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectDistributeModel extends BaseModel {
    
    protected $_validate = array(
//        array('pro_title', 'require', '请输入项目标题'),
//        array('pro_account', 'require', '请输入项目标题'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('gt_uid', 'require', '请输入项目标题'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
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
//            'as_fields' => 'user_name',
        ),
        'risk_admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'risk_admin',
            'foreign_key' => 'risk_admin_id',
//            'as_fields' => 'user_name',
        ),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 'addtime DESC') {
        $map['status'] = 0;
        $total = $this
                ->where($map)
                ->count();
        $list = $this
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function isDistribute($pro_id, $admin_id) {
        $map['pro_id'] = $pro_id;
        $map['admin_id'] = $admin_id;
        $map['status'] = 1;
        if ($this->where($map)->count() > 0) {
            return true;
        }
        return false;
    }
}

