<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller {
    protected $mid ;

    public function __construct() {
        $member = session('member');
        $this->mid = $member['mid'];
        parent::__construct();
    }
    
    public function index(){
        $this->display();
    }
    
    /* 编辑资料 */
    public function edit() {
        $model = D('Member');
        $data = $model->where(array('mid' => $this->mid))->find();
        $this->assign($data);
        $this->display();
    }
    
    /* 修改密码 */
    public function editPaswd() {
        $model = D('Member');
        $data = $model->where(array('mid' => $this->mid))->find();
        $this->assign($data);
        $this->display('edit_paswd');
    }
    
    /* 保存 */
    public function save_member() {
        $model = D('Member');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        if ($data['mid']) {
            $result = $model->save();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功');
        }
    }
}