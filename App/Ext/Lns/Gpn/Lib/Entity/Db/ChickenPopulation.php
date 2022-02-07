<?php
namespace Lns\Gpn\Lib\Entity\Db;

class ChickenPopulation extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'chicken_population';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
		'id',
		'begin_population',
    'begin_date',
    'end_population',
    'end_date'
	];
}
?>