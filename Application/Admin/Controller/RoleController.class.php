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
        //编号数字
        if(I('get.k')) $this->assign('k',I('get.k'));
        $list = D('Role')->listName($page,$pageSize,$where);
        $this->assign('list', $list['list']);
        $this->assign('total', $list['total']);
        $this->display();
    }

    /**
     * 文件夹中可以访问人员列表
     */
    public function listAllowName(){
        //显示人的类型，file:列举文件所能访问的人，folder:列举文件夹所能访问的人
        $type=empty(I('get.type'))?I('post.type'):I('get.type');
        //文件或者文件夹的id号，来源有两个，一个是从a标签中以get方式提交过来，还有一个以post方式从表单过来
        $comId=empty(I('post.comid'))?(empty(I('get.fileId'))?I('get.folderId'):I('get.fileId')):I('post.comid');
        //判断是可以访问的人员，1：可访问的人员，2：新增可访问的人员
        $personType=I('post.personType');
        $pageSize = I('post.pageSize', 30);
        $page = I('post.pageCurrent', 1);
        $where='';
        if($type=='file'){
            $allow=M('ProjectAttachment')->field('id,allow_adminid')->where(array('id'=>$comId))->find();
        }elseif($type=='folder'){
            $allow=M('ProjectFile')->field('file_id,allow_adminid,secret')->where(array('file_id'=>$comId))->find();
        }
        //找出可以访问此人
       if($allow['allow_adminid']||I('post.allow_adminid')){
           //运行可以访问的人的id号
           $allowerid=empty($allow['allow_adminid'])?I('post.allow_adminid'):$allow['allow_adminid'];
           if($personType){
                $where['a.admin_id']= array('not in',explode(',',$allowerid));
           }else{
               $where['a.admin_id']= array('in',explode(',',$allowerid));
           }
           if(I('post.real_name'))$where['a.real_name']=array('like','%'.I('post.real_name').'%');
           $list=D('Role')->listName($page,$pageSize,$where);
           $this->assign(array('list'=>$list['list'],'total'=>$list['total'],'allow_adminid'=>$allowerid));
       }
       $this->assign(array('comId'=>$comId,'type'=>$type,'personType'=>$personType));
        $this->display();
    }
    //删除已存在的用户id号
    public function delAdmin(){
        //需要删除的人物id号
        $adminId=I('get.adminId');
        //操作的类型，file：文件，folder:文件夹
        $type=I('get.type');
        //文件或者文件夹的id
        $fid=I('get.comId');
        $model=$where='';
        if($type=='file'){
            //指定文件夹可以访问的人员
            $model=M('ProjectAttachment');
            $where['id']=array('eq',$fid);
        }elseif($type=='folder'){
            //文件夹
            $model=M('ProjectFile');
            $where['file_id']=array('eq',$fid);
        }
        $persons=$model->field('allow_adminid')->where($where)->find();
        $tmp=explode(',',$persons['allow_adminid']);
        //查找出要删除的人的id在$tmp中的下标
        $index=array_search($adminId,$tmp);
        if($index)unset($tmp[$index]);
        //将删除指定人的id后的数组，用‘，’拼接为字符串
        $tmp=implode(',',$tmp);

        $result=$model->where($where)->save(array('allow_adminid'=>$tmp));
        if($result){
            $this->json_success('成功');
        }else{
            $this->json_error('失败');
        }
    }

    /**
     * 添加新用户
     */
    public function addAdmin(){
        $folderId=$fileId='';
        //获取类型
        $type=I('get.type');
        if($type=='file'){
            //获取文件的id号
            $fileId=I('get.comId');
            //获取文件所在的文件夹的id号
            $folderId=M('ProjectAttachment')->getFieldById($fileId,'file_id');
        }else{
            //获取文件夹的id号
            $folderId=I('get.comId');
        }
        //获取运行访问的人员id号
        $persons=I('get.adminId');
        //保存权限
        $result=$this->saveFileComAuth($folderId,$fileId,$persons);
        if($result){
            $this->json_success('保存成功');
        }else{
            $this->json_error('保存失败');
        }
        //退出
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
        $this->assign('pro_title',I('get.custom_pro_title'));
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
        //可访问的文件id集合
        $fileId=I('post.fileids');
        //需要操作的文件夹id
        $folderId=I('post.file_id');
        //允许人id
        $personId=I('post.person_adminId');
        //保存权限
        $result=$this->saveFileComAuth($folderId,$fileId,$personId);
        if($result){
            $this->json_success('保存成功');
        }else{
            $this->json_success('保存失败');
        }
    }

    /**
     * 保存文件或者文件夹权限
     * @param $folderId 文件夹id号
     * @param $fileid  文件id号
     * @param $persons 允许访问的人员集合
     */
    public function saveFileComAuth($folderId='',$fileId='',$personId=''){
        if(empty($folderId) || empty($personId)) return false;
        //获取pfileid的祖先级id集合
        $parentFolder=pidfile($folderId)?pidfile($folderId):array();
        array_push($parentFolder,$folderId);
        $folders=array_unique($parentFolder);
        if($fileId){
            //将权限写入到文件中
            $oldFiles=M('ProjectAttachment')->field('id,allow_adminid')->where(array('id'=>array('in',$fileId)))->select();
            //将需要添加的$personId在每条记录的allow_adminid中添加，并且去重
            $result=saveAll('ProjectAttachment','allow_adminid',$personId,$oldFiles,'id',array('id'=>array('in',$fileId)));
        }
        //将权限写入到文件夹中
        $oldFolders=M('ProjectFile')->field('file_id,allow_adminid')->where(array('file_id'=>array('in',$folders)))->select();
        $result=saveAll('ProjectFile','allow_adminid',$personId,$oldFolders,'file_id',array('file_id'=>array('in',$folders))) || $result;
        return $result;
    }
    public function saveFileDepart()
    {
        $contents=I('post.');
        $update= D('Role')->saveFileSecret($contents['file_id'],$contents['pro_id'],$contents['fileType']);
        if($update)
        {
            $this->success('添加成功');
        }else
        {
            $this->error('添加失败');
        }
    }

    /**
     * 子流程审核人员选择
     */
    public function checkSubLevel(){
        //获取子流程分类配置信息
        $listLevel= C('proLevelClass');
        //获取选中的子流程
        $subLevel=I('post.subLevel');
        if(empty($subLevel))
            goto checkSubLevelDisplay;
        $this->json_success('成功');
        checkSubLevelDisplay:
        $this->assign('listLevel',$listLevel);
        $this->display();
    }
    public function showSubLevel(){
        $subLevel=I('get.subLevel');
        $subLevel=C('proLevelClass')[$subLevel];
        $proLevel=C('proLevel');
        $list=M('SublevelCheck')->where(array('wf_id'=>array('in',implode(',',$subLevel))))->select();
        foreach($proLevel as $k => $v){
            //只获取选中的子流程所获取的信息
            if(!in_array($k,$subLevel)) unset($proLevel[$k]);
        }
        $this->assign('proLevel',$proLevel);
        $listCallBack=call_user_func_array(array('\Admin\Controller\RoleController','callBackJoin'),array($list));
        $this->assign('list',$listCallBack);
        $this->display();
    }

    public function saveSubLevel(){
        $content=I('post.');
        $result=[];
        $re=true;
        foreach($content as $k=>$v){
            if(strpos($k,'adminId')===false) continue;
            $result[]=array(
                'wf_id'=> end(explode('adminId',$k)),
                'admin_ids'=>$v
            );
        }
        //如果$result中的任意一个元素中的key存在，则说明此子流程已经添加过默认审核人了,此处取数组最后一个元素来作为检查对象
        if(M('SublevelCheck')->where("`wf_id`='%d'",array(end($result)['wf_id']))->find()){
            //更新
            foreach($result as $k=>$v){
                $update=M('SublevelCheck')->where("`wf_id` ='%s'",$v['wf_id'])->save($v);
                $re=$update===false?$update=false:$update=true && $re;
            }
            if($re){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }else{
            //新增
            if(M('SublevelCheck')->addAll($result)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }

    //将wf_id转成下标，并多拼接名字多一组数据
    public function callBackJoin($list)
    {
        $adminList=D('Admin')->getAll();//admin所有的人名和Id
        foreach ($list as $k=>$v)
        {
            $name='';
            $list[$v['wf_id']]=$v;
            $adminIdsExplode=explode(',',$v['admin_ids']);
            foreach ($adminList as $key=>$vv)
            {
                if(in_array($vv['admin_id'],$adminIdsExplode))
                {
                    $name.=$vv['real_name'].',';
                }
            }
            $list[$v['wf_id']]['real_name']=rtrim($name,',');
            unset($list[$k]);
        }
        return $list;
    }
}