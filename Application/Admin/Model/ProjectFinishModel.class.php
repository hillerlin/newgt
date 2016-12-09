<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProjectFinishModel extends BaseModel {
    
    protected $_validate = array(
//        array('pro_title', 'require', '请输入项目标题'),
//        array('pro_account', 'require', '请输入融资金额'),
//        array('pro_account', 'is_numeric', '请输入正确的融资金额', 0, 'function'),
//        array('pro_real_money', 'require', '请选择国投的跟进人'),
//        array('admin_name', '', '管理员已存在', 0, 'unique', 1),
    );
    
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
    
    public function applyList($page = 1, $pageSize = 30, $map = '', $order = '') {
        $order .= 't.addtime DESC';
        
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.finish_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
//                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=p.risk_admin_id')
                ->join('LEFT JOIN __COMPANY__ AS cp ON p.company_id=cp.company_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.finish_id AND wp.context_type="finish_id"')
                ->field('t.finish_id,t.submit_status,t.addtime,pro_title,a1.real_name as pmd_real_name,company_name,wp.current_node_index')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
//        print_r($this->_sql());exit;
        return array('total' => $total, 'list' => $list);
    }
    
    public function applyInfo($finish_id) {
        $map['finish_id'] = $finish_id;
        $info = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=p.pro_linker')
//                ->join('__WORKFLOW__ AS w ON w.step_id=t.pro_step AND w.step_pid=t.step_pid')
                ->field('t.*,pro_title,pro_account,a1.real_name as pmd_name,a2.real_name as pro_linker_name')
                ->where($map)
                ->find();
        return $info;
    }
    
    public function waitAudit($page = 1, $pageSize = 30, $map = '', $order = 't.addtime ASC',$type=0) {
        if($type==0)
        {
            $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__WORKFLOW_PROCESS__ AS wp ON wp.context=t.finish_id')
                ->where($map)
                ->count();
            $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=p.risk_admin_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=p.company_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.finish_id AND wp.`context_type`= "finish_id"')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,wp.*,a1.real_name as pmd_name,a2.real_name as rcd_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('t.pro_id')
                ->order($order)
                ->select();
            //print_r($this->_sql());
            return array('total' => $total, 'list' => $list);
        }else
        {
            $list = $this
                /*->table('__PROCESS_LOG__ AS t')*/
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __ADMIN__ AS a1 ON a1.admin_id=p.admin_id')
                ->join('LEFT JOIN __ADMIN__ AS a2 ON a2.admin_id=p.risk_admin_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=p.company_id')
                ->join('LEFT JOIN __WORKFLOW_PROCESS__ AS wp ON wp.context=t.finish_id AND wp.`context_type`= "finish_id"')
                ->join('LEFT JOIN __PROCESS_LOG__ AS l ON l.context_id=wp.context')
                ->field('t.*,pro_title,company_name,pro_account,pro_real_money,wp.*,a1.real_name as pmd_name,a2.real_name as rcd_name')
                ->where($map)
                ->page($page, $pageSize)
                ->group('finish_id')
                ->order($order)
                ->select();
            return array('list' => $list);
        }

    }
    
    public function recover() {
        return array(
            'logout_zdw' => '注销中登网登记',
            'back_invoice' => '退还发票',
            'back_Ukey' => '退还Ukey',
            'logout_account' => '注销账户',
            'change_yz' => '更改印鉴',
        );
    }

    //归档
    public function archive($finish_id, $admin_id) {
        $map['finish_id'] = $finish_id;
        $data['archived_admin'] = $admin_id;
        $data['archived_time'] = time();
        return $this->where($map)->save($data);
    }
}

