<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class BacklogModel extends BaseModel {
    
    protected $_validate = array(
//        array('pro_title', 'require', '请输入项目标题'),
//        array('pro_account', 'require', '请输入项目标题'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('gt_uid', 'require', '请输入项目标题'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
   
    protected $_link = array(
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
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 'addtime DESC') {
        $map['status'] = 0;
        $total = $this
                ->where($map)
                ->count();
        $list = $this
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function done($backlog_id) {
        return $this->updateByPk($backlog_id, array('status' => 1));
    }
    
    public function addBackLog($pro_id, $step_pid, $step_id, $status) {
        $workflow = new \Admin\Lib\Workflow();
        $nextStep = $workflow->nextStep($step_pid, $step_id, $status);
        $key = $nextStep['step_pid'].'_'.$nextStep['step_id'];
//        var_dump($key);
        $msg_tmp = $this->backlogTmplate($key);
        $receive_arr = $msg_tmp['executor'];
        $receiver_arr = json_decode($receive_arr, true);
//        var_dump($receiver_arr);
        if (!empty($receiver_arr)) {
            $receiver_ids = D('Admin')->getExecutors($receiver_arr);
        }
        $receiver_ids = array_column($receiver_ids, 'admin_id');
//        var_dump($receiver_ids);exit;
        
        $pro_info = D('Project')->findByPk($pro_id);
        $description = str_replace('###', $pro_info['pro_title'], $msg_tmp['tmp']);
//        var_dump($description,$receiver_ids);exit;
        $backlog_save = array(
            'description' => $description,
            'addtime' => time(),
            'step_pid' => $nextStep['step_pid'],
            'step_id' => $nextStep['step_id'],
            'controller' => $msg_tmp['controller'],
            'action' => $msg_tmp['action'],
            'title' => $msg_tmp['title'],
            'pro_id' => $pro_id,
        );
        if (($nextStep['step_pid'] == 1 && $nextStep['step_id'] == 4) || ($nextStep['step_pid'] == 2 && $nextStep['step_id'] == 2)) {  //
            $receiver_ids[] = $pro_info['admin_id'];
        }
        if (($nextStep['step_pid'] == 2 && $nextStep['step_id'] == 3) || ($step_pid == 5 && $step_id == 3)) {  //主审人审核需要单独判断
            $receiver_ids[] = $pro_info['risk_admin_id'];
        }
//        var_dump($receiver_ids);exit;
        foreach ($receiver_ids as $id) {
            $backlog_save['admin_id'] = $id;
            $dataList[] = $backlog_save;
        }
//        var_dump($dataList);
        $map['pro_id'] = $pro_id;
        $map['step_pid'] = $step_pid;
        $map['step_id'] = $step_id;
        $this->where($map)->save(array('status' => 1));
        $this->addAll($dataList);
    }
    
    public function backlogTmplate($key) {
        $tmp = array(
            '1_2' => array(
                'tmp' => '<code>###</code>项目等待分配',
                'title' => '项目审核',
                'controller' => 'project',
                'action' => 'auditList',
                'executor' => '{"role_id":14}'
            ),
            '1_e' => array(
                'tmp' => '<code>###</code>项目等待审核',
                'title' => '项目分配',
                'controller' => 'project',
                'action' => 'auditList',
                'executor' => ''
            ),
            '1_3' => array(
                'tmp' => '<code>###</code>项目等待上传法律意见书',
                'title' => '项目审核',
                'controller' => 'project',
                'action' => 'auditList',
                'executor' => '{"role_id":21}'
            ),
            '1_4' => array(
                'tmp' => '<code>###</code>项目等待立项会',
                'title' => '项目审核',
                'controller' => 'project',
                'action' => 'auditList',
                'executor' => ''
            ),
            '2_1' => array(
                'tmp' => '<code>###</code>项目等待分配',
                'title' => '尽调审核',
                'controller' => 'RiskControlResearch',
                'action' => 'undistributed',
                'executor' => '{"role_id":17}'
            ),
            '2_e' => array(
                'tmp' => '<code>###</code>项目等待上传尽调报告',
                'title' => '人员分配',
                'controller' => 'RiskControlResearch',
                'action' => 'auditList',
                'executor' => ''
            ),
            '2_2' => array(
                'tmp' => '<code>###</code>项目等待风控会议',
                'title' => '尽调审核',
                'controller' => 'RiskControlResearch',
                'action' => 'auditList',
                'executor' => ''
            ),
            '2_3' => array(
                'tmp' => '<code>###</code>项目等待上传审核意见书',
                'title' => '尽调审核',
                'controller' => 'RiskControlResearch',
                'action' => 'auditList',
                'executor' => ''
            ),
            '3_2' => array(
                'tmp' => '<code>###</code>项目预签申请表等待审核',
                'title' => '尽调审核',
                'controller' => 'SignApplyManage',
                'action' => 'auditList',
                'executor' => '{"role_id":17}'
            ),
            '3_3' => array(
                'tmp' => '<code>###</code>项目预签申请表等待审核',
                'title' => '尽调审核',
                'controller' => 'SignApplyManage',
                'action' => 'auditList',
                'executor' => '{"role_id":20}'
            ),
            '3_4' => array(
                'tmp' => '<code>###</code>项目预签申请表等待审核',
                'title' => '尽调审核',
                'controller' => 'SignApplyManage',
                'action' => 'auditList',
                'executor' => '{"role_id":19}'
            ),
            '3_5' => array(
                'tmp' => '<code>###</code>项目等待合同上传',
                'title' => '尽调审核',
                'controller' => 'SignApplyManage',
                'action' => 'auditList',
                'executor' => '{"role_id":21}'
            ),
            '5_2' => array(
                'tmp' => '<code>###</code>项目放款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":21}'
            ),
            '5_3' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":26}'
            ),
            '5_4' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => ''
            ),
            '5_5' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":17}'
            ),
            '5_6' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":20}'
            ),
            '5_7' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":19}'
            ),
            '5_8' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":13}'
            ),
            '5_9' => array(
                'tmp' => '<code>###</code>项目请款申请等待审核',
                'title' => '放款审批',
                'controller' => 'LoanManage',
                'action' => 'auditList',
                'executor' => '{"role_id":22}'
            ),
            '8_1' => array(
                'tmp' => '<code>###</code>项目完结申请等待审核',
                'title' => '放款审批',
                'controller' => 'ProjectDone',
                'action' => 'auditList',
                'executor' => '{"admin_id":61}'
            ),
            '8_2' => array(
                'tmp' => '<code>###</code>项目完结申请等待审核',
                'title' => '放款审批',
                'controller' => 'ProjectDone',
                'action' => 'auditList',
                'executor' => '{"role_id":13}'
            ),
            '8_3' => array(
                'tmp' => '<code>###</code>项目完结申请等待审核',
                'title' => '放款审批',
                'controller' => 'ProjectDone',
                'action' => 'auditList',
                'executor' => '{"role_id":17}'
            ),
            '8_4' => array(
                'tmp' => '<code>###</code>项目完结申请等待审核',
                'title' => '放款审批',
                'controller' => 'ProjectDone',
                'action' => 'auditList',
                'executor' => '{"role_id":20}'
            ),
            '8_5' => array(
                'tmp' => '<code>###</code>项目完结申请等待审核',
                'title' => '放款审批',
                'controller' => 'ProjectDone',
                'action' => 'auditList',
                'executor' => '{"role_id":19}'
            ),
        );
        return $tmp[$key];
    }
    
    public function exchange($pro_id, $admin_id) {
        $pro_info = D('Project')->findByPk($pro_id);
        if ($pro_info['step_pid'] == 1) {
            $key = '1_e';
        } else {
            $key = '2_e';
        }
        $msg_tmp = $this->backlogTmplate($key);
        $description = str_replace('###', $pro_info['pro_title'], $msg_tmp['tmp']);
        $backlog_save = array(
            'description' => $description,
            'addtime' => time(),
            'step_pid' => $pro_info['step_pid'],
            'step_id' => $pro_info['pro_step'],
            'controller' => $msg_tmp['controller'],
            'action' => $msg_tmp['action'],
            'pro_id' => $pro_id,
            'admin_id' => $admin_id,
            'title' => $msg_tmp['title'],
        );
//        var_dump($backlog_save);exit;$map['pro_id'] = $pro_id;
        $map['pro_id'] = $pro_id;
        $map['step_pid'] = $pro_info['step_pid'];
        $map['step_id'] = $pro_info['pro_step'];
        $this->where($map)->save(array('status' => 1));
        $this->add($backlog_save);
        
    }
}

