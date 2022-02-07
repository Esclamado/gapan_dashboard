<?php
namespace Lns\Gpn\Lib\Entity\Db;

class House extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  protected $tablename = 'house';
  protected $primaryKey = 'id';  
  const COLUMNS = [
    'id',
    'chicken_pop_id',
    'house_name',
    'capacity',
    'created_at',
    'updated_at'
  ];
  protected $_dailyhouseharvest;

  public function __construct(
    \Of\Http\Request $Request
  ) {
    parent::__construct($Request);
    $this->_dailyhouseharvest = $this->_di->get('\Lns\Gpn\Lib\Entity\Db\Dailyhouseharvest');
  }

  public function installData() {
    $this->setDatas([
			'house_name' => 5,
			'capacity' => 5000,
    ])->__save();
    $this->setDatas([
			'house_name' => 6,
			'capacity' => 5000,
    ])->__save();
    $this->setDatas([
			'house_name' => 7,
			'capacity' => 5000,
    ])->__save();
    $this->setDatas([
			'house_name' => 8,
			'capacity' => 5000,
		])->__save();
  }
  public function getHouse($id) {
    $result = $this->getByColumn(['id' => $id], 1);
    if ($result) {
      return $result->getData();
    } else {
      return null;
    }
  }
  public function getHouseType($id)
  {
    $result = $this->getByColumn(['id' => $id], 0);
    if ($result) {
      return $result;
    } else {
      return null;
    }
  }
  public function getHouses() {
    return $this->getCollection();
  }
  public function getHouseAlpha() {
    $this->resetQuery();
    $this->_select->order(['house_name' => 'asc']);
    return $this->getCollection();
  }
  public function searchHouse($param){
    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['search'])) {
      $like = $this->likeQuery(['house_name', 'capacity'], $this->escape($param['search']), 'house', true);
      $this->_select->where($like);
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    return $this->getFinalResponse($limit);
  }
  public function getProductionlisting($param)
  {
    $mainTable = $this->getTableName();
    $dailyhouseharvest = $this->_dailyhouseharvest->getTableName();
    $this->__join('dailyhouseharvest_', $mainTable . '.id', 'daily_house_harvest', '', 'left', 'house_id', Dailyhouseharvest::COLUMNS);
    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    if (isset($param['order_by_column']) && isset($param['order_by'])) {
      $this->_select->order([$param['order_by_column'] => $param['order_by']]);
    }
    if (isset($param['from']) && isset($param['to'])) {
      $where = "(date_format(`" . $dailyhouseharvest . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $dailyhouseharvest . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
      $this->_select->where($where);
    }
    return $this->getFinalResponse($limit);
  }
  public function getLatestActivity() {
    $this->_select->order(['updated_at' => 'desc']);
    $this->_select->limit(1);
    $result = $this->getCollection();
    return $result;
  }
  public function getListing($param){
    $mainTable = $this->getTableName();
    $dailyhouseharvest = $this->_dailyhouseharvest->getTableName();
    $this->resetQuery();
    $date_today = date("Y-m-d h:i:s A");
    $this->__join('dailyhouseharvest_', $mainTable . '.id', 'daily_house_harvest', '', 'left', 'house_id', Dailyhouseharvest::COLUMNS);
    /* $where = "date_format(`" . $dailyhouseharvest . "`.`created_at`, '%Y-%m') = date_format(now(), '%Y-%m')"; */
    $where = "date_format(`" . $dailyhouseharvest . "`.`created_at`, '%Y-%m') = date_format('" . $date_today . "', '%Y-%m')";
    $this->_select->where($where);
    $this->_select->group('house_id');

    $limit = 1;
    if (isset($param['limit'])) {
      $limit = $param['limit'];
    }
    return $this->getFinalResponse($limit);
  }
}
?>