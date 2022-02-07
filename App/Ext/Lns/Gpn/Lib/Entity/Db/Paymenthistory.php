<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Paymenthistory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'payment_history';
    protected $primaryKey = 'id';
    
    const COLUMNS = [
        'id',
        'order_id',
        'payment_attachments_id',
        'payment',
        'receipt_no',
        'created_by',
        'created_at'
    ];

    public function getLastPayment($order_id) {
        $this->resetQuery();

        $this->_select->where('order_id = ' . $order_id);
        $this->_select->order(['created_at' => 'desc']);
		$this->_select->limit(1);
        $result = $this->getCollection();
        
        if ($result) {
            return $result[0];
        } else {
            return null;
        }
    }
    public function getFullypaidorders($param) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`" . $mainTable . "`.`payment`)"), 'sum_payment');
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $this->_select->removeColumn("`" . $mainTable . "`.*");
        $result = $this->__getQuery($this->getLastSqlQuery());
        $sum_payment = 0;
        if ($result) {
            $sum_payment = $result->getData('sum_payment');
        }
        return $sum_payment;
    }
    public function getFullypaid($param)
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        if (isset($param['from']) && isset($param['to'])) {
            $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $param['from'] . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $param['to'] . "', '%Y-%m-%d'))";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (float) $data->getData('payment');
            }
        }
        return $count;
    }
    public function getSalesyesterday()
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $where = "DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY)";
        $this->_select->where($where);
        $this->_select->group('created_at');
        $results = $this->getCollection();
        if ($results) {
            $count = 0;
            foreach ($results as $result) {
                $count += (float) $result->getData('payment');
            }
            return $count;
        }
    }
    public function getSalestoday()
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
        $this->_select->where($where);
        $results = $this->getCollection();
        if ($results) {
            $count = 0;
            foreach ($results as $result) {
                $count += (float) $result->getData('payment');
            }
            return $count;
        }
    }
}
?>