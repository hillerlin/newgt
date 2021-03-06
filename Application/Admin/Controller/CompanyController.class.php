<?php

namespace Admin\Controller;
use Admin\Model\BaseModel;

class CompanyController extends CommonController {

    public function __construct() {
        $this->mainModel = D('Company');
        parent::__construct();
    }

    /* 客户列表 */
    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        $this->mainModel = D('Company');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', '%'.$company_name.'%');
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', '%'.$company_linker.'%');
            }
        }
        $map['status'] = 1;
        $map['type'] = 0;
        $admin = session('admin');
        if ($admin['role_id'] == 16) {
            $map['admin_id'] = $admin['admin_id'];
        }
        $total = $this->mainModel->where($map)->relation('admin')->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->relation('admin')->page($page, $pageSize)->select();
        
        $industries = C('Ind.industries');
        $this->assign('industries', $industries);
        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }

    /* 添加管理员 */
    public function add() {
        $industries = C('Ind.industries');
        $type = I('get.type', 0);
        $this->assign('type', $type);
        $this->assign('industries', $industries);
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $company_id = I('get.company_id');
        $data = $this->mainModel->where(array('company_id' => $company_id))->find();
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign($data);
        $this->display();
    }
    
    /* 保存 */
    public function save() {
        if (false === $data = $this->mainModel->create()) {
            $e = $this->mainModel->getError();
            $this->json_error($e);
        }
//        var_dump($model->role_id);exit;
        if ($data['company_id']) {
            $this->mainModel->addtime = $_SERVER['REQUEST_TIME'];
            $result = $this->mainModel->save();
        } else {
            if (empty($this->mainModel->admin_id)) {
            $admin = session('admin');
            $this->mainModel->admin_id = $admin['admin_id'];
        }
            $result = $this->mainModel->add();
        }
        if ($data['type']) {
            $refresh_type = 'dialogid';
            $refresh_id = 'supplier-add';
        } else {
            $refresh_type = 'tabid';
            $refresh_id = 'company-index';
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', true, array($refresh_type => $refresh_id));
        }
    }

    /* 删除管理员 */

    public function del() {
        $company_id = I('get.company_id');
        $state = $this->mainModel->where('company_id=' . $company_id)->save(array('status' => 0));
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    //公司查找
    public function lookup() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', "%$company_name%");
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $admin = session('admin');
        if ($admin['role_id'] == 16) {
            $map['admin_id'] = $admin['admin_id'];
        }
        $map['status'] = 1;
        $map['type'] = 0;   //0客户，1供应链
        $total = $this->mainModel->where($map)->relation('admin')->count();
        $list = $this->mainModel->where($map)->relation('admin')->order('addtime desc')->page($page, $pageSize)->select();

        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }
    
    //供应商查找
    public function lookupSupplier() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $map['status'] = 1;
        $map['type'] = 1;   //0客户，1供应链
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();

        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display('lookup_supplier');
    }
    
    /* 供应商 */
    public function supplier() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $company_name = I('post.company_name');
        $company_linker = I('post.company_linker');
        $isSearch = I('post.isSearch');
        $this->mainModel = D('Company');
        if (!empty($isSearch)) {
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
            if (!empty($company_linker)) {
                $map['company_linker'] = array('LIKE', $company_linker);
            }
        }
        $map['status'] = 1;
        $map['type'] = 1;
        $total = $this->mainModel->where($map)->count();
        $list = $this->mainModel->where($map)->order('addtime desc')->page($page, $pageSize)->select();
        
        $industries = C('industries');
        $this->assign('industries', $industries);
        $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
        $this->display();
    }
    
    //查找指定项目的供应商
    public function lookupByProId() {
        $pro_id = I('get.pro_id');
        
        if (empty($pro_id)) {
            $this->json_error('非法操作');
        }
        if (D('Project')->isReverseFactoring($pro_id)) {
            $list = $this->mainModel->getProSupplier($pro_id);
        } else {
            $list = $this->mainModel->getProCompany($pro_id);
        }
        $total = count($list);
        $this->assign(array('total'=>$total, 'pageCurrent'=>1, 'list'=>$list));
        $this->display('lookup_prosupplier');
    }
    
    public function distribute() {
        $company_id = I('get.company_id');
        $company_info = D('Company')->where('company_id=' . $company_id)->relation('admin')->find();
        $this->assign($company_info);
        $this->display();
    }
    
    public function findProjectManager() {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        if (!empty($isSearch)) {
            if (!empty($isSearch)) {
                $map['real_name'] = $real_name;
            }
        }
        $map['t.status'] = 1;
//        $map['dp_id'] = array(array('eq',2),array('eq',4), 'or');
        $map['r.role_id'] = 16;
        $model = D('Admin');
//        $list = $model->where($map)->select();
//        $total = $model->where($map)->count();
        $result = $model->getLists(1, 30, $map);
        $this->assign('total', $result['total']);
        $this->assign('list', $result['list']);
        $this->assign('post', $post);
        $this->display('lookup_pm');
    }

    //查找符合子流程的项目
    public function findRelateSubProcess()
    {
        $pre=I('get.pre')?I('get.pre'):I('post.pre');
        $proTitle=I('post.company_name');
        $proJectModel=D('Project');
        $map='';
        if (I('post.begin_time')) {
            $beginTime=strtotime(I('post.begin_time'));
            $end_time=strtotime(I('post.end_time'));
            //$map['p.addtime'][] = array('EGT', strtotime(I('post.begin_time')));
            //$map['p.addtime'][] = array('ELT', strtotime(I('post.end_time')));
            $map.=" and `addtime` >=$beginTime and `addtime` <=$end_time";
        }
        if($proTitle)
        {
            $map.=" and `pro_title` like '%".$proTitle."%'";
        }
        $pre=='18'?$map.=" and  `binding_oa`=1 ":$map.=' and `binding_oa` is null';
        $projectIng=$proJectModel->field('*,SUBSTR(`binding_oa`,1,1) as `binding_oa`')->where("`is_all_finish`=0 $map")->select();
        //同业部经理申请给资方看资料包，条件是风控会之后，风控会最后的流程是8_4
/*        if($pre=='25')
        {
            $selectProjectIds= $proJectModel->returnPjInfoFromPjWorkflow('8_4');
            foreach ($projectIng as $pk=>$pv)
            {
                if(!in_array($pv['pro_id'],$selectProjectIds))
                {
                    unset($projectIng[$pk]);
                }
            }
        }*/
        $this->assign('list',$projectIng);
        $this->assign('type',$pre);
        $this->display();
    }
    // 根据不同类型返回不同的相关部门人员
    public function findHeaderToType()
    {
        $roleList=I('post.roleList');
        //默认选择的是“全部” ，如果选择了其它选项如项目经理类，则把全部去掉，即$roleList['0']去掉
        if(count($roleList)>=2 && strpos($roleList[0],',')!==false) array_shift($roleList);
        $type=I('get.pre');
        switch ($type)
        {
            case 4:
                //返回股权部和分控部老大的adminId
                $map['r.role_id']=array('in',array('16','17'));
                break;
            case 5:
                //返回风控部所有人的信息
                $map['r.role_id']=array('in',array('16','17','18'));
                break;
            case 6:
                //返回风控部所有人的信息
                $map['r.role_id']=array('in',array('16','18','17','21'));
                break;
            case 7:
                //返回风控部所有人的信息
                $map['r.role_id']='18';
                break;
            case 8:
            case 9:
                $map['r.role_id']=array('in',array('16','18','17','21'));
                break;
            case 10:
                //项管部总监
                $map['r.role_id']='17';
                break;
            case '11':
                //返回股权部和分控部老大的adminId
                $map['r.role_id']='16';
                break;
            case '11_2':
                //返回法务adminId
                $map['r.role_id']='21';
                break;
            case '12' :
                $map['r.role_id']='21';
                break;
            case '13' :
                $map['r.role_id']=array('in',array('21'));
                break;
            case '13_2' :
                $map['r.role_id']=array('in',array('2','14'));
                break;
            case '15' ://法务所有人
                $map['r.role_id']='21';
                break;
            case '15_2' ://风控专员
                $map['r.role_id']='18';
                break;
            case '15_3' ://风控专员
                $map['r.role_id']='18';
                break;
            case '17' ://法务所有人
                $map['r.role_id']='21';
                break;
            case '17_2' ://风控专员
                $map['r.role_id']='18';
                break;
            case '17_3' ://风控专员
                $map['r.role_id']='18';
                break;
        }
        if(!empty($roleList)){
            //如何角色列表不为空，则覆盖默认的switch定义的角色列表
            $map['r.role_id']=array('in',$roleList);
        }
        // 找出所需的角色id对应的列表
        $roles=M('Role as r')->field('role_id,role_name')->where($map)->select();

        //按名字查找，则采用模糊查询
        if(!empty(I('post.real_name'))){
            $map['a.reaL_name']=array('like','%'.I('post.real_name').'%');
        }
        //指定角色所对应的所有人的列表
        $adminList=D()->table('gt_role as r')
            ->join("LEFT JOIN __ADMIN__ AS a ON a.role_id=r.role_id")
            ->where($map)
            ->select();
        //最初的角色数据传递到前台首页
        if(I('post.hroleIds') || I('post.hroleVals')){
            //前台已存在知情人的角色类型信息，则直接使用已存在的角色信息，来作为可供选择的角色列表
            $hroleIds=I('post.hroleIds');
            $hroleVals=I('post.hroleVals');
            $roles=array_map(function($role_id,$role_name){
                //将前台传递过来的role_id字符串和role_name字符串组合起来，并且其下标对应着role_id和role_name
                return array('role_id'=>$role_id,'role_name'=>$role_name);
            },explode(',',$hroleIds),explode(',',$hroleVals));
        }else{
            $hroleIds=implode(',',array_column($roles,'role_id'));
            $hroleVals=implode(',',array_column($roles,'role_name'));
        }

        $this->assign(array('roles'=>$roles,'hroleIds'=>$hroleIds,'hroleVals'=>$hroleVals,'adminList'=>$adminList));
        $this->display();
    }
}
