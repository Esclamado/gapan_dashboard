<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Roles extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'roles';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'name',
		'description'
	];

	public function getRoles(){
		return $this->getFinalResponse(20);
	}
}

 