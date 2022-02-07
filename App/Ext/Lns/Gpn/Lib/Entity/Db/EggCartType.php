<?php
namespace Lns\Gpn\Lib\Entity\Db;

class EggCartType extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'egg_cart_type';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
      'id',
      'type',
      'created_at'
  ];
    public function installData()
    {
        $this->setDatas([
            'type' => 'Case',
        ])->__save();
        $this->setDatas([
            'type' => 'Tray',
        ])->__save();
        $this->setDatas([
            'type' => 'Pieces',
        ])->__save();
    }
    public function getEggCartType($id)
    {
        $data = $this->getByColumn(['id' => $id], 1);
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }
}
?>