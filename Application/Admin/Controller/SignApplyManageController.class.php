<?php

namespace Admin\Controller;
use Admin\Lib\Workflow;
use Admin\Logic\DepartmentLogic;

class SignApplyManageController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }
    
    //合同预签申请
    public function signApplyList() {
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
//                $where['t.risk_admin_id'] = $admin['admin_id'];
                $map['t.admin_id'] = $admin['admin_id'];
//                $where['_logic'] = 'or';
//                $map['_complex'] = $where;
            } else {
                $map['submit_pre_contract'] = 1;
            }
//            $map['w.dp_id'] = $admin['dp_id'];
        }
//        $map['w.role_id'] = $admin['role_id'];
        $map['t.step_pid'] = 3;
        $map['t.pro_step'] = array('GT', 0);
        $result = $model->waitAuditContract(1, 30, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('sign_apply_list');
    }
    
    //合同预签展示表
    public function prepareSignList() {
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
            }
        }
        $map['submit_pre_contract'] = 1;
        $map['t.step_pid'] = array('EGT', 3);
        $map['t.pro_step'] = array('GT', 0);
        $result = $model->waitAuditContract($page, $pageSize, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('sign_list');
    }
    
    //选择需要合同预签的项目
    public function signApplyToAdd() {
        if (IS_POST) {
            $model = D('Project');
            $supplier_id = I('post.supplier_id');
            if (false === $data = $model->create()) {
                $e = $model->getError();
                $this->json_error($e);
            }
            if (isset($data['pro_type']) && $data['pro_type'] = 'on') {
                $model->pro_type = 1;
                $supplier_id = explode(',', $supplier_id);
                foreach ($supplier_id as $v) {
                    $surpplie[] = array('company_id' => $v);
                }
                $model->supplier = $surpplie;
            }
//            $admin = session('admin');
//            $model->pro_linker = $admin['admin_id'];
            $model->pro_step = 1;
            if ($data['pro_id']) {
                $result = $model->relation('supplier')->save();
                $action = 'mod';
                $message = "修改项目:pro_id-{$data['pro_id']}";
            } 
            if ($result === false) {
                $this->json_error('保存失败');
            } else {
                self::log($action, $message);
                $this->json_success('保存成功', '', '', true, array('tabid' => 'signapplymanage-auditList'));
            }
        }
        $this->display('add');
    }
    
    //未签订合同列表
    public function noApplyList() {
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
                $where['t.admin_id'] = $admin['admin_id'];
            } else {
                $map['submit_pre_contract'] = 1;
            }
            $map['w.dp_id'] = $admin['dp_id'];
        }
        $map['t.step_pid'] = 3;
        $map['t.pro_step'] = 0;
        $result = $model->waitAudit(1, 30, $map);
        $total = $result['total'];
        $list = $result['list'];
        foreach ($list as & $val) {
            if ($val['pro_type'] == 1) {
                $suppliers = D('Company')->getProSupplier($val['pro_id']);
                $val['suppliers_id'] = implode(',', array_column($suppliers, 'company_id'));
                $val['suppliers_name'] = implode(',', array_column($suppliers, 'company_name'));
            }
        }
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display('no_apply_list');
    }
    
    //添加预签合同展示
    public function addPreContract() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        
        $pro_info = D('Project')->findByPk($pro_id);
        $pre_contract_company = D('Company')->getCntractCompany($pro_id);
        foreach ($pre_contract_company as & $val) {
            $val['is_pre_contract'] = D('PrepareContract')->isPreContract($val['pro_id'], $val['company_id']);
            
        }
        $this->assign('pre_contract_company', $pre_contract_company);
        $this->assign('pro_info', $pro_info);
        $this->display('add_pre_contract');
    }

    //提交审核
    public function submitApply() {
        $pro_id = I('request.pro_id');
//        $status = I('request.status');
//        $pro_step = I('request.pro_step');
        $opinion = I('request.opinion');
        $review_files = I('post.reviews');
        
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        $pre_contract_company = D('Company')->getCntractCompany($pro_id);
        foreach ($pre_contract_company as & $val) {
            $is_pre_contract = D('PrepareContract')->isPreContract($val['pro_id'], $val['company_id']);
            if (!$is_pre_contract) {
                $this->json_error('您还有未添加的合同！');
            }
        }
        $p_model = D('Project');
        $data = $p_model->where(array('pro_id' => $pro_id))->find();
        $this->pro_info = $data;
        $this->process2($pro_id, 1, 1, 0, $opinion, $review_files);
        $this->assign($data);
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
        $this->process2($pro_id, $pro_step, $status, $data['submit_pre_contract'], $opinion, $review_files);
        $this->assign($data);
    }
    
    protected function process2($pro_id, $pro_step, $status, $submit_status, $opinion, $review_files) {
        $workflow = new Workflow();
        $admin = session('admin');
        $pro_detail = array('context_id' => $pro_id, 'admin_id' => $admin['admin_id'], 'status' => $status, 'opinion' => $opinion, 'addtime' => time(), 'pro_step' => $pro_step, 'step_pid' => 3, 'context_type' => 'pro_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        if (!empty($review_files)) {
            if (!D('ProjectFile', 'Logic')->addReviewFile($pro_id, $pro_model->getLastInsID(), $admin['admin_id'], $review_files)) {
                $pro_model->rollback();
                $this->json_error('审核失败。失败原因：内部错误。');
            }
        }
        //获取下一步id
        $step_pid = $this->pro_info['step_pid'];
        $next_step = $workflow->nextStep($step_pid, $pro_step, $status);
        //推送待办事项
//        $backlog_msg = MsgTmp::getBacklog($pro_step, $next_step['step_pid'], $this->pro_info['pro_title']);
        $backlog_id = 0;
//        if (!$backlog_id = $this->addBacklog($backlog_msg, $backlog_id)) {
//            $pro_model->rollback();
//            $this->json_error('失败1');
//        }
        //更新项目表，下一步
        $data = array('pro_step' => $next_step['step_id'],'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'backlog_id' => $backlog_id);
        if ($submit_status == 0) {
            $data['submit_pre_contract'] = 1;
        }
        if (!D('Project')->where('pro_id=' . $pro_id)->save($data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        //保存合同信息
//        if (!$this->saveContract($pro_id)) {
//            $pro_model->rollback();
//            $this->json_error(D('PrepareContract')->getError().'失败4');
//        }
        //推送项目变更消息
//        $this->workFlowPush($pro_id, $pro_step, $next_step_id, $status);
        D('Message')->push($admin['admin_id'], $pro_id, $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($pro_id, $step_pid, $pro_step, $status);
        self::log('mod', "项目审核:pro_id-$pro_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        session($pro_id . '-pre_contract', null);
        session($pro_id . '-edit_contract', null);
        $this->json_success('成功', '', '', true, array('tabid' => 'signapplymanage-auditList'));
    }
    
    //新增项目合同信息
    public function addContract() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $pro_linker_info = D('Admin')->getAdminInfo($pro_info['pro_linker']);
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $superviseType = D('PrepareContract')->superviseType();
        
        $this->assign('superviseType', $superviseType);
        $this->assign('company_info', $company_info);
        $this->assign('pro_linker_info', $pro_linker_info);
        $this->assign('contract_debt_type', C('contract_debt_type'));
        $this->assign($pro_info);
        $this->display('add_contract');
    }
    
    //编辑项目合同信息
    public function editContract() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $pro_linker_info = D('Admin')->getAdminInfo($pro_info['pro_linker']);
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $contract_info = D('PrepareContract')->getContract($pro_id, $company_id);
        $superviseType = D('PrepareContract')->superviseType();
        if (isset($contract_info)) {
            $contract_info['demurrage_rate_type1'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 1);
            $contract_info['demurrage_rate_type2'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 2);
            $contract_info['assure_kind'] = explode(',', $contract_info['assure_kind']);
            $contract_info['debt_type'] = explode(',', $contract_info['debt_type']);
//            $bank = D('Bank')->where('bank_id='.$contract_info['pay_for_account'])->find();
//            $this->assign('bank', $bank);
            $this->assign('pre_contract', $contract_info);
        }
        $this->assign('superviseType', $superviseType);
        $this->assign('company_info', $company_info);
        $this->assign('pro_linker_info', $pro_linker_info);
        $this->assign('contract_debt_type', C('contract_debt_type'));
        $this->assign($pro_info);
        $this->display('edit_contract');
    }
    
    //预签表展示
    public function preContractList() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        
        $pro_info = D('Project')->findByPk($pro_id);
        $pre_contract_company = D('Company')->getCntractCompany($pro_id);
        foreach ($pre_contract_company as & $val) {
            $val['is_pre_contract'] = D('PrepareContract')->isPreContract($val['pro_id'], $val['company_id']);
            
        }
        $this->assign('pre_contract_company', $pre_contract_company);
        $this->assign('pro_info', $pro_info);
        $this->display('pre_contract_list');
    }
    
    //展示项目合同信息
    public function preContract() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $pro_linker_info = D('Admin')->getAdminInfo($pro_info['pro_linker']);
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $contract_info = D('PrepareContract')->getContract($pro_id, $company_id);
        $superviseType = D('PrepareContract')->superviseType();
        if (isset($contract_info)) {
            $contract_info['demurrage_rate_type1'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 1);
            $contract_info['demurrage_rate_type2'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 2);
            $contract_info['assure_kind'] = explode(',', $contract_info['assure_kind']);
            $contract_info['debt_type'] = explode(',', $contract_info['debt_type']);
//            $bank = D('Bank')->where('bank_id='.$contract_info['pay_for_account'])->find();
//            $this->assign('bank', $bank);
            $this->assign('pre_contract', $contract_info);
        }
        $this->assign('superviseType', $superviseType);
        $this->assign('company_info', $company_info);
        $this->assign('pro_linker_info', $pro_linker_info);
        $this->assign('contract_debt_type', C('contract_debt_type'));
        $this->assign($pro_info);
        $this->display('pre_contract');
    }
    
    //保存预签合同信息
    public function saveContract() {
        if (IS_POST) {
            $model = D('PrepareContract');
            $pre_contract_id = I('post.pre_contract_id');
            $pre_contract['term'] = I('post.term');
            $pre_contract['cash_deposit'] = (float)I('post.cash_deposit');
            $pre_contract['repurchase_rate'] = (float)I('post.repurchase_rate');
            $pre_contract['handling_charge'] = (float)I('post.handling_charge');
            $pre_contract['counseling_fee'] = (float)I('post.counseling_fee');
            $pre_contract['company_id'] = I('post.company_id');
            $pre_contract['real_money'] = (float)I('post.real_money');
            $pre_contract['penalty_rate'] = (float)I('post.penalty_rate');
            $pre_contract['demurrage_rate2'] = (float)I('post.demurrage_rate2');
            $pre_contract['pro_id'] = I('post.pro_id');
            $pre_contract['purpose'] = I('post.purpose');
            $pre_contract['supervise_type'] = I('post.supervise_type', 0);
            $pre_contract['supervise_bank'] = I('post.supervise_bank');
            $pre_contract['supervise_account'] = I('post.supervise_account');
            $pre_contract['supervise_num'] = I('post.supervise_num');
            $assure_kind = I('post.assure_kind', array());
            $pre_contract['assure_detail'] = I('post.assure_detail');
            $debt_type = I('post.debt_type', array());
            $pre_contract['debt_another'] = I('post.debt_another');
            $pre_contract['pro_kind'] = I('post.pro_kind');
            $pre_contract['company_account'] = I('post.company_account');
            $pre_contract['company_bank'] = I('post.company_bank');
            $pre_contract['company_num'] = I('post.company_num');
            if (in_array(4, $debt_type)) {
                if (empty($pre_contract['debt_another'])) {
                    $this->json_error('非法操作');
                }
            }
            $pre_contract['debt_type'] = implode(',', $debt_type);
            $pre_contract['assure_kind'] = implode(',', $assure_kind);
            $demurrage_rate_type1 = I('post.demurrage_rate_type1', 0);
            $demurrage_rate_type2 = I('post.demurrage_rate_type2', 0);
            $pre_contract['demurrage_rate_type'] = bindec($demurrage_rate_type1 . $demurrage_rate_type2);
            if (empty($pre_contract_id)) {
                $pre_contract['addtime'] = time();
                $result = $model->add($pre_contract);
            } else {
                $result = $model->updateByPk($pre_contract_id, $pre_contract);
            }
            if ($result === false) {
                $this->json_error('保存失败'.$model->_sql());
            } else {
                $this->json_success('保存成功', '', '', true, array('dialogid' => 'signapplymanage-addprecontract'));
            }
        }
    }
    
    //查看项目合同信息
    public function detailContract() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $pro_linker_info = D('Admin')->getAdminInfo($pro_info['pro_linker']);
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $contract_info = D('PrepareContract')->getContract($pro_id, $company_id);
        $superviseType = D('PrepareContract')->superviseType();
        if (isset($contract_info)) {
//            $this->assign('pre_contract', $pre_contracts);
//            $contract_info = D('PrepareContract')->where(array('pro_id' => $pro_id, 'company_id' => $company_id))->find();
            $contract_info['demurrage_rate_type1'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 1);
            $contract_info['demurrage_rate_type2'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 2);
            $contract_info['assure_kind'] = explode(',', $contract_info['assure_kind']);
            $contract_info['debt_type'] = explode(',', $contract_info['debt_type']);
//            $bank = D('Bank')->where('bank_id='.$contract_info['pay_for_account'])->find();
//            $this->assign('bank', $bank);
            $this->assign('pre_contract', $contract_info);
        }
        $this->assign('superviseType', $superviseType);
        $this->assign('company_info', $company_info);
        $this->assign('pro_linker_info', $pro_linker_info);
        $this->assign('contract_debt_type', C('contract_debt_type'));
        $this->assign($pro_info);
        $this->display('detail_contract');
    }
    
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
            $map['submit_pre_contract'] = 1;
            $map['w.step_role_id'] = $admin['role_id'];
        }
        $map['t.pro_step'] = array('GT', 0);
        $map['t.step_pid'] = 3;
        
        $result = $model->waitAuditContract(1, 30, $map);
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
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $map['t.context_id'] = $pro_id;
        $map['t.context_type'] = 'pro_id';
        $map['t.step_pid'] = 3;
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        //合同预签显示合同信息
        $company_list = D('Company')->getCntractCompany($pro_id);
        $this->assign('company_list', $company_list);
        
        $exts = getFormerExts();
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list['list']);
        $this->assign($data);
        $this->display('audit_edit');
    }
    
    //合同信息显示
    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('ProjectContract');
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['p.pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            if (!$is_boss) {
//                $where['t.risk_admin_id'] = $admin['admin_id'];
                $map['p.admin_id'] = $admin['admin_id'];
//                $where['_logic'] = 'or';
//                $map['_complex'] = $where;
            } else {
                $map['submit_pre_contract'] = 1;
            }
        }
        
        $result = $model->projectContract(1, 30, $map);
        $total = $result['total'];
        $list = $model->formatData($result['list']);
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
        $this->display();
    }
    
    //查看项目正式合同信息
    public function realContract() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $contract_info = D('ProjectContract')->getContract($pro_id, $company_id);
