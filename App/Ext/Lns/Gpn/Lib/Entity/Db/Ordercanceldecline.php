<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Ordercanceldecline extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'order_cancel_decline';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'order_id',
        'type',
        'decline_cause',
        'message',
        'created_at'
    ];
    public function getByOrderId($orderId, $type) {
        $entity = $this->getByColumn(['order_id' => $orderId, 'type' => $type], 1);
        if ($entity) {
            return $entity->getData();
        } else {
            return null;
        }
    }
}
?>