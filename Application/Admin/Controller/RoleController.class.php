<?php

namespace Admin\Controller;

class RoleController extends CommonController {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Role');
        $admin = session('admin');
        if ($admin['is_supper'] == 0) {
            $where['pid'] = $admin['role_id'];
        }
        $total = $model->where($where)->count();
        $list = $model->order('add_time desc')->where($where)->page($page, $pageSize)->select();
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->display();
    }

    public function add() {
//        $menu_model = D('Menu');
//        $menu_list = $menu_model->where(array('type' => 1))->select();
        $admin = session('admin');
        if ($admin['is_supper'] == 0) {
            $model = D('Role');
            $menu = $model->get_auth_menu(0, -1);
        } else {
            $Menu= new \Admin\Model\MenuModel();
            $menu = $Menu->get_all_menu(0);
        }
        $model = D('Role');
        $role_list = $model->where(array('type' => 1))->select();
        foreach ($role_list as $v) {
            $role[$v['role_id']] = $v;
        }
        $str = "<option value='\$role_id' \$selected>\$spacer \$role_name</option>";
        $tree = new \Admin\Lib\Tree;
        $tree->init($role, 'role_id', 'pid');
        $select_role = $tree->get_tree(0, $str);
        
        $this->assign('menu', $menu);
        $this->assign('select_role', $select_role);
        $this->assign('back', 1);
        $this->display('add');
    }

    public function edit() {
        $role_id = I('role_id');
        $model = D('Role');
        $menu_model = D('Menu');
        if (!$role_id || !$model->check_exist(array('role_id' => $role_id))) {
            $this->error('参数错误！');
        }
        $data = $model->relation('auth')->where(array('role_id' => $role_id))->find();
//        var_dump($data);
        foreach ($data['auth'] as $v) {
            $select_menu_ids[] = $v['menu_id'];
        }

        $menu_list = $menu_model->where(array('type' => 1))->select();
        foreach ($menu_list as $v) {
            $array[$v['menu_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $menu = $tree->get_array(0);

        $role_list = $model->where(array('type' => 1))->select();
        foreach ($role_list as $v) {
            $v['selected'] = $v['role_id'] == $data['pid'] ? 'selected' : '';
            $role[$v['role_id']] = $v;
        }
        $str = "<option value='\$role_id' \$selected>\$spacer \$role_name</option>";
//        $tree = new \Admin\Lib\Tree;
        $tree->init($role, 'role_id', 'pid');
        $select_role = $tree->get_tree(0, $str, $role_id);

        $this->assign('menu', $menu);
        $this->assign('select_menu_ids', json_encode($select_menu_ids));
        $this->assign($data);
        $this->assign('select_role', $select_role);
        $this->assign('back', 1);
        $this->display();
    }

    /* 检测权限组是否存在 */

    public function check_role_exist() {
        $role_name = I('role_name');
        $role_id = I('role_id');
        $where['role_name'] = $role_name;
        if ($role_id) {
            $where['role_id'] = array('neq', I('role_id'));
        }
        $state = D('Role')->check_exist($where) ? false : true;
        if (IS_AJAX) {
            $this->ajaxReturn($state);
        } else {
            return $state;
        }
    }

    /* 删除权限组 */

    public function del_role() {
        $model = D('Role');
        $role_id = I('get.role_id');
        if (!$role_id)
            $this->json_error('参数错误'.$role_id);
        $status = $model->relation(true)->delete($role_id);
        if ($status !== false) {
            $this->json_success('删除成功', U('role/index'));
        } else {
            $this->json_error('操作失败');
        }
    }

    /* 保存权限组 */

    public function save_role() {
        $model = D('Role');
        $auth = I('auth');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->error($e);
        }

        if ($auth) {
            foreach ($auth as $v) {
                $auth_d[] = array('menu_id' => $v);
            }
            $model->auth = $auth_d;
        }
        if ($data['role_id']) {
            $result = $model->relation('auth')->save();
        } else {
            $result = $model->relation('auth')->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', U('role/index'));
        }
    }
    
    public function lookup() {
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $model = D('Role');
        $admin = session('admin');
        if ($admin['is_supper'] == 0) {
            $where['pid'] = $admin['role_id'];
        }
        $total = $model->where($where)->count();
        $list = $model->order('add_time desc')->where($where)->page($page, $pageSize)->select();
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->display('lookup');
    }
    
    //数据权限编辑窗口
    public function dataAuthEdit() {
        $role_id = I('get.role_id');
        $data_auth_list = D('DataAuth', 'Logic')->dataAuthList($role_id);
//        var_dump($data_auth_list);exit; 
        $this->assign('list', $data_auth_list);
        $this->assign('role_id', $role_id);
        $this->display('data_auth_edit');
    }
    
    //保存数据权限
    public function saveDataAuth() {
        $data_ids = I('post.ids');
        $role_id = I('post.role_id');
        
        $result = D('DataAuth', 'Logic')->save($role_id, $data_ids);
        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }
    //页面权限
    public  function showPage(){
        $this->display();
    }
    //显示所有的管理员姓名
    public  function listName(){
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $where='';
        //判断是否是英文输入的
        if(preg_match("/^[a-zA-Z]+$/",I('post.real_name'))){
            $where['a.admin_name']=array('like','%'.I('post.real_name').'%');
        }elseif(I('post.real_name')){
            //如果不是英文，则默认为汉字
            $where['a.real_name']=array('like','%'.I('post.real_name').'%');
        }
        //此函数可被系统设置中的消息推送权限设置中的添加调用，因其具备多选的需求，所以在这里添加这个参数判断，以前台显示不同的样式
        if(I('get.multi')) $this->assign('multi',I('get.multi'));
        if(I('get.k')) $this->assign('k',I('get.k'));
        $list = D('Role')->listName($page,$pageSize,$where);
        $this->assign('list', $list['list']);
        $this->assign('total', $list['total']);
        $this->display();
    }
    //页面权限可用使用的方法
    public function listPath(){
        $pageAuth=C('pageAuth');
        $pageAuthFun=C('pageAuthFun');
        foreach($pageAuth as $k=>$v){
            $pageAuth[$k]=$pageAuthFun[$v];
        }
        $this->assign('pageAuth',$pageAuth);
        $this->display();
    }
    //页面权限保存
    public function savePage(){
        extract(I('post.'));
        $authpage = M('Admin')->getFieldByAdminId($adminid,'authpage');
        $authpage=json_decode($authpage,true);
        if(strcmp($method,'saveAction')===0){ //保存控制器
            if(!empty($authpage[$actionname])){ //如果已经存在这样的控制器,只能修改其名字
//                $authpage[$actionname]['name']=$actionname;
                exit(json_encode(array('status'=>2,'msg'=>'该模块已经存在')));
            }else{ //空，则在这里追加，而不能清空
                $authpage[$actionname]=array();
            }
        }else if (strcmp($method,'saveMethod')===0){ //保存方法
            if(!empty($authpage[$actionname][$fix][$methodpath]))
                exit(json_encode(array('status'=>2,'msg'=>'此方法已存在')));
            $authpage[$actionname][$fix][$methodpath]=$methodname;
        }
        if(M('Admin')->where('admin_id = '.$adminid)->setField('authpage',json_encode($authpage))){//成功
            exit(json_encode(array('status'=>1)));
        }else{//失败
            exit(json_encode(array('status'=>2,'msg'=>'操作失败')));
        }
    }
    //显示权限具体信息
    public function showDetails(){
        $authpage=json_decode(M('Admin')->getFieldByRealName(I('post.realname'),'authpage'),true);
        $html=$this->assign('list',$authpage)->fetch();
        exit(json_encode(array('html'=>$html,status=>1)));
    }
    /**
     * 删除页面节点
     */
    public function del(){
        $data=I('post.');
        $authpage=M('Admin')->where('admin_id = '. $data['custom_adminId'])->getField('authpage');
        $authpage=json_decode($authpage,true);
        $action=$data['action'];
        foreach ($data as $k =>$v){
            if($data['delaction']){unset ($authpage[$action]); break;}
            if($i=strpos($k,'_method')){
               //只针对方法
               foreach($v as $vv){
                   //删除要删除的方法
                   unset($authpage[$action][substr($k,0,$i)][$vv]);
               }
               //此处补一刀，删除没有方法的操作板块
               if(empty($authpage[$action][substr($k,0,$i)]))unset($authpage[$action][substr($k,0,$i)]);
           }
        }
        if(M('Admin')->where('admin_id = '.I('post.custom_adminId'))->setField('authpage',json_encode($authpage))){//成功
            $this->json_success('删除成功，请刷新');
        }else{//失败
            $this->json_error('删除失败');
        }
    }
    /**
     * 文件夹权限
     */
    public function fileRole(){
        $map['pro_id'] = I('get.pro_id')?I('get.pro_id'):I('get.custom_pro_id');
        $file_tree = D('ProjectFile')->where($map)->select();
        $file_tree = array_reverse($file_tree);
        $admin=$_SESSION['admin'];
        $fileLevel=C('fileLevel');
        foreach ($file_tree as $k=> $v) {
            if($v['secret']>1){
                //如果文件的机密等级大于1，则代表此文件夹是机密的，需要与配置中的fileLevel进行比对，查看此人的角色是否在对应的role_id中，在则可以查，
                //不在，则需要进一步判断，此人的是否是特批查看此文件的用户，即：此文件夹中的 allow_adminid 是否包含了此人的id号
                if(strpos($fileLevel[$v['secret']]['role_id'],$admin['role_id'])===false && strpos($v['allow_adminid'],$admin['admin_id'])===false){
                    unset($file_tree[$k]);
                    continue;
                }
            }
            $array[$v['file_id']] = $v;
        }
        $tree = new \Admin\Lib\Tree;
        $tree->init($array);
        $file_tree = $tree->get_array(0);
        $this->assign('file_tree', $file_tree);
        $this->assign($map);
        $this->assign($_GET);
        if(I('get.actionname') || I('get.custom_pro_id')){
            $this->json_success('新建成功', '/Admin/Role/fileRole/pro_id/'.$map['pro_id'], '', true, array('tabid' => 'project-subwidows','tabName'=>'project-submit','tabTitle'=>'资料包'),1);
        }else{
            $this->display();
        }
    }
    //具有文件夹的项目列表
    public function listProject(){
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $where='';
        if(I('post.pro_title'))$where['p.pro_title']=array('like','%'.I('post.pro_title').'%');
        $list = D('Role')->listProject($page,$pageSize,$where);
        $this->assign('list',$list['list']);
        $this->assign('total', $list['total']);
        $this->display();
    }
    public function saveFileAuth(){
        $fields=I('post.fileids');
        $pfileid=M('ProjectAttachment')->getFieldById(end($fields),'file_id');
        //获取父文件夹集合
        $files=array_push(pidfile($pfileid),$pfileid);
        //将权限写入到文件夹中

        //将权限写入到文件中

        $this->json_success('保存成功');
    }
}