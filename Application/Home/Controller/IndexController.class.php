<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
//        $this->ajaxReturn($data);
        $this->display('index_tree');
    }
    
    public function login() {
        if (IS_POST) {
            $member_name = I('member_name', 'trim');
            $member_password = I('member_password', 'trim');
            $verify_code = I('j_captcha');
            $member_model = new \Admin\Model\MemberModel();
            if (!check_verify($verify_code)) {
                $this->error('验证码错误,请重新输入');
            }
            $member = $member_model->where(array('member_name' => $member_name, 'admin_password' => md5($member_password)))->find();
            if (!$member) {
                $this->error('账号密码错误！');
            } else {
                session('member', array(
                    'mid' => $member['mid'],
                    'member_name' => $member['member_name'],
                    'member_rate' => $member['rate'],
                    'member_level' => $member['member_level'],
//                    'role_name' => $admin['role']['role_name'],
//                    'is_supper' => $member['is_supper'],
                ));

//                $member_model->after_login($member['mid']);
                $this->success('登录成功！', U('index/index'));
            }
        } else {
            $member = session('member');
            if (!empty($member)) {
                $this->redirect('Index/index');
            }
            $this->display();
        }
    }
    
    public function makecode(){
        $Verify = new \Think\Verify(array('fontSize'=>60,'length'=>4,'fontttf'=>'5.ttf'));
        $Verify->entry();
    }
    
    /* 退出 */
    public function logout() {
        session('member', null);
        $this->success('退出成功！', U('index/login'));
        exit;
    }
    
    public function index_layout() {
        $this->display();
    }
}