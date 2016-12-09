<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class AdminModel extends BaseModel
{

    protected $_validate = array(
        array('admin_name', 'require', '请输入管理员账号'),
        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('admin_password', 'check_passowrd', 3, 'callback'),
        array('add_time', 'time', 1, 'function'),
        array('admin_password', '', 2, 'ignore'),
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

    public function check_passowrd($value)
    {
        $value = $value ? md5($value) : $value;
        return $value;
    }

    //检测操作
    public function check_exist($condition)
    {
        return $this->where($condition)->find();
    }

    public function after_login($admin_id)
    {
        if (!$admin_id) {
            return false;
        }
        $this->where(array('admin_id' => $admin_id))->save(array('last_login_time' => time(), 'last_login_ip' => get_client_ip()));
        $this->where(array('admin_id' => $admin_id))->setInc('login_times', 1);
    }

    public function getList($page = 1, $pageSize = 30)
    {
        $admin = session('admin');
        $role_id = $admin['role_id'];
        $select = 'SELECT * ';
        $count = 'SELECT COUNT(*) as total';
        $from = ' FROM gt_admin AS a,gt_role as b';
        $where = ' WHERE b.role_id=a.role_id';
        if ($admin['is_supper'] == 0) {
            $where .= ' AND b.pid=' . $role_id;
        }
        $re = $this->query($count . $from . $where);
        $result['total'] = empty($re[0]['total']) ? 0 : $re[0]['total'];
        $limit = ($page - 1) * $pageSize;
        $limit = " LIMIT $limit,$pageSize";
        $result['list'] = $this->query($select . $from . $where . $limit);
        return $result;
    }

    public function setLinkCondition($relation, $param)
    {
        $this->_link[$relation]['condition'] = $param;
    }

    public function getLists($page = 1, $pageSize = 30, $map = '', $order = 't.add_time ASC', $special = '')
    {
        if (in_array($special, $this->isLower())) {
            $deparInfo = M('Department')->select();
            $listId = $this->recuId($special, $deparInfo);
            array_push($listId, $special);
            $map['t.dp_id'] = array('IN', implode(',', $listId));
            $list = $this->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ROLE__ AS r ON r.role_id=t.role_id')
                ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dp_id')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

        } else {
            $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ROLE__ AS r ON r.role_id=t.role_id')
                ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dp_id')
                ->field('t.*,role_name,department')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();

        }
        $total = $this
            ->table($this->trueTableName . ' AS t')
            ->join('LEFT JOIN __ROLE__ AS r ON r.role_id=t.role_id')
            ->where($map)
            ->count();
        return array('total' => $total, 'list' => $list);

    }

    //判断是否有二级分类
    public function isLower()
    {
        $listInfo = M('Department')->field('pid')->select();
        $result = array();
        foreach ($listInfo as $k => $v) {
            array_push($result, $v['pid']);
        }
        return array_unique($result);

    }

    public function getExecutors($executor)
    {
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

    public function getAdminInfo($admin_id)
    {
        $map['admin_id'] = $admin_id;
        $info = $this
            ->table($this->trueTableName . ' AS t')
            ->join('LEFT JOIN __ROLE__ AS r ON r.role_id=t.role_id')
            ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dp_id')
            ->where($map)
            ->find();
        return $info;
    }

    //递归查找一级大部门的子ID    //递归查找资金客户关系
    public function recuId($parentId, $arr)
    {
        static $_arr = array();
        foreach ($arr as $k => $v) {
            if (intval($v['pid']) == intval($parentId)) {
                array_push($_arr, $v['dept_id']);
                $this->recuId($v['dept_id'], $arr);
            }
        }
        return $_arr;
    }

    //根据id获取基金部成员
    public function finderInfoById($idList, $page = 1,$pageSize = 30, $order = 't.add_time ASC' )
    {
        $map['t.dp_id'] = array('IN', $idList);

        $list = $this->table($this->trueTableName . " AS t")
            ->join("LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dp_id")
            ->field("t.admin_id as admin_id,t.real_name as real_name,t.dp_id as dept_id,d.department")
            ->where($map)
            ->page($page, $pageSize)
            ->order($order)
            ->select();
        $count = $this->table($this->trueTableName." AS t")
            ->where($map)
            ->count();
        return array('total'=>$count,'list'=>$list);
    }

}
