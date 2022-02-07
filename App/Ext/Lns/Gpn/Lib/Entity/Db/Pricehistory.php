<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Pricehistory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'price_history';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type_id',
        'price',
        'created_at',
        'updated_at'
    ];

    public function getList($param){
        $mainTable = $this->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['type'])) {
            $where = "type_id=" . $param['type'];
            $this->_select->where($where);
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`updated_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`updated_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        return $this->getFinalResponse($limit);
    }
    public function getLatestRecord($type_id, $getPrevious = false) {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $this->_select->where("type_id = ".$this->escape($type_id));
        if ($getPrevious) {
          $this->_select->where("date_format(created_at, '%Y-%m-%d') < date_format(now(), '%Y-%m-%d')");
        }
        $this->_select->limit(1);
        $this->_select->order(['created_at' => 'desc']);
        $result = $this->getCollection();
        if ($result) {
          return $result[0];
        } else {
          return false;
        }
      }
}
?>
