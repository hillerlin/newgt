<?php

//项目贷后管理
namespace Admin\Controller;

class AfterLoanController extends CommonController {
    
    public function logg() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');

        $model = D('AfterLoanLog');
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $admin = session('admin');
//        $map['p.after_loan_admin'] = $admin['admin_id'];
        $result = $model->getList($page, $pageSize, $map);
//        $total = $model->where($map)->count();
//        $list = $model->where($map)->page($page, $pageSize)->select();

        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display();
    }

    public function addLog() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('非法请求');
        }
        $pro_info = D('Project')->findByPk($pro_id);
        
        $company_list = D('Company')->getCntractCompany($pro_id);
        
        $this->assign('company_list', $company_list);
        $this->assign($pro_info);
        
        $this->display('add_log');
    }
    
    public function editLog() {
        $log_id = I('get.log_id');
        $log_info = D('AfterLoanLog')->findByPk($log_id);
        $pro_info = D('Project')->findByPk($log_info['pro_id']);
        $company_list = D('Company')->getCntractCompany($log_info['pro_id']);
        
        $this->assign('company_list', $company_list);
        $this->assign($log_info);
        $this->assign('pro_info',$pro_info);
        $this->display('edit_log');
    }
    
    /* 保存贷后日志 */
    public function save_log() {
        $model = D('AfterLoanLog');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
//        $data = I('post.role_id');
        $admin = session('admin');
        $model->admin_id = $admin['admin_id'];
        if ($data['log_id']) {
            $result = $model->save();
        } else {
            $model->addtime = time();
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array('dialogid' => 'after-loan-logg'));
        }
    }
    
    //等待分配的项目
    public function undistributed() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $status = I('post.status');
        $isSearch = I('post.isSearch');
        
        if ($isSearch) {
            if ($status !== '') {
                if ($status == 1) {
                    $map['after_loan_admin'] = 0;
                } elseif ($status == 2) {
                    $map['after_loan_admin'] = array('neq', 0);
                }
            }
            if (!empty($pro_title)) {
                $map['pro_title'] = $pro_title;
            }
        }

        $map['step_pid'] = 4;
//        var_dump($map);exit;
//        $map['after_loan_admin'] = 0;
        $model = D('Project');
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->relation(true)->order('after_loan_admin')->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }
    
    public function distribute() {
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->json_error('参数错误');
        }
        $admin = session('admin');
        $model = D('Project');
        if (IS_POST) {
            $data['after_loan_admin'] = I('post.risk_admin_id');
            $model->startTrans();
            if (!D('Project')->updateByPk($pro_id, $data)) {
                $model->rollback();
                $this->json_error('分配失败，请稍后再试');
            }
            if (D('ProcessLog')->distribution($pro_id, $admin['admin_id'], $data['after_loan_admin'], 2) === false) {
                $model->rollback();
                $this->json_error('内部错误3'.D('ProcessLog')->getError());
            }
            $model->commit();
            $this->json_success('分配成功', '', '', true, array('tabid' => 'afterloan-undistribute'));
        }
        $data = $model->where('pro_id=' . $pro_id)->relation('after_loan_admin')->find();
        $this->assign($data);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }
    
    public function myFollow() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $pro_title = I('post.pro_title');
        $auditresult=(I('auditresult'));
        $status = I('post.status');
        $isSearch = I('post.isSearch');
        
        if ($isSearch) {
            if ($status !== '') {
                if ($status == 1) {
                    $map['after_loan_admin'] = 0;
                } elseif ($status == 2) {
                    $map['after_loan_admin'] = array('neq', 0);
                }
            }
            if (!empty($pro_title)) {
                $map['pro_title'] = $pro_title;
            }
            if(!empty($auditresult)){
                $map['auditresult']=$auditresult;
            }
        }
        $admin = session('admin');
        $map['is_loan']=1;

//        $map['pro_step'] = 10;
//        $map['after_loan_admin'] = $admin['admin_id'];
        $model = D('Project');
        $total = $model->where($map)->count();
