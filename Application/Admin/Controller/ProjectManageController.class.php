<?php

namespace Admin\Controller;
use Admin\Model\CapitalFlowModel;

class ProjectManageController extends CommonController {
    
    //项目经理提交的项目查看目录
    public function myProject() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $admin = session('admin');
//        $map['pro_step'] = array('GT', 4);
        $map['pro_linker'] = $admin['admin_id'];
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('index');
    }
    
    //项目跟踪
    public function follow() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $admin = session('admin');
//        $map['pro_step'] = array('GT', 4);
        $map['admin_id'] = $admin['admin_id'];
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }
    
    //项目进度报表
    public function projectSchedule() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        $map['submit_status'] = 1;
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('index');
    }
    
    //放款操作界面
    public function loan() {
        $banks = D('Bank')->select();
        $this->assign('banks', $banks);
        $this->display();
    }
    
    //查找待放款项目
    public function unloan() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('LoanForm');
        $pro_no = I('post.pro_no');
        $submit_status = I('post.submit_status', -1);
        if (!empty($pro_no)) {
            $map['pro_no'] = $pro_no;
        }
        if ($submit_status > -1) {
            $map['submit_status'] = $submit_status;
        }

        $admin = session('admin');
//        $map['role_id'] = array('in', $admin['role_id']);
        if (!isSupper()) {
            $map['p.admin_id'] = $admin['admin_id'];
        }
        $result = $model->unloan($map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }
    
    //放款生成还款计划表
    public function loanApply() {
        $pro_id = I('post.pro_id');
        $loan_id = I('post.loan_id');
        $loan_time = strtotime(I('post.real_time'));
        $loan_money = I('post.loan_money');
        $remark = I('post.loan_remark');
        $bank_id = I('post.bank_id');
        $company_id = I('post.company_id');
        $real_time = strtotime(I('post.real_time'));
        $is_day_interest = I('post.is_day_interest');
        $begin_interest_time = I('post.begin_interest_time');
        $pay_interest_day = I('post.pay_interest_day');
        if (empty($pro_id)) {
            $this->json_error('非法请求');
        }
        $model = D('Project');
        $admin = session('admin');
        $pro_info = $model->where('pro_id=' . $pro_id)->find();

        if (!is_numeric($loan_money) || $loan_money <= 0) {
            $this->json_error('放款金额不正确');
        }
        $pro_real_money = bcadd($loan_money, $pro_info['pro_real_money'], 2);
        if ($pro_real_money > $pro_info['pro_account']) {
            $this->json_error('放款金额不能大于融资金额');
        }
        $loan_form_info = D('LoanForm')->findByPk($loan_id, 'has_loan_money');
        $has_loan_money = bcadd($loan_money, $loan_form_info['has_loan_money'], 2);
        if ($has_loan_money > $pro_info['pro_account']) {
            $this->json_error('放款金额不能大于请款金额');
        }
        $data = array('pro_id' => $pro_id, 'pro_real_money' => $pro_real_money, 'is_loan' => 1, 'pro_step' => 2);
        if (false === $data = $model->create($data)) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $contract_info = D('ProjectContract')->where(array('pro_id' => $pro_id, 'company_id' => $company_id))->find();
        //还款计划表计算信息
        $begin_interest_time = empty($begin_interest_time) ? 0 : strtotime($begin_interest_time);
        $calc_interest_info = array( 'begin_interest_time' => $begin_interest_time, 'pay_interest_day' => $pay_interest_day);
        $model->startTrans();
        $deadline = \Admin\Lib\CalcTool::deadline($real_time, $contract_info['term'], 'm');
        $loan_data = array('debt_account' => $loan_money, 'pro_id' => $pro_id,'company_id'=> $company_id,
                    'contract_id'=> $contract_info['contract_id'], 'admin_id' => $admin['admin_id'], 'remark' => $remark, 
                    'deadline' => $deadline, 'real_time' => $real_time,
                    'begin_interest_time' => $begin_interest_time, 'pay_interest_day' => $pay_interest_day
            );
        if (!D('ProjectDebt')->addDebt($loan_data)) {
            $model->rollback();
            $this->json_error('修改失败');
        }
        if (!$model->save()) {
            $model->rollback();
            $this->json_error('修改失败');
        }
        
//        var_dump($calc_interest_info);exit;
        $debt_all_id = D('ProjectDebt')->getLastInsID();
        if (!D('ProRepaymentSchedule')->addRecords($debt_all_id, $pro_id, $company_id, $loan_money, $contract_info['repurchase_rate'], $contract_info['term'], $loan_time, $contract_info['interest_type'], $calc_interest_info)) {
            $model->rollback();
            $this->json_error('还款列表生成失败');
        }
        //新增资金流水
        if (!D('CapitalFlow')->addFlow($pro_id, $company_id, $debt_all_id, $loan_money, CapitalFlowModel::FINANCING, $bank_id, $real_time, $remark)) {
            $model->rollback();
            $this->json_error('流水记录失败');
        }
        if (!D('LoanForm')->updateByPk($loan_id, array('has_loan_money' => $has_loan_money))) {
            $model->rollback();
            $this->json_error('更新请款表失败');
        }
        
        if (bccomp($has_loan_money, $pro_info['pro_account']) === 0) {  //请款的金额已经放完，修改放款申请进程的状态
            if (!D('WorkflowProcess')->where('context=' . $loan_id)->save(array('current_node_index' => 11))) {
                $model->rollback();
                $this->json_error('失败3');
            }
        }
        $model->commit();
        self::log('add', "放款操作:pro_id-$pro_id");
        $this->json_success('保存成功');
    }
    
    public function test() {
        $pro_id = I('get.pro_id', 70);
        $condition['pro_id'] = $pro_id;
        $condition['status'] = 1;
        $workflow = D('Workflow')->getWorkFlow();
        $workflow_tmp = D('WorkflowTemplate')->where('type=1')->select();
//        $pro_info = D('Project')->findByPk($pro_id);
//        var_dump($workflow_tmp);
        $list = array();
        foreach ($workflow_tmp as $val) {
            $data['jd'] = $val['description'];
            $condition['step_pid'] = $val['workflow_id'];
            $step = $workflow[$val['workflow_id']];
            $last_step = array_pop($step);
            $condition['pro_step'] = $last_step['step_id'];
//            var_dump($condition);
            $time = D('ProcessLog')->findByCondition($condition, 'addtime');
            $data['time'] = empty($time) ? -1 : date('Y-m-d H:i:s', $time['addtime']);
            $list['jdList'][] = $data;
        }
        $this->assign('list', json_encode($list));
        $this->assign('pro_id', $pro_id);
        $this->display('index_1');
    }
    
    //融资端列表（需求混乱，没有一个好的规划，想到什么就要什么，需求人自己都不清楚要什么）
    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');
        
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        $order = 'step_pid ASC, pro_step ASC';
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid '.$orderDirection;
                $order .= ',pro_step '. $orderDirection;
            }
        }
