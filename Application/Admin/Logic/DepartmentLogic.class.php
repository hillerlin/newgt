<?php

namespace Admin\Logic;

class DepartmentLogic {
    
    const PMD = 1;  //Project management department 项管部
    const RCD = 2;  //Risk control department   风控部
    const PMDD = 1;    //Project management department director 项管总监
    const PMDC = 2;    //Project management department commissioner 项管专员
    const RCDD = 1;    //Risk control department director 风控部总监
    const RCDC = 2;    //Risk control department commissioner 风控部专员

    /**
     * 审核资料入库
     * @return boolean
     */
    public function addReviewFile($pro_id, $step_id, $admin_id, $data) {
        foreach ($data as $val) {
            $save_data['step_id'] = $step_id;
            $save_data['pro_id'] = $pro_id;
            $save_data['path'] = $val['path'];
            $save_data['doc_name'] = $val['doc_name'];
            $save_data['admin_id'] = $admin_id;
            $save_data['sha1'] = isset($val['sha1']) ? $val['sha1'] : '';
            $save_data['addtime'] = time();
            $dataList[] = $save_data;
        }
        return D('ProjectReview')->addAll($dataList);
    }
    
    /**
     * 是否是项管部
     * @param type $pd_id
     * @retun boolean
     */
    public static function isPMD($pd_id) {
        return $pd_id == self::PMD ? true : false;
    }
    /**
     * 是否是项风控部
     * @param type $pd_id
     * @retun boolean
     */
    public static function isRCD($pd_id = 0) {
        if (empty($pd_id)) {
            $admin = session('admin');
            $pd_id = $admin['dp_id'];
        }
        return $pd_id == self::RCD ? true : false;
    }
    
    public static function isBoss($pd_id, $position_id) {
        
    }
}
