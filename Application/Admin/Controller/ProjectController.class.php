<?php

namespace Admin\Controller;

use Admin\Lib\Privilege;
use Admin\Logic\DepartmentLogic;
use Admin\Model\CapitalFlowModel;
use Admin\Model\WorkflowModel;
use Admin\Lib\MsgTmp;
use Admin\Lib\Workflow;
use Admin\Lib\WorkflowService;

class ProjectController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function rules()
    {
        return array(
            'del' => array('type' => 'project', 'operation' => Privilege::DEL)
        );
    }

    public function flushall()
    {
        S()->flushAll();
    }

    /* 保存项目 */

    public function save_project()
    {
        $model = D('Project');
        $supplier_id = I('post.supplier_id');
        $admin = session('admin');
        $xmlObj = logic('xml');
        $xmlObj->file = 'process1.xml';
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if ($model->pro_type == 1) {
            $supplier_id = explode(',', $supplier_id);
            foreach ($supplier_id as $v) {
                $surpplie[] = array('company_id' => $v);
            }
            $model->supplier = $surpplie;
        }

        if ($data['pro_id']) {
            $result = $model->relation('supplier')->save();
            $plId = I('post.plId');
            $wfId = I('post.wfId');
            $xmlId = I('post.xmlId');
            $proIid = I('post.pro_id');
            $auditType = I('post.auditType');
            $proLevel = I('post.proLevel');//当前审批级别
            $proTimes = I('post.proTimes');//当前审批轮次
            $proRebutter = I('post.proRebutter');//驳回人id
            $proRebutterLevel = I('post.proRebutterLevel');//第几级被驳回


            if (intval($proRebutter) > 0)//驳回重发的修改
            {
                list($pjWorkFlow, $sendProcess, $workFlowLog, $redisPost) = postRebutter($wfId, $proIid, $proRebutterLevel, $proTimes, $admin, $proRebutter, $xmlId, $plId);

            } else //正常提交新建项目
            {
                //根据name查出下个审批人的角色id
                $xmlInfo = $xmlObj->index()[xmlNameToIdAndName(C('proLevel')['0'],$xmlObj->file)['TARGETREF']];//获取即将审核人的xml信息
                $proRoleId = roleNameToid(explode('_', $xmlInfo['name'])['0']);//审批人角色id
                //$xmlId=xmlIdToInfo($xmlId)['TARGETREF'];
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>';
                list($pjWorkFlow, $sendProcess, $workFlowLog, $redisPost) = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $proRoleId, 0, $xmlId, $plId, 'list', $contents, -1);
            }

        } else {

            $xmlId = xmlNameToIdAndName(C('proLevel')['0'],$xmlObj->file)['TARGETREF'];
            $model->pro_linker = $admin['admin_id'];
            $result = $model->relation('supplier')->add();

            //审批流入库处理
            $pjWorkFlow = D('PjWorkflow')->data(array('pj_id' => $result, 'pj_state' => '待审核', 'pro_level_now' => '0', 'pro_times_now' => '1'))->add();
            $sendProcess = D('SendProcess')->data(array('wf_id' => $pjWorkFlow, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
            $workFlowLog = D('WorkflowLog')->data(array(
                'sp_id' => $sendProcess, 'pj_id' => $result, 'pro_level' => 0, 'pro_times' => 1, 'pro_state' => 0, 'pro_addtime' => time(), 'pro_author' => $admin['admin_id'],
                'wf_id' => $pjWorkFlow, 'pro_role' => $admin['role_id'], 'pro_xml_id' => $xmlId
            ))->add();
            $createFolder=createFolder($result);
            $redisPost = redisTotalPost(0, $admin['admin_id'], $admin['admin_id'] . '|admin', time(), $result, $workFlowLog);
        }
        empty($proIid)?$proIid=$result:$proIid=$proIid;
        if ($result === false || $pjWorkFlow === false || $sendProcess === false || $workFlowLog === false || $redisPost === false) {
            $this->json_error('创建失败', '/Admin/Project/detail/dataId/'.$proIid, '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
        } else {
            $this->json_success('新建成功', '/Admin/Project/detail/dataId/'.$proIid, '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
        }
    }

    //新建子流程
    public function saveSubProcess()
    {
        $pjId = I('get.pro_id');//项目id
        $auditor_id = I('get.auditor_id');//分配跟进人
        $auditor_name = I('get.auditor_name');//跟进人的名字
        $pro_level = $auditType = I('get.auditType');//子流程的类型
        $pro_subprocess_desc = I('get.pro_subprocess_desc');//子流程备注
        $admin = session('admin');
        $xmlfile='process1.xml';
        $oldProject=true;
        //添加子流程和添加子流程的代理人
        switch ($pro_level)
        {
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
            case '10':
            case '11':
            case '12':
            case '13':
            case '14':
            case '15':
            case '16':
            case '17':
            case '18':
            default:
                $oldProject=addSubProcessAuditor($pjId,$auditor_id,$auditor_name,$pro_level,$pro_subprocess_desc);
                break;
        }

        $return = addSubProcess($pjId, $pro_level, $admin,$xmlfile);
        if ($return && $oldProject) {
            $this->json_success('新建成功', '/Admin/Project/MyAudit', '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目'),1);
        } else {
            $this->json_error('创建失败，请联系开发人员查看原因', '/Admin/Project/MyAudit', '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目'),1);
        }
    }
    //编辑子流程
    public function editSubProcess()
    {
        $pro_id=I('get.pro_id');
        $proLevel=I('get.proLevel');
        $projectInfo=D('Project')->where("`pro_id`=%d",array($pro_id))->find();
        foreach (json_decode($projectInfo['finish_status'],true)[$proLevel] as $k=>$v)
        {

            $data['auditorId'][]=$v['adminId'];
            $data['auditorName'][]=$v['adminName'];

        }
        $data['auditorId']=implode(',',$data['auditorId']);
        $data['auditorName']=implode(',',$data['auditorName']);
        if($proLevel=='11' || $proLevel=='11_2' || $proLevel=='11_3' || $proLevel=='11_4')
        {
            $is_pre_contract= D('PrepareContract')->isPreContract($pro_id, $projectInfo['company_id']);
        }
        if($proLevel=='15')
        {
            $is_pre_contract=D('PrepareContract')->isLoanManager($pro_id, $projectInfo['company_id']);
        }
        $this->assign(array('companyName'=>$projectInfo['pro_title'],'company_id'=>$projectInfo['company_id'],'is_pre_contract'=>$is_pre_contract,'pre'=>$proLevel,'admin'=>session('admin'),'pro_subprocess_desc'=>$projectInfo['pro_subprocess'.explode('_',$proLevel)[0].'_desc']));
        $this->assign($data);
        $this->assign($_GET);
        $this->display();
    }

    /*********
     * author:lmj
     * 我的审核项目
     * version:new
     */
    public function MyAudit()
    {
        $admin = session('admin');
        $model = D('project');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $list = $model->isAudit($page, $pageSize, 't.addtime DESC', $admin['admin_id'], $admin['role_id'], 0);
        $this->assign(array('name' => $admin['real_name'], 'list' => $list['list'], 'total' => $list['total'], 'pageCurrent' => $page));
        $this->display();

    }

    /********
     * author:lmj
     * 去审核
     * version:new
     */
    public function MyAuditProject()
    {
        $plId = I('get.pl_id');//审核表流程id
        $wfId = I('get.wf_id');//项目总流程表
        $xmlId = I('get.xml_id');//项目总流程表
        $pjId = I('get.pro_id');//项目id
        $proLevel = I('get.pro_level');//当前审批次数
        $proTimes = I('get.pro_times');//当前审批轮次
        $this->assign(array('plId' => $plId, 'wfId' => $wfId, 'xmlId' => $xmlId, 'pro_id' => $pjId, 'proLevel' => $proLevel, 'proTimes' => $proTimes));
        $this->display();
        //$auditType=I('get.auditType');//选择的类型
    }

    /*******
     * author:lmj
     * 保存提交的审核数据
     * version:new
     */
    public function saveMyAudit()
    {
        $plId = I('get.plId');
        $wfId = I('get.wfId');
        $xmlId = I('get.xmlId');
        $proIid = I('get.pro_id');
        $auditType = I('get.auditType');
        $proLevel = I('get.proLevel');//当前审批级别
        $proTimes = I('get.proTimes');//当前审批轮次
        $spId=I('get.spId');
        $pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
        $admin = session('admin');
        $xmlfile='process1.xml';
        // $aa=xmlIdToInfo($xmlId);
        // $adminId=I('get.adminId');//管理员id,如果没有管理员id

        //根据不同的审核流来做不同的入库动作
        switch ($proLevel) {
            //分配项目跟进人
            case '0_1':
                $proAdminId = I('get.admin_id');//项目跟进人ID
                $flow = D('Project')->where("`pro_id`=%d", array($proIid))->data(array('admin_id' => $proAdminId))->save();
                if (false === $flow) {
                    $this->json_error('分配项管专员失败！');
                }
                $xmlId = xmlIdToInfo($xmlId,$xmlfile)['TARGETREF'];
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one');
                break;
            case '0_2':
                //项目归档
                /*****************驳回情况*******************/
                $status = I('get.status');//是通过或者驳回状态的判断
                if (intval($status) == 1)//驳回情况
                {
                    $reButter = I('get.reButter');//驳回人的adminId
                    //先定义驳回的级别   这里后期开发需做成动态赋值，因业务需求驳回只能指定给立项人，所以赋值为0
                    $proRebutterLevel = 0;
                    $WflMode = D('WorkflowLog');
                    //改变审批人的状态为2-已审核状态
                    $updateState = $WflMode->where("`pl_id`=%d", array($plId))->data(array('pro_state' => '2'))->save();
                    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
                    //改变pj_workflow 表 因为是驳回，所以pro_time_now + 1
                    $updatePj = D('PjWorkflow')->where("`wf_id`=%d", array($wfId))->data(array('pro_level_now' => $proRebutterLevel, 'pro_times_now' => intval($proTimes) + 1))->save();
                    //新建worklowLog表中驳回的人的相关信息
                    $WflMode->data(array(
                        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_author' => $reButter, 'pro_level' => $proRebutterLevel, 'pro_times' => intval($proTimes) + 1, 'pro_state' => 3, 'pro_addtime' => time(),
                        'wf_id' => $wfId, 'pro_role' => '0', 'pro_xml_id' => $xmlId, 'pro_rebutter' => $admin['admin_id'], 'pro_rebutter_level' => $proLevel
                    ))->add();
                    //redis推送消息
                    $contents = '项管专员<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>立项事宜驳回给<code>' . adminNameToId($reButter) . '</code>';
                    $redisPost = redisTotalPost(-1, $admin['admin_id'], $reButter . '|admin', time(), $proIid, $plId, $contents, -1);
                    $return = $updateState && $updatePj && $sendProcess && $WflMode && $redisPost;
                } else {//审批通过
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one');
                }
            break;
            case '4':
                //提交新建知情
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
              //  $pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>知情事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                break;
            case '4_1':
                //知情发起审核-项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
               // $pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                $return=true;
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    foreach ($auditor_id as $k=>$v)
                    {
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one') && $return;
                        sleep(1);
                    }

                }/*else
                {
                    /*****驳回*******/
                //}
          break;
            case '4_2':
                //分配知情人员
                $allocationId=I('get.allocationId');//分配上传人的id
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>知情分配给<code>'.adminNameToId($allocationId).'</code>';
                $return= postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$allocationId,$xmlId,$plId,'one',$contents,-1);
                break;
            case '5':
                //新建风控审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>新建风控审核流程';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                break;
            case '5_1':
                //风控流程审核-项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
               // $pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                $return=true;
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    foreach ($auditor_id as $k=>$v)
                    {
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one') && $return;
                        sleep(1);
                    }

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '6':
                //召开立项会-项管专员提交新建项目
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>立项会事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                break;
            case '6_1':
                //召开立项会-项管总监审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    foreach ($auditor_id as $k=>$v)
                    {
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one') && $updataProject;
                        sleep(1);
                    }
                    $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId
                    $contents=$admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>立项会投票事宜通知：<code>'.adminNameToId($allocationId).'</code>';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$allocationId,$xmlId,$plId,'one',$contents,-1) && $return;

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '6_2':
                //召开立项会-项管专员提交投票统计结果
                $proRoleId='14';//业务类型指定了宋波或者项管总监;
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的立项会投票结果已发出';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $proRoleId, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                break;
            case '6_3':
                //召开立项会-项管总监审核投票结果通过
                //$allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId---进过协商后，这里直接结束子流程
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                if($auditType==2)
                {
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的立项会投票结果已进行审核';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                }else{//驳回情况

                }
                break;
            case '7':
                //风控报告编写-报告编写-风控专员
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                    foreach ($auditor_id as $k=>$v)
                    {
                        $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的风控报告分配给<code>'.adminNameToId($v).'</code>';
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                        sleep(1);
                    }

                break;
            case '7_1':
                //风控专员报告编写完毕发送给项管专员审核
                $allocationId='2';//业务需求分配给项管所有专员
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的风控报告上传完毕';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $allocationId, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                break;
            case '7_2':
                //项管专员审核
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    //业务需求审核过后给项管总监宋波继续审核
                    $allocationId=14;
                   // $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId
                    $upateOldAdminId=D('WorkflowLog')->where("`pl_id`=%d",array($plId))->data(array('pro_author'=>$admin['admin_id']))->save();
                    $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $allocationId, 0, $xmlId, $plId, 'one') && $updataProject && $upateOldAdminId;

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '7_3':
                //项管总监最终审核
                if($auditType==2)
                {
                    //业务需求审核过后给项管总监宋波继续审核
                   // $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId----经过协商不需要返回adminId
                    //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                    $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的风控报告进行最终审核';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '8':
                //风控会-项管专员提交新建项目
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>风控会事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                break;
            case '8_1':
                //风控会-项管总监审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    foreach ($auditor_id as $k=>$v)
                    {
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one') && $updataProject;
                        sleep(1);
                    }
                    $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId
                    $contents=$admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>风控会投票事宜通知：<code>'.adminNameToId($allocationId).'</code>';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$allocationId,$xmlId,$plId,'one',$contents,-1) && $return;

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '8_2':
                //风控会-项管专员提交投票统计结果
                $proRoleId='14';//业务类型指定了宋波或者项管总监;
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的风控会投票结果已发出';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $proRoleId, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                break;
            case '8_3':
                //风控会-项管总监审核投票结果通过
                //$allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId----经过协商不需要返回adminId
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                if($auditType==2)
                {
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的风控会投票结果已进行审核';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                }else{//驳回情况

                }
                break;
            case '9':
                //投委会-项管专员提交新建项目
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>投委会事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-1) && $updataProject;
                break;
            case '9_1':
                //投委会-项管总监审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                /*****正常审批通过*******/
                if($auditType==2)
                {
                    foreach ($auditor_id as $k=>$v)
                    {
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one') && $updataProject;
                        sleep(1);
                    }
                    $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId
                    $contents=$admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>投委会投票事宜通知：<code>'.adminNameToId($allocationId).'</code>';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$allocationId,$xmlId,$plId,'one',$contents,-1) && $return;

                }/*else
                {
                    /*****驳回*******/
                //}
                break;
            case '9_2':
                //投委会-项管专员提交投票统计结果
                $proRoleId='14';//业务类型指定了宋波或者项管总监;
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的投委会投票结果已发出';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $proRoleId, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                break;
            case '9_3':
                //投委会-项管总监审核投票结果通过
               // $allocationId=ProjectSubmitter($spId);//返回上一级提交人的adminId----经过协商不需要返回adminId
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                if($auditType==2)
                {
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的投委会投票结果已进行审核';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one',$contents,-1) && $updataProject;
                }else{//驳回情况
                }
                break;
            case '10':
                //签约流程-风控审核意见，通知项管总监知情
                //先通知宋波
                $newProLevel=addNewLevel($proLevel);
                $auditor_id=I('get.auditor_id');
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,adminNameToId($auditor_id),$proLevel,$pro_subprocess_desc);

                $auditorids='28'; //指定宋波
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] .':</code>向<code>'.adminNameToId($auditorids).'</code>发起项目<code>' . projectNameFromId($proIid) . '</code>的风控意见审核知情';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditorids,$xmlId,$plId,'one',$content,-2) ;
                }
                //跳转到风控总监分配任务
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>已提交';
                $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$contents,-2) && $return;
                break;
            case '10_2':
                //风控部分配人员
                $auditor_id = I('get.admin_id');//分配跟进人
                $auditor_name = I('get.real_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);
                //告诉项管总监合同知情
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>已分配给'.$auditor_name.'去审核';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$contents,-2)&& $updataProject;
                break;
            case '10_3':
                //风控部总监再次审核
                $allocationId=ProjectSubmitter($spId);
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,null,null,$newProLevel,$pro_subprocess_desc);
                //告诉项管总监合同知情
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>所需资料上传完成，并反馈给'.$auditor_name;
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$allocationId,$xmlId,$plId,'one',$contents,-2)&& $updataProject;
                break;
            case '10_4':
                //通知法务，并直接归档结束
                $auditor_id = I('get.admin_id');//分配跟进人
                $auditor_name = I('get.real_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $auditor_id = $auditor_id;
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>审核项目<code>' . projectNameFromId($proIid) . '</code>后向法务'.adminNameToId($auditor_id).'发起知情';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-2) && $updataProject;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                if($auditType==2) {  //审核通过
                    $content2 = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>审核项目<code>' . projectNameFromId($proIid) . '</code>通过';
                    $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content2,-2) && $updataProject&&$return;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                }else{
                    //审核不通过
                }
                break;
            case '11':
                //合同编辑-项管专员提交合同
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $proRoleId=14;//业务类型指定了宋波或者项管总监
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $auditor_id = explode(',', $auditor_id);
                //告诉项管总监合同知情
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>合同知情事宜';

                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId,0,$xmlId,$plId,'one',$contents,-2) && $updataProject;
                foreach ($auditor_id as $k=>$v) //告诉项目经理编辑合同
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>合同编辑事宜';
           
                    $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-2) && $updataProject && $return;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                    sleep(1);
                }

                break;
            case '11_2':
                //合同预签约-法务老大审核
                $proAdminId='24';//业务类型指定了黄惠萍,法务老大，以后可以配成动态;
                //$pro_subprocess_desc =I('get.pro_subprocess_desc');//子流程备注
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                if($auditType==2) {  //审核通过
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的合同审核已发出';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -1) && $updataProject;
                }
                else
                {
                    //驳回
                }
                break;
            case '11_3':
                //合同预签约-副总裁审核
                $proAdminId='23';//业务类型指定了副总裁，以后可以配成动态;
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel(addNewLevel(addNewLevel($proLevel)));
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);
                if($auditType==2) { //审核通过
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的合同审核已发出';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -1) && $updataProject;
                }
                else{
                    //驳回
                }
                break;
            case '11_4':
                //合同预签约-总裁审核
                $proAdminId='22';//业务类型指定了总裁，以后可以配成动态;
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                if($auditType==2) {  //审核通过
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的合同审核已发出';
                    $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -1) && $updataProject;
                }
                else{
                    //驳回
                }
                break;
            case '11_5':
                //合同预签约-总裁审核
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $newProLevel=addNewLevel($proLevel);
                if($auditType==2) {  //审核通过
                    //先通知法务勾选好的人员
                    $auditor_id=getFinishStatus($newProLevel,$proIid);
                    foreach ($auditor_id as $k=>$v) //告诉项目经理编辑合同
                    {
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>合同编辑事宜';
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-1) && $updataProject;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                        sleep(1);
                    }
                    $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>的合同审核已发出';
                    $return = postNextProcess($wfId, $newProLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one', $contents, -1) && $updataProject && $return;
                }
                else{
                    //驳回
                }
                break;
            case '12':
                //合同审核流程开启
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>上传了合同，并转交给法务:<code>'.adminNameToId($auditor_id).'</code>';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-2) && $updataProject;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                break;
            case '12_1':
                //通知给法务相关人员，并且流程归档
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,'','',$proLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                $insider=array('28','24','33','72','23','22');//项管部总监，风控总监，副总裁，总裁，的role_id
                array_push($insider,D('Project')->formProIdGetInsider($proIid)['admin_id'],D('Project')->formPjIdGetInsider($proIid)[0]['admin_id']);
                $insider=array_unique($insider);
                $time=time();
                if($auditType==2) {  //审核通过，才发起知情
                    foreach ($insider as $k=>$v) //通知相关人员
                    {
                        $time=$time+$k;
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向'.roleNameToid($admin['role_id']).':<code>'.adminNameToId($v).'</code>'.'发起知情';
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-2,$time) && $updataProject;//发起知情，$proLevel ,跳到下一级，
                       // sleep(1);
                    }

                    $content2 = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>审核项目<code>' . projectNameFromId($proIid) . '</code>通过';
                    $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,'',$xmlId,$plId,'one',$content2,-2) && $updataProject && $return;//归档结束，$newProLevel，跳过两级

                }else{
                    //审核不通过
                }
                break;
            case '13':
                //线下签约-法务老大分配人手
                $proAdminId = I('get.admin_id');//风控专员跟进人ID
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$proAdminId,adminNameToId($proAdminId),$proLevel,$pro_subprocess_desc);//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
                //告知总裁与副总裁
                $auditor_id=array('22','23');
                foreach ($auditor_id as $k=>$v) //告诉项目经理编辑合同
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'向总裁部:<code>'.adminNameToId($v).'</code>'.'</code>提交项目<code>' . projectNameFromId($proIid) . '</code>线下签约流程知情事宜';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-1) && $updataProject;//告诉项目经理的时候跨了一个等级，所以用$newProLevel
                    sleep(1);
                }
                //直接跳到法务人员提交文档
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>线下签约流程';
                $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$proAdminId,$xmlId,$plId,'one',$contents,-1) && $updataProject && $return;
                break;
            case '13_2':
                //线下签约-法务专员提交文档后通知知情人员
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $auditor_id = explode(',', $auditor_id);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$newProLevel,$pro_subprocess_desc);
                foreach ($auditor_id as $k=>$v) //通知勾选好的项管部人员
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向项管部:<code>'.adminNameToId($v).'</code>'.'</code>提交项目<code>' . projectNameFromId($proIid) . '</code>线下签约文档上传知情';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-1) && $updataProject;
                    sleep(1);
                }
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>线下签约文档';
                $return = postNextProcess($wfId, $newProLevel, $proTimes, $admin, $proIid, 0, 0, $xmlId, $plId, 'one', $contents, -1) && $updataProject && $return;
                break;
            case '14':
                //放款流程-商票上传流程
                //通知给财务总监，目前只有丁总和张总
                $auditor_id=array('33','72');
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向财务部:<code>'.adminNameToId($v).'</code>'.'</code>提交项目<code>' . projectNameFromId($proIid) . '</code>商票背书知情';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                $proAdminId='34';//告诉出纳黄虹上传商票凭证
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] .'通知出纳:<code>'.adminNameToId($proAdminId).'</code>'.'</code>上传项目<code>' . projectNameFromId($proIid) . '</code>商票凭证';
                $return = postNextProcess($wfId, $newProLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -3) && $updataProject && $return;
                break;
            case '15':
                //新建放款审核流程-项管专员
                 $proAdminId = 28;//传给项管总监知情审核
                 $auditor_id = I('get.auditor_id');//分配跟进人
                 $auditor_name = I('get.auditor_name');//跟进人的名字
                 $newProLevel=addNewLevel($proLevel);
                 $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                 $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' .projectNameFromId($proIid) . '</code>放款审核流程知情';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -3) && $updataProject;
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>放款审核流程';
                    $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
               break;
            case '15_2':
                //新建放款审核流程-法务人员分配给风控A和风控B
               // $proAdminId = 28;//传给项管总监知情审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>放款法务审核事宜';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                break;
            case '15_3':
               // 放款风控A轮初审
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>放款A轮审核事宜';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                break;
            case '15_4':
                // 放款风控B轮初审----风控张总知情，黄总审批
                $auditor_id = array('10','24');//分配跟进人
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $return=true;
                foreach ($auditor_id as $k=>$v)
                {

                    if($v=='10')//张总知情
                    {
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向风控部:<code>'.adminNameToId($v).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款B轮审核知情事宜';
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject && $return;
                    }elseif ($v=='24')
                    {
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向风控部:<code>'.adminNameToId($v).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款B轮审核事宜';
                        $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject && $return;
                    }
                    sleep(1);
                }
                break;
            case '15_6':
           // 放款风控黄总审批
                $auditor_id='23';//副总裁
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向总裁办:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款风控审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '15_7':
                // 放款副总裁孙总审批
                $auditor_id='22';//总裁
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向总裁办:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '15_8':
                // 放款副总裁佟总审批
                $auditor_id='33';//财务总监丁总
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向财务总监:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '15_9':
                // 放款财务总监审批
                $auditor_id='34';//出纳
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向出纳:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>放款审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;

            //商票退票流程
            case '17':
                //新建商票审核流程-项管专员
                $proAdminId = 28;//传给项管总监知情审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' .projectNameFromId($proIid) . '</code>商票审核流程知情';
                $return = postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, 0, $proAdminId, $xmlId, $plId, 'one', $contents, -3) && $updataProject;
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>商票审核流程';
                    $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                break;
            case '17_2':
                //新建商票审核流程-法务人员分配给风控A和风控B
                // $proAdminId = 28;//传给项管总监知情审核
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>商票法务审核事宜';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                break;
            case '17_3':
                // 商票风控A轮初审
                $auditor_id = I('get.auditor_id');//分配跟进人
                $auditor_name = I('get.auditor_name');//跟进人的名字
                $updataProject=addSubProcessAuditor($proIid,$auditor_id,$auditor_name,$proLevel,$pro_subprocess_desc);
                $auditor_id = explode(',', $auditor_id);
                //通知项管总监知情
                //跳到法务人员
                foreach ($auditor_id as $k=>$v)
                {
                    $content = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>提交项目<code>' . projectNameFromId($proIid) . '</code>商票A轮审核事宜';
                    $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject;
                    sleep(1);
                }
                break;
            case '17_4':
                // 商票风控B轮初审----风控张总知情，黄总审批
                $auditor_id = array('10','24');//分配跟进人
                $newProLevel=addNewLevel($proLevel);
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $return=true;
                foreach ($auditor_id as $k=>$v)
                {

                    if($v=='10')//张总知情
                    {
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向风控部:<code>'.adminNameToId($v).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票B轮审核知情事宜';
                        $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject && $return;
                    }elseif ($v=='24')
                    {
                        $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向风控部:<code>'.adminNameToId($v).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票B轮审核事宜';
                        $return=postNextProcess($wfId,$newProLevel,$proTimes,$admin,$proIid,0,$v,$xmlId,$plId,'one',$content,-3) && $updataProject && $return;
                    }
                    sleep(1);
                }
                break;
            case '17_6':
                // 商票风控黄总审批
                $auditor_id='23';//副总裁
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向总裁办:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票风控审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '17_7':
                // 商票副总裁孙总审批
                $auditor_id='22';//总裁
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向总裁办:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '17_8':
                // 商票副总裁佟总审批
                $auditor_id='33';//财务总监丁总
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向财务总监:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;
            case '17_9':
                // 商票财务总监审批
                $auditor_id='34';//出纳
                $updataProject=addSubProcessAuditor($proIid,null,null,$proLevel,$pro_subprocess_desc);
                $content = $admin['role_name'] . '<code>' . $admin['real_name'] .'</code>向出纳:<code>'.adminNameToId($auditor_id).'</code>'.'提交项目<code>' . projectNameFromId($proIid) . '</code>商票审核事宜';
                $return=postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,0,$auditor_id,$xmlId,$plId,'one',$content,-3) && $updataProject;
                break;

        }

        if (!$return) {
            $this->json_error('创建失败', '/Admin/Project/detail/dataId/'.$proIid, '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
        } else {
            $this->json_success('新建成功', '/Admin/Project/detail/dataId/'.$proIid, '', true, array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
            //$this->json_success('成功', '', '', true, array('tabid' => 'project-auditList'));
        }
    }
    //子流程-2风控和项目经理分配人手
    public function proSubAllocation2()
    {

        $this->assign($_GET);
        $this->display();
    }

    public function ProjectMeetingCheckFile()
    {
        $admin=session('admin');
        if(I('post.plId'))//如果是查看知情就改变状态
        {
          //  $updateOldPj=D('workflowLog')->data(array('pro_state'=>2))->where("`pl_id`=%d",array(I('post.plId')))->save();
            $updateOldPj=uploadUpdataWorkFlowState('','6_2',1,$admin,I('post.proId'),I('post.plId'),2,0,'',-1);
            if($updateOldPj)
            $this->success('上传成功');
        }
    }
    //立项会审核6_2风控部查看资料
    public function proSubAllocationMember2()
    {
        $adminModel=D('Admin');
        $admin=session('admin');
        switch ($admin['role_id'])
        {
            case '16'://项目经理
                $list= $adminModel->where("`role_id`=%d",array('16'))->relation('role')->select();
                break;
            case '17'://风控总监
                $list=$adminModel->where(array('role_id'=>array('in','18,21')))->relation('role')->select();
                break;
        }
        $this->assign(array('list'=>$list));
        $this->display();
    }

    //通知知情
    public function Notice()
    {
        $adminInfo = D('Admin')->where(array('role_id' => array('in', '16,18')))->field('admin_id,role_id,real_name')->relation(true)->select();
        $wfId = I('post.wfId');
        $xmlId = I('post.xmlId');
        $proIid = I('post.pro_id');
        $spId = I('post.spId');
        $proLevel = I('post.proLevel');//当前审批级别
        $proTimes = I('post.proTimes');//当前审批轮次
        $admin = session('admin');
        if (IS_POST) {
            $flag = true;
            $adminList = I('post.ids');
            foreach ($adminList as $k => $v) {
                //$proAuthJson=preg_replace("/^\{([a-z]+)\:\'([0-9]+)\'\}/",'{"${1}":"${2}"}',$v);
                $wordFolwModel = D('WorkflowLog');
                $proAuth = json_decode(htmlspecialchars_decode($v), true)['supplierid'];
                $isNotice = $wordFolwModel->where("`pro_author`=%d and `pro_level`=%d", array($proAuth, $proLevel))->find();//如果重复通知就不新建wordflow
                if ($isNotice) {
                    $redisPostAudit = redisPostAudit($proLevel, $admin['admin_id'], $proAuth . '|admin', time() + $k, $proIid, $isNotice['pl_id'], 1);//我的待办通知
                    continue;
                } else {
                    $return = $wordFolwModel->data(array('sp_id' => $spId, 'pj_id' => $proIid, 'pro_author' => $proAuth,
                        'pro_level' => $proLevel, 'pro_times' => $proTimes, 'pro_view' => '上传资料', 'pro_state' => '0', 'pro_addtime' => time(),
                        'wf_id' => $wfId, 'pro_role' => '', 'pro_xml_id' => $xmlId))->add();

                    $redisPostAudit = redisPostAudit($proLevel, $admin['admin_id'], $proAuth . '|admin', time() + $k, $proIid, $return, 1);//我的待办通知
                }

                $redisPost = redisCollect($proLevel, $admin['admin_id'], $proAuth . '|admin', time() + $k, $proIid, 1);//消息通知库

                $flag = $flag && $return && $redisPost && $redisPostAudit;
            }
            if (!$flag) {
                $this->json_error('创建失败', '/Admin/Project/detail/dataId/'.$proIid, '', '', array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
            } else {
                $this->json_success('新建成功', '/Admin/Project/detail/dataId/'.$proIid, '', '', array('tabid' => 'Project-MyAudit','tabName'=>'Project-MyAudit','tabTitle'=>'我的项目','width'=>'1012','height'=>'800'),2,'/Admin/Project/MyAudit');
                //$this->json_success('成功', '', '', '', array('tabid' => 'project-auditList'));
            }
        }
        $this->assign('list', $adminInfo);
        $this->assign($_GET);
        $this->display();
    }


    /* 项目立项 */
    public function add()
    {
        $admin = session('admin');
        $pre = I('get.pre');
        $this->assign('pre', $pre);
        $this->assign('admin', $admin);
        if (in_array(intval($pre),array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20))) {
            //新建子流程
            $this->display('subProcess1');

        } else {
            $this->display();//新建立项
        }

    }

    /* 编辑管理员 */

    public function edit()
    {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        if (DepartmentLogic::isPMD($admin['dp_id']) && $admin['position_id'] <= 2) {
            $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();
        } else {
            $data = $p_model->where(array('pro_linker' => $admin['admin_id'], 'pro_id' => $pro_id))->relation(true)->find();
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
        $this->assign($_GET);
        $this->assign('pro_type', $data['pro_type']);
        $this->display();
    }


    //审核界面
    public function auditEdit()
    {
        $p_model = D('Project');
        $pro_id = I('get.pro_id');
        $admin = session('admin');
        $data = $p_model->where(array('pro_id' => $pro_id))->relation(true)->find();


        /*        $map['t.context_id'] = $pro_id;
                $map['t.context_type'] = 'pro_id';
                $process_list = D('ProcessLog')->getList(1, 30, $map);
                $workflow = D('Workflow')->getWorkFlow();   //工作流
                $exts = getFormerExts();
                $this->assign('exts', $exts);
                $this->assign('workflow', $workflow);
                $this->assign('review_file_autho', C('REVIEW_FILE_AUTHO'));
                $this->assign('process_list', $process_list['list']);
                $this->assign('signin_admin', $admin);*/
        $this->assign($data);
        $this->assign($_GET);

        $this->display('audit_edit');
    }

    public function chooseRebutter()
    {
        $wfId = I('post.wfId');
        $adminIdAndNameAttr = D('Project')->wfIdToAdminAndName($wfId);
        $this->ajaxReturn($adminIdAndNameAttr);
    }

    //审核资料附件
    public function fileReviewList()
    {
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


    public function submit()
    {
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
    public function audit()
    {
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
    protected function shortcutProcess($pro_id)
    {
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
        $data = array('pro_step' => $next_step_id, 'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id']);
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


    protected function setPm(& $model)
    {
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

    public function cancel()
    {
        $roles=array(
            array('role_id'=>'2,3,4,5','role_name'=>'lisi2,lisi3,lisi4,lisi5'),
      
        );
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

    public function del()
    {
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
    public function file()
    {
        $map['pro_id'] = I('get.pro_id')?I('get.pro_id'):I('get.custom_pro_id');
        $file_tree = D('ProjectFile')->where($map)->select();
        $file_tree = array_reverse($file_tree);
        $admin=$_SESSION['admin'];
        $fileLevel=C('fileLevel');
        foreach ($file_tree as $k=> $v) {
            if($v['secret']>1){
                //如果文件的机密等级大于1，则代表此文件夹是机密的，需要与配置中的fileLevel进行比对，查看此人的角色是否在对应的role_id中，在则可以查，
                //不在，则需要进一步判断，此人的是否是特批查看此文件的用户，即：此文件夹中的 allow_adminid 是否包含了此人的id号
                if(strpos($fileLevel[$v['secret']]['role_id'],$admin['role_id'])===false && strpos($v['allow_adminid'],$admin['admin_id'])===false){
                    unset($file_tree[$k]);
                    continue;
                }
            }
            $array[$v['file_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $file_tree = $tree->get_array(0);
        $this->assign('file_tree', $file_tree);
        $this->assign($map);
        $this->assign($_GET);
        if(I('get.actionname') || I('get.custom_pro_id')){
           $html=$this->fetch(I('get.actionname'));
           $this->json_success($html);

        }else{
            $this->display();
        }
    }


    //上传页面
    public function upload()
    {
        $map['pro_id'] = I('get.pro_id');
        $map['file_id'] = I('get.file_id');
        $list = D('ProjectAttachment')->where($map)->select();
        $exts = getFormerExts();

        $this->assign('exts', $exts);
        $this->assign('list', $list);
        $this->assign($map);
        if(I('get.methodname')){
            $this->display(I('get.methodname'));
        }else{
            $this->display();
        }
    }

    //上传附件
    public function upload_attachment()
    {
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
    public function uploadToReview()
    {
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
    public function upDocument()
    {
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
    protected function checkUpDocAuth($pro_id, $role_id)
    {
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
    protected function checkAuthUpload($pro_id, $file_id, $role_id)
    {
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
    public function remove_attachment()
    {
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
    public function remove_review()
    {
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
    public function follow()
    {
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

    public function source()
    {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%' . $pro_title . '%');
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
    public function finish()
    {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');

        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%' . $pro_title . '%');
        }
        $order = 'step_pid ASC, pro_step ASC';
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
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

    public function lookUp()
    {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $is_loan = I('request.is_loan');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%' . $pro_title . '%');
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
    public function undistributed()
    {
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

    public function distribute()
    {
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
    public function loanIndex()
    {
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
    public function exchange()
    {
        $proId = I('get.pro_id');
        if (empty($proId)) {
            $this->json_error('参数错误');
        }
        $pro_id = I('get.pro_id');
        $wfId = I('get.wfId');
        $xmlId = I('get.xmlId');

        //$auditType=I('get.auditType');
        $proLevel = I('get.proLevel');//当前审批级别
        $proTimes = I('get.proTimes');//当前审批轮次
        $model = D('Project');
        $map['pro_id'] = $proId;
        $data = $model->where($map)->relation(true)->find();
        $this->assign($data);
        $this->assign($_GET);
        $this->display();
    }

    //终止的项目
    public function end()
    {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        if (!empty($pro_title)) {
            $map['pro_title'] = array('LIKE', '%' . $pro_title . '%');
        }

        $admin = session('admin');
        $map['submit_status'] = 1;
        $map['pro_step'] = 0;
        $map['step_pid'] = 0;
        if (!isBoss() && !isSupper()) {
            $map['admin_id|pro_linker'] = array($admin['admin_id'], $admin['admin_id'], '_multi' => true);
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
    public function restart()
    {
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

    protected function restartDo($pro_id, $step_pid, $pro_step, $status, $opinion)
    {
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
        $data = array('pro_step' => $next_step_id, 'step_pid' => $next_step['step_pid'], 'role_id' => $next_step['step_role_id'], 'backlog_id' => $backlog_id);
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

    public function fileDonwload()
    {
        $file_id = I('get.file_id');
        $file_type = I('get.file_type');

        $file_info = $this->getFilePath($file_id, $file_type);
        $filename = $file_info['doc_name'];
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $filePath = $document_root . $file_info['path'];
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
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        //header('X-Accel-Redirect: '.$filePath);
    }

    protected function getFilePath($file_id, $file_type)
    {
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

    public function detailAll()
    {
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
    /**
     * 流程详细流程，每个子流程都显示出来
     */
    public function detail()
    {
        $admin = session('admin');

        I('get.dataId')?$dataId=I('get.dataId'):$dataId=I('get.pro_id');
        $proWorkflow=D('Project')->projectWorkflowInfo('w.pj_id = '.$dataId);
        //取出这个项目的所有子流程
        $workflowInfos=array_column($proWorkflow,'pro_level_now');
        foreach ($workflowInfos as $key => $item) {
            //将0，0_1这种下标由‘_’来分割，并取  ‘_’ 前面的数字，然后再获取配置文件中的子流程
            $tmpindex=reset(explode('_',$item));
            $reg='/^'.$tmpindex.'(_[\d])?/';
            foreach (C('proLevel') as $k=> $v) {
                //如果配置文件中存在此键，则说明它是我们要找的键值对
                if(preg_match($reg,$k)>0){
                    if(strpos($k,'_')!==false){
                        $result[$tmpindex]['sub'][$k]=trim($v);
                    }else{
                        $result[$tmpindex]['name']=trim($v);
                    }
                }
            }
            $result[$tmpindex]['current']=$item;
            $result[$tmpindex]['wfid']=$proWorkflow[$key]['wf_id'];
            $tmpindex='';
        }
        //执行人的名字和执行的时间
        $wfids=array_column($proWorkflow,'wf_id');
         $tmpexecutor=D('Project')->executorInfo('wf_id in ('.implode(',',$wfids).')');
        //补全按用户角色来区分的用户信息
        array_walk($tmpexecutor ,function(&$v,$k){
            if(empty($v['pro_author'])) $v['real_name']=M('Admin')->getFieldByRoleId($v['pro_role'],'real_name');
        });
        //转换执行人的数组形式，wf_id作为最外层的key,pro_level最为第二层的key
        foreach($result as $v){
            foreach($tmpexecutor as $ev){
                //项目处于同一个进程下，且其在此进程中的执行步骤不能大于等于用$v['current']表示现在正在执行的步骤
                if($v['wfid']==$ev['wf_id'] && strcmp($v['current'],$ev['pro_level'])>=0){
                    $executor[$ev['wf_id']][$ev['pro_level']]=$ev;
                }
            }
        }
        //项目id号
        $pj_id=end($proWorkflow)['pj_id'];
        //项目标题
        $pro_title=M('Project')->getFieldByProId($pj_id,'pro_title');
        $this->assign(array('list'=>$result,'pro_id'=>$pj_id,'title'=>$pro_title,'executor'=>$executor));
        //$this->assign($_GET);
        $this->display();
    }
    /**
     * 项目流程完结记录
     * @param string $pro_id 项目的id号
     */
    public  function workflowlog(){
        $admin = session('admin');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        //项目标题
        if(I('post.pro_title')) $map['p.pro_title']=array('like','%'.I('post.pro_title').'%');
        //项目编号
        if(I('post.pro_no')) $map['p.pro_no']=array('eq',I('post.pro_no'));
        //项目是否已经完结
        if(I('post.is_all_finish'))$map['p.is_all_finish']=array('eq',I('post.is_all_finish'));
        //项目id
        if(I('get.pro_id'))$map['p.pro_id']=array('eq',I('get.pro_id'));

        //如果是消息推送过来的就需要标记redis了
        if(I('get.type') && I('get.pro_id')&& I('get.time'))  checkMessage(I('get.time'),I('get.type'),I('get.pro_id'));

        $result=D('Project')->projectinfo($page, $pageSize,$map,$order);
        foreach($result['list'] as &$v){
            $v['authpage']=json_decode($v['authpage'],true);
        }
        //当前用户是所拥有的页面权限
        $authpage=M('Admin')->getFieldByAdminId($admin['admin_id'],'authpage');
        $authpage=json_decode($authpage,true);
        $this->assign('authpage',$authpage);
        $this->assign(array('list' => $result['list'], 'total' => $result['total'], 'pageCurrent' => $page));
        $this->display('Project/workflowlog');
    }

    //详情细化
    public  function detailMore(){
        $wf_id=I('get.wf_id');
        //获取子流程被执行的详细信息
        $list=D('Project')->WorkflowLogInfo('wf_id = '.$wf_id);

        array_walk($list ,function(&$v,$k){
            if(empty($v['pro_author'])) $v['real_name']=M('Role')->getFieldByRoleId($v['pro_role'],'role_name');
        });
        $proLevel=C('proLevel'); //等级配置文件信息
        foreach($list as $k=>$v){
            if($v['pro_level']!==null){
                //判断是否是新建的子流程
                if(strpos($v['pro_level'],'_')===false){
                    $list[$k]['content']=$v['real_name'].'&nbsp;&nbsp;新建了&nbsp;&nbsp;【'.$proLevel[$v['pro_level']].'】子流程';
                } else {
                        $tmpLevel=$v['pro_level'];
                        $tmplength=strpos($tmpLevel,'_')+1;
                        //让其自动在配置文件中取下一个元素，判断其是否为空，例如，当前是7_1 ,那么此时我们需要判断C('pro_Level')[7_3]的值是否存在，不存在就表示是最后一个元素了
                        $tmpindex=substr($tmpLevel,0,$tmplength).(substr($tmpLevel,$tmplength,1)+1);
                        if(!empty($proLevel[$tmpindex])){
                            $list[$k]['content']=$v['real_name']."&nbsp;&nbsp;".
                                ($v['pro_state']==2?'通过':($v['pro_state']==3?'驳回':($v['pro_state']==0?'待操作':'')))."&nbsp;&nbsp;【".
                                $proLevel[$v['pro_level']].'】';
                        }else{
                            //最后一个则不显示操作的人名字
                            $list[$k]['content']="【". $proLevel[$v['pro_level']] ."】";
                        }
                        $tmpindex='';
                        $tmpLevel='';
                }
            }
        }
        $this->assign('list',$list);
        $this->display();
    }
    //备注
    public  function remark(){
        if(I('get.pro_id'))
        $map['pro_id']=array('eq',I('get.pro_id'));

        $list=D('Project')->remark($map);
        foreach ($list as $key=>$v){
            if(!empty($v))
            $result[C('proLevel')[trim(preg_replace('/[^\d]/s', '', $key))]]=$v;
        }
        $this->assign('list',$result);
        $this->display();
    }
}
