<?php

namespace Admin\Controller;

class FundManageController extends CommonController
{

    public function __construct()
    {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }

    /**
     *
     */
    public function index()
    {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $auditType=(I('post.auditType')==''|| intval(I('post.auditType'))==0)?0:I('post.auditType');
        $branch_id = intval(I('post.branch_id'));
        $branch_ch_id = intval(I('post.branch_ch_id'));
        $manager_name = I('post.manager_name');
        $expire_time=I('post.expire_time');
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $select=I('post.selectType');//选择筛选类型
        $selectValue=trim(I('post.selectValue'));//筛选的值
        $selectSecond=I('post.selectTypeSecond');//第二次筛选的类型
        $selectValueSecond=trim(I('post.selectValueSecond'));//第二次过滤的值
        $model = D('FundOrder');
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
            }
        }
        if ($isSearch) {
            $fileVale=array('1'=>'partnership', '2'=>'customer_name', '3'=>'fund_title', '4'=>'real_name', '5'=>'fb.branch_name', '6'=>'fbb.branch_name','7'=>'term');
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $end_time = strtotime($end_time);
                $map['pay_time'] = array('between',"$begin_time,$end_time");
            }
            if(!empty($select) && $select!='all')
            {
                foreach($fileVale as $k=>$v)
                {
                    if(!empty($selectSecond) && $selectSecond==$k)
                    {
                        if($v=='term')
                        {
                            $map[$v]=$selectValueSecond;
                        }else
                        {

                            $map[$v]=array('like',"%$selectValueSecond%");
                        }
                    }
                    if($select==$k)
                    {
                        if($v=='term')
                        {
                            $map[$v]=$selectValue;
                        }else
                        {

                            $map[$v]=array('like',"%$selectValue%");
                        }

                    }
                }
            }
            if($expire_time)
            {
                $expire_time=strtotime($expire_time);
                $map['deadline']=array('eq',$expire_time);
            }
        }
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $admin = session('admin');
        $roleId = $admin['role_id'];
        $roleInfo = D('role')->field('role_des')->where('`role_id`=%d', array($roleId))->find();
        if (logicRoleId($roleId)) {
            $map['changer_id'] = $admin['admin_id'];
        }
            if(intval($auditType)!==3)
            {
                $map['t.isaudit']=$auditType;
            }
            $result = $model->getList($page, $pageSize, $map, $order);

        $branch_list = D('FundBranch')->where('pid=1')->select();
        $this->assign('branch_list', $branch_list);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list'], 'roleInfo' => unserialize($roleInfo['role_des'])['edit'], 'roleId' => $roleId));
        $this->assign('post', $_POST);
        $this->display();
    }

    public function exportExcel()
    {
        $admin = session('admin');
        $roleId = $admin['role_id'];
        if($roleId==32) return false;
        $expire_time=I('get.expire_time');
        $begin_time = I('get.begin_time');
        $end_time = I('get.end_time');
        $select=I('get.selectType');//选择筛选类型
        $selectValue=trim(I('get.selectValue'));//筛选的值
        $selectSecond=I('get.selectTypeSecond');//第二次筛选的类型
        $selectValueSecond=trim(I('get.selectValueSecond'));//第二次过滤的值
        $auditType=(I('get.auditType')==''|| intval(I('get.auditType'))==0)?0:I('get.auditType');//审核的状态

        $fileVale=array('1'=>'partnership', '2'=>'customer_name', '3'=>'fund_title', '4'=>'real_name', '5'=>'fb.branch_name', '6'=>'fbb.branch_name',);
        if (!empty($begin_time)) {
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time);
            $map['pay_time'] = array('between',"$begin_time,$end_time");
        }
        if(!empty($select) && $select!='all')
        {
            foreach($fileVale as $k=>$v)
            {
                if(!empty($selectSecond) && $selectSecond==$k)
                {
                    $map[$v]=array('like',"%$selectValueSecond%");
                }
                if($select==$k)
                {
                    $map[$v]=array('like',"%$selectValue%");
                }
            }
        }
        if($expire_time)
        {
            $expire_time=strtotime($expire_time);
            $map['deadline']=array('eq',$expire_time);
        }
        $model = D('FundOrder');
        $map['isaudit']=$auditType;
        $result = $model->getList(0, 100000, $map);
        $head = array('合伙企业', '打款时间', '起息时间', '成立时间', '客户姓名','认购项目','打款金额(万)','客户收益率',
            '期限(月)','到期时间', '客户经理','地区','分部','合同号','身份证号码','银行账户信息','联系方式','备注','业绩提成率','管理津贴率','到期本息');

        $dataList = array();
        $list=array();
        foreach ($result['list'] as $k=>$v)
        {
            $list['partnership'] = $v['partnership'];
            $list['pay_time'] = date('Y-m-d',$v['pay_time']);
            $list['begin_interest_time'] = date('Y-m-d',$v['begin_interest_time']);
            $list['done_time'] = date('Y-m-d',$v['done_time']);
            $list['customer_name'] = $v['customer_name'];
            $list['fund_title'] = $v['fund_title'];
            $list['money'] = $v['money'];
            $list['fund_rate'] = $v['fund_rate'];
            $list['term'] = $v['term'];
            $list['deadline'] = date('Y-m-d',$v['deadline']);
            $list['real_name'] = $v['fmanager_name'];
            $list['branch_name'] = $v['fmanager_area'];
            $list['branch_ch_name'] = $v['fmanager_branch'];
            $list['contract_no'] = $v['contract_no'];
            $list['id_no'] = $v['id_no'].' ';
            $list['bank_no'] = $v['bank_no'].' ';
            $list['link_type'] = $v['link_type'];
            $list['remark'] = $v['remark'];
            $list['performance_rate'] = $v['performance_rate'];
            $list['manage_rate'] = $v['manage_rate'];
            $list['interestDue'] = $v['interestdue'];

            $decodeAttr=json_decode($v['time_to_rate'],true);//将付息时间跟利息反转
               if(is_array($decodeAttr))
               {
                   foreach ($decodeAttr as $kk=>$vv)
                   {
                       $_kk=$kk+1;
                       $rateTime="第".$_kk."次付息时间";
                       $rate="第".$_kk."次利息";
                       if(!in_array($rateTime,$head))
                       {
                           array_push($head,$rateTime,$rate);
                       }
                       $indexRateTime="rateTime_".$_kk;
                       $indexRate="rate_".$_kk;
                       $list[$indexRateTime]=$vv['time'];
                       $list[$indexRate]=$vv['rete'];

                   }
               }
            $dataList[] = $list;
            //删除缓存数据
            foreach ($decodeAttr as $kkk=>$vvv)
            {
                $_kkk=$kkk+1;
                $indexRateTime="rateTime_".$_kkk;
                $indexRate="rate_".$_kkk;
                unset($list[$indexRateTime]);
                unset($list[$indexRate]);

            }
        }
        $excel = new \Admin\Lib\PHPexecl();

        $excel->push($head, $dataList, 'test');
    }


    public function add()
    {
        $branch_list = D('FundBranch')->select();
        $admin = session('admin');
        $roleId = $admin['role_id'];
        $roleInfo = D('role')->field('role_des')->where('`role_id`=%d', array($roleId))->find();
        $this->assign(array('branch_list' => $branch_list, 'roleInfo' => unserialize($roleInfo['role_des'])['edit'], 'roleId' => $roleId));
        $this->display();
    }

    public function edit()
    {
        $fund_id = I('get.fund_id');
        $branch_list = D('FundBranch')->select();
        $map['fund_id']=$fund_id;
        $data = D('FundOrder')->getDetail($map);
        $admin = session('admin');
        $roleId = $admin['role_id'];
        $roleInfo = D('FundOrder')->roleInfo($roleId);
        intval($data['done_time'])==0?$data['done_time']='':$data['done_time'];
        intval($data['deadline'])==0?$data['deadline']='':$data['deadline'];
        $this->assign($data);
        $this->assign(array('branch_list'=>$branch_list,'roleInfo' => unserialize($roleInfo['role_des'])['edit'], 'roleId' => $roleId));
        $this->display();
    }

    public function connet_time_rate($n, $m)
    {
        return (array('time' => $n, 'rete' => $m));
    }

    public function save()
    {
        $model = D('FundOrder');
        $logModel = D('FundOrderLog');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        //a:2:{s:4:"edit";a:10:{i:0;a:2:{s:5:"field";s:8:"pay_time";s:4:"name";s:12:"打款时间";}i:1;a:2:{s:5:"field";s:19:"begin_interest_time";s:4:"name";s:12:"起息时间";}i:2;a:2:{s:5:"field";s:13:"customer_name";s:4:"name";s:12:"客户姓名";}i:3;a:2:{s:5:"field";s:10:"fund_title";s:4:"name";s:12:"认购项目";}i:4;a:2:{s:5:"field";s:5:"money";s:4:"name";s:6:"金额";}i:5;a:2:{s:5:"field";s:9:"fund_rate";s:4:"name";s:15:"客户收益率";}i:6;a:2:{s:5:"field";s:4:"term";s:4:"name";s:6:"期限";}i:7;a:2:{s:5:"field";s:11:"fmanager_id";s:4:"name";s:12:"客户经理";}i:8;a:2:{s:5:"field";s:11:"branch_name";s:4:"name";s:6:"地区";}i:9;a:2:{s:5:"field";s:6:"remark";s:4:"name";s:6:"备注";}}s:8:"rateInfo";s:4:"none";}
        $logModel->pay_time = $model->pay_time = strtotime($data['pay_time']);
        $logModel->begin_interest_time = $model->begin_interest_time = strtotime($data['begin_interest_time']);
        $logModel->done_time = $model->done_time = strtotime($data['done_time']);
        $logModel->deadline = $model->deadline = strtotime($data['deadline']);
        $logModel->performance_rate = $model->performance_rate = floatval($data['performance_rate']);
        $logModel->manage_rate = $model->manage_rate = floatval($data['manage_rate']);
        $logModel->fmanager_id = $data['fmanager_id'];
        $logModel->fmanager_name = $data['real_name']=I('post.real_name');
        $logModel->fmanager_area = $data['branch_name']=I('post.branch_name');
        $logModel->fmanager_branch = $data['branch_ch_name']=I('post.branch_ch_name');
        $logModel->money = $data['money'];
        $logModel->customer_name = $data['customer_name'];
        $logModel->contract_no = isset($data['contract_no']) ? $data['contract_no'] : '';
        $logModel->partnership = isset($data['partnership']) ? $data['partnership'] : '';
        $logModel->addtime = time();
        $logModel->id_no = isset($data['id_no']) ? $data['id_no'] : '';
        $logModel->bank_no = isset($data['bank_no']) ? $data['bank_no'] : '';
        $logModel->link_type = isset($data['link_type']) ? $data['link_type'] : '';
        $logModel->remark = $data['remark'];
        $logModel->fund_title = $data['fund_title'];
        $logModel->fund_rate = $data['fund_rate'];
        $logModel->term = $data['term'];
        $admin = session('admin');
        $logModel->changer_id = $admin['admin_id'];
       // $logModel->changer_id = $model->changer_id = $admin['admin_id'];//项目创始人
        if (intval($_POST['is_Remark']) == 1) {
            $model->accruaType = $logModel->accruaType = 4;
        } else {
            $logModel->interestDue = isset($data['interestDue']) ? $data['interestDue'] : '';

        }
        $model->interestDue = $logModel->interestDue = isset($data['interestDue']) ? $data['interestDue'] : '';
        $logModel->accruaType = isset($data['accruaType']) ? $data['accruaType'] : '';
        $time_to_rate = array_map("self::connet_time_rate", $_POST['totime'], $_POST['rateMoney']);
        $model->time_to_rate = $logModel->time_to_rate = json_encode($time_to_rate);
        if ($data['fund_id']) {
            $model->isaudit = $admin['role_id']==23?'1':'0'; //非主管改的都要审核
            $result = $model->save();
            if ($result) {
                $logModel->fund_id = $data['fund_id'];
                $logReturn = $logModel->add();
            }
        } else {
            $logModel->changer_id = $model->changer_id = $admin['admin_id'];//项目创始人
            $model->isaudit = 0;//未审核
            $result = $model->add();
            if ($result) {
                $logModel->fund_id = $result;
                $logReturn = $logModel->add();
            }
        }

        if ($result === false || $logReturn === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }

    }

    //利息详情
    public function rateInfo()
    {
        $fundId = I('get.fund_id');
        $model = D('FundOrder');
        $admin = session('admin');
        $roleId = $admin['role_id'];
        $list = $model->fundInfo($fundId);
        $fundLogInfo=$model->fundLogInfo($fundId);
        $imgList=unserialize($list['file_path']);
        $roleInfo=D('FundOrder')->roleInfo($roleId);
        $flowDes=unserialize($roleInfo['flow_des']);//审核的状态栏
        $showInfo=unserialize($roleInfo['role_des'])['rateInfo'];
        $this->assign($list);
        $this->assign(array('roleId'=>$roleId,'flowDes'=>$flowDes,'showInfo'=>$showInfo,'imgList'=>$imgList,'fundLogInfo'=>$fundLogInfo));
        $this->display('rateInfo');
    }

    //审核提交
    public function audit()
    {
        $fundId=I('post.fundId');
        $isaudit=I('post.isaudit');
        $uploadImg=I('post.reviews');
        $fundModel=D('FundOrder');
        $fundLogModel=D('FundOrderLog');
       // $fundModel->isaudit=$fundLogModel->isaudit=intval($isaudit)==1?'1':'0';
        $fundModel->isaudit=$fundLogModel->isaudit=intval($isaudit);
        $fundModel->file_path=$fundLogModel->file_path=serialize($uploadImg);
        $fundModelObj=$fundModel->where("`fund_id`=%d",array($fundId))->save();
        $fundLogModelObj=$fundLogModel->where("`fund_id`=%d",array($fundId))->save();
        if ($fundModelObj === false || $fundLogModelObj === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }
//修改详情
    public function modify()
    {
        $fund_id = I('get.fund_id');
        $branch_list = D('FundBranch')->select();
        $map['log_id']=$fund_id;
        $data = D('FundOrder')->getDetail($map,'gt_fund_order_log');
        $admin = session('admin');
        $roleId = $admin['role_id'];
        $roleInfo = D('FundOrder')->roleInfo($roleId);
        $this->assign($data);
        $this->assign(array('branch_list'=>$branch_list,'roleInfo' => unserialize($roleInfo['role_des'])['edit'], 'roleId' => $roleId));
        $this->display();
    }

    public function file()
    {
        $map['fund_id'] = I('get.fund_id');
        $file_tree = D('FundFile')->where($map)->select();
        $file_tree = array_reverse($file_tree);
//        var_dump($file_tree);exit;
        foreach ($file_tree as $v) {
            $array[$v['file_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $file_tree = $tree->get_array(0);
//        var_dump($file_tree[1]['sub'][7]['sub']);exit;

        $this->assign('file_tree', $file_tree);
        $this->assign($map);
        $this->display();
    }

    public function upload()
    {
        $map['fund_id'] = I('get.fund_id');
        $map['file_id'] = I('get.file_id');
        $list = D('FundAttachment')->where($map)->select();
        $exts = getFormerExts();

        $this->assign('exts', $exts);
        $this->assign('list', $list);

        $this->assign($map);
        $this->display();
    }

    public function contractInfoEdit()
    {
        $map['fund_id']=I('get.fund_id');
        if(intval(I('get.type'))===1)
        {
            $list=D('FundOrder')->where("`fund_id`=%d",array($map['fund_id']))->field('file_path,fund_id')->find();
            $this->assign('type',1);
        }
         else
        {
            $list=D('FundOrder')->where("`fund_id`=%d",array($map['fund_id']))->field('contract_file,fund_id')->find();
            $this->assign('type',0);
        }
        $this->assign($list);
        $this->display();

    }
    public function contractInfoDetail()
    {
        $fundId=I("get.fund_id");
        $field='';
        if(intval(I("get.type"))===1)
        {
            $field='file_path';
            $this->assign('type',1);
            $this->assign('title','打款附件详情');

        }
        else
        {
            $field='contract_file';
            $this->assign('type',0);
            $this->assign('title','合同附件详情');
        }
        $list=D('FundOrder')->where("`fund_id`=%d",$fundId)->field($field)->find();
        $this->assign($list);
        $this->display();
    }
    public function contractInfoSave()
    {
        $mode=D('FundOrder');
        $admin = session('admin');
        $roleId = $admin['role_id'];
        if(false==$date=$mode->create())
        {
            $e=$mode->getError();
            $this->json_error($e);
        }
        $mode->isaudit=$roleId==23?'1':'0';
        if($mode->save()!==false)
        {
            $this->json_success('保存成功');
        }
        else
        {
            $this->json_error('保存失败');
        }
        
    }
    //删除图片，因与之前附件存储的方式不一样，所以另起方法
    public function deleImg()
    {
        $file_path = I('request.file_path');
        $sort = I('request.sort');
        $fund_id = I('request.fund_id');
        $fundOrderModel=D('FundOrder');
        $fundOrderInfo=$fundOrderModel->fundInfo($fund_id);
        $imgInfo=unserialize($fundOrderInfo['file_path']);
        foreach ($imgInfo as $k=>$v)
        {
            if($k==$sort)
            {
                unset($imgInfo[$k]);
            }
        }
            $updateImgInfo=empty($imgInfo)?'':serialize($imgInfo);
            $map['file_path']=$updateImgInfo;
           $updateState=$fundOrderModel->fundInfo($fund_id,1,$map);
        //删除文件
        if (file_exists('.'.$file_path)) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
        if ($updateState && $res2) {
            $this->json_success('删除成功', '', '', '', array('divid' => 'layout-01'));
        } else {
            $this->json_error('删除失败');
        }

    }
    
    
    //删除附件
    public function remove_attachment()
    {
        $file_path = I('request.file_path');
        $aid = I('request.aid');
        $fund_id = I('request.fund_id');
        $file_id = I('request.file_id');
        $admin = session('admin');
        $role_id = $admin['role_id'];
        $model = M('FundAttachment');
        $map = array('id' => $aid, 'fund_id' => $fund_id);
        $model->startTrans();
        $attachment_info = $model->where($map)->find();
        if (empty($attachment_info)) {
            $model->rollback();
            $this->json_error('文件已删除');
        }
        $res1 = $model->where($map)->delete();
        //文件不在的话就只删除数据库
        if (file_exists($attachment_info['path'])) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
        //        $res2 = unlink('.'.$file_path);
        if ($res1 && $res2) {
            self::log('del', "删除附件：aid-$aid,fund_id-$fund_id");
            $model->commit();
            $this->json_success('删除成功', '', '', '', array('divid' => 'layout-01'));
        } else {
            $model->rollback();
            $this->json_error('删除失败');
        }
    }

    //上传附件
    public function upload_attachment()
    {
        $fund_id = I('request.fund_id');
        $file_id = I('request.file_id');
//        var_dump($_POST);exit;
        $admin = session('admin');
        $role_id = $admin['role_id'];

        session('fund_id', $fund_id);
        $field = 'fund-' . $fund_id;
        $short_name = M('FundFile')->where('file_id=' . $file_id)->getField('short_name');
        $sql = M()->getLastSql();
        $upload_info = upload_file('/fund/attachment/', $field, $short_name . '-');
        if (isset($upload_info['file_path'])) {
            $save_data['file_id'] = $file_id;
            $save_data['fund_id'] = $fund_id;
            $save_data['path'] = $upload_info['file_path'];
            $save_data['doc_name'] = $upload_info['name'];
            $save_data['addtime'] = time();
            $save_data['sha1'] = $upload_info['sha1'];
            $save_data['admin_id'] = $admin['admin_id'];
            if (!($aid = M('FundAttachment')->add($save_data))) {
                $this->json_error('上传失败');
            }
            $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", $save_data['addtime']), 'aid' => $aid);
            self::log('add', json_encode($content));
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败,' . $upload_info);
    }

    /**
     * 业绩统计报表
     */
    /*public function journaling()
    {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $branch_id = intval(I('post.branch_id'));
        $branch_ch_id = intval(I('post.branch_ch_id'));
        $real_name = I('post.real_name');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FundOrder');
        $map = array();
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');

        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
            }
        }

        if ($isSearch) {
            if (!empty($branch_id)) {
                $map['fm.branch_id'] = $branch_id;
            }

            if (!empty($branch_ch_id)) {
                $map['fm.branch_ch_id'] = $branch_ch_id;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
            if (!empty($real_name)) {
                $map['real_name '] = array('like', "%$real_name%");
            }
        }
        //强制数据必须是完整的
        $map = array_merge($map, D('FundOrder')->notFieldNull());
        $map['t.fmanager_id'] = array('neq', 0);
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $result = $model->getList($page, $pageSize, $map, $order);
        $branch_list = D('FundBranch')->where('pid=1')->select();
        $this->assign('branch_list', $branch_list);

        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }*/

    public function comJournaling($where = '', $type = '')
    {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $branch_id = intval(I('post.branch_id'));
        $branch_ch_id = intval(I('post.branch_ch_id'));
        $manager_name = I('post.manager_name');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FundOrder');
        $map = array();
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');

        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
            }
        }

        if ($isSearch) {
            if (!empty($branch_id)) {
                $map['fm.branch_id'] = $branch_id;
            }

            if (!empty($branch_ch_id)) {
                $map['fm.branch_ch_id'] = $branch_ch_id;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
            if (!empty($manager_name)) {
                $map['real_name '] = array('like', "%$manager_name%");
            }
        }
//        $map = array_merge($map, D('FundOrder')->notFieldNull());
        $result = $model->getDayList($page, $pageSize, $map, $order);
        $branch_list = D('FundBranch')->where('pid=1')->select();
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
    }

    public function daysJournaling()
    {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');

        $model = D('FundOrder');
        $map = array();
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');
        $begin_time = I('post.begin_time');
        $end_time = I('end_time');
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
            }
        }
        if ($isSearch) {
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
        }
//        $map1 = D('FundOrder')->notFieldNull();
        $map = array_merge($map, $map1);
        $result = $model->getDayList($page, $pageSize, $map, $order);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }

    /**
     * 显示每天的统计
     * @return [type] [description]
     */
    public function showDay()
    {
        if (empty(I('get.branch_id')) or empty(I('get.pay_time'))) {
            $this->json_error('参数错误');
        }
        $list = D('FundOrder')->showDayData();
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 天与月的统计数据的公用部分
     * @param  integer $type 1代表天，2代表星期，3代表月
     * @return [type]        [description]
     */
    public function conShowJournaling($type = 1)
    {

        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $branch_id = intval(I('post.branch_id'));
        $branch_ch_id = intval(I('post.branch_ch_id'));
        $real_name = I('post.real_name');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FundOrder');
        $map = array();
        $orderField = I('post.orderField');
        $orderDirection = I('post.orderDirection');
        $monthtime = I('post.monthtime');
        $this->assign('post', I('post.'));
        if (!empty($orderField)) {
            $order = $orderField . ' ' . $orderDirection;
            if ($orderField == 'pro_status') {
                $order = 'step_pid ' . $orderDirection;
                $order .= ',pro_step ' . $orderDirection;
            }
        }
        if ($isSearch) {
            if (!empty($monthtime)) {
                $map['FROM_UNIXTIME(t.pay_time,"%Y-%m")'] = date('Y-', time()) . $monthtime;
            }
            if (!empty($branch_id)) {
                $map['fm.branch_id'] = $branch_id;
            }

            if (!empty($branch_ch_id)) {
                $map['fm.branch_ch_id'] = $branch_ch_id;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['pay_time'][] = array('ELT', $end_time);
            }
            if (!empty($real_name)) {
                $map['real_name '] = array('like', "%$real_name%");
            }

        } else {
            if ($type == 2) {
                //显示本周
                $map['FROM_UNIXTIME(t.pay_time,"%Y-%m-%u")'] = date('Y-m-W', time());
                //给指定的日期
                $times['begin_time'] = date('Y-m-d', strtotime('-1 week Monday'));
                $times['end_time'] = date('Y-m-d', time());
                $this->assign('times', $times);
            } else if ($type == 3) {
                //显示本月
                $map['FROM_UNIXTIME(t.pay_time,"%Y-%m")'] = date('Y-m', time());
                $this->assign('month', date('m', time()));
            }
        }
        //保证数据必须全
        /*foreach ($tmap as $k=>$v){
        	$map[$k]=$v;
        }*/
//        $map = array_merge($map, D('FundOrder')->notFieldNull());
        $branch_list = D('FundBranch')->where('pid=1')->select();
        $this->assign('branch_list', $branch_list);
        if ($type == 2) {
            $result = $model->getWeekList($page, $pageSize, $map, $order);
        } else if ($type == 3) {
            $result = $model->getMonthList($page, $pageSize, $map, $order);
        } else {
            return false;
        }
        $bumen = array_column($result['list'], 'branch_id');
        asort($bumen);
        $column = array_count_values($bumen);
        foreach ($column as $ck => $cv) {
            foreach ($result['list'] as $lk => $lv) {
                if ($ck == $lv['branch_id']) $data[$ck][] = $lv;
            }
        }
        foreach ($data as $k => $v) {
            $febumen = array_column($v, 'branch_ch_id');
            asort($febumen);
            $fencolumn = array_count_values($febumen);
            foreach ($fencolumn as $fk => $fv) {
                foreach ($v as $kk => $vv) {
                    if ($fk == $vv['branch_ch_id']) $temp[$fk][] = $vv;
                }
            }
            unset($data[$k]);
            $data[$k] = $temp;
            $femoney = array_column($v, 'money');
            foreach (array_count_values($femoney) as $mk => $mv) {
                if (empty($data[$k]['summoney'])) {
                    $data[$k]['summoney'] = $mk * $mv;
                } else {
                    $data[$k]['summoney'] += $mk * $mv;
                }
            }
            $data[$k]['num'] = count($v);
            unset($temp);
        }
         
        $btable=M('fund_branch')->field('branch_id,branch_name')->select();
        $this->assign('btable',$btable);//部门编号和名字
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('list',$data);
    }

    /**
     * 显示每周的统计数据
     * @return [type] [description]
     */
    public function weekJournaling()
    {
        $this->conShowJournaling(2);

        $this->assign('m', 'week');

        $this->display();

    }

    /**
     * 显示每月的统计数据
     * @return [type] [description]
     */
    public function monthJournaling()
    {
        $this->conShowJournaling(3);
        $this->assign('m', 'month');
        $this->display('weekJournaling');

    }

    /**
     * 导出excel表格
     * @return [type] [description]
     */
    public function export($content, $file_name)
    {
        header('Content-type: text/html; charset=utf-8');
        header("Content-type:application/vnd.ms-excel;charset=UTF-8"); //application/vnd.ms-excel指定输出Excel格式
        header("Content-Disposition:filename=" . $file_name . ".xls"); //输出的表格名称
        echo $content;
    }
    /**
     * 每日进款数据统计
     */
    public function  countIncome(){
        $result=D('FundOrder')->getdayincome();
        $total=array_sum(array_column($result['list'],'allmoney'));
        $this->assign(array('list'=>$result['list'],'total'=>$total))->display();
    }
    /**
     * 到期兑付统计
     */
    public  function repayment(){
    	$pageSize = I('post.pageSize', $this->pageDefaultSize);
    	$page = I('post.pageCurrent', 1);
    	$begin_time = I('post.begin_time');
    	$end_time = I('post.end_time');
    	if(!empty($begin_time)){
    		$map['deadline'][]=array('EGT',strtotime($begin_time));
    	}
    	if(!empty($end_time)){
    		$map['deadline'][]=array('ELT',strtotime($end_time));
    	}
    	$map['isaudit']=1;
      	$result=D('FundOrder')->repayment($page,$pageSize,$map);
      	$list=$result['list'];
        foreach($list as $k=>$v){
            //统计人数个数
            if($v['sumname']){
                $list[$k]['sumnum']=count(explode(',',$v['sumname']));
            }
            //统计是否到期,当前时间大于到期时间则表示已付
            $list[$k]['repay']= $v['deadline']<time()?'已付':'未付';
        }
        $this->assign(array('total' => $result['total'],'post'=>I('post.'), 'pageCurrent' => $page, 'list' => $list));
        $this->display();
    }
    public function ajaxcompayment($map=''){
    	if(strcasecmp(I('m'),'showname')===0) {
    		$map["FROM_UNIXTIME(deadline, '%Y-%m-%d')"] =I('detime');
            $map['isaudit']=1;
    		$result=D('FundOrder')->getrepaybytime($map);
    	}else if(strcasecmp(I('m'),'showdetails')===0){
    		$map["FROM_UNIXTIME(deadline, '%Y-%m-%d')"] =I('detime');
    		$map['customer_name']=I('customer_name');
    		$result=D('FundOrder')->getrepaybyname($map);
    	}else if(strcasecmp(I('m'),'showname_count')===0){
    		$map["FROM_UNIXTIME(deadline, '%Y-%m-%d')"] =I('detime');
            $map['isaudit']=1;
    		$result=D('FundOrder')->getcountbytime($map);
    	}else if(strcasecmp(I('m'),'showdetails_count')===0){
    		$map["FROM_UNIXTIME(deadline, '%Y-%m-%d')"] =I('detime');
    		$map['customer_name']=I('customer_name');
    		$result=D('FundOrder')->getcountbyname($map);
    	}
    	foreach ($result as $k=>$v){
    		$result[$k]['repay']= $v['deadline']<time()?'已付':'未付';
    	}
    	$data['message']=$result;
    	$data['status']=1;
    	$this->ajaxReturn($data,'JSON');
    }
    /**
     * 付息数据统计
     */
    public function countPayment(){
    	$pageSize = I('post.pageSize', $this->pageDefaultSize);
    	$page = I('post.pageCurrent', 1);
    	$begin_time = I('post.begin_time');
    	$end_time = I('post.end_time');
    	if(!empty($begin_time)){
    		$map['deadline'][]=array('EGT',strtotime($begin_time));
    	}
    	if(!empty($end_time)){
    		$map['deadline'][]=array('ELT',strtotime($end_time));
    	}
        $map['isaudit']=1;
    	$result=D('FundOrder')->interestList($page,$pageSize,$map);
    	$list=$result['list'];
    	
    	foreach($list as $k=>$v){
    		//统计人数个数
    		if($v['sumname']){
    			$list[$k]['sumnum']=count(explode(',',$v['sumname']));
    		}
    		//统计是否到期,当前时间大于到期时间则表示已付
    		$list[$k]['repay']= $v['deadline']<time()?'已付':'未付';
    	}
    	$this->assign(array('total' => $result['total'],'post'=>I('post.'), 'pageCurrent' => $page, 'list' => $list));
    	$this->display();
    }
    /**
     * 导入数据
     */
    public  function implodeExcel(){
        $file = $_FILES['file'];
        if (!file_exists($file['tmp_name'])) {
            $this->json_error('file not found!');
        }
        $ext = pathinfo($file['tmp_name'], PATHINFO_EXTENSION);
        if(!file_exists('./Uploads/excel/')){
            mkdir('./Uploads/excel/','0777');
        }
        $filepath = './Uploads/excel/'.$file['name'];

        move_uploaded_file($file['tmp_name'], $filepath);
        Vendor("PHPExcel.PHPExcel");
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $excel_version = $ext == 'xls' ?  'Excel5' : 'Excel2007';
        $objReader = \PHPExcel_IOFactory::createReader($excel_version);
        try {
            $PHPReader = $objReader->load($filepath);
        } catch (Exception $e) {

        }
        if (!isset($PHPReader)) {
            $this->json_error('read error!');
        }
        //返回一个数组
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach ($allWorksheets as $objWorksheet) {
            //获取表格标题
            $sheetname = $objWorksheet->getTitle();
            //获取总条数
            $allRow = $objWorksheet->getHighestRow(); //how many rows
			//获取总列数
            $highestColumn = $objWorksheet->getHighestColumn(); //how many columns
            //获取没列的下标
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            //统计单元格
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
            	//在$cells中提取所有单元格标记
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            //因为第一行是标题，所以从第2行开始统计
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $row = array();
                for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {
                    $cell = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col . $currentRow;
                    $value = $objWorksheet->getCell($address)->getCalculatedValue();
                    if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                        $cellstyleformat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();
                        $formatcode = $cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value =  \PHPExcel_Shared_Date::ExcelToPHP($value);
                        } else {
                            $value = \PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                        }
                    }
                    if ($isMergeCell[$col . $currentRow] && $isMergeCell[$afCol . $currentRow] && !empty($value)) {
                        $temp = $value;
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$col . ($currentRow - 1)] && empty($value)) {
                        $value = $arr[$currentRow - 1][$currentColumn];
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$bfCol . $currentRow] && empty($value)) {
                        $value = $temp;
                    }
                    $row[$currentColumn] = $value;
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
        $content = $array[0]['Content'];

        $fundcontent=[];
        $timeTorete=array('time','rete');
        foreach ($content as $k => $v) {
                $pre=array_slice($v, 1,22);
                $back=array_slice($v,23);
                if($back){
                   for($i=0;$i<7;$i=$i+2){
                        if($back[$i]){
                        	$formattime=array_slice($back, $i,2);
                        	$formattime[0]=date('Y-m-d',$formattime[0]);
                        	$formattime[1]=number_format($formattime[1],4);
                            $tmp[]=array_combine($timeTorete,$formattime);
                            unset($formattime);
                        }
                   }
                   $pre[]=empty(json_encode($tmp))?'':json_encode($tmp);
                   unset($tmp);
                }
            $fundcontent[]=$pre;
            unset($pre);unset($back);
        }
        $keys=array('accruaType','partnership','pay_time','begin_interest_time','done_time','customer_name','fund_title','money','fund_rate','term','deadline','fmanager_name','fmanager_area','fmanager_branch','contract_no','id_no','bank_no','link_type','remark','performance_rate','manage_rate','interestDue','time_to_rate');
		foreach ($fundcontent as $k=>$v){
            //凡是导入的数据，模式都是采用备注模式，accruaType=4
			array_unshift($fundcontent[$k], 4);
            //去除已付栏
            unset($fundcontent[$k][21]);
			$fundcontent[$k]=array_combine($keys, $fundcontent[$k]);
		}
        $re=M('fundOrder')->addAll($fundcontent);
        if ($re) {
            $this->json_success('导入成功');
        }
        $this->json_error('导入失败');
    }
}
