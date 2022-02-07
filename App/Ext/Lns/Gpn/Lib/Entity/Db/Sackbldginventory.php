<?php 
namespace Lns\Gpn\Lib\Entity\Db;

class Sackbldginventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'sack_bldg_inventory';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'sack_inv_id',
        'house_id',
        'count',
        'created_at'
	];

	public function getLastEnding($house_id, $date = null) {
		$mainTable = $this->getTableName();
		$this->resetQuery();

		/* if ($date) {
			$date = strtotime(date("Y-m-d", strtotime($date)) . " -1 day");
		} else {
			$date = strtotime(date("Y-m-d") . " -1 day");
		} */
		/* $this->_select->where("date_format(`".$mainTable."`.`created_at`, '%Y-%m-%d') = date_format(".$date.", '%Y-%m-%d') AND `".$mainTable."`.`house_id` = ".$this->escape($house_id)); */
		if ($date) {
			$this->_select->where("date_format(`".$mainTable."`.`created_at`, '%Y-%m-%d') = date_format(DATE_SUB('".$date."', INTERVAL 1 DAY), '%Y-%m-%d') AND `".$mainTable."`.`house_id` = ".$this->escape($house_id));
		} else {
			$this->_select->where("date_format(`".$mainTable."`.`created_at`, '%Y-%m-%d') = date_format(DATE_SUB(now(), INTERVAL 1 DAY), '%Y-%m-%d') AND `".$mainTable."`.`house_id` = ".$this->escape($house_id));
		}
		$this->_select->limit(1);
		$this->_select->order(['id' => 'desc']);
		$result = $this->getCollection();
		if ($result) {
			return $result[0];
		} else {
			return null;
		}
	}
	public function getTotalBySackInvId($sack_inv_id) {
		$mainTable = $this->getTableName();
		$total = 0;
		$this->resetQuery();
		$results = $this->getByColumn(['sack_inv_id' => $sack_inv_id], 0);
		if ($results) {
			foreach($results as $result) {
				$total += (int)$result->getData('count');
			}
		}
		return $total;
	}
	public function getSackInventory($id)
	{
		$data = $this->getbyColumn(['sack_inv_id' => $id], 0);
		if ($data) {
			return $data;
		} else {
			return null;
		}
	}
}