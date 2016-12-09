<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Model;

/**
 * Description of CalendarModel
 *
 * @author Administrator
 */
class CalendarModel extends BaseModel{
    //put your code here
    protected $_validate = array(
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
    );
    
    public function myCalendar($admin_id, $start, $end) {
        $select = 'SELECT * FROM gt_calendar ';
        $where = " where admin_id = $admin_id AND ((start > $start AND start < $end) or (end > $start)) ";
        $sql = $select . $where;
        $list = $this->query($sql);
        return $list;
    }
}
