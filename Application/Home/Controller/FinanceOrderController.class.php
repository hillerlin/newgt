<?php

namespace Home\Controller;


class FinanceOrderController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $fp_id = I('get.fp_id');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('Admin/FinanceOrder', 'Model');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.add_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($fp_id)) {
            $map['fp_id'] = $fp_id;
            $this->assign('fp_id', $fp_id);
        }
        $member = session('member');
        $map['t.mid'] = $member['mid'];
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($model->getLastSql());
//        var_dump($status);eixt;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
       
    }
  
}
