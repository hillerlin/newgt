<?php

namespace Admin\Controller;


class RepaymentRecordController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentRecord');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $rp_id = I('get.rp_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('RepaymentRecord');
        
        if ($isSearch) {
            if ($status !== '') {
                $map['status'] = $status;
            }
            
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['add_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($rp_id)) {
            $map['rp_id'] = $rp_id;
            $this->assign('rp_id', $rp_id);
        }
        
        $result = $model->getList($page, $pageSize ,$map);
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
       
    }
    
    public function add() {
        $this->display();
    }

    /* 还款 */
    public function edit() {
        $model = D('RepaymentRecord');
        $rp_id = I('get.rp_id');
        $data = $model->where(array('rp_id' => $rp_id))->find();
        $this->assign($data);
        $this->display();
    }
    
    //上传附件
    public function upload_attachment() {
        $mpay_id = I('request.mpay_id');
        $mid = I('request.mid');
        $field = 'mpay-' . $mpay_id;
        $upload_info = upload_file('/repayment/attachment/', $field, $mid.'-');
//        $this->ajaxReturn(array('status' => 1, 'data' => array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'))));
        if (isset($upload_info['file_path'])) {
//            $save_data['file_id'] = $file_id;
//            $save_data['pro_id'] = $pro_id;
//            $save_data['path'] = $upload_info['file_path'];
//            $save_data['doc_name'] = $upload_info['name'];
//            $save_data['addtime'] = time();
//            $admin = session('admin');
//            $save_data['admin_id'] = $admin['admin_id'];
//            if (!($aid = D('ProjectAttachment')->add($save_data))) {
//                $this->json_error('上传失败');
//            }
//            var_dump($upload_info);
            $content = array('file_path' => $upload_info['file_path'],'file_id' => date('YmdHis'), 'file_name'=>$upload_info['name']);
            $this->ajaxReturn(array('statusCode' => 200, 'content'=>$content, 'message'=>'上传成功'));
        }
        $this->json_error('上传失败');
    }
  
}
