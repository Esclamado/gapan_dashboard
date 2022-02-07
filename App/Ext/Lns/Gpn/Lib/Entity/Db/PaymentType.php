<?php
namespace Lns\Gpn\Lib\Entity\Db;

class PaymentType extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'payment_type';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'type',
        'created_at'
    ];
      public function installData() {
        $this->setDatas([
            'type' => 'Full Payment',
        ])->__save();
        $this->setDatas([
            'type' => 'With Credit',
        ])->__save();
        $this->setDatas([
            'type' => 'With Balance',
        ])->__save();
      }

    public function getEggPrice($id){
        $data = $this->getByColumn(['type_id'=>$id], 1);
        if($data){
            return $data;
        }else{
            return null;
        }
    }
}
?>