<?php

namespace Admin\Model;

class WorkflowModel extends BaseModel {
    
    const PRE_CONTRACT = 8; //合同预签

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_link = array(
        'role' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Role',
            'foreign_key' => 'step_role_id',
            'mapping_name' => 'role',
            'as_fields' => 'role_name'
        ),
        
    );
    
    public function getWorkFlowOld() {
        $wokflow = session('workflow');
//        $wokflow = '';
        if (empty($wokflow)) {
            $workflow = $this->relation('role')->order('step_id')->select();
            foreach ($workflow as & $val) {
                $val['step_next'] = json_decode($val['step_next'], true);
            }
            session('workflow', $wokflow);
        }   
        return $workflow;
    }
    
    public function getWorkFlow() {
//        $wokflow = session('workflow');
        if (empty($wokflow)) {
            $workflow = $this->order('step_pid ,step_id')->select();
            $arr = array();
            foreach ($workflow as & $val) {
                $val['step_next'] = json_decode($val['step_next'], true);
                $arr[$val['step_pid']][$val['step_id']] = $val;
            }
            session('workflow', $arr);
        }   
        return $arr;
    }
    
    public function getNode($step_pid, $step_id) {
        $map['step_pid'] = $step_pid;
        $map['step_id'] = $step_id;
        $result = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __WORKFLOW_TYPE__ AS wt ON wt.workflow_id=t.step_pid')
                ->field('t.*,workflow_name')
                ->where($map)
                ->find();
        return $result;
    }

}
