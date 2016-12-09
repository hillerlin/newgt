<?php

namespace Admin\Controller;


class RepaymentScheduleController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $mpay_id = I('get.mpay_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('RepaymentSchedule');
        
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
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
        if (!empty($mpay_id)) {
            $map['t.mpay_id'] = $mpay_id;
            $this->assign('mpay_id', $mpay_id);
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
    public function repay() {
        if (IS_POST) {
            $rp_id = I('post.rp_id');
            $repay_money = I('post.repay_money');
            $real_repay_time = I('post.real_repay_time');
            $repay_pic = I('post.repay_pic');
            $status = (int)I('post.status');
            if (empty($repay_money) || !is_numeric($repay_money)) {
                $this->json_error('还款金额不能为0或非数字');
            }
            if (!is_int($status) || !in_array($status, array(0,1))) {
                $this->json_error('参数不正确'.is_int($status));
            }
            $this->process($rp_id, $repay_money, strtotime($real_repay_time), $repay_pic, $status);
        }
        $model = D('RepaymentSchedule');
        $rp_id = I('get.rp_id');
        $data = $model->where(array('rp_id' => $rp_id))->find();
        $this->assign($data);
        $this->display();
    }
    
    /**
     * 处理还款操作
     * @param type $rp_id
     * @param type $repay_money
     * @param type $real_repay_time
     * @param type $repay_pic
     */
    protected function process($rp_id ,$repay_money, $real_repay_time, $repay_pic, $status) {
        $record_model = D('RepaymentRecord');
        $record_model->startTrans();
        //更新还款计划表的实际还款金额
        $map['rp_id'] = $rp_id;
        $schedule_info = $this->mainModel->where($map)->field('mid,has_repay_money,status')->find();
        $has_repay_money = bcadd($schedule_info['has_repay_money'], $repay_money, 2);
        if ($schedule_info['status'] == \Admin\Model\RepaymentScheduleModel::DONE) {
            $record_model->rollback();
            $this->json_error('本条还款已结清');
        }
        if (!$this->mainModel->where($map)->save(array('has_repay_money' => $has_repay_money, 'last_repay_time' => $real_repay_time, 'status' => $status))) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误002');
        }
        //插入还款记录
        $record = array('rp_id' => $rp_id, 'repay_money' => $repay_money, 'real_repay_time' => $real_repay_time, 'repay_pic' => $repay_pic, 'mid' => $schedule_info['mid']);
        if (!$record_model->add($record)) {
            $record_model->rollback();
            $this->json_error('还款失败。错误原因：内部错误001');
        }
        $record_model->commit();
        $this->json_success('还款成功');
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
