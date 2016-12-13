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
    
    //根据admin_id来判断审核人是否有审核的项目
    public function isAudit($page = 1, $pageSize = 30, $order='t.addtime DESC',$adminId=null,$roleId=null,$auditType)
    {
        $map['pro_state']=$auditType;
        $map['pro_author']=$adminId;
        $idList=array();
        //isset($adminId)?$map['pro_author']=$adminId:$map['pro_role']=$roleId;
        $list=D('WorkflowLog')
            ->union("select `pj_id` from`gt_workflow_log` where `pro_role`='".$roleId."' and `pro_state`='".$auditType."' ")
            ->where($map)
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
                ->join('LEFT JOIN __WORKFLOW_LOG__ as l ON t.pro_id=l.pj_id')
                ->join('__COMPANY__ AS cp ON t.company_id=cp.company_id')
                ->field('t.*,l.*,pw.pro_level_now as pro_level_now,pw.wf_id as wfid,pro_title,pro_no,a1.real_name as pmd_name,a2.authpage as authpage,company_name')
                ->where(array('pro_id'=>array('in',$idList),'l.pro_state'=>$auditType ,'_string'=>"l.pro_author='".$adminId."' or l.pro_role='".$roleId."'"))
                ->page($page, $pageSize)
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
    
}

