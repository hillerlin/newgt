<?php
namespace Admin\Controller;
use Admin\Lib\Workflow;

class RequestFoundController extends CommonController {

    public function __construct() {
        $this->mainModel = D('RepaymentSchedule');
        parent::__construct();
    }
    /**
     * 请款审核列表
     * @return [type] [description]
     */
    public function listFund(){	
            $map['rf.status']=array(array('neq',3),array('neq',4));
            $this->assign('m',__FUNCTION__);
            $this->comlist($map);
    }
    /**
     * 请款完成列表
     */
    public function finishRequest(){
        $map['rf.status']=array(array('neq',1),array('neq',2));
        $this->assign('m',__FUNCTION__);
        $this->comlist($map);
    }
    /**
     * 列表公用部分
     */
    public function comlist($newmap=''){

        $pageSize = I('post.pageSize', 4);
            $page = I('post.pageCurrent', 1);
            $status = I('post.status');
            $project_con = I('post.project_con');
            $id=I('post.id');
            $pro_title=I('post.pro_title');
            if (!empty($pro_title)) {
                $map['p.pro_title'] = $pro_title;
            }
            if (!empty($id)){
                $map['rf.id'] = $id;
            }
            if (!empty($status)) {
                $map['rf.status'] = $status;
            }
            if (!empty($project_con)) {
              $map['rf.project_con'] = array('LIKE', '%'.$project_con.'%');
            }
            foreach ($newmap as $k => $v) {
                $map[$k]=$v;
            }
            $content=D('RequestFound')->getRequestList($page, $pageSize, $map);
            $total = $content['total'];
            $list = $content['list'];
            $this->assign(array('total'=>$total,'list'=>$list));
            $this->display('list_fund');
    }
    /**
     * 添加请款申请
     */
    public function addRequestFound(){
    		$proinfo=M('Project')->field('pro_id,pro_no,pro_title')->where(array('pro_real_money'=>array('gt',0),'finish_status'=>array('neq',1)))->select();  
    		$sql=M()->getLastSql();
            $this->assign('proinfo',$proinfo);     
    		$this->display('add_request_found',$result);
    }
    /**
     * 保存要添加的请款申请
     */
    public function saveRequestFound(){
        if(!empty(I('method'))){
            switch (I('method')) {
                case 'edit':
                    	if(!(D('RequestFound')->checkuser())){
                            $this->json_error('操作失败,您现在无权操作');
                        }
                        $re=D('RequestFound')->upcheck();
                        if($re===false){
                            $this->json_error('审核未通过,请求被驳回');
                        }else if($re===true){
                            $this->json_success('审核通过了');
                        }else if($re==2){
                        	$this->json_error('操作失败，您已审核过了');
                        }else{
                        	$this->json_error('系统正忙，请稍后重试');
                        }
                    break;
            }
        }
       	$rf=D('RequestFound');
       	if($rf->createQequest()){
            $this->json_success('添加成功');
        }else{
            $this->json_error('添加失败');
        }
    }
    /**
     * 编辑请款申请
     */
    public function edit(){
        $data=D('RequestFound')->getAll();
        $data['sdetail']=json_decode($data['sdetail']);
        $data['qdetail']=json_decode($data['qdetail']);
        $data['zdetail']=json_decode($data['zdetail']);
        $this->assign('data',$data)->display('edit_request_found');
    }
    /**
     * 审核详情
     */
    public function details(){
        $data=D('RequestFound')->getAll();
        $data['sdetail']=json_decode($data['sdetail']);
        $data['qdetail']=json_decode($data['qdetail']);
        $data['zdetail']=json_decode($data['zdetail']);
        $result=D('RequestFound')->checkflow();
        $this->assign('chresult',$result);
        
        $this->assign('data',$data)->display('details');
    }
    /**
     * 删除
     */
    public  function del(){
        M()->startTrans();
        $result=M('RequestFound')->where('id='.I('get.id'))->delete();
        $sql=M()->getLastSql();
        $result2=M('RequestCheckinfo')->where('grf_id='.I('get.id'))->delete();
        $sql1=M()->getLastSql();
        if($result && $result2){
            M()->commit();
            $this->json_success('删除成功');
        }
        M()->rollback();
        $this->json_error('删除失败');
    }

}
?>