<?php

namespace Admin\Lib;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Privilege {

    const ADD = 'add';  //新增
    const MOD = 'mod';  //修改
    const FIND = 'find';    //查找
    const DEL = 'del';  //删除

    /**
     * 检查数据权限
     * @param type $dataType
     * @param type $role_id
     * @param type $operation
     * @return boolean
     */
    public static function checkDataAuth($dataType, $role_id, $operation) {
        //获取文件id
        $dataId = D('dataAuth', 'Logic')->getDataId($dataType);
        if (empty($dataId)) {   //数据不需要检验权限
            return true;
        }
        //获取权限
        $dataPrivilege = D('DataAuth', 'Logic')->dataAuthList($role_id);
//        var_dump($dataPrivilege[$dataId]['auth']);
        if (isset($dataPrivilege[$dataId]) && in_array($operation, $dataPrivilege[$dataId]['auth'])) {
            return true;
        }
        return false;
    }

}
