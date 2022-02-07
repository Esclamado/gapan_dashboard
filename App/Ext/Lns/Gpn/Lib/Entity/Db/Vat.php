<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Vat extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    
    protected $tablename = 'vat';
    protected $primaryKey = 'id';
    
    const COLUMNS = [
		'id',
		'vat',
		'created_at',
	];

	public function installData() {
		$this->setDatas([
			'vat' => 0.12,
		])->__save();
	}
}
?>