<?php

namespace Admin\Controller;

use Admin\Lib\Privilege;
use Admin\Logic\DepartmentLogic;
use Admin\Model\CapitalFlowModel;
use Admin\Model\WorkflowModel;
use Admin\Lib\MsgTmp;
use Admin\Lib\Workflow;
use Admin\Lib\WorkflowService;

class ProjectController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    protected function rules() {
        return array(
            'del' => array('type' => 'project', 'operation' => Privilege::DEL)
        );
    }

    //列表
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
        $map['step_pid'] = array('GT', 0);
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
        $this->display('all_list');
    }
    /*********
     * author:lmj
     * 我的审核项目
     * version:new
     */
    public function MyAudit()
    {
        $admin = session('admin');
        $model=D('project');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pageAction=D('admin')->where('`admin_id`=%d',array($admin['admin_id']))->field('authpage')->find();
        $list= $model->isAudit($page,$pageSize,'t.addtime DESC',$admin['admin_id'],$admin['role_id'],0);
        $this->assign(array('name'=>$admin['real_name'],'list'=>$list['list'],'total' => $list['total'], 'pageCurrent' => $page,));
        $this->display();
      // logic('xml')->index();
        
    }
    /********
     * author:lmj
     * 去审核
     * version:new
     */
    public function MyAuditProject()
    {
        $plId=I('get.pl_id');//审核表流程id
        $wfId=I('get.wf_id');//项目总流程表
        $xmlId=I('get.xml_id');//项目总流程表
        $pjId=I('get.pj_id');//项目id
        $proLevel=I('get.pro_level');//当前审批次数
        $proTimes=I('get.pro_times');//当前审批轮次
        $this->assign(array('plId'=>$plId,'wfId'=>$wfId,'xmlId'=>$xmlId,'pjId'=>$pjId,'proLevel'=>$proLevel,'proTimes'=>$proTimes));
        $this->display();
        //$auditType=I('get.auditType');//选择的类型
    }
    /*******
     * author:lmj
     * 保存提交的审核数据
     * version:new
     */
    public  function saveMyAudit()
    {
        $plId=I('get.plId');
        $wfId=I('get.wfId');
        $xmlId=I('get.xmlId');
        $pjId=I('get.pjId');
        $auditType=I('get.auditType');
        $proLevel=I('get.proLevel');//当前审批级别
        $proTimes=I('get.proTimes');//当前审批轮次
        $admin = session('admin');
       // $aa=xmlIdToInfo($xmlId);
       // $adminId=I('get.adminId');//管理员id,如果没有管理员id
        $xmlInfo=logic('xml')->index()[xmlIdToInfo($xmlId)['TARGETREF']];//获取即将审核人的xml信息

        $proRoleId=roleNameToid(explode('_',$xmlInfo['name'])[0]);//审批人角色id   用explode  因为xml软件不允许项目框同名

        //更新旧流程日志表的状态workflow_log
         $oldWorkFolwObj=D('WorkflowLog')->where("`pl_id`=%d",array($plId))->data(array('pro_state'=>$auditType,'pro_last_edit_time'=>time()))->save();

        //更新项目流程表的审批次数
         $oldPjWorkFolwObj=D('PjWorkflow')->where("`wf_id`=%d",array($wfId))->setInc('pro_level_now',1);

        //新建send_process表
        $newSendProcess=D('SendProcess')
            ->data(array('wf_id'=>$wfId,'sp_author'=>$admin['admin_id'],'sp_message'=>'已提交','sp_addtime'=>time(),'sp_role_id'=>$admin['role_id']))
            ->add();

        //新建workflow表
        $newWorkFlowLog=D('WorkflowLog')
            ->data(array('pj_id'=>$pjId,'sp_id'=>$newSendProcess,'wf_id'=>$wfId,
                'pro_level'=>$proLevel+1,'pro_times'=>$proTimes,'pro_state'=>0,'pro_addtime'=>time(),'pro_role'=>$proRoleId,'pro_xml_id'=>xmlIdToInfo($xmlId)['TARGETREF']))
            ->add();

        if($oldWorkFolwObj && $oldPjWorkFolwObj)
        {
            $this->json_success('成功', '', '', true, array('tabid' => 'project-auditList'));
        }
    }














    
    //项目立项
    public function start() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $submit_status = I('post.submit_status', -1);
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        if ($submit_type > -1) {
            $map['submit_status'] = $submit_status;
        }
        $admin = session('admin');
        $map['pro_linker'] = $admin['admin_id'];
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('start');
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
                if (!DepartmentLogic::isRCD()) {
                    $map['t.admin_id'] = $admin['admin_id'];
                } else {
                    $map['w.step_role_id'] = $admin['role_id'];
                }
                $map['w.dp_id'] = $admin['dp_id'];
            } else {
                $map['submit_status'] = 1;
            }
        }
