<?php

namespace Admin\Controller;


class ContractController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }

    public function index() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $type = I('post.type');
        
        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('CapitalFlow');
        
        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }
            
            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['add_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['add_time'][] = array('ELT', $end_time);
            }   
        }
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $result = $model->getList($page, $pageSize ,$map);
        $type_describe = $model->getTypeDescribe();
        
        $this->assign('type_describe', $type_describe);
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
    public function add() {
        $type_describe = D('CapitalFlow')->getTypeDescribe();
        $banks = D('Bank')->select();
        $this->assign('banks', $banks);
        $this->assign('type_describe', $type_describe);
        $this->display();
    }
    
    public function save() {
        $model = D('CapitalFlow');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        if ($data['id']) {
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
    
    //新增项目合同信息
    public function projectAdd() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        if (IS_POST) {
            $pre_contract['term'] = I('post.term');
            $pre_contract['cash_deposit'] = I('post.cash_deposit');
            $pre_contract['repurchase_rate'] = I('post.repurchase_rate');
            $pre_contract['handling_charge'] = I('post.handling_charge');
            $pre_contract['counseling_fee'] = I('post.counseling_fee');
            $pre_contract['company_id'] = I('post.company_id');
            $pre_contract['real_money'] = I('post.real_money');
            $pre_contract['penalty_rate'] = I('post.penalty_rate');
            $pre_contract['demurrage_rate_type1'] = I('post.demurrage_rate_type1', 0);
            $pre_contract['demurrage_rate_type2'] = I('post.demurrage_rate_type2', 0);
            $pre_contract['demurrage_rate2'] = I('post.demurrage_rate2');
            $pre_contract['pro_id'] = $pro_id;
            
            if (empty($pre_contract['term']) || empty($pro_id)) {
                $this->json_error('参数错误');
            }
            $pre_contract['demurrage_rate_type'] = bindec($pre_contract['demurrage_rate_type1'] . $pre_contract['demurrage_rate_type2']);
            $pre_contracts = session($pro_id . '-pre_contract');
            $pre_contracts[$pre_contract['company_id']] = $pre_contract;
            session($pro_id . '-pre_contract', $pre_contracts);
            $this->json_success('保存成功', '', '', true);
        }
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $pre_contracts = session($pro_id . '-pre_contract');
        if (isset($pre_contracts[$company_id])) {
            $this->assign('pre_contract', $pre_contracts[$company_id]);
        }
        $this->assign('company_info', $company_info);
        $this->assign($pro_info);
        $this->assign('pro_id', $pro_id);
        $this->display('pre_contract');
    }
    
    //新增项目合同信息
    public function edit() {
        $pro_id = I('request.pro_id');
        $company_id = I('get.company_id');
        if (IS_POST) {
            $pre_contract['term'] = I('post.term');
            $pre_contract['cash_deposit'] = I('post.cash_deposit');
            $pre_contract['repurchase_rate'] = I('post.repurchase_rate');
            $pre_contract['handling_charge'] = I('post.handling_charge');
            $pre_contract['counseling_fee'] = I('post.counseling_fee');
            $pre_contract['company_id'] = I('post.company_id');
            $pre_contract['real_money'] = I('post.real_money');
            $pre_contract['penalty_rate'] = I('post.penalty_rate');
            $pre_contract['demurrage_rate_type1'] = I('post.demurrage_rate_type1', 0);
            $pre_contract['demurrage_rate_type2'] = I('post.demurrage_rate_type2', 0);
            $pre_contract['demurrage_rate2'] = I('post.demurrage_rate2');
            $pre_contract['pro_id'] = $pro_id;
            
            if (empty($pre_contract['term']) || empty($pro_id)) {
                $this->json_error('参数错误');
            }
            $pre_contract['demurrage_rate_type'] = bindec($pre_contract['demurrage_rate_type1'] . $pre_contract['demurrage_rate_type2']);
            $pre_contracts = session($pro_id . '-edit_contract');
            $pre_contracts[$pre_contract['company_id']] = $pre_contract;
            session($pro_id . '-edit_contract', $pre_contracts);
            $this->json_success('保存成功', '', '', true);
        }
        $map['pro_id'] = $pro_id;
        $pro_info = D('Project')->where($map)->find();
        $company_info = D('Company')->getSpecificCompany($pro_id, $company_id);
        $pre_contracts = session($pro_id . '-edit_contract');
        if (isset($pre_contracts[$company_id])) {
            $this->assign('pre_contract', $pre_contracts[$company_id]);
        } else {
            $contract_info = D('ProjectContract')->where(array('pro_id' => $pro_id, 'company_id' => $company_id))->find();
            $contract_info['demurrage_rate_type1'] = D('ProjectContract')->demurrageRateType($contract_info['demurrage_rate_type'], 1);
            $contract_info['demurrage_rate_type2'] = D('ProjectContract')->demurrageRateType($contract_info['demurrage_rate_type'], 2);
            $this->assign('pre_contract', $contract_info);
        }
//        var_dump($contract_info);exit;
        $this->assign('company_info', $company_info);
        $this->assign($pro_info);
        $this->assign('pro_id', $pro_id);
        $this->display();
    }
}
