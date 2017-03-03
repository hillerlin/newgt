<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 15:00
 */
namespace Api\Model;
use Api\Model\BaseModel;
use Think\Model\RelationModel;
class AdminModel extends BaseModel{

    public function login($condition)
    {
        $adminModel=D('Admin');
        $list=$adminModel->getUserInfoByNameAndPassWord($condition);
        return $list;

    }


}