//        $result=$model->_link['audit']['condition']='auditresult=1';
        $list = $model->where($map)->order('addtime desc')->relation(true)->order('after_loan_admin')->page($page, $pageSize)->select();
        $workflow = D('Workflow')->getWorkFlow();
        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list' => $list));
        $this->display('my_follow');
    }
    
    //上传贷后报告
    public function upload() {
        $pro_id = I('request.pro_id');
        $log_id = I('request.log_id');
        $field = 'pro-' . $pro_id;
        $upload_info = upload_file('/project/afterloan/', $field);
        $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", time()));
        $admin = session('admin');
        if (isset($upload_info['file_path'])) {
            $save_data['log_id'] = $log_id;
            $save_data['path'] = $upload_info['file_path'] ;
            $save_data['file_name'] = $upload_info['name'];
            $save_data['addtime'] = time();
            $save_data['admin_id'] = $admin['admin_id'];
            $save_data['sha1'] = $upload_info['sha1'];
            if (D('AfterLoanFile')->add($save_data)) {
                $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
            }
        }
        $this->json_error('上传失败,' . $upload_info);
    }
    
    public function files() {
        $log_id = I('request.log_id');
        $list = D('AfterLoanFile')->where('log_id='.$log_id)->select();
        $exts = getFormerExts();
        
        $this->assign('log_id', $log_id);
        $this->assign('exts', $exts);
        $this->assign('list', $list);
        $this->display();
    }
    
    //贷后日志
    public function afterLoanLog() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $model = D('AfterLoanLog');
        $map['t.pro_id'] = $pro_id;
        $this->assign('pro_id', $pro_id);
        $result = $model->getList($page, $pageSize, $map);
//        $total = $model->where($map)->count();
//        $list = $model->where($map)->page($page, $pageSize)->select();
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->display('after_loan_log');
    }

    /**
     * 内审管理
     */
    public function audit(){
        $data=I('post.');
        if(empty($data['m'])) goto auditdisplay;
        foreach(array('basicinfo','ticket','trade','warrenttype','paydemand','contractinfo') as $v){
            $result[$v]=$this->sortaudit($data,$v);
        }
        $result['illustration']=$data['illustration'];
        $result['auditresult']=$data['auditresult'];
        $result['pro_id']=$data['pro_id'];
        if(strcasecmp($data['m'],'edit')===0 ){
            if(M('audit')->getFieldByProId($data['pro_id'],'id')){
                $this->json_error('新增失败，此项目已内审过');
            }
            if(M('audit')->data($result)->add()){
                $this->json_success('新增成功');
            }else{
                $this->json_error('新增失败');
            }
        }else if(strcasecmp($data['m'],'update')===0 && M('audit')->where('pro_id = '.$data['pro_id'])->save($result)  ){
            $this->json_success('更新成功');
        } else{
            $this->json_error('操作失败');
        }
        auditdisplay:
        $list=M('audit')->where('pro_id = '.I('get.pro_id'))->find();
        foreach($list as $k =>$v){
            if(in_array($k,array('basicinfo','ticket','trade','warrenttype','paydemand','contractinfo'))) $list[$k]=json_decode($v);
            continue;
        }
        $this->assign('list',$list);
        $this->assign('pro_id',I('get.pro_id'));
        $this->display();
    }
    public function sortaudit($data,$pre){
        $tmp=[];
        foreach($data as $k=>$v){
            $newpre=substr($k,0,strpos($k,'_'));
            if(strcasecmp($newpre,$pre)===0){
                if(in_array($newpre,array('basicinfo','ticket'))){
                    array_push($tmp,$v);
                }else{
                    $tmp[$k]=$v;
                }
            }
        }
        if(count($tmp)!=count($tmp,1)){
            array_unshift($tmp,null);
            $tmp=call_user_func_array('array_map',$tmp);
        }
        return json_encode($tmp);
    }
}
