<?php
namespace Lns\Gpn\Lib\Entity\Db;

class OrderDetails extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order_details';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_id',
        'type_id',
        'qty',
        'price'
    ];
}
