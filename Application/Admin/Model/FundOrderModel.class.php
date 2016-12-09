<?php

namespace Admin\Model;

class FundOrderModel extends BaseModel {

    protected $_validate = array(
//        array('role_name', 'require', '请输入权限组'),
//        array('role_name', '', '权限组已存在', 0, 'unique', 1),
    );
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
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
            'pay_time' => $real_time,
            'fid' => 0
        );
        if ($this->create($data) && $this->add()) {
            return true;
        }
        return false;
    }
    
    public function addFlowWithFinance($pro_id, $company_id, $debt_all_id, $money, $type, $bank_id, $real_time, $fid, $remark = '') {
        $data = array(
            'pro_id' => $pro_id,
            'money' => $money,
            'company_id' => $company_id,
            'debt_all_id' => $debt_all_id,
            'type' => $type,
            'bank_id' => $bank_id,
            'remark' => $remark,
            'pay_time' => $real_time,
            'fid' => $fid
        );
        if ($this->create($data) && $this->add()) {
            return true;
        }
        return false;
    }

    public function getList($page, $pageSize, $map = '', $order = 't.addtime DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fbb ON fbb.branch_id=fm.branch_ch_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fbb ON fbb.branch_id=fm.branch_ch_id')
                ->field('t.*,real_name,fb.branch_name as branch_name, fbb.branch_name as branch_ch_name')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    /**
     * 显示每一天的统计数据
     * @param  integer $page     页数
     * @param  integer $pageSize 每一页的数量
     * @param  string  $map      条件
     * @param  string  $order    排序
     * @return [type]            返回统计的总条数和所有数量
     */
    public function getDayList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC'){        
        $total=$this
                ->table($this->trueTableName . ' AS t')
                ->field(array('FROM_UNIXTIME(pay_time,"%Y%m%d")'=>'dayss'))
                ->where($map)
                ->group('dayss')
                ->select();
        $total=count($total);
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->field(array('sum(money)'=>'count_money','pay_time','FROM_UNIXTIME(pay_time,"%Y-%m-%d")'=>'dayss'))
                ->where($map)
                ->group('dayss')
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    /**
     * 按区域显示销售统计数据
     * @return [type] 返回指定区域的销售信息
     */
    public function showDayData(){
       $sql="select * from(SELECT fm.fmanager_id, fm.branch_id, sum(fo.money) as money,fb.branch_name, fm.real_name, FROM_UNIXTIME(fo.pay_time,'%Y-%m-%d') as dayss FROM gt_fund_manager as fm LEFT JOIN gt_fund_order as fo on fo.fmanager_id=fm.fmanager_id LEFT JOIN gt_fund_branch fb on fb.branch_id=fm.branch_ch_id GROUP BY dayss ,fm.branch_id ,fm.fmanager_id) as aa where aa.dayss = FROM_UNIXTIME(".I('pay_time').",'%Y-%m-%d') AND aa.branch_id = ".I('branch_id');
        return M()->query($sql);
    }
    public function getDetailList($page = 1, $pageSize = 30, $map = '', $order = 't.addtime DESC') {
        $total = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->where($map)
                ->count();
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->join('__PROJECT__ AS p ON p.pro_id=t.pro_id')
                ->join('LEFT JOIN __COMPANY__ AS c ON c.company_id=t.company_id')
                ->field('t.*,pro_title,company_address,bank_no,bank_name,company_name,company_phone')
                ->where($map)
                ->page($page, $pageSize)
                ->order($order)
                ->select();
        return array('total' => $total, 'list' => $list);
    }
    
    public function getDetail($map,$table='') {
        
        $tableName=empty($table)?$this->trueTableName:$table;

        $list = $this
                ->table($tableName . ' AS t')
                //->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                //->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
               // ->join('LEFT JOIN __FUND_BRANCH__ AS fbb ON fbb.branch_id=fm.branch_ch_id')
               // ->field('t.*,real_name,fb.branch_name as branch_name, fbb.branch_name as branch_ch_name')
                ->where($map)
                ->find();
        return $list;
    }
    //根据fund_id返回详情内容
    public function fundInfo($fund_id,$type=0,$map=null)
    {

        switch ($type){
            case 0:  //查询
                $list=$this->table($this->trueTableName)->where('`fund_id`=%d',array($fund_id))->find();
                return $list;
            case 1: //更新
                $result=$this->table($this->trueTableName)->where('`fund_id`=%d',array($fund_id))->data($map)->save();
                return $result;
            default:
                return true;

        }

    }
    //查找fund_order_log详细内容
    public function fundLogInfo($fund_id)
    {
        $list=$this->table('gt_fund_order_log AS t')
            ->join('LEFT JOIN __ADMIN__ AS a ON t.changer_id=a.admin_id')
            ->join('LEFT JOIN __DEPARTMENT__ AS d ON a.dp_id=d.dept_id')
            ->field('a.real_name as real_name,d.department as department,t.log_id as log_id,t.fund_id as fund_id,t.addtime as addtime')
            ->where("`fund_id`=%d",array($fund_id))->select();
        return $list;
    }
    //根据role_id返回角色详情
    public function roleInfo($roleId)
    {
        $roleInfo = D('role')->where('`role_id`=%d', array($roleId))->find();
        return $roleInfo;
    }
    
    /**
     * 字段不为空的选项
     * @return [type] 返回字段不能为空的字段集合
     */
    public function notFieldNull(){
        $map['t.fmanager_id']=array('NEQ', '0');
        $map['t.money']=array('NEQ', '0');
        $map['t.customer_name']=array('NEQ', '');
        $map['t.pay_time']=array('NEQ', '0' );
        $map['t.addtime']=array('NEQ', '0' );
        $map['t.contract_no']=array('NEQ',  '' );
        $map['t.partnership']=array('NEQ',  '' );
        $map['t.begin_interest_time']=array('NEQ',  '0' );
        $map['t.done_time']=array('NEQ',  '0' );
        $map['t.fund_title']=array('NEQ', '' );
        $map['t.fund_rate']=array('NEQ', '0' );
        $map['t.term']=array('NEQ', '0' );
        $map['t.deadline']=array('NEQ', '0' );
        $map['t.id_no']=array('NEQ', '' );
        $map['t.bank_no']=array('NEQ', '' );
        $map['t.link_type']=array('NEQ', '' );
        $map['t.performance_rate']=array('NEQ', '0');
        $map['t.manage_rate']=array('NEQ','' );
        return $map;
    }
    /**
     * 显示每一天的统计数据
     * @param  integer $page     页数
     * @param  integer $pageSize 每一页的数量
     * @param  string  $map      条件
     * @param  string  $order    排序
     * @return [type]            返回统计的总条数和所有数量
     */
    public function getWeekList($page = 1, $pageSize = 30, $map = ''){        
        $total=$this
        		->table($this->trueTableName . ' AS t')
                ->field(array('t.money','t.pay_time','FROM_UNIXTIME(t.pay_time,"%Y%u")'=>'weeks','fm.real_name','fm.branch_ch_id','fb.branch_name'))
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->where($map)
                ->select();
        $total=count($total);
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->field(array('t.money','t.pay_time','t.fmanager_id','FROM_UNIXTIME(t.pay_time,"%Y/%m/%d 第%u周")'=>'weeks','fm.branch_id','fm.real_name','fm.branch_ch_id','fb.branch_name'))
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->where($map)
                ->page($page,$pageSize)
                ->select();

        return array('total' => $total, 'list' => $list);
    }
    public function getMonthList($page = 1, $pageSize = 8, $map = ''){
        $total=$this
        		->table($this->trueTableName . ' AS t')
                ->field(array('t.money','t.pay_time','FROM_UNIXTIME(t.pay_time,"%Y年/%m月")'=>'weeks','fm.real_name','fm.branch_ch_id','fb.branch_name'))
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->where($map)
                ->select();
        $total=count($total);
        $list = $this
                ->table($this->trueTableName . ' AS t')
                ->field(array('t.money','t.pay_time','t.fmanager_id','FROM_UNIXTIME(t.pay_time,"%Y年%/%m月")'=>'weeks','fm.branch_id','fm.real_name','fm.branch_ch_id','fb.branch_name'))
                ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
                ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fb.branch_id=fm.branch_id')
                ->where($map)
                ->page($page,$pageSize)
                ->select();

        return array('total' => $total, 'list' => $list);
    }
    //每天实时出进款数据汇总数据
    public function getdayincome($map=''){
        $list=$this->table($this->trueTableName.' AS t')
        ->field(array('sum(t.money) as allmoney','fm.real_name','fm.branch_id','fb.branch_name', 'FROM_UNIXTIME(t.pay_time,"%Y年%m月%d日 %W") as today'))
        ->join('LEFT JOIN __FUND_MANAGER__ AS fm ON fm.fmanager_id=t.fmanager_id')
        ->join('LEFT JOIN __FUND_BRANCH__ AS fb ON fm.branch_id=fb.branch_id')
        ->where("FROM_UNIXTIME(t.pay_time,'%Y%m%d')=FROM_UNIXTIME(UNIX_TIMESTAMP(now()),'%Y%m%d')")
        ->group('fm.fmanager_id')
        ->order('fm.branch_id')
        ->select();
        return array('list'=>$list);
    }
    //到期兑付按日期统计统计
    public function repayment($page = 1, $pageSize = 30, $map = ''){
        $total=$this->field(array('GROUP_CONCAT(DISTINCT customer_name) as sumname', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(money) as summoney', 'sum(interestDue) as suminterestDue'))
                ->where($map)
                ->group("FROM_UNIXTIME(deadline,'%Y-%m-%d')")
                ->select();
        $total=count($total);
        $list=$this->field(array('GROUP_CONCAT(DISTINCT customer_name) as sumname', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(money) as summoney', 'sum(interestDue) as suminterestDue'))
                ->where($map)
                ->group('detime')
                ->order('detime')
                ->page($page,$pageSize)
                ->select();
        return array('list'=>$list,'total'=>$total);
    }
    //到期兑付按日期兑付后，显示人名统计
    public function getrepaybytime($map){
        $list=$this->table($this->trueTableName.' AS t')
                ->field(array('customer_name', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(money) as summoney', 'sum(interestDue) as suminterestDue'))
                ->where($map)
                ->group('detime,customer_name')
                ->order('customer_name')
                ->select();
        return $list;
    }
    //到期兑付按日期兑付后，显示项目分类统计
    public function getrepaybyname($map){
        $list=$this->table($this->trueTableName.' AS t')
                ->field(array('customer_name','fund_title','deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(money) as summoney', 'sum(interestDue) as suminterestDue'))
                ->where($map)
                ->group('detime,fund_title')
                ->order('customer_name')
                ->select();
        
        return $list;
    }
    //付息统计
    public function interestList($page = 1, $pageSize = 30, $map = ''){
        $total=$this->table($this->trueTableName.' AS t')
                ->field(array('GROUP_CONCAT(DISTINCT customer_name) as sumname', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(interestDue-money) as interest'))
                ->where($map)
                ->group('detime')
                ->select();
        $total=count($total);
        $list=$this->table($this->trueTableName.' AS t')
                ->field(array('GROUP_CONCAT(DISTINCT customer_name) as sumname', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(interestDue-money) as interest'))
                ->where($map)
                ->group('detime')
                ->order('detime')
                ->page($page,$pageSize)
                ->select();
        return array('list'=>$list,'total'=>$total);
    }
    //到期兑付按日期兑付后，显示人名统计
    public function getcountbytime($map){
        $list=$this->table($this->trueTableName.' AS t')
                ->field(array('customer_name', 'deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime", 'sum(interestDue-money) as interest'))
                ->where($map)
                ->group('detime,customer_name')
                ->order('customer_name')
                ->select();
        return $list;
    }
    //到期兑付按日期兑付后，显示项目分类统计
    public function getcountbyname($map){
        $list=$this->table($this->trueTableName.' AS t')
                ->field(array('customer_name','fund_title','deadline',"FROM_UNIXTIME(deadline,'%Y-%m-%d') as detime",'sum(interestDue-money) as interest'))
                ->where($map)
                ->group('detime,fund_title')
                ->order('customer_name')
                ->select();
        return $list;
    }
}
