<?php

namespace Home\Logic;
use Think\Model;
class UserLogic extends Model{

    public function testName(){
        $model=D('User','Model');
        echo $model->testName();
        return "user logic";
    }
}