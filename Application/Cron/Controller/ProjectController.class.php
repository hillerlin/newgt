<?php

namespace Cron\Controller;
use Think\Controller;
use Admin\Model\MpayRecordModel;
use Admin\Lib\HttpHelper;

class ProjectController extends Controller {
    
    //计算逾期利息罚息
    public function caclOverdueFee() {
        $schedule_model = D('Admin/ProjectDebt', 'Model');
        $overdue_list = $schedule_model->overdueList(); //获取过期项目列表
        var_dump($overdue_list);
        foreach ($overdue_list as $overdue) {
            $datetime1  = new  \DateTime ( date('Y-m-d', $overdue['deadline']) );
            $datetime2  = new  \DateTime ( '2017-6-13' );
            $interval  =  $datetime1 -> diff ( $datetime2 );
            $demurrage_days = $interval->format('%a');
//            var_dump($demurrage_days);exit;
            $penalty = $demurrage_days * $overdue['debt_account'] * $overdue['penalty_rate'] / 100;     //违约金=本金*违约金利率*违约天数
            //逾期利息
            $demurrage = $this->calcDemurrage($overdue['demurrage_rate_type'], $overdue['debt_account'], $overdue['deadline'], $demurrage_days, $overdue['interest'], $overdue['demurrage_rate2']);
            $data = array('penalty' => $penalty, 'demurrage' => $demurrage);
            var_dump($data);
            if (!$schedule_model->upOverdueFee($overdue['debt_all_id'], $data)) {
                //记录没有成功的
                echo 'fail'.$overdue['rp_id'];
            } else {
                echo 'success'.$overdue['rp_id'];
            }
        }
    }
    
    /**
     * 计算逾期利息
     * @param type $demurrage_rate_type 逾期算法
     * @param type $interest    第二种逾期算法需要的利息
     * @param type $demurrage_rate2     //第二种逾期算法需要的利率
     * @return type
     */
    protected function calcDemurrage($demurrage_rate_type, $principal, $deadline, $demurrage_days, $interest, $demurrage_rate2) {
        $demurrage_1 = 0;
        $demurrage_2 = 0;
        $contract_model = D('Admin/ProjectContract', 'Model');
        $demurrage_rate_type1 = $contract_model->demurrageRateType($demurrage_rate_type, 1);
        var_dump($demurrage_rate_type1);
        if ($demurrage_rate_type1) {
            $laon_rate = $this->getLoanRate($deadline);
            $demurrage_1 = $principal * $laon_rate * 0.04 / 365 * $demurrage_days;
        }
        $demurrage_rate_type2 = $contract_model->demurrageRateType($demurrage_rate_type, 2);
        var_dump($demurrage_rate_type2);
        if ($demurrage_rate_type2) {
            $demurrage_2 = bcmul($interest, $demurrage_rate2 / 100, 2);
        }
        
        return bcadd($demurrage_1, $demurrage_2, 2) ;
    }
    
    protected function getLoanRate($deadline) {
        $loan_list = D('LoanRate')->where('id=1')->find();
        if ($deadline <= 6) {
            $rate = $loan_list['in_six_month'];
        } elseif ($deadline > 6 && $deadline <= 12) {
            $rate = $loan_list['in_year'];
        } elseif ($deadline > 12 && $deadline <= 36) {
            $rate = $loan_list['in_three_year'];
        } elseif ($deadline > 36 && $deadline <= 60) {
            $rate = $loan_list['in_five_year'];
        } else {
            $rate = $loan_list['other'];
        }
        return $rate;
    }
    
    //到期利息未还，计算下期剩余本金
    public function getNextSurplusPrincipal() {
        $schedule_model = D('Admin/ProRepaymentSchedule', 'Model');
        $overdue_list = $schedule_model->overdueList();
        var_dump($overdue_list);
        foreach ($overdue_list as $overdue) {
            if (!$schedule_model->updateNextSurplurPrincipal($overdue['debt_all_id'], $overdue['n_term'], $overdue['surplus_principal'])) {
                //记录没有成功的
                echo 'fail'.$overdue['rp_id'];
            } else {
                echo 'success'.$overdue['rp_id'];
            }
        }
    }
    
    //到期提醒
    public function dueRemind() {
        $remind_day = array(1, 5, 10, 30);
        foreach ($remind_day as $val) {
            $this->projectDueRemind($val);
            $this->debtDueRemind($val);
//            $this->ebillDueRemind($val);
        }
    }
    
