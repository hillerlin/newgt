<?php
namespace Admin\Model;

use Think\Model\RelationModel;

class BaseModel extends RelationModel {
    
    public function setLinkCondition($relation, $param) {
        $this->_link[$relation]['condition'] = $param;
    }
    
    public function getById($prId) {
        $pk = $this->getPk();
        $map[$pk] = $prId;
        return $this->where($map)->find();
    }
    
    public function updateByPk($pk, $data) {
        $map[$this->pk] = $pk;
        return $this->where($map)->save($data);
    }
    
    public function findByPk($pk, $select = '') {
        $map[$this->pk] = $pk;
        return $this->field($select)->where($map)->find();
    }
    
    public function findByCondition($condition, $select = '') {
        return $this->field($select)->where($condition)->find();
    }
    
    /**
     * 根据一组pk id查找出信息
     * @param array ids
     * @param string field
     * @return array
     */
    public function selectByPks($pks, $field) {
        $map[$this->pk] = array('in', $pks);
        return $this->field($field)->where($map)->select();
    }
}

