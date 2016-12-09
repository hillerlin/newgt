<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class MemberModel extends BaseModel {
    
    protected $_validate = array(
        array('member_name', 'require', '请输入登录名'),
        array('member_name', 'only', '登录名已存在，请重新输入', '','', self::MODEL_UPDATE),
        array('member_password', 'require', '请输入登录密码'),
        array('company_name', 'require', '请输入公司名称'),
//        array('debt_account', 'require', '请输入债权金额'),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('member_password', 'check_passowrd', 3, 'callback'),
    );
    
    public function after_login($mid) {
        if (!$mid) {
            return false;
        }
        $this->where(array('mid' => $mid))->save(array('last_login_time' => time(), 'last_login_ip' => get_client_ip()));
        $this->where(array('mid' => $mid))->setInc('login_times', 1);
    }
    
    public function check_passowrd($value) {
        $value = $value ? md5($value) : $value;
        return $value;
    }
   
}

