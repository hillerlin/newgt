<?php

namespace Home\Controller;


class MpayRecordController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $oid = I('get.oid');
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('MpayRecord');
        
        if ($isSearch) {
            if ($status !== '') {
                $map['status'] = $status;
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
        if (!empty($oid)) {
            $map['oid'] = $oid;
            $this->assign('oid', $oid);
        }
        $member = session('member');
        $map['mid'] = $member['mid'];
//        $result = $model->getList($page, $pageSize ,$map);
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('add_time desc')->page($page, $pageSize)->select();
//        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('post', $_POST);
        $this->display();
       
    }
    
    public function add() {
        $oid = I('get.oid');
        $this->assign('oid', $oid);
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $model = D('Member');
        $mid = I('get.mid');
        $data = $model->where(array('mid' => $mid))->find();
        $this->assign($data);
        $this->display();
    }
    
    public function save() {
        $model = D('MpayRecord');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $model->pay_time = strtotime($data['pay_time']);
        $member = session('member');
        $model->mid = $member['mid'];
        if ($data['pay_id']) {
            $result = $model->save();
        } else {
            $fp_id = D('FinanceOrder')->where('oid=' . $data['oid'])->getField('fp_id');    //跟进订单id查找到打款的融资项目id
            $model->fp_id = $fp_id;
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }
    
    //提交给后台
    public function submit() {
        $pay_id = I('get.pay_id');
        
        if (empty($pay_id)) {
            $this->json_error('参数不完整');
        }
        $model = D('MpayRecord');
        $map['pay_id'] = $pay_id;
        if (!$model->updateStatus($map, \Home\Model\MpayRecordModel::SUBMIT)) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $this->json_success('提交成功');
    }
    
    public function del() {
        $pay_id = I('pay_id');
        $model = D('MpayRecord');
        $state = $model->delete($pay_id);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
}
