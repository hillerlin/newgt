<?php

namespace Admin\Model;

use Admin\Model\BaseModel;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class MessageModel extends BaseModel {
    
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
    
    //所有列表
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 'addtime DESC') {
//        $map['status'] = 0;
        $order = 'status ASC,addtime DESC';
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
    
    //未读消息
    public function unReadList($page = 1, $pageSize = 30, $map = '', $order = 'addtime DESC') {
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
    
    public function unReadNums($admin_id) {
        $map['status'] = 0;
        $map['admin_id'] = $admin_id;
        $total = $this
                ->where($map)
                ->count();
        return $total;
    }
    
    //更新单个消息
    public function read($id) {
        return $this->updateByPk($id, array('status' => 1));
    }
    
    //跟新全部消息
    public function readAll($ids) {
        $map['id'] = array('in', $ids);
        return $this->where($map)->save(array('status' => 1));
    }
    
    public function push($admin_id, $pro_id, $step_pid, $step_id, $status) {
        $workflow = new \Admin\Lib\Workflow();
        $msg_tmp = $workflow->nextStepMsgTmp($step_pid, $step_id, $status);
        $receive_arr = D('MessagePush')->where('msg_id=' . $msg_tmp['msg_id'])->field('receiver')->find();
        $receiver_arr = json_decode($receive_arr['receiver'], true);
        $receiver_ids = D('Admin')->getExecutors($receiver_arr);
        $receiver_ids = array_column($receiver_ids, 'admin_id');
//        var_dump($receiver_ids);exit;
        $admin = D('Admin')->findByPk($admin_id);
        $pro_info = D('Project')->findByPk($pro_id);
        $description = str_replace('{admin}', $admin['real_name'], $msg_tmp['description']);
        $description = str_replace('###', $pro_info['pro_title'], $description);
//        var_dump($description,$receiver_ids);exit;
        $msg_save = array(
            'description' => $description,
            'title' => $msg_tmp['title'],
            'addtime' => time(),
        );
//        $receiver_ids[] = $pro_info['pro_linker'];
        in_array($pro_info['pro_linker'], $receiver_ids) ? '' : $receiver_ids[] = $pro_info['pro_linker'];
        if (!empty($pro_info['admin_id'])) {    //消息提醒默认加入项管专员
            in_array($pro_info['admin_id'], $receiver_ids) ? '' : $receiver_ids[] = $pro_info['admin_id'];
//            $receiver_ids[] = $pro_info['admin_id'];
        }
        if ($step_pid == 5 && $step_id == 3) {  //主审人审核需要单独判断
            in_array($pro_info['risk_admin_id'], $receiver_ids) ? '' : $receiver_ids[] = $pro_info['risk_admin_id'];
//            $receiver_ids[] = $pro_info['risk_admin_id'];
        }
//        var_dump($receiver_ids);exit;
        foreach ($receiver_ids as $id) {
            $msg_save['admin_id'] = $id;
            $dataList[] = $msg_save;
        }
//        var_dump($dataList);exit;
        $this->addALL($dataList);
    }
    
    //分配项管部
    public function exechangePmd($recevier_id, $pro_id) {
        $pro_info = D('Project')->findByPk($pro_id);
        $msg_save = array(
            'description' => "<code>{$pro_info['pro_title']}</code>项目分配给你",
            'title' => '项目分配信息',
            'addtime' => time(),
            'admin_id' => $recevier_id
        );
        $this->add($msg_save);
    }
    
    //分配尽调
    public function exechangeRcd($recevier_id, $pro_id) {
        $pro_info = D('Project')->findByPk($pro_id);
        $msg_save = array(
            'description' => "<code>{$pro_info['pro_title']}</code>项目分配给你",
            'title' => '项目分配信息',
            'addtime' => time(),
            'admin_id' => $recevier_id
        );
        $this->add($msg_save);
    }
    
    //流水认领通知
    public function fininaceFlow($counterparty, $money, $type) {
        if ($type === 'out') {
            $description = "有一笔￥{$money}款项打入{$counterparty}";
        } else {
            $description = "从{$counterparty}有一笔￥{$money}款项打入";
        }
        $msg_save = array(
            'description' => $description,
            'title' => '资金流水信息',
            'addtime' => time(),
        );
        $receiver_ids = D('Admin')->getExecutors(array('role_id' => '2,14'));
        $receiver_ids = array_column($receiver_ids, 'admin_id');
        $msg_list = array();
        foreach ($receiver_ids as $v) {
            $msg_save['admin_id'] = $v;
            $msg_list[] = $msg_save;
        }
//        var_dump($msg_list);exit;
        $this->addAll($msg_list);
    }
    
    //财务流水分配成功，发送通知
    public function checkFinanceFlow($pro_id, $money, $type) {
        $pro_info = D('Project')->findByPk($pro_id);
        $half_tmp = $this->flowTmp($type, $money);
        $description = '项目' . $pro_info['pro_title'] . $half_tmp;
        $msg_save = array(
            'description' => $description,
            'title' => '资金流水信息',
            'addtime' => time(),
        );
        $receiver_ids = D('Admin')->getExecutors(array('role_id' => '2,14,19,20,26'));
        $receiver_ids = array_column($receiver_ids, 'admin_id');
        $msg_list = array();
        foreach ($receiver_ids as $v) {
            $msg_save['admin_id'] = $v;
            $msg_list[] = $msg_save;
        }
        $this->addAll($msg_list);
    }
    
    protected function flowTmp($type, $money) {
        $money = round($money / 10000, 4);
        switch ($type) {
            case 'financing': 
                $tmp = "放款{$money}万";
                break;
            case 'cash_deposit': 
                $tmp = "付保证金{$money}万";
                break;
            case 'handling_charge': 
                $tmp = "付手续费{$money}万";
                break;
            case 'counseling_fee': 
                $tmp = "付咨询费{$money}万";
                break;
            case 'principal': 
                $tmp = "还本金{$money}万";
                break;
            case 'back_cash_deposit': 
                $tmp = "退还保证金{$money}万";
                break;
            case 'interest': 
                $tmp = "付利息{$money}万";
                break;
            case 'overdue_pay': 
                $tmp = "付罚息{$money}万";
                break;
            case 'back_interest': 
                $tmp = "退还利息{$money}万";
                break;
            case 'back_handling_charge': 
                $tmp = "退还手续费{$money}万";
                break;
        }
        return $tmp;
    }
}

