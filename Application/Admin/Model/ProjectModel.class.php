<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_title', 'require', '请输入项目标题'),
        array('pro_account', 'require', '请输入融资金额'),
        array('pro_account', 'is_numeric', '请输入正确的融资金额', 0, 'function'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('pro_no', 'getProNo', 1, 'callback'),
    );
    
    //生成项目编号
    public function getProNo() {
        $data = date('ymd');
        $today_midnight = strtotime('midnight');
        $map = 'addtime >= ' . $today_midnight . ' AND addtime < ' . strtotime('+1 days', $today_midnight);
        $count = $this->where($map)->count();
        return $data . '-' . ($count + 1);
    }
    
    public $_link = array(
        'linker' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'linker',
            'foreign_key' => 'pro_linker',
//            'as_fields' => 'user_name',
        ),
        'admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'admin',
            'foreign_key' => 'admin_id',
//            'as_fields' => 'user_name',
        ),
        'risk_admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'risk_admin',
            'foreign_key' => 'risk_admin_id',
//            'as_fields' => 'user_name',
        ),
        'after_loan_admin' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'admin',
            'mapping_name' => 'after_loan_admin',
            'foreign_key' => 'after_loan_admin',
//            'as_fields' => 'user_name',
        ),
        'company' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'company',
            'mapping_name' => 'company',
            'foreign_key' => 'company_id',
