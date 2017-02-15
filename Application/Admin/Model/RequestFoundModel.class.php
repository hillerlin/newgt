<?php 
namespace Admin\Model;

use Admin\Model\BaseModel;
class RequestFoundModel extends BaseModel {
	public function createQequest(){
		//同时在project表里面增加一条新数据
		$admin=session('admin');
		//$projectModel=D('Project');
		//if(!$_POST['id']) $projectModel->create();
		//$projectModel->pro_title=I('post.product_name');
		//$projectModel->pro_account=I('post.collect_money');
		//$projectModel->admin_id=$admin['admin_id'];
		foreach ($_POST as $k => $v) {
			if(is_array($v)){ //把数组装成字符串
				$_POST[$k]=trim($v[0]);
			}
			if($k=='addtime' || $k=='full_scale_time'){
				$_POST[$k]=strtotime($v);
			}
		}

		$RequestFound=D('RequestApply');
		$RequestFound->startTrans();
		if($_POST['id'])
		{
			$result=$RequestFound->where("`id`=%d",array($_POST['id']))->save($_POST);
			//$resultProject=$projectModel->where("`binding_oa`='%s'",array('1_'.$_POST['id']))->save();
		}
		else
		{
			$result=$RequestFound->add($_POST);
			//$projectModel->binding_oa='1_'.$result;//定义project表中的bingding_oa字段的数值
			//$resultProject=$projectModel->add();
		}


		$result=$result===false?false:true;
		if(!$result){
			//失败
			$RequestFound->rollback();
			return false;
		}else{
			//成功
			$RequestFound->commit();
			return true;
		}	
	}
	/**
	 * 将有前缀的字段，分割开来
	 */
	public function getSufFix($str){
		if(strpos($str, '_')){
			return  explode('_', $str);
		}
		return false;
	}
	/**
	 * 获取所有的请款申请信息
	 */
	public  function getRequestList($page = 1, $pageSize = 30, $map = '', $order = 'rf.addtime DESC'){
		$total=$this->alias('rf')
			->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=rf.project_id')
			->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=rf.user_id')
			->where($map)
			->count();
		$list=M('RequestFound')
			->alias('rf')
			->join('LEFT JOIN __PROJECT__ AS p ON p.pro_id=rf.project_id')
			->join('LEFT JOIN __ADMIN__ AS a ON a.admin_id=rf.user_id')
			->field('rf.id,rf.addtime,rf.guarantee_way,rf.project_con,rf.pay_way,rf.status,p.pro_title,a.real_name')
			->where($map)
			->page($page,$pageSize)
			->order($order)
			->select();
		return array('total'=>$total,'list'=>$list);
	}
	/**
	 * 获取指定请款的详细信息
	 */
	public function getAll(){
		if(empty(I('id'))) return false;
		$result=$this->where(array('id'=>array('eq',I('id'))))->find();
		$result['userinfo']=M('admin')->field('real_name,role_id')->where(array('admin_id'=>array('eq',$result['user_id'])))->find();
		$result['pro_title']=M('project')->getFieldByPro_id($result['project_id'],'pro_title');
		$result['pro_id']=M('project')->getFieldByPro_id($result['project_id'],'pro_id');
		return $result;
	}
	/**
	 * 更新审核结果
	 */
	public function upcheck(){
        $data['grf_id']=I('id');//请款id
        $data['checkinfo']=I('checkinfo');//审核情况
        $data['user_id']=session('admin')['admin_id'];
        $data['chresult']=I('chresult');
        $data['addtime']=time();
      	//判断是否审核过
      	if(M('RequestCheckinfo')->where(array('grf_id'=>I('id'),'user_id'=>$data['user_id']))->find()){
      		return 2 ;
      	}
        $this->startTrans(); 
        $result=M('RequestCheckinfo')->add($data);
        
      
        if(I('chresult')==2){
        	//审核通过
        	if(is_numeric($result)){
        		$re=M('RequestFound')->where(array('id'=>array('eq',I('id'))))->setInc('step');
        		
        		if($re!==false){
        			if($re==9){
        				$re2=M('RequestFound')->where(array('id'=>array('eq',I('id'))))->setField('status',3);
        				
        				if(empty($re2)){$this->rollback();return false;}
        			}else{
        				if(M('RequestFound')->getFieldById(I('id'),'status')!=2){
        					$re2=M('RequestFound')->where(array('id'=>array('eq',I('id'))))->setField('status',2);
        					
        					if(empty($re2)){$this->rollback();return false;}
        				}
        			}
        			$this->commit();
        			return true;
        		}
        	}
        	$this->rollback();
        	return false;
        }else{
        	//审核未通过,作废
        	if(is_numeric($result)){
        		$re=M('RequestFound')->where(array('id'=>array('eq',I('id'))))->setField('status',4);
        		if(!empty($re)){
        			$this->commit();return false;
        		}
        	}
        	$this->rollback();return false;
        }
	}
	/**
	 * 判断用户是否有权限进行操作
	 */
	public function checkuser(){
		$step=M('RequestFound')->getFieldById(I('id'),'step');
		$role=session('admin')['role_id'];
		$admin_id=session('admin')['admin_id'];
		if($step!=2){
			if(C('checkuser')[$step][0]==$role){
				return true;
			}
		}else{
			if($admin_id==M('Project')->getFieldByProId(I('pro_id'),'after_loan_admin')){
				return true;
			}
		}
		return false;
	}
	/**
	 * 查看流程
	 */
	public function checkflow(){
		if(empty(I('id'))) return false;
		$result=M('RequestCheckinfo')->where(array('grf_id'=>array('eq',I('id'))))->select();
		foreach ($result as $k => $v) {
			$result[$k]['real_name']=M('admin')->getFieldByAdmin_id($v['user_id'],'real_name');
			$result[$k]['role_id']=M('admin')->getFieldByAdmin_id($v['user_id'],'role_id');
		}
		return $result;
	}
	/**
	 * 查找
	 */
	public  function serach(){
		if(!empty(I('orderField'))){
			$sql='select rf.id,rf.guarantee_way,rf.project_con,rf.pay_way,rf.status,p.pro_title,a.real_name from gt_request_found as rf left JOIN ';
			$sql.='gt_project as p on p.pro_id=rf.project_id left join gt_admin as a on a.admin_id=rf.user_id order by '.I('orderField').' '.I('orderDirection');
			return $this->query($sql);
		}
	}

	//通过proId返回OA的信息
	public function returnOaInfoFromProId($appId,$searchType=null,$field='id')
	{
		switch ($searchType)
		{
			case 'in':
				$list=D('RequestApply')->where(array($field=>array('in',$appId)))->select();
				break;
			default:
				$list=D('RequestApply')->where("$field=%d",$appId)->find();
				break;
		}

		return $list;
	}
	//通过类型返回相对应的OA信息
	public function applicationFundsInfo($page,$pageNum,$map)
	{
		$rqa=D('RequestApply');
		$total=$rqa->where($map)
			->count();
		$list=$rqa->where($map)
			->page($page,$pageNum)
			->select();
		return array('list'=>$list,'total'=>$total);

	}
}