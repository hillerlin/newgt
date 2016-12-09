<?php

namespace Admin\Controller;

class WorkflowController extends CommonController {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('list', $workflow);
        $this->assign('workflow', $workflow);
        $this->display();
    }
    
    /* 添加管理员 */
    public function add() {
        $this->display();
    }

    /* 编辑 */
    public function edit() {
        $model = D('workflow');
        $step_id = I('get.step_id');
        $data = $model->relation('role')->where(array('step_id' => $step_id))->find();
        $this->assign($data);
        $this->display();
    }
    
    /* 保存管理员 */
    public function save() {
        $model = D('Workflow');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        if ($data['step_id']) {
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
        $mid = I('mid');
        $model = D('Member');
        $state = $model->delete($mid);
        if ($state !== false) {
            $this->json_success('删除成功', U('admin/index'));
        } else {
            $this->json_error('操作失败');
        }
    }
}
