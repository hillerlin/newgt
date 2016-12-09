<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectReviewModel extends BaseModel {
    
    public function getMaxId($pro_id) {
        $map['pro_id'] = $pro_id;
        $max_id = $this->where($map)->Max('file_id');
        if (empty($max_id)) {
            return 0;
        }
        return $max_id;
    }
    
    
    public function makeDir($pro_id, $dirs) {
        foreach ($dirs as $key => $val) {
            $data['file_id'] = $val['file_id'];
            $data['pro_id'] = $pro_id;
            $data['pid'] = $val['pid'];
            $data['file_name'] = $key;
            $save_data[] = $data;
        }
        return $this->addAll($save_data);
    }
}

