<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Dailysortinginventoryhistory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'daily_sorting_inventory_history';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'sorted_inv_id',
        'original_count',
        'updated_count'
    ];

    public function getSortinginventoryhistory($id){
       $data = $this->getByColumn(['sorted_inv_id'=>$id], 1);
       if($data){
           return $data->getData();
       }else{
           return null;
       }
    }
}

?>