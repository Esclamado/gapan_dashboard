<?php
namespace Lns\Gpn\Lib\Entity\Db;

class CustomerType extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'customer_type';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type',
        'created_at'
    ];
    public function installData() {
        $this->setDatas([
            'type' => 'Wholesaler',
        ])->__save();
        $this->setDatas([
            'type' => 'Retailer',
        ])->__save();
        $this->setDatas([
            'type' => 'Consumer',
        ])->__save();
    }
}
?>