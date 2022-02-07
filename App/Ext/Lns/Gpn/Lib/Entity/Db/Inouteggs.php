<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Inouteggs extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

    protected $tablename = 'in_out_eggs';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'egg_in',
        'egg_out',
        'created_at',
        'updated_at'
    ];

    public function getList($param, $getcount = null) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        /* $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'date'); */
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d')"), 'grouped_date');
        
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`egg_in`)"), 'total_egg_in');
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`egg_out`)"), 'total_egg_out');
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
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        
        $this->_select->group('grouped_date');
        /* $this->sum('egg_in');
        $this->sum('egg_out'); */
        if ($getcount) {
            if ($this->getCollection()) {
                return count($this->getCollection());
            } else {
                return 0;
            }
            /* $this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
            $this->_select->removeColumn("`" . $mainTable . "`.*");
            var_dump($this->getLastSqlQuery());
            $count = $this->__getQuery($this->getLastSqlQuery());
            $totalCount = 0;
            if ($count) {
                $totalCount = $count->getData('count');
            }
            var_dump($totalCount);die;
            return $totalCount; */
        } else {
            if ($this->getCollection()) {
                return $this->getFinalResponse($limit);
            } else {
                return null;
            }
        }
    }
    public function validate($date) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
        $this->_select->where($where);
        $this->_select->limit(1);
        $result = $this->getCollection();
        if ($result) {
            return $result[0];
        } else {
            return false;
        }
    }
}
?>