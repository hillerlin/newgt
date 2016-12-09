<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectAttachmentModel extends BaseModel {
    //检查文件sha1是否存在
    public function sha1Exists($pro_id, $sha1) {
        $map['pro_id'] = $pro_id;
        $map['sha1'] = $sha1;
        if ($this->where($map)->count() > 0) {
            return true;
        }
        return false;
    }
    
    public function getOneByCondition($map) {
        return $this->where($map)->find();
    }
}

