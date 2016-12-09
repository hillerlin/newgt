<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class AnnouncementModel extends RelationModel {

    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
    protected $_link = array(
        'role' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'role',
            'mapping_name' => 'role',
            'foreign_key' => 'role_id',
//            'as_fields' => 'role_name',
        ),
        'admin_role' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'class_name' => 'admin_role',
            'foreign_key' => 'admin_id',
            'relation_foreign_key' => 'role_id',
            'relation_table' => 'gt_admin_role',
        ),
    );

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime ASC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->field('t.*,real_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function getDetail($id) {
        $map['id'] = $id;
        $info = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->field('t.*,real_name')
                ->where($map)
                ->find();
        return $info;
    }
}
