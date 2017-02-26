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
            }elseif ($v['menu_name']=='OA流程')
            {
                $oaFlow=$this->menuRec($menuInfo,$v['menu_id']);
                $oaFlow=$this->authRec($authInfo,$oaFlow);
            }elseif ($v['menu_name']=='完结流程')
            {
                $endFlow=$this->menuRec($menuInfo,$v['menu_id']);
                $endFlow=$this->authRec($authInfo,$endFlow);
            }
        }
        $this->assign(array('project'=>$project,'contract'=>$contract,'loan'=>$loan,'nonFlow'=>$nonFlow,'oaFlow'=>$oaFlow,'endFlow'=>$endFlow));
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
    public function analysis()
    {
        //$this->json_success('该功能正在开发！', '', '', false, array('dialogid' => 'project-oaFlow'));
       // $this->json_success('保存成功');
        $this->display();
    }  
    public function dataCenter()
    {
        //$this->json_success('该功能正在开发！', '', '', false, array('dialogid' => 'project-oaFlow'));
       // $this->json_success('保存成功');
        $this->display();
    }
    //下载中心
    public function download()
    {
        $admin = session('admin');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        //项目标题
        if (I('post.pro_title')) $map['p.pro_title'] = array('like', '%' . I('post.pro_title') . '%');
        //项目编号
        if (I('post.pro_no')) $map['p.pro_no'] = array('eq', I('post.pro_no'));
        //项目是否已经完结
        if (I('post.is_all_finish')) $map['p.is_all_finish'] = array('eq', I('post.is_all_finish'));
        //项目id
        if (I('get.pro_id')) $map['p.pro_id'] = array('eq', I('get.pro_id'));
        if (I('get.type')) {
            $map['p.binding_oa'] = array('EXP', 'is not null');
        }/*else //追加OA显示流程  在OA流程新建的  新建请款
        {
            $map['p.binding_oa']=array('EXP','is null');
        }*/
        if (I('post.begin_time')) {
            $map['p.addtime'][] = array('EGT', strtotime(I('post.begin_time')));
            $map['p.addtime'][] = array('ELT', strtotime(I('post.end_time')));
        }
        //预留功能，读配置来判断是否可以查看其他人的项目C(lookUpAll)
        $map['_string']="( w.pro_author=".$admin['admin_id']." and w.pro_role=0) or ( w.pro_role= ".$admin['role_id']." and w.pro_author=0 ) 
        or ( w.pro_author=".$admin['admin_id']." and w.pro_role=".$admin['role_id']." ) or p.admin_id=" .$admin['admin_id'];// p.admin_id=".$admin['admin_id'];


        //如果是消息推送过来的就需要标记redis了
        if(I('get.type') && I('get.pro_id')&& I('get.time'))  checkMessage(I('get.time'),I('get.type'),I('get.pro_id'));

        $result=D('Project')->projectinfo($page, $pageSize,$map);
        foreach($result['list'] as &$v){
            $v['authpage']=json_decode($v['authpage'],true);
        }
        //当前用户是所拥有的页面权限
        $authpage=M('Admin')->getFieldByAdminId($admin['admin_id'],'authpage');
        $authpage=json_decode($authpage,true);
        $this->assign('authpage',$authpage);
        $this->assign(array('list' => $result['list'], 'total' => $result['total'], 'pageCurrent' => $page,'type'=>I('get.type')));
        $this->display('Project/workflowlog');
    }
    //流程监控的商票、流水、放款等详细表格
    public function formDetailList()
    {
        $proId=I('get.proId');
        $admin=session('admin');
        $proLevel=array('11','13','15','17','20','21','22','23','24');
        foreach ($proLevel as $k=>$v)
        {
            switch ($v)
            {
                case '11':
                    $list= D('Project')->filterMemberToFrom($proId,$v,$admin['admin_id']);
                    break;
                case '13':
                    break;
                case '15':
                    break;
                case '17':
                    break;
                case '20':
                    break;
                case '21':
                    break;
                case '22':
                    break;
                case '23':
                    break;
                case '24':
                    break;
            }

        }


    }
}
