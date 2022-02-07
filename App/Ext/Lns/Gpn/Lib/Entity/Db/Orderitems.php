<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Orderitems extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order_items';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_id',
        'type_id'
    ];
}
?>
