<?php

namespace Admin\Controller;
use Admin\Lib\Workflow;
use Admin\Lib\MsgTmp;

class RiskControlResearchController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }
    
    //风控尽调待分配状态
    public function undistributed() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            if (!$is_boss) {
                $map['t.admin_id'] = $admin['admin_id'];
            } else {
                $map['submit_status'] = 1;
            }
//            $map['w.dp_id'] = $admin['dp_id'];
        }
        $map['risk_admin_id'] = 0;
        $order = 'risk_admin_id ASC,';
        $map['w.step_pid'] = array(array('GT', 1), array('LT', 3));
        $result = $model->waitAudit(1, 30, $map, $order);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display();
    }
    
    //项目分配
    public function exchange() {
        $pro_id = I('request.pro_id');
        if (empty($pro_id)) {
            $this->json_error('参数错误');
        }
        $model = D('Project');
        $map['pro_id'] = $pro_id;
        $admin = session('admin');
        if (IS_POST) {
            $type = I('post.type'); //0第一次分配1交接
            $save_data['risk_admin_id'] = I('post.risk_admin_id');
            $model->startTrans();
            if (!$model->where($map)->save($save_data)) {
                $model->rollback();
                $this->json_error('内部错误');
            }
            D('Message')->exechangeRcd($save_data['risk_admin_id'], $pro_id);
            
            if (D('ProcessLog')->distribution($pro_id, $admin['admin_id'], $save_data['risk_admin_id'], 2) === false) {
                $model->rollback();
                $this->json_error('内部错误3'.D('ProcessLog')->getError());
            }
            if ($type == 0) {
                D('Backlog')->exchange($pro_id, $save_data['risk_admin_id']);
            }
            $model->commit();
            $this->json_success('修改成功', '', '', true, array('tabid' => 'riskcontrolresearch-undistributed'));
        }
        $data = $model->where($map)->relation(true)->find();
        $this->assign($data);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }

    //项目审核
    public function auditList() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            if (!$is_boss) {
                $where['t.risk_admin_id'] = $admin['admin_id'];
                $where['t.admin_id'] = $admin['admin_id'];
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
            } else {
                $map['submit_status'] = 1;
            }
            $map['w.dp_id'] = $admin['dp_id'];
        }
        $map['w.step_pid'] = 2;
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
    
    public function undo() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            if (!$is_boss) {
                $map['t.admin_id'] = $admin['admin_id'];
                $map['w.dp_id'] = $admin['dp_id'];
            } else {
                $map['submit_status'] = 1;
            }
        }
        $map['pro_step'] = array(array('LT', 11), array('neq', 0), 'and');
        $map['w.step_pid'] = 2;
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

    public function save() {
        $model = D('CapitalFlow');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if ($data['id']) {
            $result = $model->save();
        } else {
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }
    
    //审核界面
    public function auditEdit() {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $map['t.context_id'] = $pro_id;
        $map['t.context_type'] = 'pro_id';
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $exts = getFormerExts();
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('review_file_autho', C('REVIEW_FILE_AUTHO'));
        $this->assign('signin_admin', $admin);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->display('audit_edit');
    }
    
    //项目审核提交接口
    public function audit() {
        $p_model = D('Project');
        $pro_id = I('request.pro_id');
        $status = I('request.status');
        $pro_step = I('request.pro_step');
        $opinion = I('request.opinion');
        $review_files = I('post.reviews');

        $data = $p_model->where(array('pro_id' => $pro_id))->find();
        $this->pro_info = $data;
        $this->process2($pro_id, $pro_step, $status, $data['submit_status'], $opinion, $review_files);
        $this->assign($data);
    }

    /**
     * 
     * @param type $pro_step 现在状态值
     * @param type $status 现在状态通过与否
     * @param type $opinion 意见
     */
    protected function process2($pro_id, $pro_step, $status, $submit_status, $opinion, $review_files) {
        $workflow = new Workflow();
        $admin = session('admin');
        $step_pid = $this->pro_info['step_pid'];
        $pro_detail = array('context_id' => $pro_id, 'admin_id' => $admin['admin_id'], 'status' => $status, 'opinion' => $opinion, 'addtime' => time(), 'pro_step' => $pro_step, 'step_pid' => $step_pid, 'context_type' => 'pro_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        if (!empty($review_files)) {    //插入附件
            if (!D('ProjectFile', 'Logic')->addReviewFile($pro_id, $pro_model->getLastInsID(), $admin['admin_id'], $review_files)) {
                $pro_model->rollback();
                $this->json_error('审核失败。失败原因：内部错误。');
            }
        }
        //获取下一步id
        $next_step = $workflow->nextStep($step_pid, $pro_step, $status);
        //推送待办事项
//        $backlog_msg = MsgTmp::getBacklog($pro_step, $next_step['step_pid'], $this->pro_info['pro_title']);
        $backlog_id = 0;
        //更新项目表，下一步
        $next_step_id = $next_step['step_id'];
        $data = array('pro_step' => $next_step_id,'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'backlog_id' => $backlog_id);
//        if ($submit_status == 0) {
            $data['submit_status'] = $workflow->sumbitStatus($next_step['is_auto']);;
//        }
        if (!D('Project')->updateByPk($pro_id, $data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        //推送项目变更消息
//        $this->workFlowPush($pro_id, $pro_step, $next_step_id, $status);
        D('Message')->push($admin['admin_id'], $pro_id, $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($pro_id, $step_pid, $pro_step, $status);
        self::log('mod', "项目审核:pro_id-$pro_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        session($pro_id . '-pre_contract', null);
        session($pro_id . '-edit_contract', null);
        $this->json_success('成功', '', '', true, array('tabid' => 'riskcontrolresearch-auditList'));
    }
}
