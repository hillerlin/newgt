<?php

namespace Admin\Controller;

class FundManagerController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    /* 管理员列表 */

    public function index()
    {
       $model = D('FundBranch');
                $list = $model->select();
                foreach ($list as $v) {
                    $array[$v['branch_id']] = $v;
                }
                $tree = new \Admin\Lib\Tree;
                $tree->init($array);
                $list = $tree->get_array(0);
                $this->assign('list', $list);
                $this->display();
/*        if(IS_POST)
        {
            $pageSize = I('post.pageSize', 30);
            $page = I('post.pageCurrent', 1);
        }
        $model = D('Department');
        $depart = D('FundManager');
        $adminMode=D('Admin');
        $listInfo = $model->select();
        $defaultId=$adminMode->recuId(11,$listInfo);//查出资金部的所有分部的id
        $funderInfo=$adminMode->finderInfoById($defaultId,$page,$pageSize);
        $list = $depart->funderManager(11, $listInfo);//11代表是资金部的主id
        $this->assign(array('list'=>$list,'defaultList'=>$funderInfo['list'],'total'=>$funderInfo['total']));
        $this->display();*/


    }

    public function listByDepartment()
    {
       $branch_id = I('get.branch_id');
                $branch_type = I('get.branch_type');
                $pageSize = I('post.pageSize', 10);
                $page = I('post.pageCurrent', 1);
                $isJob=(I('post.isJob')!=='')?I('post.isJob'):1;
                $model = D('FundManager');
                $map="(t.$branch_type=$branch_id or fb.pid=$branch_id)";//基础筛选数据
               if($isJob==1)//在职过滤
               {
                   if (!empty($branch_id)) {
                       $map.=' and t.status=1';
                   }
               }else //离职
               {
                   if (!empty($branch_id)) {
                       $map.=' and t.status=0';
                   }
               }

                $result = $model->getLists($page, $pageSize, $map);
                $list = $result['list'];
                $total = $result['total'];
                foreach ($list as & $val) {
                    $val['last_login_time'] = date('Y-m-d H:s:i', $val['last_login_time']);
                }
                $this->assign(array('total'=>$total, 'pageCurrent'=>$page, 'list'=>$list));
                $this->assign('branch_id', $branch_id);
                $this->assign('branch_type', $branch_type);
                $this->assign('isJob', $isJob);
                $this->display('list_by_department');
/*        $dept_id = I('get.dept_id');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Admin');
        if (!empty($dept_id)) {
            $map['t.dp_id'] = $dept_id;
        }
        else
        {
            return false;
        }
        $result = $model->getLists($page, $pageSize, $map, 't.add_time ASC', $dept_id);
        $this->assign(array('total'=>$result['total'],'pageCurrent'=>$page,'list'=>$result['list']));
        $this->display('list_by_department');*/


    }

    /* 添加管理员 */

    public function add()
    {
        $branch_list = D('FundBranch')->where('pid=1')->select();

        $this->assign('branch_list', $branch_list);
        $this->display();
    }

    /* 编辑管理员 */
    public function edit()
    {
        $fmanager_id = I('get.fmanager_id');
        $model = D('FundManager');
        $data = $model->getFundManager($fmanager_id);
        $branch_list = D('FundBranch')->where('pid=1')->select();
        $branch_ch_list = D('FundBranch')->where('pid=' . $data['branch_id'])->select();

        $this->assign('branch_ch_list', $branch_ch_list);
        $this->assign('branch_list', $branch_list);
        $this->assign($data);
        $this->display();
    }

    public function childBranch()
    {
        $branch_id = I('get.branch_id');
        $list = D('FundBranch')->where('pid=' . $branch_id)->select();
        $data[] = array(
            'label' => '全部',
            'value' => 0
        );
        foreach ($list as $v) {
            $tmp['label'] = $v['branch_name'];
            $tmp['value'] = $v['branch_id'];
            $data[] = $tmp;
        }
        $this->ajaxRe($data);
    }

    /* 编辑管理员 */
    public function editPaswd()
    {
        $model = D('Admin');
        $admin_id = I('get.admin_id');
        $data = $model->where(array('admin_id' => $admin_id))->find();
        $this->assign($data);
        $this->display('edit_paswd');
    }

    /* 保存管理员 */
    public function save()
    {
        $model = D('FundManager');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }

        $model->join_time = strtotime($data['join_time']);
        if ($data['fmanager_id']) {
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

    /* 删除 */
    public function del()
    {
        $fmanager_id = I('get.fmanager_id');
        $model = D('FundManager');
        $state = $model->delete($fmanager_id);
        if ($state !== false) {
            $this->json_success('删除成功', '', '', '', array('divid' => 'layout-fund'));
        } else {
            $this->json_error('操作失败');
        }
    }

    public function lookup()
    {
        $real_name = I('post.real_name');
        $isSearch = I('post.isSearch');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        if (!empty($isSearch)) {
            $map['real_name'] = $real_name;
        }
//        var_dump($isSearch);
        $map['t.status'] = 1;
        $model = D('FundManager');
        $order = 'branch_id';
        $result = $model->getLists($page, $pageSize, $map, $order);
//        $total = $model->where($map)->count();
//        $list = $model->where($map)->select();
        $this->assign('list', $result['list']);
        $this->assign('total', $result['total']);
        $this->display('lookup');
    }

}
