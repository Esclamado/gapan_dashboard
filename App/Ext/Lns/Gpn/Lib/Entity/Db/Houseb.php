<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Houseb extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  protected $tablename = 'house';
  protected $primaryKey = 'id';  
  const COLUMNS = [
    'id',
    'chicken_pop_id',
    'house_name',
    'capacity',
    'created_at',
    'updated_at'
  ];
}
?>