<?php
namespace Home\Controller;

class FinanceProjectController extends CommonController {
    public function index(){
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $isSearch = I('post.isSearch');
        $status = I('post.status');
        $pro_no = I('post.pro_no');
        $debt_no = I('post.debt_no');
        $model = D('FinanceProject');
//        $total = $model->count();
//        $list = $model->relation(true)->order('end_time desc')->page($page, $pageSize)->select();
        if ($isSearch) {
            if ($status !== '') {
                $map['t.status'] = $status;
            }
            if (!empty($pro_no)) {
                $map['p.pro_no'] = $pro_no;
            }
            if (!empty($debt_no)) {
                $map['t.debt_no'] = $debt_no;
            }
        }
        $member = session('member');
        $map['mid'] = $member['mid'];
        $result = $model->getList($page, $pageSize ,$map);
//        var_dump($status);eixt;
        $this->assign(array('total'=>$result['total'], 'pageCurrent'=>$page, 'list'=>$result['list']));
        $this->assign('post', $_POST);
        $this->display();
    }
    
    public function confirmBuy() {
        $fp_id = I('get.fp_id');
        if (empty($fp_id)) {
            $this->json_error('id不能为空');
        }
        $data = D('Admin/FinanceProject', 'Model')->where('fp_id='.$fp_id)->find();
        $project = D('Admin/Project', 'Model')->where('pro_id=' . $data['pro_id'])->relation(true)->find();
        $this->assign($data);
        $this->assign('project', $project);
        $this->display('confirm_buy');
    }
    
    public function buy() {
        if (IS_POST) {
            $fp_id = I('post.fp_id');
            $money = abs(I('post.money'));
            $member = session('member');
            
            $fp_info = D('FinanceProject')->where('fp_id=' . $fp_id)->find();
            if ($_SERVER['REQUEST_TIME'] > $fp_info['end_time']) {
                $this->json_error('已超过该项目截止日期');
            }
            $sum_money = D('FinanceOrder')->sumBuyMoney($fp_id, $member['mid']);
            if ($sum_money > 0) {
                $this->json_error('同一项目只能认购一次');
            }
//            if (bccomp($fp_info['max_money'], bcadd($sum_money, $money, 2), 2) === -1) {    //不大于项目最大认购金额
//                $this->json_error('超过该项目最大认购金额');
//            }
            $left_money = bcsub($fp_info['left_money'], $money, 2);
            if ($left_money < 0) {
                $this->json_error('超过该项目剩余认购金额');
            }
//            $member_info = D('Member')->where('mid=' . $member['mid'])->field('credit_line,frozen_credit')->find();
//            $frozen_credit_current = bcadd($member_info['frozen_credit'], $money, 2);
//            if (bccomp($member_info['credit_line'], $frozen_credit_current, 2) === -1) {   //不大于账户最大认购金额
//                $this->json_error('超过账户最大认购金额');
//            }
//            $rate = (int)$member['member_rate'] > 0 ? $member['member_rate'] : $fp_info['rate'];
            $this->processBuy($fp_id, $fp_info['pro_id'], $member['mid'], $money, $left_money);
        }
    }
    
    protected function processBuy($fp_id, $pro_id, $mid, $money, $left_money) {
        $model = D('FinanceOrder');
        $model->startTrans();
        $order_data = array('fp_id' => $fp_id, 'pro_id' => $pro_id, 'mid' => $mid, 'money' => $money);
        if (!$model->create($order_data) || !$model->add()) {   //插入新订单
            $model->rollback();
            $this->json_error('购买失败，请稍后再试');
        }
//        if (!D('Member')->where('mid=' . $mid)->save(array('frozen_credit' => $frozen_credit_current))) {   //更新会员单位可认购金额
//            $model->rollback();
//            $this->json_error('购买失败，请稍后再试');
//        }
        if (!D('FinanceProject')->where('fp_id=' . $fp_id)->save(array('left_money' => $left_money))) {     //更新项目剩余金额
            $model->rollback();
            $this->json_error('购买失败，请稍后再试3'.$left_money);
        }
        $model->commit();
        $this->json_success('认购成功');
    }
    
    public function detail() {
        $fp_id = I('get.fp_id');
        if (empty($fp_id)) {
            $this->json_error('id不能为空');
        }
        $member = session('member');
        $map['fp_id'] = $fp_id;
        $map['mid'] = $member['mid'];
        $data = D('FinanceProject')->where($map)->find();
//        $project = D('Project')->where('pro_id=' . $data['pro_id'])->relation(true)->find();
        $this->assign($data);
        $this->assign('project', $project);
        $this->display();
    }
}