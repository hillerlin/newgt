<?php

namespace Admin\Model;

class ElectronicBillModel extends BaseModel {

    const ENDORSE = 'endorse';   //背书
    const ACCEPTANCE = 'acceptance';   //托收承兑
    const UNACCEPTANCE = 'unacceptance';   //未兑付
    const PLEDGED = 'pledged';   //已质押
    const PLEDGED_FREE = 'pledged_free';   //质押已解除

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );

    public function addFlow($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $real_time, $remark = '') {
        $data = array(
            'pro_id' => $pro_id,
            'money' => $money,
            'company_id' => $company_id,
            'debt_all_id' => $debt_all_id,
            'type' => $type,
            'bank_id' => $bank_id,
            'remark' => $remark,
            'pay_time' => $real_time
        );
        if ($this->create($data) && $this->add()) {
            return true;
        }
        return false;
    }
    
    /**
     * 插入凭证，与fid关联
     * @param type $fid
     * @param type $list
     * @return boolean 成功or失败
     */
    public function addVoucher($eb_id, $list, $admin_id) {
        $time = time();
        $dataList = array();
        foreach ($list as & $v) {
            $v['eb_id'] = $eb_id;
            $v['addtime'] = $time;
            $v['admin_id'] = $admin_id;
            $dataList[] = $v;
        }
//        var_dump($dataList);exit;
        return D('EbillVoucher')->addAll($dataList);
    }

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 't.receive_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc1 ON bc1.bc_id=t.out_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc2 ON bc2.bc_id=t.recevier_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc3 ON bc3.bc_id=t.before_company_id')
                ->field("t.*,bc1.company_name as out_company,bc2.company_name as recevier_company,bc3.company_name as before_company")
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    //获取关联的项目信息
    public function getListPro($page = 1, $pageSize = 30, $map = '', $order = 't.receive_time DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc1 ON bc1.bc_id=t.out_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc2 ON bc2.bc_id=t.recevier_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc3 ON bc3.bc_id=t.before_company_id')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->field("t.*,bc1.company_name as out_company,bc2.company_name as recevier_company,bc3.company_name as before_company,pro_title")
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function getBillInfo($pk) {
        $map['eb_id'] = $pk;
        $request = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc1 ON bc1.bc_id=t.out_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc2 ON bc2.bc_id=t.recevier_company_id')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc3 ON bc3.bc_id=t.before_company_id')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->field("t.*,bc1.company_name as out_company,bc2.company_name as recevier_company,bc3.company_name as before_company,pro_title")
                ->where($map)
                ->find();
        return $request;
    }
    
    //
    public static function getTypeDescribe() {
        $type_describe = array(
            self::ENDORSE => '已退回',
            self::ACCEPTANCE => '托收承兑',
            self::UNACCEPTANCE => '未兑付',
            self::PLEDGED => '已质押',
            self::PLEDGED_FREE => '质押已解除',
        );
        return $type_describe;
    }
    
    //根据状态统计
    public function sumByStatus() {
        $result[self::ENDORSE] = $this->where(array('status' => self::ENDORSE))->sum('money');
        $result[self::ACCEPTANCE] = $this->where(array('status' => self::ACCEPTANCE))->sum('money');
        $result[self::UNACCEPTANCE] = $this->where(array('status' => self::UNACCEPTANCE))->sum('money');
        $result[self::PLEDGED] = $this->where(array('status' => self::PLEDGED))->sum('money');
        return $result;
    }
    
    //根据前手统计
    public function sumByBeforeCompany() {
        $result = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __BILL_COMPANY__ AS bc ON bc.bc_id=t.before_company_id')
                ->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->field("t.*,sum(money) as money,bc.company_name as before_company,pro_title")
                ->group('before_company_id')
                ->select();
        return $result;
    }
    
    //获取指定日期的电子商票信息
    public function getDueList($map) {
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->field('t.*,p.*')
                ->where($map)
                ->select();
        return $list;
    }
    
    /**
     * 关联项目
     * @param type $eb_id
     * @param type $pro_id
     * @return boolean
     */
    public function linkPro($eb_id, $pro_id) {
        return $this->where('eb_id=' . $eb_id)->save(array('pro_id' => $pro_id));
    }

}
