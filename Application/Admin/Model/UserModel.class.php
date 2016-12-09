<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class UserModel extends RelationModel{
    
    protected $_validate = array(
        
    );
    protected $_auto = array(
       
    );
    protected $_link = array(
        'dept' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'department',
            'mapping_name' => 'dept',
            'foreign_key' => 'dept_id',
            'as_fields' => 'department',
        ),
    );
    public function testName(){
        return "user model";
    }
    
    //检查用户是否存在
    public function checkUserExist($uid) {
        $map['uid'] = $uid;
        if ($this->where($map)->count() > 0) {
            return true;
        }
        return false;
    }

}