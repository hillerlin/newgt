<?php

namespace Admin\Lib;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Workflow {

    private $workflow;
    private $workflowService;

    public function __construct() {
        $this->workflow = D('Workflow')->getWorkFlow();
//        $this->workflowService = new WorkflowService();
    }
    
    public function nextStep($step_pid, $step_id, $status) {
        $now_step = $this->workflow[$step_pid][$step_id];
        
        if ($now_step['is_over']) {
            $next_step_pid = $now_step['step_next'][$status]['step_pid'];
            $next_step_id = $now_step['step_next'][$status]['next_id'];
        } else {
            $next_step_pid = $now_step['step_next'][$status]['step_pid'];
            $next_step_id = $now_step['step_next'][$status]['next_id'];
        }
//        var_dump($now_step,$next_step_id, $next_step_pid);exit;
        return $this->workflow[$next_step_pid][$next_step_id];
    }
    
    //获取对应状态的提示信息
    public function nextStepMsgTmp($step_pid, $step_id, $status) {
        $now_step = $this->workflow[$step_pid][$step_id];
//        var_dump($now_step);exit;
        $msg_id = $now_step['step_next'][$status]['msg_id'];
        $msg_tmp = D('MessageTmp')->where('msg_id=' . $msg_id)->find();
        return $msg_tmp;
    }
    
    public function sumbitStatus($is_auto) {
        return $is_auto;
    }

}
