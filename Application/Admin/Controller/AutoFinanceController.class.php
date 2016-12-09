<?php

namespace Admin\Controller;
use Admin\Model\MpayRecordModel;
use Admin\Lib\HttpHelper;

class AutoFinanceController extends CommonController {

    private $oid;
    private $key = '123asd!@￥';


    public function __construct() {
        $this->mainModel = D('FinanceOrder');
        parent::__construct();
    }

    public function autoBuy() {
        $config = C('DM_COLLECT');
        $mid = $config['mid'];
        if ($this->isGet($mid)) {
            exit('已经获取');
        }
        $url = $config[''];
        $data['key'] = md5($this->key + md5('damailicai123'));
        $collect = HttpHelper::post($url, $data);
        if ($collect === false) {
            exit('获取失败');
        }
        $collect_info = json_decode($collect, true);
        $time = time();
        if (empty($collect_info['totalmoney'])) {
            $this->addLog($mid);
            exit('ss');
        }
        foreach ($collect_info['data'] as $bid_info) {
            if (empty($bid_info['gt_id'])) {
                $member_pay = array(
                    'oid' => $config['oid'],
                    'mid' => $mid,
                    'fp_id' => $config['fp_id'],
                    'money' => $bid_info['moneytotal'],
                    'repayment_type' => '1',
                    'term' => $bid_info['limit_time'],
                    'rate' =>$bid_info['rate'],
                    'add_time' => $time,
                    'pay_time' => $time,
                    'status' => '2',
                    'remark' => '大麦自动打款',
                );
                $this->confirm($member_pay, $bid_info['rate']);
            }
            
        }
    }

    protected function processBuy($fp_id, $pro_id, $mid, $money) {
        $model = D('FinanceOrder');
        $model->startTrans();
        //默认直接购买成功
        $order_data = array('fp_id' => $fp_id, 'pro_id' => $pro_id, 'mid' => $mid, 'money' => $money, 'confirm_time' => time(), 'status' => '1', 'remark' => '大麦自动认购');
        if (!$model->create($order_data) || !$model->add()) {   //插入新订单
            $model->rollback();
            $this->json_error('购买失败，请稍后再试');
        }
        $this->oid = $model->getLastInsID();
        $model->commit();
        $this->json_success('认购成功');
    }
    
    //确认还款，生成还款列表
    public function confirm($member_pay, $rate) {
        $pay_model = D('MpayRecord');
        $pay_model->startTrans();
        $pay_model->add($member_pay);
        $pay_id = $pay_model->getLastInsID();
//        $finance_order_rate = D('FinanceOrder')->where('oid=' . $mpay_info['oid'])->getField('rate');
        $finance_order_rate = $rate;
        
        //生成还款列表
        if (!D('RepaymentSchedule')->addRecords($pay_id, $member_pay, $finance_order_rate)) {
            $pay_model->rollback();
            $this->json_error('还款列表生成失败');
        }
        if (!$this->addLog($member_pay['mid'])) {
            $pay_model->rollback();
            $this->json_error('ss');
        }
        $pay_model->commit();
        $this->json_success('成功');
    }
    
    protected function addLog($mid) {
        return D('AutoFinance')->add(array('mid' => $mid, 'status' => 1, 'addtime' => time()));
    }
    
    protected function isGet($mid) {
        $map['mid'] = $mid;
        $map['status'] = 1;
        $begin_time = strtotime('midnight');
        $end_time = $begin_time + 86400;
        $map['addtime']  = array(array('gt', $begin_time), array('lt', $end_time));
        $count = D('AutoFinance')->where($map)->count();
        return $count > 0 ? true : false;
    }
    
}