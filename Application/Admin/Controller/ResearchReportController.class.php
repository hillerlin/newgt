<?php

namespace Admin\Controller;

class ResearchReportController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    //研报管理
    public function manage() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $dept_id = I('post.dept_id');
        $isSearch = I('post.isSearch');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ResearchReport');

        if ($isSearch) {
            if ($dept_id !== '') {
                $map['d.dept_id'] = $dept_id;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.edit_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.edit_time'][] = array('ELT', $end_time);
            }
        }
        $result = $model->getList($page, $pageSize, $map);
        $departments = D('Department')->where('pid!=0')->select();
//        var_dump($result);exit;
        $this->assign('departments', $departments);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
    //我的研报管理
    public function myResearch() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $type = I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ResearchReport');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.add_time'][] = array('ELT', $end_time);
            }
        }
        $admin = session('admin');
        $map['t.admin_id'] = $admin['admin_id'];
        $result = $model->getList($page, $pageSize, $map);
        
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display('my_research');
    }

    public function add() {
        $exts = getFormerExts();
        $tabid = I('get.tabid');
        $department = D('Department')->where('pid!=0')->select();
        $admin = session('admin');
        
        $this->assign('current_admin', $admin);
        $this->assign('department', $department);
        $this->assign('tabid', $tabid);
        $this->assign('exts', $exts);
        $this->display();
    }

    public function save() {
        $model = D('ResearchReport');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $model->edit_time = strtotime($data['edit_time']);
        $admin = session('admin');
        if ($data['id']) {
            $result = $model->save();
        } else {
            $model->admin_id = $admin['admin_id'];
            $result = $model->add();
        }
        
        if ($result === false) {
            $model->rollback();
            $this->json_error('保存失败1');
        }
        $tabid = I('post.tabid', 'researchreport-myresearch');
        $this->json_success('保存成功', '', '', true, array('tabid' => $tabid));
    }
    
    //上传审核资料
    public function upload() {
        $field = date('Y-m-d');
        $upload_info = upload_file('/reserch/', $field);
        $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", time()));
        if (isset($upload_info['file_path'])) {
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败,' . $upload_info);
    }
    
    
    //删除附件
    public function remove() {
        $file_path = I('request.file_path');
        //文件不在的话就只删除数据库
        if (file_exists($file_path)) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
//        $res2 = unlink('.'.$file_path);
        if ($res2) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('删除失败');
        }
    }
    
    public function edit() {
        $id = I('get.id');
        if (empty($id)) {
            $this->json_error('非法请求');
        }
        $map['id'] = $id;
        $data = D('ResearchReport')->getList(1, 1, $map);   
        $exts = getFormerExts();
        $department = D('Department')->where('pid!=0')->select();
//        var_dump($department);exit;
        $this->assign('department_lists', $department);
        $this->assign('exts', $exts);
        $this->assign($data['list'][0]);
        $this->display();
    }
    
    public function del() {
        $id = I('get.id');
        $model = D('ResearchReport');
        
        $state = $model->delete($id);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    //研报列表
    public function more() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $dept_id = I('post.dept_id');
        $isSearch = I('post.isSearch');
        $author = I('post.author');
        $title = I('post.title');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('ResearchReport');

        if ($isSearch) {
            if ($dept_id !== '') {
                $map['d.dept_id'] = $dept_id;
            }
            if ($author !== '') {
                $map['t.author'] = array('LIKE', "%$author%");
            }
            if ($title !== '') {
                $map['t.title'] = array('LIKE', "%$title%");
            }
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.edit_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.edit_time'][] = array('ELT', $end_time);
            }
        }
        
        $map['t.status'] = 1;
        $result = $model->getList($page, $pageSize, $map);
        $departments = D('Department')->where('pid!=0')->select();
        
        $this->assign('departments', $departments);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
    //下载
    public function download() {
        $id = I('get.id');
        $file_info = D('ResearchReport')->findByPk($id);
//        var_dump($file_info);exit;
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $filePath = $_SERVER["DOCUMENT_ROOT"].$file_info['path'];
        $filename = $file_info['filename'];
        header('pragma:public');
        $encoded_filename = rawurlencode($filename);
        //处理中文文件名
        if (preg_match("/MSIE/", $ua)) {
         header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
         header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
         header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        header("Content-Length: ". filesize($filePath));
        readfile($filePath);
    }
}
