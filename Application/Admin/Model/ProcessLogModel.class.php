<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProCessLogModel extends BaseModel {
    
    protected $_validate = array(
        array('pro_title', 'require', '请输入项目标题'),
        array('pro_account', 'require', '请输入项目标题'),
        array('pro_real_money', 'require', '请选择国投的跟进人'),
        array('gt_uid', 'require', '请输入项目标题'),
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
            'as_fields' => 'real_name',
        ),
        'company' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'company',
            'mapping_name' => 'company',
            'foreign_key' => 'company_id',
//            'as_fields' => 'user_name',
        ),
    );
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('LEFT JOIN __PROJECT_REVIEW__ AS pr ON pr.log_id=t.id')
                ->field('t.*,real_name,count(pr.id) as files')
                ->where($map)
                ->group('t.id')
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function getLoanList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC') {
        $order = 'context_id DESC ,t.addtime DESC';
        $map['t.step_pid'] = 5;
        $map['t.context_type'] = 'loan_id';
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __LOAN_FORM__ AS lf ON lf.loan_id=t.context_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __LOAN_FORM__ AS lf ON lf.loan_id=t.context_id')
//                ->join('LEFT JOIN __LOAN_FORM__ AS lf ON lf.loan_id=t.pro_id')
                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('LEFT JOIN __PROJECT_REVIEW__ AS pr ON pr.log_id=t.id')
                ->field('t.*,real_name,count(pr.id) as files')
                ->where($map)
                ->group('t.id')
                ->page($page, $pageSize)
                ->order($order)
                ->select();
//        var_dump($this->_sql());
        return array('total' => $total, 'list' => $list);
    }
    
    /**写入项目人员分配情况
     * 
     */
    public function distribution($pro_id, $admin_id, $receiver_id, $step_id) {
        $reveiver_name = D('Admin')->findByPk($receiver_id, 'real_name');
        $log_detail = array(
            'context_id' => $pro_id, 
            'admin_id' => $admin_id, 
            'status' => 1, 
            'opinion' => '分配给：' . $reveiver_name['real_name'], 
            'addtime' => time(), 
            'pro_step' => $step_id, 
            'step_pid' => 10, 
            'context_type' => 'pro_id');
        return $this->add($log_detail);
    }
    //判断项目完结申请记录
    public function historyProject($admin_id)
    {
        $list=$this->where("`step_pid`=8 and `admin_id`=%d",array($admin_id))->field('context_id')->select();
        $projectId=array();
        if($list)
        {
            foreach ($list as $v)
            {
                array_push($projectId,$v['context_id']);
            }
        }
         return !empty($list)?$projectId:false;

    }
}

