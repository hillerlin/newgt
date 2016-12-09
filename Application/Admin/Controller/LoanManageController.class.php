<?php

namespace Admin\Controller;
use Admin\Lib\Workflow;

class LoanManageController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }
    
    //合同预签申请
    public function loanApplyList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $pmd_admin = I('post.pmd_admin');
        
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        if (!empty($pmd_admin)) {
            $map['p.admin_id'] = $pmd_admin;
        }
        
        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        $model = D('LoanForm');
        $result = $model->getList($page, $pageSize, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();
        $map1['status'] = 1;
        $map1['role_id'] = 2;
        $pmd = D('Admin')->where($map1)->select();

        $this->assign('pmd', $pmd);
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('loan_apply_list');
    }
    
    //未申请放款的合同列表
    public function couldApplyList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('ProjectContract');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
       
        $map['p.admin_id'] = $admin['admin_id'];
        $result = $model->selectContract($page, $pageSize, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('no_apply_list');
    }
    
    //选择需要放款的项目
    public function loanApplyToAdd() {
        $admin = session('admin');
        if (IS_POST) {
            $model = D('LoanForm');
            if (false === $data = $model->create()) {
                $e = $model->getError();
                $this->json_error($e);
            }
            $model->assure_type = implode(',', $data['assure_type']);
            $model->apply_time = strtotime($data['apply_time']);
            if (empty($data['loan_id'])) {
                $result = $model->add();
            } else {
                $result = $model->save();
            }
            if ($result === false) {
                $this->json_error('保存失败');
            } else {
                $this->json_success('保存成功', '', '', true, array('tabid' => 'loanmanage-loanApplyList'));
            }
        }
        $contract_pay_type = C('contract_pay_type');
        
        $this->assign('contract_pay_type', $contract_pay_type);
        $assure_type = C('assure_type');
        $this->assign('assure_type', $assure_type);
        $this->assign('admin', $admin);
        $this->display('add');
    }
    
    public function submit() {
        $p_model = D('Project');
        $loan_id = I('request.loan_id');
        $admin = session('admin');
        $data = $p_model->where(array('pro_linker' => $admin['admin_id'], 'pro_id' => $loan_id))->relation(true)->find();
        $this->pro_info = $data;
        if ($data['submit_status'] == 1) {
            $this->json_error('此项目已提交，不能重复提交');
        }
        if (IS_POST) {
            $opinion = '--';
            $this->start($loan_id, $opinion);
        }
        $this->assign($data);
        $this->display();
    }
    
    protected function start($loan_id, $opinion) {
        $admin = session('admin');
        D('LoanForm')->updateByPk($loan_id, array('submit_status' => 1));
        D('WorkflowProcess')->add(array('workflow_id' => 5, 'process_desc' => '放款申请', 'context' => $loan_id, 'current_node_index' => 1, 'state' => 1, 'start_user' =>$admin['admin_id']));
        $refresh = array('tabid'  => 'loanmanage-loanapplylist');
        $this->process2($loan_id, 1, 1, 0, $opinion, '', $refresh);
    }
    
    public function auditList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('LoanForm');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
                $map['t.submit_status'] = 1;
        }
        $map['wp.admin_id|wp.role_id|wp.dp_id'] = array($admin['admin_id'], $admin['role_id'], $admin['dp_id'], '_multi'=>true);
        $map['current_node_index'] = array(array('LT', 10), array('neq', 0), 'and');
        $result = $model->waitAudit(1, 30, $map);
        $total = $result['total'];
        $list = $result['list'];
