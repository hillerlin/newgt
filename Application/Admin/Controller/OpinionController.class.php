<?php
//项目贷后管理
namespace Admin\Controller;
class OpinionController extends CommonController
{
	/**
	 * 内审管理
	 */
	public function audit()
	{
		$this->display();
	}
    /**
     * 按记录调试显示内审记录
     */
    public function oplist()
    {
        $post=I('post.');
        $pageSize = I('post.pageSize', $this->pageDefaultSize);
        $page = I('post.pageCurrent', 1);
        $map='';
        if($post['pro_title'])$map['pro_title']=$post['pro_title'];
        if($post['begin_time'])$map['addtime'][]=array('egt',strtotime($post['begin_time']));
        if($post['end_time'])$map['addtime'][]=array('elt',strtotime($post['end_time']));
        $result=D('Opinion')->getList($page,$pageSize,$map);
        $this->assign('list',$result['list']);
        $this->assign('total',$result['total']);
        $this->display();
    }

    /**
     * 添加，内审记录
     */
    public function add()
    {
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit(){
        $result=D('Opinion')->showDetail();
        foreach ($result as $k=>$v){
            if(strcmp($k,'opinion_id')===0)continue;
            foreach($v as $key=>$value){
                $result[$k][$key]['content']=json_decode($value['content']);
            }
        }
        $this->assign('result',$result)->display();
    }
    /**
     * 显示内审记录
     */
    public function showdetails()
    {
        $result=D('Opinion')->showDetail();
        unset($result['opinion_id']);
        foreach($result as $k=>$v){
            foreach($v as $kk=>$vv){
                $result[$k][$kk]['content']=json_decode($vv['content']);
            }
        }
        $this->assign('list',$result);
        $this->display();
    }

    /**
     * 保存记录
     */
    public function save()
    {
        $data = I('post.');
        $financing = $partner = $purchaser = $guarantor = '';
        //需要更新的数据
//        $updatas = [];
        //更新的结果,默认是没有更新的
        $updataresult=true;
        //此处顺序不能乱，与前段页面的顺序进行对应的
        //融资方
        $result['financing'] = $this->handleSave($data, 'financing_');
        //股东
        $result['partner'] = $this->handleSave($data, 'partner_');
        //担保人
        $result['guarantor'] = $this->handleSave($data, 'guarantor_');
        //买方
        $result['purchaser'] = $this->handleSave($data, 'purchaser_');

        //添加代码
        M()->startTrans();
        foreach ($result as $k => $v) {
            foreach ($v as $kk => $vv) {
                if (in_array($k, array('financing', 'partner', 'purchaser', 'guarantor'))) {
                    $re = M('opinionDetails')->add($vv);
                    $$k .= $re . ',';
                }
            }
        }
        if (!empty($financing) & !empty($partner) & !empty($purchaser) & !empty($guarantor)) {
            if(!$data['opinion_id']){
                //添加数据
                if (M('opinion')->add(array('financing' => $financing, 'partner' => $partner, 'guarantor' => $guarantor, 'purchaser' => $purchaser,'addtime'=>time(),'pro_title'=>$data['pro_title']))) {
                    M()->commit();
                    $this->json_success('插入成功');
                }else{
                    M()->rollback();
                    $this->json_error('插入失败');
                }
            }else{
                //更新数据
                $opinionData=M('opinion')->where('opinion_id='.$data['opinion_id'])->find();
                $tmp='';
                foreach($opinionData as $k=>$v){
                    if(in_array($k,array('opinion_id','pro_title','addtime')))continue;
                    $tmp.=$v;
                }
                $tmp=explode(',',rtrim($tmp,','));
                $opinionDetails=M('opinionDetails');
                foreach ($tmp as $v){
                    $updataresult= $updataresult &&  $opinionDetails->where('opdetail_id='.$v)->delete();
                }
                if($updataresult){
                    if(M('opinion')->where('opinion_id = '.$data['opinion_id'])->data(array('financing' => $financing, 'partner' => $partner, 'guarantor' => $guarantor, 'purchaser' => $purchaser,'pro_title'=>$data['pro_title']))->save()){
                        M()->commit();
                        $this->json_success('数据更新成功');
                    }
                }
                M()->rollback();
                $this->json_error('数据更新失败');
            }
        }
        M()->rollback();
        $this->json_error('操作有误');
    }

    /**
     * @param $data 提交过来的数据
     * @param $pre  数据前缀
     * @return array    排序好的数据
     */
    public function handleSave($data,$pre){
        $tempdata = [];
        foreach ($data as $k=>$v){
            if(strpos($k,$pre)!==false){
                if(in_array($k,array($pre.'link',$pre.'title',$pre.'addtime',$pre.'countlink'))) {
                    $content[substr($k, strlen($pre))] = $v;
                    continue;
                }
                if(strpos($k,'ftime')!==false){
                    $tempdata[substr($k,strlen($pre))]=strtotime($v);
                }
                $tempdata[substr($k,strlen($pre))]=$v;
            }
        }
        $temp=array_map(function($link,$title,$addtime){
            return array('link'=>$link,'title'=>$title,'addtime'=>$addtime);
        },$content['link'],$content['title'],$content['addtime']);
        foreach($content['countlink'] as $v){
            $contents[]=json_encode(array_slice($temp,0,$v));
            array_splice($temp,0,$v);
        }
        $result=array_map(null,$tempdata['financing'],$tempdata['ftime'],$tempdata['fileid'],$tempdata['result'],$tempdata['integrity'],$tempdata['fclass'],$contents);
        $keymap=array('name','ftime','fileid','result','integrity','fclass','content');
        foreach ($result as $v){
            $adddata[]=array_combine($keymap,$v);
        }
        return $adddata;
    }
}
?>