//            'as_fields' => 'user_name',
        ),
        'supplier' => array(
            'mapping_type' => self::MANY_TO_MANY,
            'class_name' => 'company',
            'foreign_key' => 'pro_id',
            'relation_foreign_key' => 'company_id',
            'relation_table' => 'gt_project_supplier',
//            'as_fields' => 'real_name',
        ),
        'audit'=>array(
            'mapping_type'=>self::HAS_ONE,
            'class_name'=>'audit',
            'foreign_key' => 'pro_id',
            'mapping_name' => 'audit',
            'as_fields'=>'auditresult',
        ),
    );
    public function waitAudit($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order .= 't.addtime DESC';
        $map['is_audit'] = 1;
        
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('LEFT JOIN __WORKFLOW_ROLE__ AS wr ON wr.step_id=w.step_id and wr.step_pid=w.step_pid')
                ->where($map)
                ->group('pro_id')
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=t.risk_admin_id')
                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('__PROJECT_SUPPLIER__ AS ps ON t.pro_=pc.contract_id')
                ->field('t.*,pro_title,pro_no,a1.real_name as pmd_name,a2.real_name as rcd_name,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('pro_id')
                ->order($order)
                ->select();
//        print_r($this->_sql());exit;
        return array('total' => $total, 'list' => $list);
    }
    
    public function waitAuditContract($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order = 't.addtime DESC';
        $map['is_audit'] = 1;
        
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('LEFT JOIN __WORKFLOW_ROLE__ AS wr ON wr.step_id=w.step_id and wr.step_pid=w.step_pid')
                ->where($map)
                ->count();
//        var_dump($this->_sql());exit;
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=t.risk_admin_id')
                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('__PROJECT_SUPPLIER__ AS ps ON t.pro_=pc.contract_id')
                ->field('t.*,pro_title,pro_no,a1.real_name as pmd_name,a2.real_name as rcd_name,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('pro_id')
                ->order($order)
                ->select();
//        var_dump($this->_sql());exit;
        return array('total' => $total, 'list' => $list);
    }
    
    public function waitDone($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order .= 't.addtime DESC';
        $map['t.step_pid'] = 4;
        $map['t.pro_step'] = 2;
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
//                ->join('LEFT JOIN __WORKFLOW_ROLE__ AS wr ON wr.step_id=w.step_id and wr.step_pid=w.step_pid')
                ->where($map)
                ->group('pro_id')
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=t.pro_linker')
                ->join('LEFT JOIN __COMPANY__ AS cp ON t.company_id=cp.company_id')
//                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
                ->field('t.*,pro_title,pro_no,a1.real_name as pmd_name,a2.real_name as pro_linker_name,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('pro_id')
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    //获取项目提交状态
    public function getSubStatus($pro_id) {
        return $this->where('pro_id=' . $pro_id)->getField('submit_status');
    }
    
    /**
     * 重新发起项目
     * @param type $pro_id
     */
    public function restart($pro_id) {
        $this->pk = $pro_id;
        $data = array(
            'pro_step' => 2,
        );
        $pro_detail = array('pro_id'=>$pro_id,'admin_id'=>$admin['admin_id'],'status'=>$status,'opinion'=>$opinion,'addtime'=> time(), 'pro_step' => $pro_step);
        $pro_model = D('ProcessLog');
        return $this->save($data);
    }
    
    //跟进项目id判断是否为反向保理
    public function isReverseFactoring($pro_id) {
        $pro_type = D('Project')->where('pro_id=' . $pro_id)->getField('pro_type');
        if ($pro_type) {
            return true;
        }
        return false;
    }
    
    public function unloan() {
        $map['wp.current_node_index'] = 10;
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __LOAN_FORM__ AS lf ON lf.pro_id=t.pro_id')
//                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('__COMPANY__ AS c ON c.company_id=lf.company_id')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=lf.loan_id')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,wp.*')
                ->where($map)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function finish($pro_id) {
        $map['pro_id'] = $pro_id;
        $data = array(
            'step_pid' => '20',
            'pro_step' => '1',
            'finish_status' => 1
            );
        $this->where($map)->save($data);
    }
    
    //获取所有完结的项目
    public function done() {
        $where['finish_status'] = 1;
        $list = $this->where($where)->select();
        return $list;
    }
    
    public function projectWhiteList($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order .= ' ,t.addtime DESC';
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT_WHITE__ AS pw ON pw.pro_id=t.pro_id')
                ->where($map)
                ->group('t.pro_id')
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT_WHITE__ AS pw ON pw.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=t.risk_admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a3 ON a3.admin_id=t.after_loan_admin')
                ->join('LEFT JOIN __COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->field('t.*,pro_title,pro_no,a1.real_name as pmd_name,a2.real_name as rcd_name,a3.real_name as after_loan_admin,company_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('t.pro_id')
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    //根据admin_id来判断审核人是否有待办的项目
    public function isAudit($page = 1, $pageSize = 30, $order='t.addtime DESC',$adminId=null,$roleId=null,$auditType)
    {
        $map['pro_state']=$auditType;
        $map['pro_author']=$adminId;
        $idList=array();
        //isset($adminId)?$map['pro_author']=$adminId:$map['pro_role']=$roleId;
        $list=D('WorkflowLog')
            //->union("select `pj_id` from`gt_workflow_log` where `pro_role`='".$roleId."'and `pro_author`='0' and (`pro_state`='".$auditType."' or `pro_state`='3') and 'pj_type'=0")
            ->union("select `pj_id` from`gt_workflow_log` where `pro_role`='".$roleId."'and `pro_author`='0' and (`pro_state`='".$auditType."' or `pro_state`='3')")
            //->where(array('pro_author'=>$adminId,'_string'=>"`pro_state`='".$auditType."' or `pro_state`='3'",'pj_type'=>0))
            ->where(array('pro_author'=>$adminId,'_string'=>"`pro_state`='".$auditType."' or `pro_state`='3'"))
            ->field('pj_id')
            ->select();//查出不同状态的项目id
       if($list)
        {
          foreach($list as $k=>$v)
          {
              array_push($idList,$v['pj_id']);
          }
            $idList=implode(',',$idList);
            $total = count($list);
            $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=t.admin_id')
                ->join("LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id='".$adminId."'")
                ->join('LEFT JOIN __PJ_WORKFLOW__ AS pw ON t.pro_id=pw.pj_id')
                ->join('LEFT JOIN __WORKFLOW_LOG__ as l ON l.wf_id=pw.wf_id')
                ->join('LEFT JOIN __COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->field('t.*,l.*,l.pro_level as pro_level_now,pw.wf_id as wfid,pro_title,pro_no,a1.real_name as pmd_name,a2.authpage as authpage,company_name')
                //->where(array('pro_id'=>array('in',$idList) ,'_string'=>"(l.pro_author='".$adminId."' or l.pro_role='".$roleId."') and (l.pro_state='0' or l.pro_state='3')",'l.pj_type'=>array('eq',0)))
                ->where(array('pro_id'=>array('in',$idList) ,'_string'=>"(l.pro_author='".$adminId."' or l.pro_role='".$roleId."') and (l.pro_state='0' or l.pro_state='3')"))
                ->page($page, $pageSize)
               // ->group('pro_level_now')
                ->order($order)
                ->select();
            foreach ($list as $kk=>$vv)
            {
                 $list[$kk]['authpage']=projectToAction($vv['pro_level_now'],json_decode($vv['authpage'],true));
            }

           return array('total' => $total, 'list' => $list);

        }
        else
        {
            return false;
        }
    }


    //根据wfid返回admind id 和名字
    public function wfIdToAdminAndName($wfId)
    {
        $adminIdAndName=array();
        $adminIdList=D('SendProcess')->where("`wf_id`=%d",array($wfId))->field('sp_author')->group('sp_author')->select();
        foreach($adminIdList as $k=>$v)
        {
            $adminName=adminNameToId($v['sp_author']);
            $adminIdAndName[$k]['adminName']=$adminName;
            $adminIdAndName[$k]['adminId']=$v['sp_author'];
        }
        return $adminIdAndName;
    }
    //获取项目和绑定公司的信息
    public function projectinfo($page = 1, $pageSize = 30, $map = '', $order = ''){
        $total = $this
            ->table($this->trueTableName . ' AS p')
            ->join('LEFT JOIN __WORKFLOW_LOG__ w ON w.pj_id=p.pro_id')
            ->where($map)
            ->group("p.pro_id")
            ->count();
        $list = $this
            ->table($this->trueTableName . ' AS p')
            ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id = p.company_id')
            ->join('LEFT JOIN __WORKFLOW_LOG__ w ON w.pj_id=p.pro_id')
            ->field('p.pro_id,p.pro_no,p.admin_id,p.pro_title,p.addtime,c.company_name,p.is_all_finish,p.binding_oa')
            ->where($map)
            ->page($page, $pageSize)
            ->group("p.pro_id")
            ->order($order)
            ->select();
        return array('total' => $total, 'list' => $list);
    }
    //获取项目流程信息以及执行人的信息
    public function projectWorkflowInfo($proId){
        $admin=session('admin');
        $map['w.pj_id']=$proId;
        $map['_string']=" (wl.pro_author = ".$admin['admin_id']." AND wl.pro_role = 0) OR (wl.pro_role=".$admin['role_id']." AND wl.pro_author=0)
                         OR (wl.pro_author=".$admin['admin_id']." and wl.pro_role=".$admin['role_id'].")";
        $result=M('PjWorkflow')->alias('w')
          //  ->field('w.wf_id, w.pj_id, w.pro_level_now,wl.pro_role ,wl.pro_author, wl.pro_addtime, GROUP_CONCAT(a.real_name) as name')
            ->field('w.wf_id, w.pj_id, w.pro_level_now,wl.pro_role ,wl.pro_author, wl.pro_addtime')
            //->join('LEFT JOIN __WORKFLOW_LOG__ AS wl ON  w.wf_id= wl.wf_id and w.pro_level_now=wl.pro_level')
            ->join('LEFT JOIN __WORKFLOW_LOG__ AS wl ON  w.wf_id= wl.wf_id')
         //   ->join('LEFT JOIN __ADMIN__ AS a ON (a.admin_id = wl.pro_author AND wl.pro_role = 0) OR (a.role_id=wl.pro_role AND wl.pro_author=0)')
           // ->join('LEFT JOIN __ADMIN__ AS a ON (a.admin_id = '.$admin['admin_id'].' AND wl.pro_role = 0) OR (a.role_id='.$admin['role_id'].' AND wl.pro_author=0)')
            ->where($map)
           ->group('w.wf_id')
            ->select();
        return $result;
    }
    //获取项目流程被执行的日志信息
    public function WorkflowLogInfo($map){
        $result=M('WorkflowLog')->alias('wl')
            ->field('wl.pj_id,wl.pro_level,wl.pro_author, wl.pro_role,wl.pro_view,wl.pro_state,wl.pro_addtime,a.real_name,wl.pro_rebutter')
            ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=wl.pro_author')
            ->where($map)
            ->select();
        return $result;
    }
    //获取项目跟进人信息
    public function formProIdGetInsider($pro_id){
        return M('Project')->alias('p')
            ->field('a.admin_id,a.role_id')
            ->join('LEFT JOIN __ADMIN__ as a ON a.admin_id=p.admin_id')
            ->where('pro_id = '.$pro_id)
            ->find();
    }
    //获取项目创建人的ID
    public function fromProLinkerGetProId($proId)
    {
        $list=$this->where("`pro_id`=%d",array($proId))->field('pro_linker')->find();
        return array($list['pro_linker']);
    }
    //根据gt_workflow_log中的pro_id来获取创建项目的人的role_id
    public function formPjIdGetInsider($pro_id){
        return M()->query('select a.admin_id from gt_workflow_log as wl LEFT JOIN gt_admin as a on a.admin_id=wl.pro_author where wl.pro_level=0 and wl.pj_id='.$pro_id.' order by wl.pro_times desc limit 1');
    }

    //获取执行人信息,姓名，执行时间，执行的步骤
    public  function executorInfo($map){
        $result=M('WorkflowLog')->query("select re.* from( SELECT wl.pro_level, wl.wf_id, wl.pro_author, wl.pro_role, wl.pro_addtime, a.real_name FROM gt_workflow_log wl LEFT JOIN gt_admin AS a ON a.admin_id = wl.pro_author WHERE ".$map." ORDER BY wf_id ASC ,pro_addtime DESC ) as re GROUP BY re.pro_level,re.wf_id ");
        return $result;
    }
    //项目备注
    public function remark($map,$field=null){
        if(!$field)
        {
            $field="pro_subprocess0_desc,pro_subprocess4_desc,pro_subprocess5_desc,pro_subprocess6_desc, pro_subprocess7_desc, pro_subprocess8_desc, 
            pro_subprocess9_desc, pro_subprocess10_desc,pro_subprocess11_desc,pro_subprocess12_desc,pro_subprocess13_desc, pro_subprocess14_desc, pro_subprocess15_desc, 
            pro_subprocess16_desc, pro_subprocess17_desc, pro_subprocess18_desc";
        }else
        {
            $fieldV='';
            foreach($field as $k=>$v)
            {
                if($v==end($field))
                {
                    $fieldV.='pro_subprocess'.$v.'_desc';
                }else
                {
                    $fieldV.='pro_subprocess'.$v.'_desc,';
                }

            }
            $field=$fieldV;
        }
        $content=M('Project')
            ->field($field)
            ->where($map)
            ->find();
        return $content;
    }
    //返回OA事项对应的id
    public function returnRequestInfo($proId)
    {
        $projectInfo=$this->where("`pro_id`=%d",array($proId))->field('binding_oa')->find();
        $oaId=explode('_',$projectInfo['binding_oa']);
        unset($oaId[0]);
        sort($oaId);
        $bid=D('RequestApply')->where(array('id'=>array('in',implode(',',$oaId))))->getField('bid',true);
        return array('oaType'=>explode('_',$projectInfo['binding_oa'])[0],'oaId'=>explode('_',$projectInfo['binding_oa'])[1],'bid'=>$bid);

    }
    //根据项目id和文件夹名称返回文件夹下面的文件信息
    public function returnFolderInfo($proId,$folderName,$adminId=null)
    {
        $projectFile=D('projectFile');
        $projectAttachment=D('projectAttachment');
        $fileIdAttr=$projectFile->where("`pro_id`=%d and `file_name`='%s'",array($proId,$folderName))->find();
        if($adminId)
        {
            $where="`pro_id`=$proId and `file_id`=$fileIdAttr[file_id] and `admin_id`=$adminId";
        }
        else
        {
            $where="`pro_id`=$proId and `file_id`=$fileIdAttr[file_id]";
        }
        $listInfo=$projectAttachment->where($where)->select();
        return array('list'=>$listInfo,'fileId'=>$fileIdAttr['file_id']);
    }
    //返回将驳回人的信息
    public function reButter($wfId)
    {
        $wfPjMode=D('workflowLog');
        $preLevel=$wfPjMode->where("`wf_id`=%d",array($wfId))->getField('pro_level,pro_author,pro_role',true);
        return $preLevel;
    }
    //返回后台配置好的通知人
    public function checkSublevel($proLevel,$proId)
    {
       $sublevelCheckMode=D('sublevelCheck');
        $info=$sublevelCheckMode->where("`wf_id`='%s'",array($proLevel))->select();
        if($info)
        {
            foreach ($info as $k=>$v)
            {
                if($v['pro_id']==$proId)
                {
                        $adminIds=$v['admin_ids'];
                        break;
                }else{
                    if($v['pro_id']=='0')
                    {
                        $adminIds=$v['admin_ids'];
                    }
                }
            }
            return $adminIds;
        }
        else
        {
            return false;
        }

    }
    //返回后台设置的对应等级的审核人id
    public function returnCheckIdFromProLevel($proLevel,$pro_id)
    {
        $adminIds=$this->checkSublevel($proLevel,$pro_id);
        $adminRealName= array_reduce(explode(',',$adminIds),function($vv,$ww){
            return $vv.=adminNameToId($ww).',';
        });
        $adminRealName=rtrim($adminRealName,',');
        $data['auditorId']=$adminIds;
        $data['auditorName']=$adminRealName;
        return $data;
    }
    //更新项目完结状态
    public function updateProjectStatus($proId)
    {
       $updata= $this->where('`pro_id`=%d',array($proId))->data(array('is_all_finish'=>'1'))->save();
        return $updata;
    }
    //传项目id返回整个项目详细信息
    public function returnProjectInfo($proId)
    {
        $projectInfo=$this->where("`pro_id`=%d",array($proId))->find();
        return $projectInfo;
    }
    //从pj_workflow表中返回指定projectId
    public function returnPjInfoFromPjWorkflow($proLevel)
    {
        $list=D('PjWorkflow')->where("`pro_level_now`='%s'",array($proLevel))->getField('pj_id',true);
        return $list;
    }
    //过滤表单列表的可看人员
    public function filterMemberToFrom($proId,$proLevel,$adminId)
    {
        $map['_string']="substr(`pro_level_now`,1,2)=$proLevel and `pj_id`=$proId";
        $pjId=D('PjWorkflow')->where($map)->field('wf_id')->find();
        $list=D('WorkflowLog')->where("`wf_id`=%d and `pro_author`=%d and (`pro_state`=2 or `pro_state`=3)",array($pjId['wf_id'],$adminId))->find();
        return $list;
    }
}

