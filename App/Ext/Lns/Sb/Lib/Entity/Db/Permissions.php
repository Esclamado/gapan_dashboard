<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Permissions extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'permissions';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'name',
		'description',
		'code',
		'created_at',
		'update_at'
	];
}

 