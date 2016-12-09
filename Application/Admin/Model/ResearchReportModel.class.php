<?php

namespace Admin\Model;

class ResearchReportModel extends BaseModel {

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );

    
    /**
     * 插入凭证，与fid关联
     * @param type $fid
     * @param type $list
     * @return boolean 成功or失败
     */
    public function addVoucher($fid, $list, $admin_id) {
        $time = time();
        $dataList = array();
        foreach ($list as & $v) {
            $v['fid'] = $fid;
            $v['addtime'] = $time;
            $v['admin_id'] = $admin_id;
            $dataList[] = $v;
        }
//        var_dump($dataList);exit;
        return D('FinanceVoucher')->addAll($dataList);
    }

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.add_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dept_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=t.admin_id')
                ->join('LEFT JOIN __DEPARTMENT__ AS d ON d.dept_id=t.dept_id')
                ->field("t.*,real_name,department")
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }

}
