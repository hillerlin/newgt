<?php

namespace Admin\Controller;

class ProjectDebtDetailController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
//        if (IS_POST) {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $pro_title = I('post.pro_title');
        $debt_no = I('post.debt_no');
        $model = D('ProjectDebtDetail');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        $map['t.status'] = 1;
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            } else {
                unset($map['t.status']);
            }
            if (!empty($pro_title)) {
                $map['p.pro_title'] = $pro_title;
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $order = 'end_time ASC';
        $result = $model->getList($page, $pageSize ,$map, $order);
//        var_dump($result);exit;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
//            $this->ajaxRe(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
//        }
        $this->assign('status', $map['t.status']);
        $this->assign('post', $_POST);
        $this->display();
       
    }
    
    public function specified() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $debt_all_id = I('get.debt_all_id');
        $model = D('ProjectDebtDetail');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($pro_no)) {
                $map['p.pro_no'] = $pro_no;
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $map['t.debt_all_id'] = $debt_all_id;
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($status);eixt;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('debt_all_id', $debt_all_id);
        $this->assign('post', $_POST);
        $this->display();
       
    }

    public function add() {
        $this->display();
    }
    
    public function doAdd() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('请先选择项目');
        }
        $this->assign('pro_id', $pro_id);
        $this->display('do_add');
    }
    
    public function getDebtList() {
        
        $pro_id = I('post.pro_id');
        
        $order = 'end_time ASC';
        $map['t.pro_id'] = $pro_id;
        $map['t.status'] = 1;
        $model = D('ProjectDebtDetail');
        $result = $model->getList(1, 30 ,$map, $order);
//        var_dump($result);exit;
        $this->sendData($result['list']);
    }

    public function edit() {
        $debt_id = I('get.debt_id');
        if (empty($debt_id)) {
            $this->json_error('非法请求');
        }
        $debt_info = D('ProjectDebtDetail')->findByPk($debt_id);
        
        $this->assign($debt_info);
        $this->display();
    }

    public function del() {
        
    }
    
    public function save_debt() {
        $model = D('ProjectDebtDetail');
        $admin = session('admin');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        $model->start_time = strtotime($model->start_time);
        $model->end_time = strtotime($model->end_time);
        if ($data['debt_id']) {
            $result = $model->save();
        } else {
            $model->admin_id = $admin['admin_id'];
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true);
        }
    }
    
    //换质操作
    public function exchange() {
        if (IS_POST) {
            $pro_id = I('post.pro_id');
            $exchange_debt_ids = I('post.exchange_debt_ids');
            
            if (empty($pro_id)) {
                $this->json_error('本项目还未发放过贷款');
            }
            //防止提交的时候被篡改，再次判断
            $map['debt_id'] = array('in', $exchange_debt_ids);
            $pro_debt = D('ProjectDebtDetail')->where($map)->select();
            $n = count($pro_debt);
            $tmp_pro_id = $pro_debt['0']['pro_id'];
            $debt_money = 0;
            for ($i = 0; $i < $n; $i++) {
                if ($pro_debt[$i]['pro_id'] != $tmp_pro_id) {
                    $this->json_error('请选择同一个项目的债权');
                }
                $tmp_pro_id = $pro_debt[$i]['pro_id'];
            }
            
//            $exchange_debt = session("exchange_debt_{$pro_id}");
            $model = D('ProjectDebtDetail');
            $model->startTrans();
            $map1['pro_id'] = $pro_id;
            $map1['status'] = -1;
            if (!$model->where($map1)->save(array('status'=>1))) {  //插入新的债权
                $model->rollback();
                $this->json_error('内部错误1');
            }
            if (!$model->where($map)->save(array('status'=>0))) {   //修改被替换的债权状态
                $model->rollback();
                $this->json_error('内部错误2');
            }
            $model->commit();
            $this->json_success('换质成功', '', '', true, array('dialogid'=>'projectdebt-detail-specified'));
        }
        $debt_all_id = I('get.debt_all_id');
        $debt_id = I('get.debt_id');
        if (empty($debt_all_id)) {
            $this->json_error('本项目还未发放过贷款');
        }
        $this->assign('debt_all_id', $debt_all_id);
        $this->assign('debt_id', $debt_id);
        $this->display();
    }
    
    //先将要换质的信息加入session
    public function doExchangeAdd() {
        if (IS_POST) {
            $model = D('ProjectDebtDetail');
            $admin = session('admin');
            if (false === $data = $model->create()) {
                $e = $model->getError();
                $this->json_error($e);
            }

            $model->start_time = strtotime($model->start_time);
            $model->end_time = strtotime($model->end_time);
            if ($data['debt_id']) {
                $result = $model->save();
            } else {
                $model->admin_id = $admin['admin_id'];
                $model->status = -1;
                $result = $model->add();
            }

            if ($result === false) {
                $this->json_error('保存失败');
            } else {
                $this->json_success('保存成功', '', '', true);
            }
        }
        $pro_id = I('get.pro_id');
        $this->assign('pro_id', $pro_id);
        $this->display('do_exchange_add');
    }
    
    //编辑
    public function doExchangeEdit() {
        $pro_id = I('get.pro_id');
        $uniqid= I('get.uniqid');
        $exchange_debt = session("exchange_debt_{$pro_id}");
        
        $this->assign($exchange_debt[$uniqid]);
        $this->display('do_exchange_edit');
    }
    
    public function getExchangeAdd() {
        $pro_id = I('post.pro_id');
        $map['pro_id'] = $pro_id;
        $map['status'] = -1;
        $list = D('ProjectDebtDetail')->where($map)->select();
//        $exchange_debt = session("exchange_debt_$pro_id");
//        $list = array();
//        foreach($exchange_debt as  $v) {
//            $list[] = $v;
//        }
        $this->sendData($list);
    }
    
    //换质界面
    public function exchangeAdd() {
        $debt_id = I('get.debt_id');
        if (empty($debt_id)) {
            $this->json_error('本项目还未发放过贷款');
        }
        $map['debt_id'] = array('in', $debt_id);
        $pro_debt = D('ProjectDebtDetail')->where($map)->select();
        $n = count($pro_debt);
        $tmp_pro_id = $pro_debt['0']['pro_id'];
        $debt_money = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($pro_debt[$i]['pro_id'] != $tmp_pro_id) {
                $this->json_error('请选择同一个项目的债权');
            }
            $tmp_pro_id = $pro_debt[$i]['pro_id'];
            $debt_money += $pro_debt[$i]['debt_value'];
        }
        $pro_info = D('Project')->findByPk($pro_debt[0]['pro_id']);
        $pro_info['debt_money'] = $debt_money;
        session("exchange_debt_{$pro_debt[0]['pro_id']}", null);  //清除之前添加的质换信息
        $this->assign($pro_info);
        $this->assign('exchange_debt_ids', $debt_id);
        $this->display('exchange_add');
    }
    
    //文件树
    public function file() {
        $map['debt_id'] = I('get.debt_id');
        $list = D('DebtAttachment')->where($map)->select();
        $this->assign('list', $list);
        $this->assign($map);
        $this->display();
    }
    
    //上传附件
    public function upload_attachment() {
        $debt_id = I('request.debt_id');
        if (empty($debt_id)) {
            $this->json_error('非法操作');
        }
        $admin = session('admin');
//        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
//            $this->json_error('您没有上传的权限');
//        }
        $field = 'debt-'.$debt_id;
        $upload_info = upload_file('/project/attachment/', $field);
//        $this->ajaxReturn(array('status' => 1, 'data' => array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'))));
        if (isset($upload_info['file_path'])) {
            $save_data['debt_id'] = $debt_id;
            $save_data['path'] = $upload_info['file_path'];
            $save_data['doc_name'] = $upload_info['name'];
            $save_data['addtime'] = time();
            
            $save_data['admin_id'] = $admin['admin_id'];
            if (!($aid = D('DebtAttachment')->add($save_data))) {
                $this->json_error('上传失败');
            }
            $content = array('file_path' => $upload_info['file_path'],'file_id' => date('YmdHis'), 'file_name'=>$upload_info['name'], 'addtime'=> date("Y-m-d H:i:s", $save_data['addtime']), 'aid' => $aid, 'debt_id' => $debt_id);
            $this->ajaxReturn(array('statusCode' => 200, 'content'=>$content, 'message'=>'上传成功'));
        }
        $this->json_error('上传失败');
    }
    
    //删除附件
    public function remove_attachment() {
        $aid = I('request.aid');
        $debt_id = I('request.debt_id');
        $admin = session('admin');
        //只删数据库
//        if (!$this->checkAuthUpload($pro_id, $file_id, $role_id)) {
//            $this->json_error('您没有删除的权限');
//        }
        $model = D('DebtAttachment');
        $res1 = $model->where(array('id' => $aid, 'debt_id' => $debt_id))->delete();
//        $res2 = unlink('.'.$file_path);
        if ($res1) {
            $this->json_success('删除成功','', '','', array('divid'=>'layout-01'));
        } else {
            $this->json_error('删除失败');
        }
    }

}
