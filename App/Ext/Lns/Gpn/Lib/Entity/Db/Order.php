<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Order extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'user_id',
        'reference_no',
        'order_status',
        'payment_status',
        'mode_of_payment',
        'totalCost',
        'discount',
        'note',
        'created_at'
    ];
}
?>
