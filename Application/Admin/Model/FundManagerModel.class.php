<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class FundManagerModel extends BaseModel {

    protected $_validate = array(
        array('real_name', 'require', '请输入客户经理姓名'),
    );
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
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
//        'admin_role' => array(
//            'mapping_type' => self::HAS_MANY,
//            'class_name' => 'admin_role',
//            'foreign_key' => 'admin_id',
//            'mapping_name' => 'admin_role',
//            'mapping_fields' => 'role_id'
//        )
    );

    public function check_passowrd($value) {
        $value = $value ? md5($value) : $value;
        return $value;
    }

    //检测操作
    public function check_exist($condition) {
        return $this->where($condition)->find();
    }

    public function after_login($admin_id) {
        if (!$admin_id) {
            return false;
        }
        $this->where(array('admin_id' => $admin_id))->save(array('last_login_time' => time(), 'last_login_ip' => get_client_ip()));
        $this->where(array('admin_id' => $admin_id))->setInc('login_times', 1);
    }
    
    public function getLists($page = 1, $pageSize = 10, $map = '', $order = 't.add_time ASC',$special='') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=t.branch_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=t.branch_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fbb ON fbb.branch_id=t.branch_ch_id')
                ->field('t.*,fb.branch_name as branch_name,fbb.branch_name as branch_ch_name')
                ->where($map) 
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);

    }
    
    public function getExecutors($executor) {
        $map = '';
        if (!empty($executor['dp_id'])) {
            $map = 'dp_id IN(' . $executor['dp_id'] . ')';
        }
        if (!empty($executor['role_id'])) {
            $role_id_in = ' t.role_id IN(' . $executor['role_id'] . ')';
            $map .= empty($map) ? $role_id_in : ' OR ' . $role_id_in;
        }
        if (!empty($executor['admin_id'])) {
            $admin_id_in .= ' admin_id IN(' . $executor['admin_id'] . ')';
            $map .= empty($map) ? $admin_id_in : ' OR ' . $admin_id_in;
        }
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ROLE__ AS r ON r.role_id=t.role_id')
                ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dp_id')
                ->field('admin_id')
                ->where($map)
                ->select();
//        var_dump($this->_sql());exit;
        return $list;
    }
    
    //根据id,获取基金经理信息
    public function getFundManager($fmanager_id, $select = '') {
        $map['fmanager_id'] = $fmanager_id;
        $selects = 't.*,fm1.branch_name as branc_name,fm2.branch_name as branch_ch_name';
        $selects .= $select;
        $info = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fm1 ON fm1.branch_id=t.branch_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fm2 ON fm2.branch_id=t.branch_ch_id')
                ->field($selects)
                ->where($map)
                ->find();
        return $info;
    }
    //基金部客户经理资料
    public function funderManager($parentId,$arr)
    {
        static $_arr=array();
        foreach ($arr as $k=>$v)
        {
            if($parentId==$v['pid'])
            {
                $_arr[$k]=$v;
                foreach ($arr as $k1=>$v1)
                {
                    if($v['dept_id']==$v1['pid'])
                    {
                        $_arr[$k]['sub'][]=$v1;
                        $this->funderManager($v1['dept_id'],$arr);
                    }
                }
            }
        }
        return $_arr;
    }

    
}
