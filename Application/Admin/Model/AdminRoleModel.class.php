<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class AdminRoleModel extends RelationModel {

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_link = array(
        'auth' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'class_name' => 'menu',
            'foreign_key' => 'role_id',
            'relation_foreign_key' => 'menu_id',
            'relation_table' => 'gt_auth',
        ),
        'admin' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'admin',
            'foreign_key' => 'role_id',
            'mapping_name' => 'admin',
        ),
    );

}
