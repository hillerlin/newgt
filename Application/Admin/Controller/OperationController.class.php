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
    //获取OA要审核的项目信息
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
        $this->display('getproject');
    }

}
?>