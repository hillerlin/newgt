<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class RoleModel extends BaseModel {

    protected $_validate = array(
        array('role_name', 'require', '请输入权限组'),
        array('role_name', '', '权限组已存在', 0, 'unique', 1),
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

    public function get_auth_menu($pid, $type = 1) {
        $pid = intval($pid);
        $admin = session('admin');
//        $where['role_id'] = is_array($admin['role_id']) ? array('in', '2,7') : $admin['role_id'];
        
        $select = 'SELECT b.menu_id,menu_name,pid,type,module_name,action_name,class_name,data,remark,sort,status ';
        $from = ' FROM gt_auth AS a,gt_menu as b';
        $where = ' WHERE  a.menu_id = b.menu_id AND status=1';
        if (strlen($admin['role_id']) > 1) {
            $where .= " AND role_id in({$admin['role_id']})";
        } else {
            $where .= " AND role_id={$admin['role_id']}";
        }
        if ($type >= 0 ) {
            $where .= " and type= $type ";
        }
        $order = ' ORDER BY sort ASC';
//        var_dump($select.$from.$where);exit;
//        $menu = $this->relation('auth')->where('role_id in (2,7)')->find();
        $sql = $select . $from . $where . $order;
        $menu = $this->query($sql);
        
        foreach ($menu as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array, 'menu_id', 'pid');
        $auth_menu = $tree->get_array($pid);
//        var_dump($auth_menu);
        return $auth_menu;
    }
    
    public function check_auth() {
        $admin = session('admin');
        $where['role_id'] = $admin['role_id'];
        $this->_link['auth']['condition'] = ' module_name="' . CONTROLLER_NAME . '" and action_name="' . ACTION_NAME . '"';
        $select_menu = $this->relation('auth')->where($where)->find();
        return $select_menu['auth'];
    }

    //检测操作
    public function check_exist($condition) {
        return $this->where($condition)->count();
    }
    //列出所有管理员
    public function listName($page = 1, $pageSize = 30, $map = ''){
        $total=M('Admin')
            ->alias('a')
            ->field(array('a.admin_id,a.role_id,a.real_name,r.role_name'))
            ->join('LEFT JOIN __ROLE__ AS r ON a.role_id=r.role_id')
            ->where($map)
            ->count();
        $list = M('Admin')
            ->alias('a')
            ->field(array('a.admin_id,a.role_id,a.real_name,r.role_name'))
            ->join('LEFT JOIN __ROLE__ AS r ON a.role_id=r.role_id')
            ->where($map)
            ->page($page,$pageSize)
            ->select();
        return array('total' => $total, 'list' => $list);
    }
}
