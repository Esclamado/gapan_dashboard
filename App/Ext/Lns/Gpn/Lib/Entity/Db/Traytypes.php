<?php 
namespace Lns\Gpn\Lib\Entity\Db;

class Traytypes extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'tray_types';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'type',
		'created_at'
	];

	public function installData() {
		$this->setDatas([
			'type' => 'Carton Trays',
        ])->__save();
        $this->setDatas([
			'type' => 'Plastic Trays',
		])->__save();
	}
}