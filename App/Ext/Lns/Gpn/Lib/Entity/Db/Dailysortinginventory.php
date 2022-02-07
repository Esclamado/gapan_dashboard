<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Dailysortinginventory extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
    protected $tablename = 'daily_sorting_inventory';
    protected $primaryKey = 'id';
    const COLUMNS = [
        'id',
        'sorted_report_id',
        'house_id',
        'type_id',
        'egg_count',
        'created_at'
    ];

    public function getDailySortingInventory($id){
        $data = $this->getbyColumn(['sorted_report_id'=>$id], 0);
        if($data){
            return $data;
        }else{
            return null;
        }
    }
    public function getEgglist($param)
    {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        if (isset($param['date'])) {
            $where = "date_format(created_at, '%Y-%m-%d') = date_format('" . $param['date'] . "', '%Y-%m-%d') ";
            $where .= "Group by type_id";
            $this->_select->where($where);
        }
        $datas = $this->getCollection();
        if ($datas) {
            return $datas;
        }
    }
    public function getEachCount($type_id,$date)
    {
        $this->resetQuery();
        $where = "date_format(created_at, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d') AND ";
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
    public function getPrevious($date, $type) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') < date_format('" . $date . "', '%Y-%m-%d') AND `" . $mainTable . "`.`type_id` = " . $type;
        $this->_select->where($where);
        $this->_select->order(['created_at' => 'desc']);
        $this->sum('egg_count');
        $this->_select->group('type_id');
        $this->_select->limit(1);
        $result = $this->getCollection();
        /* var_dump($this->getLastSqlQuery()); */
        if ($result) {
            return $result[0];
        } else {
            return null;
        }
    }
    public function getToday($date, $type) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d') AND `" . $mainTable . "`.`type_id` = " . $type;
        $this->_select->where($where);
        $this->_select->order(['created_at' => 'desc']);
        $this->sum('egg_count');
        $this->_select->group('type_id');
        $this->_select->limit(1);
        $result = $this->getCollection();
        /* var_dump($this->getLastSqlQuery()); */
        if ($result) {
            return $result[0];
        } else {
            return null;
        }
    }
    public function getByCreatedAt($date, $param = null) {
        $this->resetQuery();
        $mainTable = $this->getTableName();
        $this->sum('egg_count');
        $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') = date_format('" . $date . "', '%Y-%m-%d')";
        $this->_select->where($where);
        if (isset($param['house_id'])) {
            $this->_select->where("`" . $mainTable . "`.`house_id` = " . $param['house_id']);
        }
        $this->_select->group('type_id');
        return $this->getCollection();
    }
}

?>