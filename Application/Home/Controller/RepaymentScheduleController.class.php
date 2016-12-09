<?php

namespace Home\Controller;


class RepaymentScheduleController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $mpay_id = I('get.mpay_id');
        
        if ($isSearch) {
            if ($status !== '') {
                $map['status'] = $status;
            }
            
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($mpay_id)) {
            $map['mpay_id'] = $mpay_id;
            $this->assign('mpay_id', $mpay_id);
        }
        $model = D('RepaymentSchedule');
        $member = session('member');
        $map['mid'] = $member['mid'];
//        $result = $model->getList($page, $pageSize ,$map);
        $total = $model->where($map)->count();
        $list = $model->where($map)->page($page, $pageSize)->select();
        
//        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('post', $_POST);
        $this->display();
    }
  
}