//        var_dump($order);exit;
        $map['submit_status'] = 1;
        $map['step_pid'] = array('GT', 4);
//        $map['_string'] = '(submit_status=1 AND pro_step>0) OR (submit_status=0 AND step_pid>1)';
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order($order)->relation(true)->page($page, $pageSize)->select();
        $is_pmd_boss = isPmdBoss();
        $is_supper = isSupper();
        
        $this->assign(array('is_pmd_boss' => $is_pmd_boss, 'is_supper' => $is_supper));
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }
    
    //白名单展示
    public function whiteList() {
        $pro_id = I('get.pro_id');
        $model = D('Department');
        $list = $model->select();
        foreach ($list as $v) {
            $array[$v['dept_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $de_list = $tree->get_array(0);
        $admin_list = D('Admin')->where('status=1')->select();
//        var_dump($admin_list); 
//        $admin_list = array_switch_key($admin_list, 'dp_id');
        $white_list = D('ProjectWhite')->where('pro_id=' . $pro_id)->select();
        $white_list = array_column($white_list, 'admin_id');
//        var_dump($white_list);
        $admin_names = '';
        $admin_ids = '';
        foreach ($admin_list as $val) {
            if (in_array($val['admin_id'], $white_list)) {
                $val['checked'] = 1;
                $admin_names .= $val['real_name'] . ',';
                $admin_ids .= $val['admin_id'] . ',';
            }
            $arr[$val['dp_id']][] = $val;
            
        }
        if (strlen($admin_names) > 0) {
            $admin_names = mb_substr($admin_names, 0, -1);
            $admin_ids = substr($admin_ids, 0, -1);
        }
//        var_dump($admin_names);
//        var_dump($arr);exit;
        $this->assign(array('admin_ids' => $admin_ids, 'admin_names' => $admin_names));
        $this->assign('admin_list', $arr);
        $this->assign('list', $de_list);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }
    
    public function addWhite() {
        $pro_id = I('post.pro_id');
        $admin_ids = I('post.admin_ids');
        $admin_arr = explode(',', $admin_ids);
        foreach ($admin_arr as $value) {
            $data['pro_id'] = $pro_id;
            $data['admin_id'] = $value;
            $save_data[] = $data;
        }
        $model = D('ProjectWhite');
        $model->startTrans();
        if ($model->where('pro_id=' . $pro_id)->delete() === false) {
            $model->rollback();
            $this->json_error('添加失败');
        }
        if ($model->addAll($save_data) === false) {
            $model->rollback();
            $this->json_error('添加失败');
        }
        $model->commit();
        $this->json_success('添加成功');
    }
    
    public function projectList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');
        
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        $order = 'step_pid ASC, pro_step ASC';
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid '.$orderDirection;
                $order .= ',pro_step '. $orderDirection;
            }
        }
//        var_dump($order);exit;
        $map['submit_status'] = 1;
        $map['step_pid'] = array('GT', 4);
        $admin = session('admin');
        $map['pw.admin_id'] = $admin['admin_id'];
//        $map['_string'] = '(submit_status=1 AND pro_step>0) OR (submit_status=0 AND step_pid>1)';
        $model = D('Project');
        $result = $model->projectWhiteList($page, $pageSize, $map, $order);
//        var_dump($model->_sql());exit;
        $is_pmd_boss = isPmdBoss();
        $is_supper = isSupper();
        
        $this->assign(array('is_pmd_boss' => $is_pmd_boss, 'is_supper' => $is_supper));
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('project_list');
    }
}


