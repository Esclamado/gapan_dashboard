<?php
namespace Lns\Gpn\Lib\Entity\Db;

class Trayinventoryreport extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity {
  
  protected $tablename = 'tray_inventory_report';
  protected $primaryKey = 'id';
    
  const COLUMNS = [
      'id',
      'type_id',
      'in_return',
      'sorting',
      'marketing',
      'out_hiram',
      'total_end',
      'tray_report_id',
      'created_at'
  ];
    public function getLastDataByType($id, $requiredLength) {
        $mainTable = $this->getTableName();
        $this->resetQuery();
        $this->_select->where('type_id = '.$this->escape($id));
        $this->_select->order(['id' => 'desc']);
        $this->_select->limit(2);
        $result = $this->getCollection();
        if ($result && count($result) == (int)$requiredLength) {
            return $result[(int)$requiredLength-1]->getData();
        } else {
            return null;
        }
    }
    public function getTrayInventoryReport($id)
    {
        $data = $this->getbyColumn(['tray_report_id' => $id], 0);
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }
    public function getLastEnding($id, $date=null){
        $mainTable = $this->getTableName();
        $this->resetQuery();
        if ($date) {
            $where = "date_format(`" . $mainTable . "`.`created_at`, '%Y-%m-%d') < date_format('" . $date . "', '%Y-%m-%d') AND type_id =" . $this->escape($id);
            $this->_select->where($where);
        }
        $this->_select->order(['created_at' => 'desc']);
        $this->_select->limit(1);
        /* var_dump($this->getLastSqlQuery()); */
        $result = $this->getCollection();
        if ($result) {
            return $result[0]->getData();
        } else {
            return null;
        }
    }
    public function getPlasticTray()
    {
        $this->resetQuery();
        $where = '';
        $where = "type_id = 2";
        $this->_select->where($where);
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (int) $data->getData('total_end');
            }
        }
        return $count;
    }
    public function getCartonTray()
    {
        $this->resetQuery();
        $where = '';
        $where = "type_id = 1";
        $this->_select->where($where);
        $datas = $this->getCollection();
        $count = 0;
        if ($datas) {
            foreach ($datas as $data) {
                $count += (int) $data->getData('total_end');
            }
        }
        return $count;
    }
}
?>