<?php

namespace Admin\Model;

class OpinionModel extends BaseModel {

    public function getList($page = 1, $pageSize = 30, $map = '', $order = 'opinion_id DESC') {
        $total = $this->where($map)->count();
        $list = $this->where($map)->page($page, $pageSize)->order($order)->select();
        return array('total' => $total, 'list' => $list);
    }
    public function showDetail(){
        $list=M('Opinion')->where('opinion_id = '.I('get.opinion_id'))->find();
        $list['financing']=$this->getDetails($list['financing']);
        $list['partner']=$this->getDetails($list['partner']);
        $list['guarantor']=$this->getDetails($list['guarantor']);
        $list['purchaser']=$this->getDetails($list['purchaser']);
        return $list;
    }
    public function getDetails($data){
        if(empty($data))return false;
        $tmp=explode(',',$data);
        foreach ($tmp as $k=>$v){
            if(!empty($v)){
                $returnData[]=M('opinionDetails')->where('opdetail_id = '.$v)->find();
            }
        }
        return $returnData;
    }
}