    //到期项目提醒
    protected function projectDueRemind($days) {
        $model = D('Admin/ProjectDebt', 'Model');
        $Ymd = date('Y-m-d', time());
        $end = strtotime($Ymd . "+ $days day");
        $map['deadline'] = $end;
        var_dump(date('Y-m-d H:i:s', $end));
        $project_due = $model->getDueList($map);
        var_dump($project_due);
        if (empty($project_due)) {
            return true;
        }
        $receiver_arr = array('role_id' => '2,14,17');
        $receiver_ids = D('Admin/Admin', 'Model')->getExecutors($receiver_arr);
        $receiver_admins = array_column($receiver_ids, 'admin_id');
        $messages = array();
        foreach ($project_due as $value) {
            $receiver_admins = array_unique(array_merge($receiver_admins, array($value['pro_linker'], $value['admin_id'], $value['risk_admin_id'], $value['after_loan_admin'])));
//            var_dump($receiver_admins);
            $pro_debt_money = $value['debt_account'] / 10000;
            foreach ($receiver_admins as $admin_id) {
                $message['title'] = '项目到期提醒';
                $message['addtime'] = time();
                $message['description'] = "<code>{$value['pro_title']}</code>项目{$pro_debt_money}万{$days}天后即将到期";
                $message['admin_id'] = $admin_id;
                $messages[] = $message;
            }
        }
//        var_dump($messages);
//        return true;
        D('Admin/Message', 'Model')->addAll($messages);
    }
    
    //到期债权提醒
    public function debtDueRemind($days) {
        $model = D('Admin/ProjectDebtDetail', 'Model');
        $Ymd = '2016-8-18';//date('Y-m-d', time());
        $end = strtotime($Ymd . "+ $days day");
        $map['end_time'] = $end;
        $project_debt_due = $model->getDueList($map);
        if (empty($project_debt_due)) {
            return true;
        }
        $receiver_arr = array('role_id' => '2,14,17');
        $receiver_ids = D('Admin/Admin', 'Model')->getExecutors($receiver_arr);
        $receiver_admins = array_column($receiver_ids, 'admin_id');
        $messages = array();
        foreach ($project_debt_due as $value) {
            $receiver_admins = array_unique(array_merge($receiver_admins, array($value['pro_linker'], $value['admin_id'], $value['risk_admin_id'], $value['after_loan_admin'])));
//            var_dump($receiver_admins);
            $title = $value['type'] == 1 ? '商票' : '债权';
            foreach ($receiver_admins as $admin_id) {
                $message['title'] = $title . '到期提醒';
                $message['addtime'] = time();
                $message['description'] = "<code>{$value['pro_title']}</code>项目{$days}天后{$title}即将到期";
                $message['admin_id'] = $admin_id;
                $messages[] = $message;
            }
        }
//        var_dump($message_list);
//        return true;
        D('Admin/Message', 'Model')->addAll($messages);
    }
    
    //到期商票提醒
    protected function ebillDueRemind($days) {
        $model = D('Admin/ElectronicBill', 'Model');
        $Ymd = date('Y-m-d', time());
        $end = strtotime($Ymd . "+ $days day");
        $map['due_time'] = $end;
        var_dump(date('Y-m-d H:i:s', $end));
        $project_ebill_due = $model->getDueList($map);
        if (empty($project_ebill_due)) {
            return true;
        }
        $receiver_arr = array('role_id' => '2,14');
        $receiver_ids = D('Admin/Admin', 'Model')->getExecutors($receiver_arr);
        $receiver_admins = array_column($receiver_ids, 'admin_id');
        $messages = array();
        foreach ($project_ebill_due as $value) {
            $receiver_admins = array_unique(array_merge($receiver_admins, array($value['pro_linker'], $value['admin_id'], $value['risk_admin_id'], $value['after_loan_admin'])));
            foreach ($receiver_admins as $admin_id) {
                $message['title'] = '项目到期提醒';
                $message['addtime'] = time();
                $message['description'] = "<code>{$value['pro_title']}</code>项目电子商票{$days}天后即将到期";
                $message['admin_id'] = $admin_id;
                $messages[] = $message;
            }
        }
//        return true;
        D('Admin/Message', 'Model')->addAll($messages);
    }
}