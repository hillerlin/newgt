<?php
namespace Admin\Controller;

class IndexController extends CommonController {
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
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
    
    public function index_layout() {
        $admin = session('admin');
        $map['admin_id'] = $admin['admin_id'];
        $messages = D('Message')->getlist(1, 3, $map);
        //$backlog = D('Backlog')->getlist(1, 3, $map);
        $announcement_list = D('Announcement')->getlist(1, 3, array('t.status' => 1));
        $research_list = D('ResearchReport')->getlist(1, 3, array('t.status' => 1));
        $unReadNums = D('Message')->unReadNums($admin['admin_id']);

        $this->assign('unReadNums', $unReadNums);
        $this->assign('announcement_list', $announcement_list['list']);
        $this->assign('research_list', $research_list);
        $this->assign('messages', $messages);
        $backlog=array();
        $redisAdminKeys=S()->hKeys('admin:'.$admin['admin_id']);
        if(is_array($redisAdminKeys))
        {
            foreach ($redisAdminKeys as $k=>$v)
            {
                $getRedis=S()->hMGet('admin:'.$admin['admin_id'],array($v));
                krsort($getRedis);
                array_push($backlog,json_decode($getRedis[$v],true));
            }
        }
        $this->assign('backlog', $backlog);
        $this->assign('admin', $admin);
//        $this->start();
//        $this->risk();
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