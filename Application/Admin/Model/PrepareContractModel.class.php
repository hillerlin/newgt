<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PrepareContractModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_id', 'require', '请先选择项目'),
//        array('pro_account', 'require', '请输入项目标题'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('gt_uid', 'require', '请输入项目标题'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
//        array('pro_no', 'getProNo', 1, 'callback'),
    );
    
    /**
     * 现在只有两种违约条款，故只需两位来标示；先向左移动$type-1位，再与10按位与如果为真，说明是$type指定的类型
     * @param type $demurrageType 数据库存的十进制数
     * @param type $type 第几种方法 1,2
     * @return boolean
     */
    public function demurrageRateType($demurrageType, $type) {
        $xor = '10';
        return ($demurrageType << ($type - 1) & $xor) == true;
    }
    
    public function getContract($pro_id, $company_id) {
        $map['pro_id'] = $pro_id;
        $map['company_id'] = $company_id;
        return $this->where($map)->find();
    }
    
    public function isPreContract($pro_id, $company_id) {
        $map['pro_id'] = $pro_id;
        $map['company_id'] = $company_id;
        return $this->where($map)->count();
    }
    
    public function superviseType() {
        return array(
            'gongguan' => '共管',
            'jianguan' => '监管',
            'yunzhanghu' => '云账户',
        );
    }
}

