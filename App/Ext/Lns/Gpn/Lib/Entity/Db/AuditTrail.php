<?php
namespace Lns\Gpn\Lib\Entity\Db;

class AuditTrail extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    
  protected $tablename = 'audit_trail';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
		'id',
		'user_id',
    'name',
    'action',
    'code',
    'created_at'
  ];
  
  public function save($data) {
    $this->setDatas($data);
    $result = $this->__save();
    if ($result) {
      return $result;
    } else {
      return null;
    }
  }
  public function getList($param){
    $mainTable = $this->getTableName();
    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['user_id'])) {
      $this->_select->where("user_id = " .$param['user_id']);
    }
    if (isset($param['code'])) {
      $this->_select->where("code = '". $param['code'] . "'");
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['search'])) {
      $where = $this->likeQuery(['name', 'action', 'created_at'], $this->escape($param['search']), 'audit_trail', true);
      $this->_select->where($where);
    }
    return $this->getFinalResponse($limit);
  }
  public function saveAudittrail($user_id,$name,$action,$code){
    $entity = $this;
    $entity->setData('user_id', $user_id);
    $entity->setData('name', $name);
    $entity->setData('action', $action);
    $entity->setData('code', $code);
    $entity->__save();
  }
  public function getLatestActivities($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => 'desc']);
        $this->_select->limit(10);
        $result = $this->getCollection();
        return $result;
  }
}
?>