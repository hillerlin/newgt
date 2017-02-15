<?php

namespace Admin\Controller;

use Admin\Lib\Privilege;
use Admin\Logic\DepartmentLogic;
use Admin\Model\CapitalFlowModel;
use Admin\Model\WorkflowModel;
use Admin\Lib\MsgTmp;
use Admin\Lib\Workflow;
use Admin\Lib\WorkflowService;

class OperationController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){

    }
    public function newTable(){
        $this->display();
    }

    /**
     * 添加申请
     */
    public function addApplication(){
        $type=I('get.type');
        $this->assign(array('type'=>$type));
        $this->display();
    }
    //从第三方数据库获取OA要审核的项目信息
    public function aysnProjectInfo()
    {
        header("Content-type:text/html:charset=utf-8");
        $pageSize = I('post.pageSize', 10);
        $page = I('post.pageCurrent', 1);
        $url = 'http://ndm.atrmoney.com/admin/dmlc/ProjectApi/waitLoan'; // 平台接口地址前缀
        $params['page']=$page;
        $params['pageNum']=$pageSize;
        $key=md5('xiaopinguo');
        $json=json_encode($params);
        $sign=md5($json.$key);
        $params['sign']=$sign;
        //new AsynReturn类
        $asynClass=new \Admin\Lib\AsynReturn;
        $asynClass->init($url,array('data'=>json_encode($params)));
        $result = $asynClass->request_post();
        $return_data = json_decode($result, true);
        $this->assign(array('total' => $return_data['data']['total'], 'pageCurrent' => $page, 'list' => $return_data['data']['list']));
        $this->display('getProject');
    }
    //从request_apply表中获取OA的信息
    public function applicationFundsList()
    {
     
        $pageSize = I('post.pageSize', 10);
        $page = I('post.pageCurrent', 1);
        $map['status']=0;
        $list=D('RequestFound')->applicationFundsInfo($page,$pageSize,$map);
        $this->assign(array('list'=>$list['list'],'total'=>$list['total']));
        $this->display();
    }
    //保存选中的项目，并在project表里新建OA项目
    public function saveProjects()
    {
        $admin=session('admin');
        $projectType='1';
        $projectname='';
        $proAccount=0;
        $updata=true;
        $raModel=D('RequestApply');
        $ids=I('post.ids');
        $proTitle=I('post.pro_title');

        //{&quot;id&quot;:&quot;16&quot;,&quot;pro_title&quot;: &quot;房屋贷款&quot;,&quot;pro_account&quot;:&quot;1000000&quot;}
        if(!I('post.ids'))
        {
            $this->error('请至少勾选一个项目');
        }
        foreach ($ids as $k=>$v)
        {
            $infoDecode=json_decode(htmlspecialchars_decode($v),true);
            $projectType.='_'.$infoDecode['id'];
            $projectname.='【'.$infoDecode['pro_title'].'】,';
            $proAccount+=$infoDecode['pro_account'];
            $updata= $raModel->where('`id`=%d',array($infoDecode['id']))->setInc('status') && $updata;
        }
        $projectname=rtrim($projectname,',');
        $projectModel=D('Project');
        $projectModel->create();
        $projectModel->pro_title=$proTitle==''?$projectname:$proTitle;
        $projectModel->pro_account=$proAccount;
        $projectModel->admin_id=$admin['admin_id'];
        $projectModel->binding_oa=$projectType;
        $projectModel->addtime=time();
        $resultAdd=$projectModel->add();
        if($resultAdd)
        {
            $this->json_success('添加成功', '', '', false, array('dialogid' => 'project-oaFlow'));
           // $this->success('成功！');
        }else
        {
            $this->error('新建失败！');
        }


    }

}
?>