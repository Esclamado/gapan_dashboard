<?php
namespace Lns\Gpn\Lib\Entity\Db;

class CartDetails extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {

  protected $tablename = 'cart_details';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
        'id',
        'cart_id',
        'type_id',
        'qty',
        'created_at'
  ];
}
