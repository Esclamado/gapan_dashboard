<?php
namespace Lns\Gpn\Lib\Entity\Db;

class MedicineUnit extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'medicine_unit';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'unit',
        'created_at'
    ];
}
?>
