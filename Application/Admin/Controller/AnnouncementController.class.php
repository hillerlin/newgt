<?php

namespace Admin\Controller;

class AnnouncementController extends CommonController {

    public function __construct() {
        parent::__construct();
    }
    
    //新闻列表
    public function index() {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
            $model = D('Announcement');
            $result = $model->getList($page, $pageSize);
            $list = $result['list'];
            $total = $result['total'];
            $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }

    /* 添加管理员 */

    public function add() {
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $id = I('get.id');
        $model = D('Announcement');
        $detail = $model->getDetail($id);
        $this->assign($detail);
        $this->display();
    }
    
    /* 保存管理员 */
    public function save() {
        $model = D('Announcement');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $admin = session('admin');
        $model->admin_id = $admin['admin_id'];

        if ($data['id']) {
            $result = $model->save();
        } else {
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }

    /* 删除管理员 */

    public function del() {
        $id = I('get.id');
        $model = D('Announcement');
        $state = $model->delete($id);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    public function detail() {
        $id = I('get.id');
        $model = D('Announcement');
        $detail = $model->getDetail($id);
        $this->assign($detail);
        $this->display();
    }

}