//        $map['pro_step'] = array(array('LT', 11), array('neq', 0), 'and');
        $map['t.step_pid'] = 1;
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
        //更新项目表，下一步
        $next_step_id = $next_step['step_id'];
        $data = array('pro_step' => $next_step_id,'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id']);
//        if ($submit_status == 0) {
            $data['submit_status'] = $workflow->sumbitStatus($next_step['is_auto']);
//        }
        if (!D('Project')->updateByPk($pro_id, $data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        //发送邮件
//        send_mail($to_mail, $title, $content, $from);
        //推送项目变更消息
        D('Message')->push($admin['admin_id'], $pro_id, $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($pro_id, $step_pid, $pro_step, $status);
        self::log('mod', "项目审核:pro_id-$pro_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        session($pro_id . '-pre_contract', null);
        session($pro_id . '-edit_contract', null);
        $this->json_success('成功', '', '', true, array('tabid' => 'project-auditList'));
    }

    /* 项目立项 */

    public function add() {
        $admin = session('admin');
        $this->assign('admin', $admin);
        $this->display();
    }

    /* 编辑管理员 */

    public function edit() {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        if (DepartmentLogic::isPMD($admin['dp_id']) && $admin['position_id'] <= 2) {
            $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        } else {
            $data = $p_model->where(array('pro_linker' =>$admin['admin_id'], 'pro_id' => $pro_id))->relation(true)->find();
            if ($data['submit_status'] == 1 && $data['pro_step'] != 0) {
                $this->json_error('此项目已提交，不能修改');
            }
        }
        if ($data['pro_type'] == 1) {
            foreach ($data['supplier'] as $val) {
                $data['supplier_id'][] = $val['company_id'];
                $data['supplier_name'][] = $val['company_name'];
            }
            $data['supplier_id'] = implode(',', $data['supplier_id']);
            $data['supplier_name'] = implode(',', $data['supplier_name']);
        }
        $this->assign($data);
        $this->assign('pro_type',$data['pro_type']);
        $this->display();
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
        $this->assign('process_list', $process_list['list']);
        $this->assign('signin_admin', $admin);
        $this->assign($data);
        $this->display('audit_edit');
    }

    //审核资料附件
    public function fileReviewList() {
        $map['pro_id'] = I('get.pro_id');
        $map['log_id'] = I('get.step_id');
        $admin = session('admin');
        $log_info = D('ProcessLog')->findByPk($map['log_id']);
        if ($log_info['step_pid'] == 1 && $log_info['pro_step'] == 4) {
            if (!in_array($admin['admin_id'], C('REVIEW_FILE_AUTHO'))) {
                $this->json_error('您没有查看文件权限');
            }
        }
        $list = D('ProjectReview')->where($map)->select();
        $this->assign('list', $list);
        $this->display('file_review_list');
    }

    public function detail() {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $map['t.context_id'] = $pro_id;
        $map['t.context_type'] = 'pro_id';
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $map1['lf.pro_id'] = $pro_id;
        $loan_log = D('ProcessLog')->getLoanList(1, 30, $map1);
        $process_list = array_merge($loan_log['list'], $process_list['list']);
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $exts = getFormerExts();
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list);
        $this->assign('review_file_autho', C('REVIEW_FILE_AUTHO'));
        $this->assign('signin_admin', $admin);
        $this->assign($data);
        $this->display();
    }

    public function submit() {
        $p_model = D('Project');
        $pro_id = I('request.pro_id');
        $admin = session('admin');
        $data = $p_model->where(array('pro_linker' => $admin['admin_id'], 'pro_id' => $pro_id))->relation(true)->find();
        $this->pro_info = $data;
        if ($data['submit_status'] == 1) {
            $this->json_error('此项目已提交，不能重复提交');
        }
        if (IS_POST) {
            $opinion = '--';
            if ($data['shorcut_flow'] == 1) {   //快捷方式
                $this->shortcutProcess($pro_id);
            } else {    //正常流程
                $this->process2($pro_id, 1, 1, 1, $opinion);
            }
        }
        $this->assign($data);
        $this->display();
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
    protected function shortcutProcess($pro_id) {
        $workflow = new Workflow();
//        $now_step = $workflow[$pro_step];
        $step_pid = 1;
        $pro_step = 1;
        $status = 1;
        $admin = session('admin');
        $pro_detail = array('context_id' => $pro_id, 'admin_id' => $admin['admin_id'], 'status' => 1, 'opinion' => '--', 'addtime' => time(), 'pro_step' => 1, 'step_pid' => 1, 'context_type' => 'pro_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        
        //获取下一步id
        $next_step = $workflow->nextStep(1, 3, 1);
        $next_step_id = $next_step['step_id'];
        //更新项目表，下一步
        $data = array('pro_step' => $next_step_id,'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id']);
        $data['submit_status'] = 1;
        $before_pro_info = D('Project')->findByPk($this->pro_info['before_pro_id']);
        $data['admin_id'] = $before_pro_info['admin_id'];
        $data['pro_linker'] = $before_pro_info['pro_linker'];
        if (!D('Project')->where('pro_id=' . $pro_id)->save($data)) {
            $pro_model->rollback();
            $this->json_error('失败');
        }
        
        //推送项目变更消息
        D('Message')->push($admin['admin_id'], $pro_id, $step_pid, $pro_step, $status);
        D('Backlog')->addBackLog($pro_id, $step_pid, 3, $status);
        self::log('mod', "项目审核:pro_id-$pro_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        session($pro_id . '-pre_contract', null);
        session($pro_id . '-edit_contract', null);
        $this->json_success('成功', '', '', true, array('tabid' => 'project-auditList'));
    }

    /* 保存项目 */

    public function save_project() {
        $model = D('Project');
        $supplier_id = I('post.supplier_id');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if ($model->pro_type == 1) {
            $supplier_id = explode(',', $supplier_id);
            foreach ($supplier_id as $v) {
                $surpplie[] = array('company_id' => $v);
            }
           // put(serialize($surpplie));
            $model->supplier = $surpplie;
        }
        
        if ($data['pro_id']) {
            $result = $model->relation('supplier')->save();
            $action = 'mod';
            $message = "修改项目:pro_id-{$data['pro_id']}";
        } else {
            $admin = session('admin');
            $model->pro_linker = $admin['admin_id'];
//            $this->setPm($model);
            $result = $model->relation('supplier')->add();
            $action = 'add';
            $message = "新增项目:pro_id-$result";
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            self::log($action, $message);
            $this->json_success('保存成功', '', '', true, array('tabid' => 'project-start'));
        }
    }
    
    protected function setPm(& $model) {
        if (isset($model->before_pro_id) && !empty($model->before_pro_id)) {
            $pro_obj = new \Admin\Model\ProjectModel();
            $before_pro_info = $pro_obj->findByPk($model->before_pro_id);
            $model->pro_linker = $before_pro_info['pro_linker'];
        } else {
            $admin = session('admin');
            $model->pro_linker = $admin['admin_id'];
        }
        return true;
    }

    /* 取消提交项目，由于区分和提交过后的项目删除操作 */

    public function cancel() {
        $pro_id = I('get.pro_id');
        $model = D('Project');
        $pro_info = $model->where('pro_id=' . $pro_id)->find();
        if ($pro_info['is_submit'] == 1) {
            $this->json_error('你没有操作权限，请联系管理员');
        }
        $state = $model->delete($pro_id);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }

    /* 删除项目 */

    public function del() {
        $pro_id = I('get.pro_id');
        $model = D('Project');
        $state = $model->delete($pro_id);
        if ($state !== false) {
            self::log('del', __FUNCTION__, "删除项目：pro_id-$pro_id");
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }

    //文件树
    public function file() {
        $map['pro_id'] = I('get.pro_id');
        $file_tree = D('ProjectFile')->where($map)->select();
        $file_tree = array_reverse($file_tree);
//        var_dump($file_tree);exit;
        foreach ($file_tree as $v) {
            $array[$v['file_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $file_tree = $tree->get_array(0);
//        var_dump($file_tree[1]['sub'][7]['sub']);exit;
        $this->assign('file_tree', $file_tree);
        $this->assign($map);
        $this->display();
    }

    //上传页面
    public function upload() {
        $map['pro_id'] = I('get.pro_id');
        $map['file_id'] = I('get.file_id');
        $list = D('ProjectAttachment')->where($map)->select();
        $exts = getFormerExts();
        
        $this->assign('exts', $exts);
        $this->assign('list', $list);
        $this->assign($map);
        $this->display();
    }

    //上传附件
    public function upload_attachment() {
        $pro_id = I('request.pro_id');
        $file_id = I('request.file_id');
//        var_dump($_POST);exit;
        $admin = session('admin');
        $role_id = $admin['role_id'];
        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
            $this->json_error('您没有上传的权限');
        }
        session('pro_id', $pro_id);
        $field = 'pro-' . $pro_id;
        $short_name = D('ProjectFile')->where('file_id=' . $file_id)->getField('short_name');
        $upload_info = upload_file('/project/attachment/', $field, $short_name . '-');
        if (isset($upload_info['file_path'])) {
            $save_data['file_id'] = $file_id;
            $save_data['pro_id'] = $pro_id;
            $save_data['path'] = $upload_info['file_path'];
            $save_data['doc_name'] = $upload_info['name'];
            $save_data['addtime'] = time();
            $save_data['sha1'] = $upload_info['sha1'];
            $save_data['admin_id'] = $admin['admin_id'];
            if (!($aid = D('ProjectAttachment')->add($save_data))) {
                $this->json_error('上传失败');
            }
            $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", $save_data['addtime']), 'aid' => $aid);
            self::log('add', json_encode($content));
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败,' . $upload_info);
    }


    //上传审核资料
    public function uploadToReview() {
        $pro_id = I('request.pro_id');
        $field = 'pro-' . $pro_id;
        $upload_info = upload_file('/project/review/', $field);
        $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", time()));
        if (isset($upload_info['file_path'])) {
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败,' . $upload_info);
    }

    //文件夹上传(没用)
    public function upDocument() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->error('非法操作');
        }
        $admin = session('admin');
        $role_id = $admin['role_id'];
        if (!$this->checkUpDocAuth($pro_id, $role_id)) {
            $this->error('项目已被提交，禁止使用文件夹上传功能');
        }
        $field = 'pro-' . $pro_id;
        $upload_info = upload_document('/project/attachment/', $field);
        foreach ($upload_info as $val) {
            $file_names = explode('-', $val['savename']);
            $file_id = $file_names[0];
            $save_data['file_id'] = $file_id;
            $save_data['pro_id'] = $pro_id;
            $save_data['path'] = '/Uploads' . $val['savepath'] . $val['savename'];
            $save_data['doc_name'] = $val['name'];
            $save_data['addtime'] = time();
            $save_data['admin_id'] = $admin['admin_id'];
            $save_data['sha1'] = $val['sha1'];
            $save_datas[] = $save_data;
        }
        if (!($aid = D('ProjectAttachment')->addAll($save_datas))) {
            $this->error('上传失败');
        }
        self::log('add', json_encode($save_datas));
        var_dump($upload_info);
    }

    /**
     * 文件夹上传权限判断，提交项管以后就不让使用文件夹上传功能
     * @param type $pro_id
     * @param type $role_id
     * @return boolean
     */
    protected function checkUpDocAuth($pro_id, $role_id) {
        $sumbit_stauts = D('Project')->getSubStatus($pro_id);
        if ($sumbit_stauts == 1) {
            return false;
        }
//        if ($sumbit_stauts == 1 && !in_array($role_id, array(14, 2))) {
//            return false;
//        }
        return true;
    }

    /**
     * 查询上传的权限
     * @param type $pro_id
     * @param type $file_id
     * @param type $role_id
     * @return boolean
     */
    protected function checkAuthUpload($pro_id, $file_id, $role_id) {
        if (isSupper()) {
            return true;
        }
        $file_auth_flag = D('ProjectFile')->where('file_id=' . $file_id)->getField('is_auth');
        if ($file_auth_flag == 0) {     //不用鉴权
            return true;
        }
        $pro_step = D('Project')->where('pro_id=' . $pro_id)->getField('pro_step');
        //现在项目提交以后项目经理不能提交文档；判断权限是否在可上传的权限中；暂时不能灵活配置
        if ($pro_step > 1) {
            if (!in_array($role_id, array(14, 2))) {
                return false;
            }
        }
        return true;
    }

    //删除附件
    public function remove_attachment() {
        $file_path = I('request.file_path');
        $aid = I('request.aid');
        $pro_id = I('request.pro_id');
        $file_id = I('request.file_id');
        $admin = session('admin');
        $role_id = $admin['role_id'];
        //只删数据库
        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
            $this->json_error('您没有删除的权限');
        }
        $model = D('ProjectAttachment');
        $map = array('id' => $aid, 'pro_id' => $pro_id);
        $model->startTrans();
        $attachment_info = $model->getOneByCondition($map);
        if (empty($attachment_info)) {
            $model->rollback();
            $this->json_error('文件已删除');
        }
        $res1 = $model->where($map)->delete();
        //文件不在的话就只删除数据库
        if (file_exists($attachment_info['path'])) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
//        $res2 = unlink('.'.$file_path);
        if ($res1 && $res2) {
            self::log('del', "删除附件：aid-$aid,pro_id-$pro_id");
            $model->commit();
            $this->json_success('删除成功', '', '', '', array('divid' => 'layout-01'));
        } else {
            $model->rollback();
            $this->json_error('删除失败');
        }
    }
    //删除附件
    public function remove_review() {
        $file_path = I('request.file_path');
        //文件不在的话就只删除数据库
        if (file_exists($file_path)) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
//        $res2 = unlink('.'.$file_path);
        if ($res2) {
            $this->json_success($file_path);
        } else {
            $this->json_error('删除失败');
        }
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
    
    public function source() {
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
        $this->display('source');
    }

    //完结项目
    public function finish() {
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
        $map['finish_status'] = 1;
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

    public function lookUp() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $is_loan = I('request.is_loan');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }
        
        if (!empty($is_loan)) {
            $map['is_loan'] = $is_loan;
        }
        $admin = session('admin');
        if ($admin['role_id'] == 16) {
            $map['pro_linker'] = $admin['admin_id'];
        }
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('look_up');
    }

    //等待分配的项目
    public function undistributed() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_no = I('post.pro_no');
        if (!empty($pro_no)) {
            $map['pro_no'] = $pro_no;
        }

        $map['submit_status'] = 1;
        $map['_query'] = " admin_id=''&risk_admin_id= '&_logic=or";
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }

    public function distribute() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('参数错误');
        }
        $model = D('Project');
        $data = $model->where('pro_id=' . $pro_id)->relation(true)->find();
        $this->assign($data);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }

    //已放款项目和放款管理
    public function loanIndex() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Project');
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
        $map['is_loan'] = array('EQ', 1);
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('loan_index');
    }

    //项目交接
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
            $save_data['admin_id'] = I('post.admin_id');
            $model->startTrans();
            if (!$model->where($map)->save($save_data)) {
                $model->rollback();
                $this->json_error('内部错误1');
            }
            if (D('Message')->exechangePmd($save_data['admin_id'], $pro_id) === false) {
                $model->rollback();
                $this->json_error('内部错误2');
            }
            if (D('ProcessLog')->distribution($pro_id, $admin['admin_id'], $save_data['admin_id'], 1) === false) {
                $model->rollback();
                $this->json_error('内部错误3'.D('ProcessLog')->getError());
            }
            if ($type == 0) {
                D('Backlog')->exchange($pro_id, $save_data['admin_id']);
            }
            $model->commit();
            $this->json_success('修改成功', '', '', true, array('tabid' => 'project-auditList'));
        }
        $data = $model->where($map)->relation(true)->find();
        $this->assign($data);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }

    //终止的项目
    public function end() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%'.$pro_title.'%');
        }

        $admin = session('admin');
        $map['submit_status'] = 1;
        $map['pro_step'] = 0;
        $map['step_pid'] = 0;
    if (!isBoss() && !isSupper()) {
            $map['admin_id|pro_linker'] = array($admin['admin_id'], $admin['admin_id'], '_multi'=>true);
        }
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }

    //重新发起
    public function restart() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        $model = D('Project');
        $pro_info = $model->getById($pro_id);
        $this->pro_info = $pro_info;
