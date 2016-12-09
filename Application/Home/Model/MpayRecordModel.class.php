<?php

namespace Home\Model;
use Think\Model;
class MpayRecordModel extends Model{
    
    const DRAft = 0;    //草稿
    const SUBMIT = 1;   //提交
    const CONFIRM = 2;  //后台确认

    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    
    
    /**
     * 更新状态
     * @param max $condition
     * @param int $next_status
     * @return boolean boolean
     */
    public function updateStatus($condition, $next_status) {
        $before_status = $this->where($condition)->getField('status');
        if ($next_status <= $before_status) {
            $this->error = '状态已更新，请刷新页面';
            return false;
        }
        if (!$this->where($condition)->save(array('status' => $next_status))) {
            $this->error = '状态更新失败';
            return false;
        }
        return true;
    }

}