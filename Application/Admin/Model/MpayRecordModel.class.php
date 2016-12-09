<?php

namespace Admin\Model;

class MpayRecordModel extends BaseModel{
    
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
    
    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
//                ->join('__MEMBER__ AS m ON m.mid=t.mid')
//                ->join('__ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__MEMBER__ AS m ON m.mid=t.mid')
                ->join('__FINANCE_PROJECT__ AS fp ON fp.fp_id=t.fp_id')
                ->field('t.*,company_name,fp_title')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }

}