<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Outflow extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'outflow';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type',
        'reference_id',
        'created_at'
    ];
 
    public function getOutflow($param){
        $mainTable = $this->getTableName();
        $limit = 1;
        $order = 'desc';
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['order'])) {
            $order = $param['order'];
        }
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => $order]);
        return $this->getFinalResponse($limit);
      }
}
?>
