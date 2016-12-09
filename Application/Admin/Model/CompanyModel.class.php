<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CompanyModel extends BaseModel {
    
    protected $_link = array(
        'admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'admin',
            'foreign_key' => 'admin_id',
//            'as_fields' => 'user_name',
        ),
    );
//    protected $_validate = array(
//        array('pro_title', 'require', '请输入项目标题'),
//        array('pro_account', 'require', '请输入项目标题'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('gt_uid', 'require', '请输入项目标题'),
////        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
//    );
//    
//    protected $_auto = array(
//        array('addtime', 'time', 1, 'function'),
//        array('pro_no', 'getProNo', 1, 'callback')
//    );
    
    public function getProSupplier($pro_id, $company_id = 0) {
        $map['pro_id'] = $pro_id;
        if (!empty($company_id)) {
            $map['t.company_id'] = $company_id;
        }
        $join = '__PROJECT_SUPPLIER__ AS ps ON t.company_id=ps.company_id';
        $list = $this->table($this->trueTableName . ' AS t')->join($join)->where($map)->select();
        return $list;
    }
    
    public function getProCompany($pro_id, $company_id = 0) {
        $map['pro_id'] = $pro_id;
        if (!empty($company_id)) {
            $map['t.company_id'] = $company_id;
        }
        $join = '__PROJECT__ AS ps ON t.company_id=ps.company_id';
        $list = $this->table($this->trueTableName . ' AS t')->join($join)->where($map)->select();
        return $list;
    }
    
    /**
     * 返回需要签订合同的公司信息
     * @param type $pro_id
     * @return array 需要签订合同的公司
     */
    public function getCntractCompany($pro_id) {
        if (D('Project')->isReverseFactoring($pro_id)) {
            $list = $this->getProSupplier($pro_id);
        } else {
            $list = $this->getProCompany($pro_id);
        }
        return $list;
    }
    
    /**
     * 返回需要签订合同的公司信息
     * @param type $pro_id
     * @param type $company_id
     * @return array 需要签订合同的公司
     */
    public function getSpecificCompany($pro_id, $company_id) {
        if (D('Project')->isReverseFactoring($pro_id)) {
            $list = $this->getProSupplier($pro_id, $company_id);
        } else {
            $list = $this->getProCompany($pro_id, $company_id);
        }
        return $list[0];
    }
}

