<?php
namespace Lns\Gpn\Lib\Entity\Db;

class FresheggInventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'freshegg_inventory';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
    'id',
    'beginning_stocks',
    'total_harvested',
    'waste_sales',
    'total_remaining_stocks',
    'created_at',
    'updated_at'
  ];

  public function getList($param){
      $this->resetQuery();
    $mainTable = $this->getTableName();
    $limit = 1;
    $this->_select->order(['created_at' => 'desc']);
    if(isset($param['limit'])){
      $limit = $param['limit'];
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    if (isset($param['search'])) {
      $where = $this->likeQuery(['beginning_stocks', 'total_harvested', 'waste_sales', 'total_remaining_stocks', 'created_at'], $this->escape($param['search']), 'freshegg_inventory', true);
      $this->_select->where($where);
    }
    return $this->getFinalResponse($limit);
  }
  public function getLastEnding($date = null){
    $mainTable = $this->getTableName();
    $this->resetQuery();
    if ($date) {
      $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') < date_format('" . $date . "', '%Y-%m-%d')";
      $this->_select->where($where);
    }
    $this->_select->order(['created_at' => 'desc']);
    $this->_select->limit(1);
    $result = $this->getCollection();
    if ($result) {
      return $result[0];
    } else {
      return null;
    }
  }
  public function getRecordtoday($date=null){
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
    $this->_select->where($where);
    $this->_select->limit(1);
    $result = $this->getCollection();
    /* var_dump($this->getLastSqlQuery());die; */
    if($result){
      return $result[0];
    }else{
      return null;
    }
  }
  public function getLatestActivity($date = null) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    if ($date) {
      $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
      $this->_select->where($where);
    }
		$this->_select->order(['updated_at' => 'desc']);
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
  }
  public function completeSales($date, $type_id, $qty) {
    $this->resetQuery();
    $mainTable = $this->getTableName();
    $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
    $this->_select->where($where);
    $this->_select->limit(1);
    $result = $this->getCollection();
   if($result){
      $result = $result[0];
      $total_harvested = $result->getData('total_harvested');
      $waste_sales = $result->getData('waste_sales');
      $total_remaining_stocks = $result->getData('total_remaining_stocks');

      $result->setData('waste_sales', (int) $waste_sales + (int) $qty);
      $result->setData('total_remaining_stocks', (int) $total_remaining_stocks - (int) $qty);
      $result->__save();
   }
  }
}
?>