<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

/**
 * Description of CalendarController
 *
 * @author Administrator
 */
class CalendarController extends CommonController{
    //put your code here
    protected $minute = array('00', '10', '20', '30', '40', '50');

    //添加
    public function add() {
        $date = I('get.date');
        $hour = $this->hour();
        $this->assign(array('hour' => $hour, 'minute' => $this->minute));
        $this->assign('date', $date);
        $this->display();
    }
    
    //生成格式化的24小时
    protected function hour() {
        for ($index = 0; $index < 24; $index++) {
            $hour[] = sprintf('%02d', $index);
        }
        return $hour;
    }
    
    public function edit() {
        $id = I('get.id');
        $event = D('Calendar')->findByPk($id);
        $hour = $this->hour();
        $this->assign(array('hour' => $hour, 'minute' => $this->minute));
        $this->assign($event);
        $this->display();
    }
    
    public function save() {
        $model = D('Calendar');
        if (false === $data = $model->create()) {
            $e = $model->getError();
            $this->json_error($e);
        }
        
        $model->start = strtotime($model->start);
        $end_time = strtotime($model->end);
        $model->end = empty($end_time) ? 0 : $end_time;
        if ($data['id']) {  
            $result = $model->save();
        } else {
            $admin = session('admin');
            $model->admin_id = $admin['admin_id'];
            $result = $model->add();
        }

        if ($result === false) {
            $this->json_error('保存失败');
        } else {
            $this->json_success('保存成功', '', '', 'true', array('tabid' => 'main'));
        }
    }
    
    public function myCalendar() {
        $model = D('Calendar');
        $start = I('get.start');
        $end = I('get.end');
        $admin = session('admin');
        $result = $model->myCalendar($admin['admin_id'], $start, $end);
        $this->ajaxRe($result);
    }
}
