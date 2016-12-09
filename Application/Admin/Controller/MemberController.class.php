<?php

namespace Admin\Controller;

class MemberController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    /* 会员单位列表 */
    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $company_name = I('post.company_name');
        $member_name = I('post.member_name');
        $member_mobile = I('post.member_mobile');
        
        $model = D('Member');
        $map = array();
        if ($isSearch) {
            if ($status !== '') {
                $map['status'] = $status;
            }
            if (!empty($company_name)) {
                $map['company_name'] = array('LIKE', $company_name);
            }
            if (!empty($member_name)) {
                $map['member_name'] = $member_name;
            }
            if (!empty($member_mobile)) {
                $map['member_mobile'] = $member_mobile;
            }
        }
        $total = $model->where($map)->count();
        $list = $model->where($map)->order('addtime desc')->page($page, $pageSize)->select();
//        var_dump($status);eixt;
        $this->assign(array('total' => $total, 'pageCurrent' => $page, 'list'=> $list));
        $this->assign('post', $_POST);
        $this->assign('member_level', C('symbiosis_levels'));
        $this->display();
    }

    /* 添加管理员 */

    public function add() {
        $this->assign('symbiosis_levels', C('symbiosis_levels'));
        $this->display();
    }

    /* 编辑管理员 */
    public function edit() {
        $model = D('Member');
        $mid = I('get.mid');
        $data = $model->where(array('mid' => $mid))->find();
        $this->assign($data);
        $this->assign('symbiosis_levels', C('symbiosis_levels'));
        $this->display();
    }
    
    /* 编辑管理员 */
    public function editPaswd() {
        $model = D('Member');
        $mid = I('get.mid');
        $data = $model->where(array('mid' => $mid))->find();
        $this->assign($data);
        $this->display('edit_paswd');
    }
    
    /* 保存管理员 */
    public function save_member() {
        $model = D('Member');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        if ($data['mid']) {
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

    /* 删除管理员 */

    public function del() {
        $mid = I('mid');
        $model = D('Member');
        $state = $model->delete($mid);
        if ($state !== false) {
            $this->json_success('删除成功', U('admin/index'));
        } else {
            $this->json_error('操作失败');
        }
    }
}
