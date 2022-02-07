<?php
namespace Lns\Gpn\Lib\Entity\Db;

class EggInventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'egg_inventory';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
      'id',
      'type_id',
      'house_id',
      'egg_count',
      'created_at'
  ];

  public function getEggs(){
    return $this->getCollection();
  }

  public function getCount($type_id){
    $this->resetQuery();
    $where = '';
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d') AND ";
    $where .= "type_id=" . $type_id;
    $this->_select->where($where);
    $datas = $this->getCollection();
    $count = 0;
    if($datas){
      foreach ($datas as $data) {
        $count += (int)$data->getData('egg_count');
      }
    }
    return $count;
  }
    public function getTotalCount()
  {
    $this->resetQuery();
    $datas = $this->getCollection();
    $count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $count += (int) $data->getData('egg_count');
      }
    }
    return $count;
  }
  public function getEachCount($type_id)
  {
    $this->resetQuery();
    $where = '';
    $where .= "type_id=" . $type_id;
    $this->_select->where($where);
    $datas = $this->getCollection();
    $count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $count += (int) $data->getData('egg_count');
      }
    }
    return $count;
  }
  public function getEggharvested($type)
  {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $from = date('Y') . '-01-01';
    $to = date('Y-m-d');
    $where = '';
    $where = "date_format(created_at, '%Y-%m-%d') = date_format(now(), '%Y-%m-%d')";
    if ($type == 1) {
      $where = "(date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') >= date_format('" . $from . "', '%Y-%m-%d') AND date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') <= date_format('" . $to . "', '%Y-%m-%d'))";
    }
    $this->_select->where($where);
    $datas = $this->getCollection();
    $count = 0;
    if ($datas) {
      foreach ($datas as $data) {
        $count += (int) $data->getData('egg_count');
      }
    }
    return $count;
  }
  public function getEggstocks($eggtypeId){


    $this->resetQuery();
    $mainTable = $this->getTableName();
    $this->_select->setComlumn(new \Zend\Db\Sql\Expression("SUM(`".$mainTable."`.`egg_count` )"), 'total_of_egg_type');
    $this->_select->where("`".$mainTable."`.`type_id` =  $eggtypeId");
    $datas = $this->getCollection();
     if($datas){
        return $datas[0]->getData('total_of_egg_type');
        
      }else{
         return 0; 
      }

      
    // $this->resetQuery();
    // $mainTable = $this->getTableName();
    // $where = "type_id = " . $eggtypeId;
    // $this->_select->where($where);
    // $datas = $this->getCollection();
    // $count = 0;
    // if($datas){
    //   foreach ($datas as $data) {
    //     $count += (int) $data->getData('egg_count');
    //   }
    // }
    // return $count;
  }
  public function markAsComplete($type_id) {
    $mainTable = $this->getTableName();
    $this->resetQuery();
    $this->_select->where("`".$mainTable."`.`type_id` = " . $type_id);
    $this->_select->where("`".$mainTable."`.`egg_count` > 0");
    $this->_select->order(['created_at' => 'asc']);
    $datas = $this->getCollection();
    if ($datas) {
        return $datas;
    } else {
        return [];
    }
  }
}
?>