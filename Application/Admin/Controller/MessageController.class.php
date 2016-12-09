<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

/**
 * Description of MessageController
 *
 * @author Administrator
 */
class MessageController extends CommonController{
    //put your code here
    public function myMessage() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        
        $admin = session('admin');
        $map['admin_id'] = $admin['admin_id'];
        $result = D('Message')->getList($page , $pageSize, $map);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('my_message');
    }
    
    public function myUnReadMessage() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        
        $admin = session('admin');
        $map['admin_id'] = $admin['admin_id'];
        $result = D('Message')->unReadList($page , $pageSize, $map);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('my_message');
    }
    
    public function getNewMessage() {
        $admin = session('admin');
        $unReadNums = D('Message')->unReadNums($admin['admin_id']);
        $this->sendData(array('unReadNums' => $unReadNums));
    }
    
    public function read() {
        $id = I('post.id');
        
        if (!D('Message')->read($id)) {
            $this->json_error('');
        }
        $this->json_success();
    }
    
    public function readAll() {
        $readids = I('get.readids');
        D('Message')->readAll($readids);
        $this->json_success();
    }
    
    //更多消息
    public function more() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        
        $admin = session('admin');
        $map['admin_id'] = $admin['admin_id'];
        $result = D('Message')->getList($page , $pageSize, $map);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('more');
    }
    
    public function edit() {
        $flow_id = I('get.flow_id');
        $msg_id = I('get.msg_id');
        
        if (!empty($msg_id)) {
            $msg_tmp = D('MessageTmp')->where('msg_id=' . $msg_id)->find();
            $recevier = D('MessagePush')->where('msg_id=' . $msg_id)->find();
            $recevier = json_decode($recevier['receiver'], true);
//            var_dump($recevier);
            if (isset($recevier['dp_id'])) {
                $dp = D('Department')->selectByPks($recevier['dp_id'], 'dept_id,department');
                $dp_s['dept_id'] = implode(',', array_column($dp, 'dept_id'));
                $dp_s['deparment'] = implode(',', array_column($dp, 'department'));
                $data['dp'] = $dp_s;
            }
            if (isset($recevier['role_id'])) {
                $role = D('Role')->selectByPks($recevier['role_id'], 'role_id,role_name');
//                var_dump(D('Role')->_sql());
                $role_s['role_id'] = implode(',', array_column($role, 'role_id'));
                $role_s['role_name'] = implode(',',  array_column($role, 'role_name'));
                $data['role'] = $role_s;
            }
            if (isset($recevier['admin_id'])) {
                $admin = D('Admin')->selectByPks($recevier['admin_id'], 'admin_id,real_name');
                $admin_s['admin_id'] = implode(',',  array_column($admin, 'admin_id'));
                $admin_s['real_name'] = implode(',', array_column($admin, 'real_name'));
                $data['admin'] = $admin_s;
            }
//            var_dump($data);
            $this->assign($data);
            $this->assign($msg_tmp);
        }
        
        $this->assign('flow_id', $flow_id);
        $this->assign('msg_id', $msg_id);
        $this->display();
    }
    
    public function save() {
        $data['title'] = I('post.title');
        $data['description'] = I('post.description');
        $flow_id = I('post.flow_id');
        $msg_id = I('post.msg_id');
        $dp_ids = I('post.dp_id');
        $role_ids = I('post.role_id');
        $admin_ids = I('post.admin_id');
        
//        var_dump($msg_id);exit;
        $recevier = json_encode(array('dp_id' => $dp_ids, 'role_id' => $role_ids, 'admin_id' => $admin_ids));
//        var_dump($recevier);exit;
        if (empty($msg_id)) {
            
        } else {
            $model = D('MessageTmp');
            $model->startTrans();
            if (D('MessageTmp')->where('msg_id='.$msg_id)->save($data) === false) {
                $model->rollback();
                $this->json_error('更新失败1');
            }
            if (D('MessagePush')->where('msg_id='.$msg_id)->count() > 0) {
                if (!D('MessagePush')->where('msg_id='.$msg_id)->save(array('receiver' => $recevier))) {
                    $model->rollback();
                    $this->json_error('更新失败2');
                }
            } else {
                if (!D('MessagePush')->add(array('receiver' => $recevier, 'msg_id' => $msg_id))) {
                    $model->rollback();
                    $this->json_error('插入失败');
                }
            }
        }
        $model->commit();
        $this->json_success('成功');
    }
}