//        $total = $model->where($map)->count();
//        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
    

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display();
    }
    
    public function auditEdit() {
        $p_model = D('LoanForm');
        $loan_id = I('get.loan_id');
//        $admin = session('admin');
        $map['t.context_id'] = $loan_id;
        $map['t.step_pid'] = 5;
        $process_info = D('WorkflowProcess')->where('context='.$loan_id)->find();
        $admin = session('admin');
        if ($admin['admin_id'] != $process_info['admin_id'] && $admin['role_id'] != $process_info['role_id'] && $admin['dp_id'] != $process_info['dp_id']) {
            $this->json_error('项目已过本审核阶段，请刷新页面');
        }
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->auditInfo($loan_id);
//        var_dump($data);exit;
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        $data['assure_type'] = explode(',', $data['assure_type']);
//        var_dump($data['assure_type']);exit;
        $exts = getFormerExts();
        $assure_type = C('assure_type');
        $contract_pay_type = C('contract_pay_type');
        
        $this->assign('contract_pay_type', $contract_pay_type);
        $this->assign('assure_type_list', $assure_type);
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->display('audit_edit');
    }
    
    //提交审核
    public function audit(){
        
        $loan_id = I('request.loan_id');
        $status = I('request.status');
        $pro_step = I('request.pro_step');
        $opinion = I('request.opinion');
        $review_files = I('post.reviews');
//        var_dump($pro_id);exit;
//        $data = $p_model->where(array('pro_id' => $pro_id))->find();
//        $this->pro_info = $data;
        $this->process2($loan_id, $pro_step, $status, 0, $opinion, $review_files);
        $this->assign($data);
    }
    
    
    protected function process2($loan_id, $pro_step, $status, $submit_status, $opinion, $review_files, $refresh = array('tabid' => 'loanmanage-auditList')) {
        $workflow = new Workflow();
        $admin = session('admin');
        $pro_detail = array('context_id' => $loan_id, 'admin_id' => $admin['admin_id'], 'status' => $status, 'opinion' => $opinion, 'addtime' => time(), 'pro_step' => $pro_step, 'step_pid' => 5, 'context_type' => 'loan_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        if (!empty($review_files)) {
            if (!D('ProjectFile', 'Logic')->addReviewFile($loan_id, $pro_model->getLastInsID(), $admin['admin_id'], $review_files)) {
                $pro_model->rollback();
                $this->json_error('审核失败。失败原因：内部错误。');
            }
        }
        //获取下一步id
        $step_pid = 5;
        $next_step = $workflow->nextStep($step_pid, $pro_step, $status);
        
        //推送待办事项
//        $backlog_msg = MsgTmp::getBacklog($pro_step, $next_step['step_pid'], $this->pro_info['pro_title']);
        //更新项目表，下一步
        //获取下一步执行的后台管理员
        $next_admin_id = $this->nextStepSpecifiedAdmin($next_step['step_id'], $loan_id);
        $data = array('current_node_index' => $next_step['step_id'],'workflow_id' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'dp_id' => $next_step['dp_id'], 'admin_id' => $next_admin_id);
//        if ($submit_status == 0) {
//            $data['submit_status'] = 1;
//        }
        if (!D('WorkflowProcess')->where('context=' . $loan_id)->save($data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        $loanFormInfo = D('LoanForm')->findByPk($loan_id);
        if ($this->updateMainProcess($loanFormInfo['pro_id'], $next_step['step_id']) === false) {
            $pro_model->rollback();
            $this->json_error('失败4');
        }
        //推送项目变更消息
        D('Message')->push($admin['admin_id'], $loanFormInfo['pro_id'], $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($loanFormInfo['pro_id'], $step_pid, $pro_step, $status);
        self::log('mod', "项目审核:loan_id-$loan_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        $this->json_success('成功', '', '', true, $refresh);
    }
    
    //更新主流程线进度
    protected function updateMainProcess($pro_id, $branch_id) {
        $result = true;
        switch ($branch_id) {
            case 1:     //发起放款审核，主流程前进
                $main_pro_step = 1;
                break;
            case 10:    //出纳确认，放款成功
                $main_pro_step = 2;
                break;
            default:
                $main_pro_step = 0;
                break;
        }
        if ($main_pro_step > 0) {
            $result = D('Project')->updateByPk($pro_id, array('pro_step' => $main_pro_step));
        }
        return $result;
    }
    
    protected function nextStepSpecifiedAdmin($step_id, $loan_id) {
        if ($step_id == 4) {
            $pro_info = D('LoanForm')->findProInfoByPk($loan_id);
            return $pro_info['risk_admin_id'];
        }
        return 0;
    }
    
    //
    public function detail() {
        $p_model = D('LoanForm');
        $loan_id = I('get.loan_id');
        $admin = session('admin');
        $map['t.context_id'] = $loan_id;
        $map['t.step_pid'] = 5;
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->auditInfo($loan_id);
//        var_dump($data);exit;
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        $data['assure_type'] = explode(',', $data['assure_type']);
//        var_dump($data['assure_type']);exit;
        $exts = getFormerExts();
        $assure_type = C('assure_type');
        $contract_pay_type = C('contract_pay_type');
        
        $this->assign('contract_pay_type', $contract_pay_type);
        $this->assign('assure_type_list', $assure_type);
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->display('detail');
    }
    
    public function edit() {
        $p_model = D('LoanForm');
        $loan_id = I('get.loan_id');
//        var_dump($loan_id);exit;
        $admin = session('admin');
       
        $data = $p_model->applyInfo($loan_id);
//        var_dump($data);exit;
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        $data['assure_type'] = explode(',', $data['assure_type']);
//        var_dump($data['assure_type']);exit;
        $assure_type = C('assure_type');
        $contract_pay_type = C('contract_pay_type');
        
        $this->assign('contract_pay_type', $contract_pay_type);
        $this->assign('assure_type_list', $assure_type);
        $this->assign('workflow', $workflow);
        $this->assign($data);
        $this->display();
    }
    
    public function del() {
        $loan_id = I('get.loan_id');
        
        if (empty($loan_id)) {
            $this->json_error('非法请求');
        }
        $model = D('LoanForm');
        $loan_form = $model->findByPk($loan_id);
        if (empty($loan_form)) {
            $this->json_error('非法请求');
        }
        if (D('WorkflowProcess')->where('context=' . $loan_id)->count() > 0) {
            $this->json_error('放款申请已经提交，不能删除');
        }
        if ($model->delete($loan_id) === false) {
            $this->json_error('删除失败');
        }
        $this->json_success('删除成功');
    }
}
