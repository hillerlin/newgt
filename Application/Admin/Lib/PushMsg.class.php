<?php
namespace Admin\Lib;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PushMsg {
    
    protected $msgTmp;
    protected $msgId;
    
    /**
     * 
     * @param type $msgId
     */
    public function __construct($msgId) {
        $this->msgId = $msgId;
        $this->tmplate();
    }
    
    /**
     * 推送消息
     * @param int $admin_id
     * @param boolean $is_role
     * @return boolean
     */
    public function push($admin_id, $is_role = false) {
        $data['controller'] = $this->msgTmp['controller'];
        $data['action'] = $this->msgTmp['action'];
        $data['title'] = $this->msgTmp['title'];
        $data['description'] = $this->msgTmp['description'];
        $data['addtime'] = time();
        if ($is_role) {
            $data['role_id'] = $admin_id;
        } else {
            $data['admin_id'] = $admin_id;
        }
        return D('Message')->add($data);
    }
    
    protected function tmplate() {
        $arr = array(
            'lxsq' => array(
                'controller' => 'Project',
                'action' => 'undistributed',
                'description' => '###项目-待分配',
                'title' => '待分配项目'
            ),
            'fpxm' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '###项目-项管部审核',
                'title' => '项管审核'
            ),
            'audit' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目-',
                'title' => '项管审核'
            ),
        );
        $this->msgTmp = isset($arr[$this->msgId]) ? $arr[$this->msgId] : ''; 
    }
    
    public function workFlowTmpMsg($pro_title, $pro_step_desc) {
        $this->msgTmp['description'] = $pro_title . $this->msgTmp['description'] .$pro_step_desc;
    }
    
    public function setTmp($tmp) {
        $this->msgTmp = $tmp;
    }
    
    //消息推送
    public function pushMessage($step_pid, $step_id, $status) {
        $node = D('Workflow')->getNode($step_pid, $step_id);
        $node_status = json_decode($node['step_next']);
        $message = $node['workflow'] . '-' . $node['step_desc'] . '-' . $node_status[$status]['desc'];
        //获取要推送的人的id
        $admin_ids = array();
        $dataList = array();
        foreach ($admin_ids as $admin_id) {
            $data['description'] = $message;
            $data['admin_id'] = $admin_id;
            $dataList[] = $data;
        }
        if (!D('Message')->addAll($dataList)) {
            return false;
        }
        return true;
    }
    
}
