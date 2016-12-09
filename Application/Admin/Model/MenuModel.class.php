<?php

namespace Admin\Model;

use Think\Model\RelationModel;

class MenuModel extends RelationModel {

    protected $_validate = array(
        array('menu_name', 'require', '菜单名不能为空'),
            //array('menu_name','','菜单已存在',0,'unique',1),
    );
    protected $_auto = array(
        array('sort', '_auto_sort', 1, 'callback'),
    );
    
    protected function _auto_sort($value) {
        $value = $value ? $value : 255;
        return $value;
    }

    //获取后台菜单
    public function get_menu($pid, $with_self = false) {
        $pid = intval($pid);
        $condition = array('pid' => $pid);
        if ($with_self) {
            $condition['id'] = $pid;
            $condition['_logic'] = 'OR';
        }
        $map['_complex'] = $condition;
        $map['display'] = 1;
        $map['type'] = 1;
        $menu = $this->where($map)->order('sort')->select();
        return $menu;
    }

    public function get_all_menu($pid, $type = 1) {
        $pid = intval($pid);
        $admin = session('admin');
        $where['type'] = 1;
        if ($type >= 0) {
            $where['status'] = $type;
        }
        $menu = $this->where($where)->order('sort')->select();
        foreach ($menu as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $menu = $tree->get_array($pid);
        return $menu;
    }

    //检测操作
    public function check_exist($condition) {
        return $this->where($condition)->find();
    }

}
