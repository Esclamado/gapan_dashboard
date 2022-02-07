<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Orderitemdetails extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order_item_details';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_item_id',
        'type_id',
        'qty',
        'price'
    ];
}
?>