//        var_dump($pro_info);exit;
        if ($pro_info['pro_step'] != 0 && $pro_info['step_pid'] != 0) {
            $this->json_error('非法操作');
        }
        $this->restartDo($pro_id, 6, 1, 1, '', array());
        if (!$model->restart($pro_id)) {
            $this->json_error('修改失败');
        }
        $this->json_success('修改成功');
    }
    
    protected function restartDo($pro_id, $step_pid, $pro_step, $status, $opinion) {
        $workflow = new Workflow();
        $admin = session('admin');
        $pro_detail = array('context_id' => $pro_id, 'admin_id' => $admin['admin_id'], 'status' => $status, 'opinion' => $opinion, 'addtime' => time(), 'pro_step' => $pro_step, 'step_pid' => $step_pid, 'context_type' => 'pro_id');
        $pro_model = D('ProcessLog');
        $pro_model->startTrans();
        if (!$pro_model->add($pro_detail)) {     //更新意见表
            $pro_model->rollback();
            $this->json_error('审核失败。失败原因：内部错误。');
        }
        //获取下一步id
        $next_step = $workflow->nextStep($step_pid, $pro_step, $status);
        //推送待办事项
        $backlog_id = 0;
        //更新项目表，下一步
        $next_step_id = $next_step['step_id'];
        $data = array('pro_step' => $next_step_id,'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'backlog_id' => $backlog_id);
//        if ($submit_status == 0) {
            $data['submit_status'] = $workflow->sumbitStatus($next_step['is_auto']);
//        }
        if (!D('Project')->updateByPk($pro_id, $data)) {
            $pro_model->rollback();
            $this->json_error('失败3');
        }
        //发送邮件
//        send_mail($to_mail, $title, $content, $from);
        //推送项目变更消息
//        $this->workFlowPush($pro_id, $pro_step, $next_step_id, $status);
        self::log('mod', "项目审核:pro_id-$pro_id,status-$status,pro_step-$pro_step");
        $pro_model->commit();
        session($pro_id . '-pre_contract', null);
        session($pro_id . '-edit_contract', null);
        $this->json_success('成功', '', '', true, array('tabid' => 'project-auditList'));
    }
    
    public function fileDonwload() {
        $file_id = I('get.file_id');
        $file_type = I('get.file_type');
        
        $file_info = $this->getFilePath($file_id, $file_type);
        $filename = $file_info['doc_name'];
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $filePath = $document_root.$file_info['path'];
        header("Content-type: application/octet-stream");
        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
         header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
         header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
         header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header("Content-Length: ". filesize($filePath));
        readfile($filePath);
        //header('X-Accel-Redirect: '.$filePath);
    }
    
    protected function getFilePath($file_id, $file_type) {
        switch ($file_type) {
            case 'review':
                $model = D('ProjectReview');
                break;
            default:
                break;
        }
        $file_info = $model->findByPk($file_id);
        return $file_info;
    }
    
    public function detailAll() {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $map['t.context_id'] = $pro_id;
        $map['t.context_type'] = 'pro_id';
        $process_list = D('ProcessLog')->getList(1, 30, $map);
        $map1['lf.pro_id'] = $pro_id;
        $loan_log = D('ProcessLog')->getLoanList(1, 30, $map1);
        $process_list = array_merge($loan_log['list'], $process_list['list']);
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        
        $workflow = D('Workflow')->getWorkFlow();   //工作流
        
        $exts = getFormerExts();
        $this->assign('exts', $exts);
        $this->assign('workflow', $workflow);
        $this->assign('process_list', $process_list);
        $this->assign('review_file_autho', C('REVIEW_FILE_AUTHO'));
        $this->assign('signin_admin', $admin);
        $this->assign($data);
        $this->display('detail_all');
    }
}
