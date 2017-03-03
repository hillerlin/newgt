<?php
namespace Api\Controller;
use Think\Controller;
use Api\Model;


class IndexController extends Controller{
     public function __construct()
     {
         parent::__construct();
     }
    public function login()
    {
      //  $uid=I('post.uid');
        $userName=I('post.userName','admin');
        $passWord=I('post.passWord','7fef6171469e80d32c0559f88b377245');
        $list=D('Admin')->getUserInfoByNameAndPassWord("`admin_name`='$userName' and `admin_password`='$passWord'");
        if($list)
        {
            $data['uid']=$list['admin_id'];
            $data['json_code']=200;
            $data['Token']=setToken($list['admin_id']);
            $this->ajaxReturn($data);
        }else
        {
            $data['json_code']=-100;
            $this->ajaxReturn($data);
        }





       // $getTokenUid = S('TOKEN_' . $uid);
    /*    checkToken($_REQUEST['token']); //检查token
        if ((String)$getTokenUid !== (String)$_REQUEST['token']) {
            $json['retCode'] = 1000;
            $json['retMessage'] = "账号已在其他地方登录";
            exit(json_encode($json));
        }*/
        
    }

}