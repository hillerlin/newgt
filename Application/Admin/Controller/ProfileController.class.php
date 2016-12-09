<?php
namespace Admin\Controller;

class ProfileController extends CommonController {
    
    public function __construct() {
        parent::__construct();
    }

    public function editMyPaswd() {
        $admin = session('admin');
        $model = D('Admin');
        $admin_id = $admin['admin_id'];
        $data = $model->where(array('admin_id' => $admin_id))->find();
        $this->assign($data);
        $this->display('edit_paswd');
    }
    
    public function save_profile() {
        $model = D('Admin');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        if ($data['admin_id']) {
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
}