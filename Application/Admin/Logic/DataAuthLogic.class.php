<?php

namespace Admin\Logic;

class DataAuthLogic {

    /**
     * 获取数据权限列表
     * @return array
     */
    public function dataAuthList($role_id) {
        $map['role_id'] = $role_id;
        $data_list = D('Data')->select();
        $data_auth = D('DataAuth')->where($map)->select();
        $data_list_id = array_switch_key($data_list, 'id');    //转换成以id为键值的数组
        
        if (!empty($data_auth)) {
            $data_auth_id = array_switch_key($data_auth, 'data_id');    //转换成以数据id为键值的数组
        }
        $data_ids = array_keys($data_list_id);
        foreach ($data_ids as $id) {
            $data_list_id[$id]['auth'] = isset($data_auth_id[$id]) ? json_decode($data_auth_id[$id]['auth'], true) : array();   //如果存在相对应的权限，就获取到权限
        }
        return $data_list_id;
    }
    
    /**
     * 保存数据权限；先删除之前的权限，再插入新的权限
     * @param type $role_id
     * @param type $data_ids
     * @return boolean 
     */
    public function save($role_id, $data_ids) {
        if (empty($role_id)) {
            return false;
        }
        foreach ($data_ids as $id => $auth) {
            $save_data['role_id'] = $role_id;
            $save_data['data_id'] = $id;
            $save_data['auth'] = json_encode(array_keys($auth));
            $save_datas[] = $save_data;
        }
//        var_dump($save_datas);exit;
        $model = D('DataAuth');
        $model->startTrans();
        $map['role_id'] = $role_id;
        if ($model->where($map)->delete() === false) {  //只要删除失败了才回退
            $model->rollback();
            return false;
        }
        if (!$model->addAll($save_datas)) {
            $model->rollback();
            return false;
        }
        $model->commit();
        return true;
    }
    
    public function getDataId($dataType) {
        $map['type'] = $dataType;
        $id = D('Data')->where($map)->getField('id');
        return $id;
    }
}
