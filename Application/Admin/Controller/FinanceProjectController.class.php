<?php

namespace Admin\Controller;


class FinanceProjectController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $pro_no = I('post.pro_no');
        $debt_no = I('post.debt_no');
        $model = D('FinanceProject');
        
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($pro_no)) {
                $map['p.pro_no'] = $pro_no;
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($result);exit;;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
       
    }

    public function add() {
        $this->assign('symbiosis_levels', C('symbiosis_levels'));
        $this->display();
    }

    public function edit() {
        $fp_id = I('get.fp_id');
        if (empty($fp_id)) {
            $this->json_error('id不能为空');
        }
        $data = D('FinanceProject')->where('fp_id='.$fp_id)->find();
        $project = D('Project')->where('pro_id=' . $data['pro_id'])->relation(true)->find();
        //获取项目白名单
        $white_list = D('Member')->join('__FINANCEPRO_WHITE__ ON __FINANCEPRO_WHITE__.mid=__MEMBER__.mid ')->field('gt_member.mid,company_name')->where('fp_id='.$fp_id)->select();
        $white_mids = implode(',' ,  array_column($white_list, 'mid'));
        $white_name = implode(',', array_column($white_list, 'company_name'));
        $this->assign($data);
        $this->assign('project', $project);
        $this->assign('symbiosis_levels', C('symbiosis_levels'));
        $this->assign('white_mids', $white_mids);
        $this->assign('white_name', $white_name);
        $this->display();
    }
    
    public function detail() {
        $fp_id = I('get.fp_id');
        if (empty($fp_id)) {
            $this->json_error('id不能为空');
        }
        $data = D('FinanceProject')->where('fp_id='.$fp_id)->find();
        $project = D('Project')->where('pro_id=' . $data['pro_id'])->relation(true)->find();
        $this->assign($data);
        $this->assign('project', $project);
        $this->display();
    }

    public function del() {
        $fp_id = I('get.fp_id');
        $model = D('FinanceProject');
        if (D('FinanceOrder')->where('fp_id=' . $fp_id)->count() > 0) {
            $this->json_error('已经存在认购记录，不能被删除');
        }
        $state = $model->delete($fp_id);
        if ($state !== false) {
            $this->json_success('删除成功', U('admin/index'));
        } else {
            $this->json_error('操作失败');
        }
    }
    
    public function save_finance() {
        $model = D('FinanceProject');
        $white_list = I('post.mid');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if (!empty($white_list)) {
            $white_list = explode(',', $white_list);
            foreach ($white_list as $v) {
                $white[] = array('mid' => $v);
            }
            $model->white = $white;
        }
        $model->end_time = strtotime($data['end_time']);
        if ($data['fp_id']) {
            $result = $model->relation('white')->save();
        } else {
            $model->left_money = $data['finance_money'];
            $result = $model->relation('white')->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('tabid'=>'bjui-hnav-tree8_2_a'));
        }
    }
    
    public function exchange() {
        if (IS_POST) {
            $model = D('ProjectDebt');
            $admin = session('admin');
            if (false === $data = $model->create()) {
                $e = $model->getError();
                $this->json_error($e);
            }
            $model->startTrans();
            
            $model->admin_id = $admin['admin_id'];
            $model->start_time = strtotime($data['start_time']);
            $model->end_time = strtotime($data['end_time']);
            if (!$model->add()) {
                $model->rollback();
                $this->json_error('内部错误');
            }
            if (!$model->where('debt_id='.$data['parent_id'])->save(array('status'=>0))) {
                $model->rollback();
                $this->json_error('内部错误');
            }
            $model->commit();
            $this->json_success('换质成功');
        }
        $pro_id = I('get.pro_id');
        $debt_id = I('get.debt_id');
        if (empty($pro_id)) {
            $this->json_error('项目id不能为空');
        }
        $this->assign('pro_id', $pro_id);
        $this->assign('debt_id', $debt_id);
        $this->display();
    }
    
    public function whiteAdd() {
        $fp_id = I('get.fp_id');
        $member_list = D('Member')->where('status=1')->select();
        $array = array();
        if (!empty($fp_id)) {
            $white_list = D('FinanceproWhite')->where('fp_id=' . $fp_id)->select();
            foreach ($white_list as $v) {
                $array[] = $v['mid'];
            }
        }
        $this->assign('white_list', $array);
        $this->assign('member_list', $member_list);
        $this->display('white_add');
    }
   
}
