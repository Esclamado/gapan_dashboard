<?php 
namespace Lns\Sb\Lib\Entity\Db;

class Permission extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
	
	protected $tablename = 'permission';
	protected $primaryKey = 'id';
	
	const COLUMNS = [
		'id',
		'role_id',
		'permissions_id',
		'permissions_code',
		'created_at',
		'update_at',
		'create',
		'read',
		'update',
		'delete',
		'view',
	];
}

 