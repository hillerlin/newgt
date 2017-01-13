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
}
?>