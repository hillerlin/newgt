<?php

namespace Admin\Logic;

class ProjectFileLogic {

    /**
     * 审核资料入库
     * @return boolean
     */
    public function addReviewFile($pro_id, $log_id, $admin_id, $data) {
        foreach ($data as $val) {
            $save_data['log_id'] = $log_id;
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
}
