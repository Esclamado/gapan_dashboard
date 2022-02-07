<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Medicine extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'medicine';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'medicine',
        'unit_id',
        'net_weight',
        'pieces',
        'delivery_date',
        'expiration_date',
        'unit_price',
        'remarks',
        'created_at',
        'updated_at'
    ];
    public function installData() {
        $this->setDatas([
			'medicine' => 'Optipro',
        ])->__save();
        $this->setDatas([
			'medicine' => 'Vitavet',
        ])->__save();
        $this->setDatas([
			'medicine' => 'Plain water',
        ])->__save();
        $this->setDatas([
			'medicine' => 'Multilyt',
        ])->__save();
        $this->setDatas([
			'medicine' => 'GLCD',
        ])->__save();
        $this->setDatas([
			'medicine' => 'Dis',
        ])->__save();
    }
    public function getMedicine($id) {
        return $this->getByColumn(['id' => $id], 1)->getData();
    }
    public function getMedicines(){
        return $this->getCollection();
      }
    public function getMedicinelist($param)
    {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $limit = 1;
        if (isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if (isset($param['medicine'])) {
            $where = "medicine='" . $param['medicine'] . "'";
            $this->_select->where($where);
        }
        if (isset($param['order_by_column']) && isset($param['order_by'])) {
            $this->_select->order([$param['order_by_column'] => $param['order_by']]);
        }
        if (isset($param['delivery_date'])) {
            $where = "date_format(delivery_date, '%Y-%m-%d') = date_format('" . $param['delivery_date'] . "', '%Y-%m-%d')";
            $this->_select->where($where);
        }
        if (isset($param['expiration_date'])) {
            $where = "date_format(expiration_date, '%Y-%m-%d') = date_format('" . $param['expiration_date'] . "', '%Y-%m-%d')";
            $this->_select->where($where);
        }
        if (isset($param['search'])) {
            $where = $this->likeQuery(['medicine', 'unit_id', 'net_weight', 'pieces', 'delivery_date', 'expiration_date', 'price', 'created_at'], $this->escape($param['search']), 'medicine', true);
            $this->_select->where($where);
        }
        return $this->getFinalResponse($limit);
    }
    public function getCount()
    {
        $this->resetQuery();
        $result = $this->getCollection();
        $getCount = count($result);
        return $getCount;
    }
    public function getLatestActivity() {
		$this->_select->order(['updated_at' => 'desc']);
		$this->_select->limit(1);
		$result = $this->getCollection();
		return $result;
	}
	public function getLatestMedicines() {
        $this->resetQuery();
        $this->_select->order(['medicine' => 'asc']);
        $this->_select->limit(5);
        $result = $this->getCollection();
        return $result;
  }
}
?>