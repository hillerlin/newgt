<?php

namespace Admin\Controller;

class FinanceFlowController extends CommonController {

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
        $model = D('FinanceFlow');
        $counterparty=I('post.counterparty');


        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.pay_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        if(!empty($counterparty))
        {
            $map['t.counterparty']=array('like','%'.$counterparty.'%');
        }
        $result = $model->getList($page, $pageSize, $map);
        $type_describe = $model->getTypeDescribe();
        
        $this->assign('type_describe', $type_describe);
        $inMoney=0;
        $outMoney=0;
        foreach ($result['list'] as $kk=>$vv)
        {
               $inMoney+=$vv['in_money'];
               $outMoney+=$vv['out_money'];
        }
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list'],'inMoney'=>$inMoney,'outMoney'=>$outMoney));
        $this->assign('post', $_POST);
        $this->display();
    }

    public function add() {
        $banks = D('Bank')->select();
        $exts = getFormerExts();
        $type_dsc = D('FinanceFlow')->getTypeDescribe();
        
        $this->assign('type_dsc', $type_dsc);
        $this->assign('exts', $exts);
        $this->assign('banks', $banks);
        $this->assign($_GET);
        $this->assign('pro_id', I('get.pro_id'));
        $this->display();
    }

    public function save() {
        $model = D('FinanceFlow');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        $type=I('post.showType');
        $voucher = I('post.voucher');
        $model->pay_time = strtotime($data['pay_time']);
        $admin = session('admin');
        $model->startTrans();   //开启事务
        $model->admin_id = $admin['admin_id'];
        if ($data['fid']) {
            $result = $model->save();
        } else {
            $result = $model->add();
           // D('Message')->fininaceFlow($data['counterparty'], $data['money'], $data['type']);
        }
        
        if ($result === false) {
            $model->rollback();
            $this->json_error('保存失败1');
        }
        
        if (!empty($voucher)) {
            if ($data['fid']) {
                $fid = $data['fid'];
            } else {
                $fid = $result;
            }
            if ($model->addVoucher($fid, $voucher, $admin['admin_id']) === false) {
                $model->rollback();
                $this->json_error('保存失败2');
            }
        }
        
        $model->commit();
        if($type=='front')
        {
            $this->json_success('保存成功', '', '', true, array('dialogid' => 'project-checkcontract'));
        }
        else
        {
            $this->json_success('保存成功', '', '', true, array('dialogid' => 'project-submit'));
        }
    }
    
    //编辑后
    public function editSave() {
        $pay_time = I('post.pay_time');
        $bank_id = I('post.bank_id');
        $fid = I('post.fid');
        $voucher = I('post.voucher');
        $remark = I('post.remark');
        
        $pay_time = strtotime($pay_time);
        $admin = session('admin');
        $model = D('FinanceFlow');
        $model->startTrans();   //开启事务
        $model->admin_id = $admin['admin_id'];
        if ($fid) {
            $finance_flow_info = $model->findByPk($fid);
            if ($finance_flow_info['has_distribute'] > 0 && $finance_flow_info['pay_time'] != $pay_time) {
                $model->rollback();
                $this->json_error('已经被关联的流水，无法修改打款时间');
            }
            $result = $model->where('fid=' . $fid)->save(array('pay_time' => $pay_time, 'bank_id' => $bank_id, 'remark' => $remark));
        }
        
        if ($result === false) {
            $model->rollback();
            $this->json_error('保存失败1');
        }
        
        if (!empty($voucher)) {
            if ($data['fid']) {
                $fid = $data['fid'];
            } else {
                $fid = $result;
            }
            if ($model->addVoucher($fid, $voucher, $admin['admin_id']) === false) {
                $model->rollback();
                $this->json_error('保存失败2');
            }
        }
        $model->commit();
        $this->json_success('保存成功', '', '', true, array('dialogid' => 'project-submit'));
    }
    
    //上传审核资料
    public function upload() {
        $field = date('Y-m-d');
        $upload_info = upload_file('/finance/', $field);
        $content = array('file_path' => $upload_info['file_path'], 'file_id' => date('YmdHis'), 'file_name' => $upload_info['name'], 'addtime' => date("Y-m-d H:i:s", time()));
        if (isset($upload_info['file_path'])) {
            $this->ajaxReturn(array('statusCode' => 200, 'content' => $content, 'message' => '上传成功'));
        }
        $this->json_error('上传失败,' . $upload_info);
    }
    
    public function uploadBase64() {
        $field = date('Y-m-d');
//        $upload_info = upload_file('/finance/', $field);
        $file = $_POST['file'];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $file, $result)) {//base64上传
            $data = base64_decode(str_replace($result[1], '', $file));
            $save_path = './Uploads/finance/' . $field ;
            $file_path = $save_path . '/' . uniqid() . '.' . $result[2];
            $upload = new \Think\Upload\Driver\Local();
            if ($upload->checkSavePath($save_path) === false) {
                $this->json_error('上传出错');
            }
//            var_dump(file_put_contents($dataname, $data));
            if (file_put_contents($file_path, $data)) {
                $this->ajaxReturn(substr($file_path, 1)); //返回数据结构自行封装
            }else{
                 $this->json_error('上传出错');
            }
        }
    }
    
    //删除附件
    public function remove() {
        $file_path = I('request.file_path');
        //文件不在的话就只删除数据库
        if (file_exists($file_path)) {
            $res2 = unlink('.' . $file_path);
        } else {
            $res2 = true;
        }
//        $res2 = unlink('.'.$file_path);
        if ($res2) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('删除失败');
        }
    }
    
    public function edit() {
        $fid = I('get.fid');
        if (empty($fid)) {
            $this->json_error('非法请求');
        }
        $flow = D('FinanceFlow')->findByPk($fid);   //流水信息
        $voucherList = D('FinanceVoucher')->where('fid=' . $fid)->select();  //查找附件
        $banks = D('Bank')->select();
        $exts = getFormerExts();
        $type_dsc = D('FinanceFlow')->getTypeDescribe();
        
        $this->assign('type_dsc', $type_dsc);
        $this->assign('exts', $exts);
        $this->assign('banks', $banks);
        $this->assign('voucherList', $voucherList);
        $this->assign('flow', $flow);
        $this->display();
    }
    
    //导出
    public function export() {
        $model = D('FinanceFlow');
        $result = $model->getList(1, 1000);
        $this->exportToExcel($result['list'], '流水表');
    }
    
    public function exportChecked() {
        $fids = I('get.expfids');
        $map['fid'] = array('in', $fids);
        $model = D('FinanceFlow');
        $result = $model->getList(1, 100, $map);
        $this->exportToExcel($result['list'], '流水表');
    }
    
    protected function exportToExcel($data, $filename) {
        $head = array('公司名称', '开户行账户', '交易对手', '增加金额', '减少金额');
        $dataList = array();
        foreach ($data as $v) {
            $list['account_name'] = $v['account_name'];
            $list['bank_name'] = $v['bank_name'];
            $list['counterparty'] = $v['counterparty'];
            $list['in_money'] = $v['in_money'];
            $list['out_money'] = $v['out_money'];
            $dataList[] = $list;
        }
        $excel = new \Admin\Lib\PHPexecl();
        $excel->push($head, $dataList, $filename);
    }
    
    //放款凭证
    public function voucher() {
        $fid = I('get.fid');
        $list = D('FinanceVoucher')->where('fid=' . $fid)->select();
        $this->assign('list', $list);
        $this->display();
    }
    
    //放款凭证
    public function getVouchers() {
        $fids = I('post.fids');
        $list = D('FinanceVoucher')->where("fid in ($fids)")->select();
        $this->sendData($list);
    }
    
    public function lookupNew() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $type = I('get.type')?I('get.type'):I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FinanceFlow');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.pay_time'][] = array('ELT', $end_time);
            }
        }
     //   $map['is_exchange'] = 0;  //这里流水筛选类型放开  author：lmj
        $result = $model->getSurplusList($page, $pageSize, $map);
        $type_describe = $model->getTypeDescribe();

       // $result = $model->getList($page, $pageSize, $map);
        //$type_describe = $model->getTypeDescribe();
        
        $this->assign('type_describe', $type_describe);
        $this->assign('list', $result);
        $this->assign('post', $_POST);
        $this->display('specified');
    }
    
    public function del() {
        $fid = I('get.fid');
        $model = D('FinanceFlow');
        $map['fid'] = $fid;
        $link = D('CapitalFlow')->findByCondition($map);
        if (!empty($link)) {
            $this->json_error('已经被关联的流水，无法删除');
        }
        $state = $model->delete($fid);
        if ($state !== false) {
            $this->json_success('删除成功');
        } else {
            $this->json_error('操作失败');
        }
    }
    
    //给非财务人员的展示页面，所有记录
    public function noFinanceList() {
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $pro_id = I('get.pro_id');
        $isSearch = I('post.isSearch');
        $type = I('post.type');

        $begin_time = I('post.begin_time');
        $end_time = I('post.end_time');
        $model = D('FinanceFlow');

        if ($isSearch) {
            if ($type !== '') {
                $map['t.type'] = $type;
            }

            if (!empty($begin_time)) {
                $begin_time = strtotime($begin_time);
                $map['t.pay_time'][] = array('EGT', $begin_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $map['t.pay_time'][] = array('ELT', $end_time);
            }
        }
        if (!empty($pro_id)) {
            $map['t.pro_id'] = $pro_id;
            $this->assign('pro_id', $pro_id);
        }
        $result = $model->getList($page, $pageSize, $map);
        $type_describe = $model->getTypeDescribe();
        
        $this->assign('type_describe', $type_describe);
        $this->assign(array('total' => $result['total'], 'pageCurrent' => $page, 'list' => $result['list']));
        $this->assign('post', $_POST);
        $this->display('no_finance_list');
    }
}
