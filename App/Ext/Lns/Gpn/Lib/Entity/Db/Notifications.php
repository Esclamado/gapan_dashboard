<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Notifications extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'notifications';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'item_id',
        'owner_user_id',
        'type',
        'url',
        'data',
        'read_by_user_id',
        'isRead',
        'read_at',
        'created_at',
        'updated_at'
    ];

    public function getNotification($param, $user_id){
        $limit = 1;
        // $this->setOrderBy('created_at', 'desc');
        $this->resetQuery();
        
        if(isset($param['limit'])){
            $limit = $param['limit'];
        }
        if(isset($user_id)){
            if (isset($param['isUnread'])) {
                // $this->getByColumn(['read_by_user_id'=>$user_id, 'isRead' => 0], 0);
                $this->_select->where("read_by_user_id = ".$user_id." AND isRead = 0");
            } else {
                // $this->getByColumn(['read_by_user_id'=>$user_id], 0);
                $this->_select->where("read_by_user_id = ".$user_id);
            }
        }else{
            $this->getCollection();
        }
        $this->_select->order(['created_at' => 'desc']);
        return $this->getFinalResponse($limit);
    }
}
?>