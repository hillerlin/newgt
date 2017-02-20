<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    
    public function __construct() {
        parent::__construct();
    }

    public function flushRedis()
    {
        S()->FLUSHALL();
    }
    public function redisComment()
    {
        $all=S()->keys('*');
        var_dump($all);
    }
    public function index() {
     /*   $xmlClass=logic('xml');
          $xmlClass->file='process1.xml';
          $xmlInfo = logic('xml')->index()[xmlIdToInfo('el_292541503583fc0a60758b7036469204')['TARGETREF']];//获取即将审核人的xml信息*/
        //$xmlInfo = logic('xml')->index();
        //el_292541503583fc0a60758b7036469204
       // $fileLevel=C('Pro.aaaa');
       //  $fileLevel=C('Pro.aaaa','999999','','./Application/Admin/Conf/process.php');  //封装了C方法的写入
       // $userInfo=D('Admin')->where('`admin_id`=2')->field('authpage')->find();
       // $userInfo=D('Admin')->where('`admin_id`=16')->field('authpage')->find();
        //$authpage=$userInfo['authpage'];
       // $unseriAuth=json_decode($authpage,true);
/*foreach ($unseriAuth as $ukey=>$uval)
{
    if(strpos($ukey,'分配') || strpos($ukey,'知情') || strpos($ukey,'上报'))
    {
        $unseriAuth[$ukey]['pre']['/Admin/Project/file']='资料包';
        continue;
    }
    else
    {
        $unseriAuth[$ukey]['pre']['/Admin/Project/editSubProcess']='审核';
        $unseriAuth[$ukey]['pre']['/Admin/Project/file']='资料包';
    }
}
         $newAuth=json_encode($unseriAuth);*/
       //  $map['authpage']=$authpage;
        // $update=D('Admin')->where('`role_id`=2')->data($map)->save();
         //$update=D('Admin')->where(1)->data($map)->save();

         $admin = session('admin');
         if ($admin['is_supper'] == 0) {
            $model = D('Role');
            $menu = $model->get_auth_menu(0);
        } else {
            $Menu= new \Admin\Model\MenuModel();
            $menu = $Menu->get_all_menu(0);
        }
        $unReadNums = D('Message')->unReadNums($admin['admin_id']);
        
        $this->assign('unReadNums', $unReadNums);
        $this->assign('auth_menu', $menu);
        $this->display('index_tree');
    }
    
    public function index_tree() {
        $model = D('Role');
        $admin = session('admin');
        if ($admin['is_supper'] == 0) {
            $menu = $model->get_auth_menu(0);
        } else {
            $admin_password = new \Admin\Model\MenuModel();
            $menu = $admin_password->get_all_menu(0);
        }
        $unReadNums = D('Message')->unReadNums($admin['admin_id']);
        
        $this->assign('unReadNums', $unReadNums);
        $this->assign('auth_menu', $menu);
        $this->display();
    }
    
    /* 登录 */
    public function login() {
        if (IS_POST) {
            $admin_name = I('admin_name', 'trim');
            $admin_password = I('admin_password', 'trim');
            $verify_code = I('j_captcha');
            $admin_model = new \Admin\Model\AdminModel();
//            $role_model = D('AdminRole');
            if (!check_verify($verify_code)) {
                $this->error('验证码错误,请重新输入');
            }
            $admin = $admin_model->where(array('admin_name' => $admin_name, 'admin_password' => md5($admin_password), 'status' => 1))->relation('role')->find();
            //$admin = $admin_model->where(array('admin_name' => $admin_name, 'admin_password' => md5($admin_password), 'status' => 1))->find();
            if (!$admin) {
                $this->error('账号密码错误！');
            } else {
                session('admin', array(
                    'admin_id' => $admin['admin_id'],
                    'admin_name' => $admin['admin_name'],
                    'role_id' => $admin['role_id'],
                    'real_name' => $admin['real_name'],
                    'is_supper' => $admin['is_supper'],
                    'dp_id'     => $admin['dp_id'],
                    'position_id'     => $admin['role']['position_id'],
                    'role_name'=>$admin['role']['role_name'],
                    'flow_des'=>$admin['role']['flow_des'],
                    'flag'=>I('flag'),
                ));

                $admin_model->after_login($admin['admin_id']);
                $this->success('登录成功！', U('index/index'));
            }
        } else {
            $admin = session('admin');
            if (!empty($admin)) {
                $this->redirect('Index/index');
            }
            $this->display();
        }
    }
    
    /* 退出 */
    public function logout() {
        session('admin', null);
        $this->success('退出成功！', U('index/login'));
        exit;
    }
    
    public function makecode(){
        $Verify = new \Think\Verify(array('fontSize'=>60,'length'=>4,'fontttf'=>'5.ttf', 'useAl' => true));
        $Verify->entry();
    }

    /**
     * 代办消息提醒
     * @param $admin  管理员信息，必须包括admin_id, role_id
     * @return 二维数组，从redis中取出的所有代办消息记录
     */
    public  function backlog($admin){
        $redisAdminKeys=S()->hKeys('admin:'.$admin['admin_id']);
        $redisRoleKeys=S()->hKeys('role:'.$admin['role_id']);
        $redisTotalKeys=array_merge($redisAdminKeys,$redisRoleKeys);
        $backlog=[];
        if(is_array($redisTotalKeys))
        {
            foreach ($redisTotalKeys as $k=>$v)
            {
                //根据类型admin或者role以及登录用户的admin_id来获取redis中详细的信息
                $getAdminRedis=  $redisAdminKeys?S()->hMGet('admin:'.$admin['admin_id'],array($v)):array();
                $getRoleRedis=  $redisRoleKeys?S()->hMGet('role:'.$admin['role_id'],array($v)):array();

                $getTotalRedis=$getAdminRedis+$getRoleRedis;//数组合并并保留索引

                krsort($getTotalRedis);
                array_push($backlog,json_decode($getTotalRedis[$v],true));
            }
        }
        return empty($backlog)?array():$backlog;
    }

    /**
     * 项目立项消息提醒
     * @param $admin  管理员信息，必须包括admin_id, role_id
     * @param $type   对应配置文件C('messAuth')中的四个值的depict,$type的值：
     *          1 ----项目管理流程，2----签约流程，3----放款流程，4----项目完结流程
     * @return 二维数组，从redis中取出的所有项目立项消息提醒记录，按时间从大到小进行排序
     */
    public function workFlowMessage($admin,$type){
        $workFlowMessage=[];
        foreach (C('messAuth') as $key=>$value)
        {
            if($key==$type)
            {
                $sType=S()->sMembers('sType:'.$key);//判断如果是项目流程则取出项目流程中的消息集合
                foreach ($sType as $tkey=>$tvalue)
                {
                    $wordFlowKeys=S()->hKeys('Type:'.$key.':Time:'.$tvalue);
                    rsort($wordFlowKeys);
                    foreach ($wordFlowKeys as $wordFlowIndex=>$wordFlowValue)
                    {
                        $tmp=json_decode(S()->hMGet('Type:'.$key.':Time:'.$tvalue,array($wordFlowValue))[$wordFlowValue],true);
                        if(!empty($tmp)){ //将消息类型也添加进去
                            $tmp['Type'] = $key;
                        }
                        //判断此人是否已经查看过此消息了，查看了in就设置为1
                        if(!empty($tmp['adminIds'])){
                            if(in_array($admin['admin_id'],explode(',',$tmp['adminIds']))){
                                $tmp['in']=1;
                            }
                        }
                        array_push($workFlowMessage,$tmp);
                        $tmp='';
                    }
                }
            }
        }
        if($workFlowMessage){
            $sordtime=array_column($workFlowMessage,'time');
            //按照时间从大到小进行排序
            array_multisort($sordtime,SORT_DESC,$workFlowMessage);
        }
        //返回的必须是数组类型，不能返回false ,因 count(false) ==1 而不是0
        return empty($workFlowMessage)?array():$workFlowMessage;
    }
    /**
     * 我的主页
     */
    public function index_layout() {
        $admin = session('admin');
        $map['admin_id'] = $admin['admin_id'];
        $announcement_list = D('Announcement')->getlist(1, 3, array('t.status' => 1));
        $research_list = D('ResearchReport')->getlist(1, 3, array('t.status' => 1));
        $this->assign('announcement_list', $announcement_list['list']);
        $this->assign('research_list', $research_list);

        //消息提醒-我的待办
        $backlog=$this->backlog($admin);
        //消息提醒-项目立项类消息
        $workFlowMessage=$this->workFlowMessage($admin,1);
        //消息提醒-签约流程消息
        $contractMessage=$this->workFlowMessage($admin,2);
        //消息提醒-放款流程消息
        $loantMessage=$this->workFlowMessage($admin,3);
        //项目立项消息提醒显示5条
        $this->assign('workFlowMessage',array_slice($workFlowMessage,0,5));
        $this->assign('loantMessage',array_slice($loantMessage,0,5));
        //代办显示10条
        $this->assign('backlog', array_slice($backlog,0,10));

        $this->assign('contractMessage',array_slice($contractMessage,0,5));

        $this->assign('backLogCount', count($backlog));
        $this->assign('admin', $admin);
        if(strcmp(I('post.m'),'ajaxMessage')===0){
            $html=$this->fetch('ajaxMessage');
            $this->success($html);
        }
        $this->display();
    }


    /**
     * 更多消息连接的详细内容，
     */
    public function ajaxMessageMore(){
        $admin= session('admin');
        //需要显示哪个版块的信息,值为，backlog--代办 , workFlowMessage--项目立项
        $type=empty(I('get.type'))?'':I('get.type');
        $messageType=empty(('get.messageType'))?'':I('get.messageType');
        $message=$this->$type($admin,$messageType);
        $this->assign('message',$message);
        $this->display();
    }
    protected function start() {
        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        $model = D('Project');
        if (!$is_supper) {
            if (!$is_boss) {
                if (!DepartmentLogic::isRCD()) {
                    $map1['t.admin_id'] = $admin['admin_id'];
                }
            } else {
                $map1['submit_status'] = 1;
            }
            $map1['w.dp_id'] = $admin['dp_id'];
        }
        $map1['t.step_pid'] = 1;
        $result = $model->waitAudit(1, 3, $map1);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'alist' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
    }
    
    //项目审核
    protected function risk() {
        $model = D('Project');
        $pro_title = I('post.pro_title');

        $admin = session('admin');
        $is_boss = isBoss();
        $is_supper = isSupper();
        if (!$is_supper) {
            if (!$is_boss) {
                $where['t.risk_admin_id'] = $admin['admin_id'];
                $where['t.admin_id'] = $admin['admin_id'];
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
            } else {
                $map['submit_status'] = 1;
            }
            $map['w.dp_id'] = $admin['dp_id'];
        }
        $map['w.step_pid'] = 2;
        $result = $model->waitAudit(1, 3, $map);
        $total = $result['total'];
        $list = $result['list'];
        $workflow = D('Workflow')->getWorkFlow();

        $this->assign('workflow', $workflow);
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'blist' => $list));
        $this->assign('is_boss', $is_boss);
        $this->assign('is_supper', $is_supper);
    }
}