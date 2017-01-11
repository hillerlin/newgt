<?php

namespace Admin\Controller;

class WorkflowController extends CommonController {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $menuModel=D('menu');
        $authMode=D('auth');
        $menuInfo=$menuModel->select();
        $authInfo=$authMode->where("`role_id`=%d",array(session('admin')['role_id']))->select();

        foreach ($menuInfo as $k=>$v) {
            //立项流程
            if ($v['menu_name'] == '立项流程') {
                $project = $this->menuRec($menuInfo, $v['menu_id']);
                $project=$this->authRec($authInfo,$project);
            } elseif ($v['menu_name'] == '签约流程') {
                $contract = $this->menuRec($menuInfo, $v['menu_id']);
                $contract=$this->authRec($authInfo,$contract);
            } elseif ($v['menu_name'] == '放款流程')
            {
                $loan=$this->menuRec($menuInfo,$v['menu_id']);
                $loan=$this->authRec($authInfo,$loan);
            }elseif ($v['menu_name']=='非流程操作')
            {
                $nonFlow=$this->menuRec($menuInfo,$v['menu_id']);
                $nonFlow=$this->authRec($authInfo,$nonFlow);
            }
        }
        $this->assign(array('project'=>$project,'contract'=>$contract,'loan'=>$loan,'nonFlow'=>$nonFlow));
        $this->display();
    }

    public function menuRec($arr,$id)
    {
        $recAttr=array();
        foreach ($arr as $k=>$v)
        {
            if($v['pid']==$id)
            {
               $recAttr[$v['menu_id']]=$v;
            }
        }
        return $recAttr;
    }
    public function authRec($authInfo,$menuInfo)
    {
        $realObj=array();
        foreach ($authInfo as $k=>$v)
        {
            if(array_key_exists($v['menu_id'],$menuInfo))
            {
                 $realObj[$v['menu_id']]=$menuInfo[$v['menu_id']];
            }
        }
        return $realObj;
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
