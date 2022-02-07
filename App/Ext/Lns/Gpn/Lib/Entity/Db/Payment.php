<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Payment extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'payment';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_id',
        'payment',
        'balance',
        'reason',
        'due_date',
        'approved_by',
        'approved_path',
        'approved_date',
        'receipt_no',
        'created_at'
    ];
}
?>