//        var_dump($contract_info);exit;
        if (empty($contract_info)) {
            $contract_info = D('PrepareContract')->getContract($pro_id, $company_id);
        }
        $contract_info['demurrage_rate_type1'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 1);
        $contract_info['demurrage_rate_type2'] = D('PrepareContract')->demurrageRateType($contract_info['demurrage_rate_type'], 2);
        $interest_type = D('ProjectContract')->interestTypeDesc();
        
        $this->assign('interest_type', $interest_type);
        $this->assign('pre_contract', $contract_info);
        $this->assign('company_info', $company_info);
        $this->assign($pro_info);
        $this->display('real_contract');
    }
    
    public function saveRealContract() {
        if (IS_POST) {
            $model = D('ProjectContract');
            $contract_id = I('post.contract_id');
            $pre_contract['term'] = I('post.term');
            $pre_contract['cash_deposit'] = (float)I('post.cash_deposit');
            $pre_contract['repurchase_rate'] = (float)I('post.repurchase_rate');
            $pre_contract['handling_charge'] = (float)I('post.handling_charge');
            $pre_contract['counseling_fee'] = (float)I('post.counseling_fee');
            $pre_contract['company_id'] = I('post.company_id');
            $pre_contract['real_money'] = (float)I('post.real_money');
            $pre_contract['penalty_rate'] = (float)I('post.penalty_rate');
            $pre_contract['demurrage_rate2'] = (float)I('post.demurrage_rate2');
            $pre_contract['contract_no'] = I('post.contract_no');
            $pre_contract['pro_id'] = I('post.pro_id');
            $pre_contract['interest_type'] = I('post.interest_type');
            $pre_contract['is_day_interest'] = I('post.is_day_interest', 0);
            
            $demurrage_rate_type1 = I('post.demurrage_rate_type1', 0);
            $demurrage_rate_type2 = I('post.demurrage_rate_type2', 0);
            $pre_contract['demurrage_rate_type'] = bindec($demurrage_rate_type1 . $demurrage_rate_type2);
            $pre_contract['addtime'] = time();
            if (empty($contract_id)) {
                $result = $model->add($pre_contract);
            } else {
                $result = $model->updateByPk($contract_id, $pre_contract);
            }
            if ($result === false) {
                $this->json_error('保存失败'.$model->_sql());
            } else {
                $this->json_success('保存成功', '', '', true, array('tabid' => 'signapplymanage-index'));
            }
        }
    }
    
    //删除未提交的合同预签表
    public function del() {
        $pro_id = I('get.pro_id');
        $model = D('PrepareContract');
        $pro_info = D('Project')->where('pro_id=' . $pro_id)->find();
        if ($pro_info['is_submit'] == 1) {
            $this->json_error('你没有操作权限，请联系管理员');
        }
        $model->startTrans();
        if ($model->where('pro_id=' . $pro_id)->delete() === false) {
            $model->rollback();
            $this->json_error('操作失败');
        }
        if (D('Project')->where('pro_id=' . $pro_id)->save(array('pro_step' => 0)) === false) {
            $model->rollback();
            $this->json_error('操作失败');
        }
        $model->commit();
        $this->json_success('操作成功');
    }
    
    //合同扫描件
    public function contractScanFile() {
        $contract_id = I('get.contract_id');
        $list = D('ContractFile')->where('contract_id=' . $contract_id)->select();
        $this->assign('list', $list);
        $this->assign('contract_id', $contract_id);
        $this->display('contract_scan_file');
    }
    
    //上传附件
    public function upload_attachment() {
        $contract_id = I('request.contract_id');
        if (empty($contract_id)) {
            $this->json_error('非法操作');
        }
        $admin = session('admin');
//        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
//            $this->json_error('您没有上传的权限');
//        }
        $field = 'contract-'.$contract_id;
        $upload_info = upload_file('/project/contract/', $field);
//        $this->ajaxReturn(array('status' => 1, 'data' => array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'))));
        if (isset($upload_info['file_path'])) {
            $save_data['contract_id'] = $contract_id;
            $save_data['path'] = $upload_info['file_path'];
            $save_data['filename'] = $upload_info['name'];
            $save_data['addtime'] = time();
            
            $save_data['admin_id'] = $admin['admin_id'];
            if (!($aid = D('ContractFile')->add($save_data))) {
                $this->json_error('上传失败');
            }
            $content = array('file_path' => $upload_info['file_path'],'file_id' => date('YmdHis'), 'file_name'=>$upload_info['name'], 'addtime'=> date("Y-m-d H:i:s", $save_data['addtime']), 'aid' => $aid, 'contract_id' => $contract_id);
            $this->ajaxReturn(array('statusCode' => 200, 'content'=>$content, 'message'=>'上传成功'));
        }
        $this->json_error('上传失败');
    }
    
    //删除附件
    public function remove_attachment() {
        $id = I('request.id');
        $contract_id = I('request.contract_id');
        $admin = session('admin');
        //只删数据库
//        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
//            $this->json_error('您没有删除的权限');
//        }
        $model = D('ContractFile');
        $res1 = $model->where(array('id' => $id, 'contract_id' => $contract_id))->delete();
        if ($res1) {
            $this->json_success('删除成功','', '','', array('dialog'=>'contract-file'));
        } else {
            $this->json_error('删除失败');
        }
    }
    
    public function saveContractDone() {
        $this->json_success('保存成功', '', '', true);
    }
    
    public function downloadPrecontract() {
        $pre_contract_id = I('get.pre_contract_id');
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $pro_linker_info = D('Admin')->getAdminInfo($pro_info['pro_linker']);
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $contract_info = D('PrepareContract')->getContract($pro_id, $company_id);
        $superviseType = D('PrepareContract')->superviseType();
        
        $contract_info['assure_kind'] = explode(',', $contract_info['assure_kind']);
        $contract_info['debt_type'] = explode(',', $contract_info['debt_type']);
        $bank = D('Bank')->where('bank_id='.$contract_info['pay_for_account'])->find();
        $debt = '';
        $contract_debt_type = C('contract_debt_type');
        $enter = chr(13);
        foreach($contract_debt_type as $key => $v) {
            $debt .= (in_array($key, $contract_info['debt_type']) ? '√' : '□') . $v .$enter;
        }
        $debt = substr($debt, 0, -1);  //去掉最后一个换行
        $debt .= $contract_info['debt_another'];
        $total_fee = $contract_info['repurchase_rate'] + $contract_info['handling_charge'];
        $str_bank = '银行账户:';
        foreach($superviseType as $key => $val) {
            $str_bank .= '   ' . ($key == $contract_info['supervise_type'] ? '√' : '□') . $val;
        }
        $assure_str = '担保方名称：';
        $assure_str .= in_array(1, $contract_info['assure_kind']) ? '√个人' : '□个人';
        $assure_str .= '   ' . in_array(2, $contract_info['assure_kind']) ? '√企业' : '□企业';
        
        $map1['t.context_id'] = $pro_id;
        $map1['t.context_type'] = 'pro_id';
        $map1['t.step_pid'] = 3;
        $process_list = D('ProcessLog')->getList(1, 30, $map1);  //审核意见
        $ar = array(
            'A2' => $pro_linker_info['department'],   //业务部门
            'C2' => $pro_linker_info['real_name'],   //项目经理
            'E2' => date('Y-m-d', time()),
            'C3' => $company_info['company_name'],  //客户名称
            'C4' => $contract_info['pro_kind'] == 0 ? '√明保理' : '□明保理',
            'D4' => $debt,
            'C5' => $contract_info['pro_kind'] == 1 ? '√暗保理' : '□暗保理',
            'C6' => " {$contract_info['term']} 个月，以实际放款日为准起算。",   //保理期限
            'C7' => "￥{$contract_info['real_money']}万元",     //保理金额
            'C8' => "￥{$contract_info['cash_deposit']}%",                               //保证金费率
            'C9' => $contract_info['purpose'],
            'E7' => sprintf('共计：   %d   %%（年化）%s%s费率明细：%s回购费率  %d  %%%s%s手续费率  %d %%',$total_fee, $enter, $enter, $enter, $contract_info['repurchase_rate'], $enter, $enter, $contract_info['handling_charge'], $enter, $contract_info['handling_charge']),
            'B10' => sprintf($str_bank.'%s户名：%s%s%s户行：%s%s%s帐号：%s',$enter, $contract_info['supervise_account'], $enter, $enter, $contract_info['supervise_bank'], $enter, $enter, $contract_info['supervise_num']),
            'B11' => sprintf($assure_str . '%s%s', $enter, $contract_info['assure_detail']),
            'B12' => sprintf('放款账户:%s户名：%s%s开户行：%s%s账号：%s', $enter, $bank['account_name'], $enter, $bank['bank_name'], $enter, $bank['bank_no']),
            'B13' => $process_list['list'][1]['opinion'],
            'B14' => $process_list['list'][2]['opinion'],
            'B15' => $process_list['list'][3]['opinion'],
            'D16' => '申请日期：' . date('Y', $contract_info['addtime']) . '年' .date('n', $contract_info['addtime']) .'月' . date('d', $contract_info['addtime']) .'日',
        );
        $file = TMP_PATH.'excel/20160810_pre_contract.xls';
        $execl = new \Admin\Lib\PHPexecl();
        $filename = "合同预签申请表({$pro_info['pro_title']})";
        $execl->importExecl($file, $ar, $filename);
    }
    
    //根据项目返回合同信息
    public function contract() {
        $pro_id = I('get.pro_id');

        $model = D('ProjectContract');
        $map['proc.pro_id'] = $pro_id;
        $result = $model->projectContract(1, 30, $map);
        $total = $result['total'];
        $list = $model->formatData($result['list']);

        $this->assign(array('total' => $total, 'list' => $list));
        $this->display();
    }
}
