<?php

namespace Admin\Controller;
use Admin\Lib\Workflow;

class ProjectDoneController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }
    
    //申请表
    public function index() {
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
        
        $is_boss = isBoss();
        $is_supper = isSupper();
        $model = D('ProjectFinish');
        $map['t.submit_status'] = 1;
        $result = $model->applyList($page, $pageSize, $map);
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
        $this->display();
    }
    
    public function add() {
        $pro_id = I('get.pro_id');
        $list = D('ProjectDebt')->getProfit($pro_id);
        $this->assign('profit', $list);
        $this->display();
    }
    
    public function getProfit() {
        $pro_id = I('post.pro_id');
        $list = D('ProjectDebt')->getProfit($pro_id);
        $this->sendData($list);
    }
    
    //申请表
    public function applyList() {
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
        
        $is_boss = isBoss();
        $is_supper = isSupper();
        $model = D('ProjectFinish');
        $result = $model->applyList($page, $pageSize, $map);

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
        $this->display('apply_list');
    }
    
    //申请
    public function couldApplyList() {
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
//        $is_boss = isBoss();
//        $is_supper = isSupper();
        $model = D('Project');
        $result = $model->waitDone($page, $pageSize, $map);
        $total = $result['total'];
        $list = $result['list'];
        foreach ($list as & $v) {
            $v['pro_time_limit'] = D('ProjectDebt')->field('MIN(real_time) as start_time,MAX(real_pay_time) as end_time')->where('pro_id=' . $v['pro_id'])->find();
        }
        
        $workflow = D('Workflow')->getWorkFlow();
        
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
//        $this->assign('is_boss', $is_boss);
//        $this->assign('is_supper', $is_supper);
        $this->display('could_apply_list');
    }
    
    //选择需要放款的项目
    public function save() {
        $admin = session('admin');
        if (IS_POST) {
            $model = D('ProjectFinish');
            if (false === $data = $model->create()) {
                $e = $model->getError();
                $this->json_error($e);
            }
//            $model->apply_time = strtotime($data['apply_time']);
            $check_back_ukey = I('post.check_back_ukey', 0);
            $check_back_invoice = I('post.check_back_invoice', 0);
            $check_other = I('post.check_other', 0);
            if ($check_back_ukey == 0) {
                $model->back_ukey = 0;
                $model->back_ukey_detail = '';
            }
            if ($check_back_invoice == 0) {
                $model->back_invoice = 0;
            }
            if ($check_other == 0) {
                $model->other = '';
            }
            if (empty($data['finish_id'])) {
                $model->admin_id = $admin['admin_id'];
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
    }
    
    public function submit() {
        $p_model = D('ProjectFinish');
        $finish_id = I('request.finish_id');
        $admin = session('admin');
        $data = $p_model->where(array('admin_id' => $admin['admin_id'], 'finish_id' => $finish_id))->find();
        $this->pro_info = $data;
        if ($data['submit_status'] == 1) {
            $this->json_error('此项目已提交，不能重复提交');
        }
        if (IS_POST) {
            $opinion = '--';
            $this->start($finish_id, $opinion);
        }
        $this->assign($data);
        $this->display();
    }
    
    protected function start($finish_id, $opinion) {
        $admin = session('admin');
        D('ProjectFinish')->updateByPk($finish_id, array('submit_status' => 1));
        D('WorkflowProcess')->add(array('workflow_id' => 8, 'process_desc' => '项目完结申请', 'context' => $finish_id, 'current_node_index' => 1, 'state' => 1, 'start_user' =>$admin['admin_id'], 'context_type' => 'finish_id'));
        $refresh = array('tabid' => 'projectdone-applylist');
        $this->process2($finish_id, 0, 1, $opinion, $refresh);
    }
    
    public function auditList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('ProjectFinish');
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
        $map['current_node_index'] = array(array('LT', 6), array('neq', 0), 'and');
        $result = $model->waitAudit(1, 30, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display();
    }
    
    public function auditEdit() {
        $finish_id = I('get.finish_id');
        $p_model = D('ProjectFinish');
        
        $where['context'] = $finish_id;
        $where['context_type'] = 'finish_id';
        $process_info = D('WorkflowProcess')->where($where)->find();
//        var_dump($process_info);exit;
        $admin = session('admin');
        if ($admin['admin_id'] != $process_info['admin_id'] && $admin['role_id'] != $process_info['role_id'] && $admin['dp_id'] != $process_info['dp_id']) {
            $this->json_error('项目已过本审核阶段，请刷新页面');
        }
        $map['t.context_id'] = $finish_id;
        $map['t.step_pid'] = 8;
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->applyInfo($finish_id);
//        var_dump($process_info);exit;
        $profit = D('ProjectDebt')->getProfit($data['pro_id']);
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $this->assign('workflow', $workflow);
        $this->assign('process_info', $process_info);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->assign('profit', $profit);
        $this->display('audit_edit');
    }
    
    public function audit() {
        $finish_id = I('post.finish_id');
        $opinion = I('post.opinion');
        $map['context'] = $finish_id;
        $map['context_type'] = 'finish_id';
        $process_info = D('WorkflowProcess')->where($map)->find();
//        var_dump($process_info);exit;
        if ($process_info['current_node_index'] == 1) { //前一步为提交时，下一步是财务审核
            $close_off = I('post.close_off');
            $close_off_time = I('post.close_off_time') ;
            $close_off_time =  empty($close_off_time) ? 0 : strtotime($close_off_time);
            D('ProjectFinish')->where('finish_id='.$finish_id)->save(array('close_off' => $close_off, 'close_off_time' => $close_off_time));
        }
        $this->process2($finish_id, $process_info['current_node_index'], 1, $opinion);
    }
    
    protected function process2($context_id, $pro_step, $status, $opinion, $refresh = array('tabid' => 'projectdone-auditList')) {
        $workflow = new Workflow();
        $admin = session('admin');
        $step_pid = 8;
        $pro_detail = array('context_id' => $context_id, 'admin_id' => $admin['admin_id'], 'status' => $status, 'opinion' => $opinion, 'addtime' => time(), 'pro_step' => $pro_step, 'step_pid' => $step_pid, 'context_type' => 'finish_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        //获取下一步id
        $next_step = $workflow->nextStep($step_pid, $pro_step, $status);
//        var_dump($next_step);exit;
        //更新项目表，下一步
        //获取下一步执行的后台管理员
        $next_admin_id = $this->nextStepSpecifiedAdmin($next_step['step_id'], $context_id);
        $data = array('current_node_index' => $next_step['step_id'],'workflow_id' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'dp_id' => $next_step['dp_id'], 'admin_id' => $next_admin_id);
        if (!D('WorkflowProcess')->updateProcess($context_id, 'finish_id', $data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        $FormInfo = D('ProjectFinish')->findByPk($context_id);
        if ($this->updateMainProcess($FormInfo['pro_id'], $next_step['step_id']) === false) {
            $pro_model->rollback();
            $this->json_error('失败4');
        }
        //推送项目变更消息
        D('Message')->push($admin['admin_id'], $FormInfo['pro_id'], $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($FormInfo['pro_id'], $step_pid, $pro_step, $status);
        self::log('mod', "项目审核:loan_id-$context_id,status-$status,pro_step-$pro_step");
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
            case 5:    //出纳确认，放款成功
                $main_pro_step = 2;
                break;
            default:
                $main_pro_step = 0;
                break;
        }
        if ($main_pro_step > 0) {
            $result = D('Project')->updateByPk($pro_id, array('pro_step' => $main_pro_step, 'step_pid' => 8));
        }
        return $result;
    }
    
    protected function nextStepSpecifiedAdmin($step_id, $finish_id) {
        if ($step_id == 1) {
            return 61;
        }
        return 0;
    }
    
    //
    public function detail() {
        $finish_id = I('get.finish_id');
        $p_model = D('ProjectFinish');
        
        $where['context'] = $finish_id;
        $where['context_type'] = 'finish_id';
        $process_info = D('WorkflowProcess')->where($where)->find();
        $admin = session('admin');
        $map['t.context_id'] = $finish_id;
        $map['t.step_pid'] = 8;
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->applyInfo($finish_id);
        $profit = D('ProjectDebt')->getProfit($data['pro_id']);
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $this->assign('workflow', $workflow);
        $this->assign('process_info', $process_info);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->assign('profit', $profit);
        $this->display('detail');
    }
    
    public function edit() {
        
        $finish_id = I('get.finish_id');
        $admin = session('admin');
        $p_model = D('ProjectFinish');
        $data = $p_model->applyInfo($finish_id);
        $profit = D('ProjectDebt')->getProfit($data['pro_id']);
        
        $this->assign($data);
        $this->assign('profit', $profit);
        $this->display();
    }
    
    public function del() {
        $finish_id = I('get.finish_id');
        
        if (empty($finish_id)) {
            $this->json_error('非法请求');
        }
        $model = D('ProjectFinish');
        $form_info = $model->findByPk($finish_id);
        if (empty($form_info)) {
            $this->json_error('非法请求');
        }
        $where['context'] = $finish_id;
        $where['context_type'] = 'finish_id';
        if (D('WorkflowProcess')->where($where)->count() > 0) {
            $this->json_error('放款申请已经提交，不能删除');
        }
        if ($model->delete($finish_id) === false) {
            $this->json_error('删除失败');
        }
        $this->json_success('删除成功');
    }
    
    //注销显示业
    public function back() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('ProjectFinish');
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
//        $map['wp.start_user|wp.role_id|wp.dp_id'] = array($admin['admin_id'], $admin['role_id'], $admin['dp_id'], '_multi'=>true);
        $map['current_node_index'] = 6;//array(array('LT', 7), array('neq', 0), 'and');
        $result = $model->waitAudit(1, 30, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('back');
    }

    //注销操作
    public function excuteBack() {
        $finish_id = I('get.finish_id');
        $p_model = D('ProjectFinish');
        $type=I('get.type');
        $where['context'] = $finish_id;
        $where['context_type'] = 'finish_id';
        $process_info = D('WorkflowProcess')->where($where)->find();
//        var_dump($process_info);exit;
        $admin = session('admin');
//        if ($admin['admin_id'] != $process_info['admin_id'] || $admin['role_id'] != $process_info['role_id'] || $admin['dp_id'] != $process_info['dp_id']) {
//            $this->json_error('项目已过本审核阶段，请刷新页面');
//        }
        $map['t.context_id'] = $finish_id;
        $map['t.step_pid'] = 8;
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->applyInfo($finish_id);
        $profit = D('ProjectDebt')->getProfit($data['pro_id']);
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        $recover = D('ProjectFinish')->recover();
        $back_list = D('ProjectBack')->where('finish_id='.$finish_id)->relation(true)->select();
        $back_list = array_switch_key($back_list, 'type');
//        var_dump($back_list);exit;

        $this->assign('back_list', $back_list);
        $this->assign('type', $type);
        $this->assign('recover', $recover);
        $this->assign('workflow', $workflow);
        $this->assign('process_info', $process_info);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->assign('profit', $profit);
        $this->display('excute_back');
    }
    
    public function execute() {
        $finish_id = I('get.finish_id');
        $type = I('get.type');
        $admin = session('admin');
        $save_data = array(
            'finish_id' => $finish_id,
            'executor_id' => $admin['admin_id'],
            'execute_time' => time(),
            'addtime' => time(),
            'type' => $type,
        );
        $result = D('ProjectBack')->add($save_data);
        if ($result === false) {
            $this->json_error('操作失败');
        }
        $this->json_success('操作成功');
    }
    
    public function sign() {
        $finish_id = I('get.finish_id');
        $type = I('get.type');
        $admin = session('admin');
        $save_data = array(
            'sign_man_id' => $admin['admin_id'],
            'sign_time' => time(),
        );
        $finish_loan_form = D('ProjectFinish')->findByPk($finish_id, 'admin_id');
        if ($finish_loan_form['admin_id'] !== $admin['admin_id']) {
            $this->json_error('你不是申请人，请勿操作。');
        }
        $map['type'] = $type;
        $map['finish_id'] = $finish_id;
        $result = D('ProjectBack')->where($map)->save($save_data);
        if ($result === false) {
            $this->json_error('操作失败');
        }
        $this->json_success('操作成功');
    }
    
    //项目完结归档
    public function archive() {
        $finish_id = I('get.finish_id');
        $admin = session('admin');
        $form_model = D('ProjectFinish');
        
        $form_info = $form_model->findByPk($finish_id);
        $form_model->startTrans();
        if ($form_model->archive($finish_id, $admin['admin_id']) ===  false) {
            $form_model->rollback();
            $this->json_error('系统错误！000001');
        }
        if (D('Project')->finish($form_info['pro_id']) === false) {
            $form_model->rollback();
            $this->json_error('系统错误！000002');
        }
        $data = array('current_node_index' => 7);
        if (!D('WorkflowProcess')->updateProcess($finish_id, 'finish_id', $data)) {
            $form_model->rollback();
            $this->json_error('失败3');
        }
        $form_model->commit();
        $this->json_success('操作成功');
    }

    /*****
     * 项目完成历史记录
     */
    public function history()
    {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('ProjectFinish');
        $ProcessLogMode=D('ProcessLog');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $logInfo=$ProcessLogMode->historyProject($admin['admin_id']);//获得该角色审核过的项目ID集合
        $total=count($logInfo);
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            $map['t.submit_status'] = 1;
        }
        $map['l.admin_id']=$admin['admin_id'];
        $map['current_node_index'] = array('EGT', 6);//项目完结标示
//        $map['l.pro_step']=1;
       // $map['l.admin_id|l.step_pid|l.pro_step'] = array($admin['admin_id'], 8, 1);
        $result = $model->waitAudit(1, 30, $map,1,1);
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('history');
    }
}
