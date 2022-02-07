<?php 
namespace Lns\Gpn\Lib\Entity\Db;

class Roles extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'roles';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'name',
		'description'
	];

	public function installData() {
		$this->setDatas([
			'name' => 'Manager',
			'description' => 'Unlike "super admin", manager can be edited, changed or deleted.',
		])->__save();
		$this->setDatas([
			'name' => 'Sales Agent',
			'description' => 'For Sales Agent only. Can access dashboard but limited functionalities.',
		])->__save();
		$this->setDatas([
			'name' => 'Inspector 1',
			'description' => 'For Inspector 1 only. Can access dashboard but limited functionalities.',
		])->__save();
		$this->setDatas([
			'name' => 'Inspector 2',
			'description' => 'For Inspector 2 only. Can access dashboard but limited functionalities.',
		])->__save();
		$this->setDatas([
			'name' => 'Flockman',
			'description' => 'For Flockman only. Cannot access admin/super admin rights.',
		])->__save();
		$this->setDatas([
			'name' => 'Sorter',
			'description' => 'For Sorter only. Cannot access admin/super admin rights.',
		])->__save();
		$this->setDatas([
			'name' => 'Warehouse Man 1',
			'description' => 'For Warehouse Man 1 only. Cannot access admin/super admin rights.',
		])->__save();
		$this->setDatas([
			'name' => 'Warehouse Man 2',
			'description' => 'For Warehouse Man 2 only. Cannot access admin/super admin rights.',
		])->__save();
	}
}